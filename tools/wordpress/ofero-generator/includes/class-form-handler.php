<?php
/**
 * Form Handler Class
 *
 * Handles form submissions and AJAX requests.
 *
 * @package Ofero_Generator
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Every $_POST read in this file happens downstream of handle_form_submission(), which verifies
// the ofero_generator_save nonce and the manage_options capability before dispatching, or of an
// AJAX handler that calls check_ajax_referer(). PHPCS cannot follow the nonce check across those
// call boundaries, so it reports each read individually.
// phpcs:disable WordPress.Security.NonceVerification.Missing

class Ofero_Form_Handler {

    /**
     * Parts of ofero.json the editor writes: true = the form owns the whole value, an array =
     * the form owns only those sub-keys. Everything else in the saved file is kept on save.
     * The v1 inline catalog keys are listed so they are still removed (the form never sets them).
     */
    const MANAGED_FIELDS = array(
        'language' => true,
        'domain' => true,
        'canonicalUrl' => true,
        'metadata' => array('version' => true, 'schemaVersion' => true, 'lastUpdated' => true, 'createdAt' => true),
        'organization' => array(
            'legalName' => true, 'brandName' => true, 'entityType' => true, 'legalForm' => true,
            'industry' => true, 'description' => true, 'website' => true, 'contactEmail' => true,
            'contactPhone' => true,
            'identifiers' => array(
                'primaryIncorporation' => array('country' => true, 'registrationNumber' => true, 'taxId' => true, 'vatNumber' => true)
            )
        ),
        'businessClassification' => array(
            'industry' => true, 'primaryProducts' => true, 'targetMarket' => true, 'serviceArea' => true, 'operationalStatus' => true
        ),
        'locations' => true,
        'banking' => true,
        'wallets' => true,
        'branding' => true,
        'catalog' => array(
            'defaultCurrency' => true, 'priceListUrl' => true, 'wholesale' => true, 'feeds' => true,
            'signature' => true, 'highlights' => true,
            'menu' => true, 'dailyMenu' => true, 'services' => true, 'packages' => true,
            'portfolio' => true, 'productFeeds' => true, 'serviceFeeds' => true
        ),
        'communications' => array('social' => true, 'support' => true),
        'keywords' => true,
        '_translations' => true
    );

    /**
     * Location fields the Locations tab edits; id, coordinates, amenities, paymentMethods, … are kept
     */
    const LOCATION_FIELDS = array(
        'name' => true, 'type' => true, 'facility' => true, 'publicAccess' => true, 'phone' => true,
        'email' => true, 'photos' => true, 'businessHours' => true, 'specialHours' => true, 'contacts' => true,
        'address' => array('street' => true, 'city' => true, 'region' => true, 'postalCode' => true, 'country' => true)
    );

    /**
     * Contact fields the Locations tab edits; languages and other fields are kept
     */
    const CONTACT_FIELDS = array(
        'name' => true, 'role' => true, 'email' => true, 'phone' => true, 'public' => true, 'photo' => true
    );

    /**
     * Lay the form's values over the saved data. Managed keys come from the form (and are removed
     * when the form leaves them out); other keys keep their saved value and position.
     *
     * @param mixed $existing Saved value
     * @param array $form     Value built from the form
     * @param array $managed  One of the *_FIELDS maps
     * @return array
     */
    public static function keep_unmanaged($existing, $form, $managed) {
        if (!is_array($existing) || (!empty($existing) && array_keys($existing) === range(0, count($existing) - 1))) {
            return $form;
        }
        $form = is_array($form) ? $form : array();
        $result = $existing;
        foreach ($managed as $key => $sub) {
            if ($sub !== true) {
                $result[$key] = self::keep_unmanaged($existing[$key] ?? array(), $form[$key] ?? array(), $sub);
            } elseif (array_key_exists($key, $form)) {
                $result[$key] = $form[$key];
            } else {
                unset($result[$key]);
            }
        }
        // Keys the form fills but does not own (e.g. an empty identifiers.global) only fill gaps
        foreach ($form as $key => $value) {
            if (!array_key_exists($key, $managed) && !array_key_exists($key, $result)) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Saved location/contact posted back by the editor as JSON (a hidden field), sanitized
     *
     * @param string $json
     * @return array
     */
    private function decode_original($json) {
        $decoded = json_decode((string) $json, true);
        return is_array($decoded) ? self::sanitize_original($decoded) : array();
    }

    private static function sanitize_original($value) {
        if (is_array($value)) {
            $clean = array();
            foreach ($value as $key => $item) {
                $key = is_int($key) ? $key : preg_replace('/[^A-Za-z0-9_\-]/', '', (string) $key);
                if ($key !== '') {
                    $clean[$key] = self::sanitize_original($item);
                }
            }
            return $clean;
        }
        if (is_string($value)) {
            return sanitize_text_field($value);
        }
        return (is_bool($value) || is_int($value) || is_float($value) || $value === null) ? $value : '';
    }

    /**
     * File manager instance
     */
    private $file_manager;

    /**
     * Validator instance
     */
    private $validator;

    /**
     * Constructor
     */
    public function __construct($file_manager, $validator) {
        $this->file_manager = $file_manager;
        $this->validator = $validator;
    }

    /**
     * Handle form submission
     */
    public function handle_form_submission() {
        if (!isset($_POST['ofero_generator_action'])) {
            return;
        }

        // Verify nonce
        $nonce = isset($_POST['ofero_generator_nonce'])
            ? sanitize_text_field(wp_unslash($_POST['ofero_generator_nonce']))
            : '';
        if (!wp_verify_nonce($nonce, 'ofero_generator_save')) {
            wp_die(esc_html__('Security check failed.', 'ofero-generator'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'ofero-generator'));
        }

        $action = sanitize_text_field(wp_unslash($_POST['ofero_generator_action']));

        switch ($action) {
            case 'save':
                $this->handle_save();
                break;

            case 'restore_backup':
                $this->handle_restore_backup();
                break;

            case 'delete_backup':
                $this->handle_delete_backup();
                break;

            case 'import':
                $this->handle_import();
                break;

            case 'settings':
                $this->handle_settings();
                break;

            case 'emergency_reset':
                $this->handle_emergency_reset();
                break;
        }
    }

    /**
     * Handle save action
     */
    private function handle_save() {
        // Save business type (UI preference, stored in wp_options, not in ofero.json)
        $allowed_types = array('general', 'restaurant', 'hotel', 'hotel_restaurant', 'online_store', 'clinic', 'auto_service', 'services');
        $business_type = isset($_POST['ofero_business_type'])
            ? sanitize_text_field(wp_unslash($_POST['ofero_business_type']))
            : '';
        if (in_array($business_type, $allowed_types, true)) {
            update_option('ofero_generator_business_type', $business_type);
        }

        // If this was only a business type change, redirect without saving ofero.json
        if (!empty($_POST['ofero_change_business_type'])) {
            wp_safe_redirect(admin_url('admin.php?page=ofero-generator'));
            exit;
        }

        $data = $this->collect_form_data();
        $existing = $this->file_manager->load();
        if (is_array($existing)) {
            // Keep what the editor has no fields for (partnerships, security, identifiers.global, …)
            $data = self::keep_unmanaged($existing, $data, self::MANAGED_FIELDS);
        }
        $result = $this->file_manager->save($data);

        if (is_wp_error($result)) {
            $this->add_admin_notice('error', $result->get_error_message());
        } else {
            $this->add_admin_notice('success', __('ofero.json saved successfully.', 'ofero-generator'));
        }

        wp_safe_redirect(admin_url('admin.php?page=ofero-generator&saved=1'));
        exit;
    }

    /**
     * Handle restore backup
     */
    private function handle_restore_backup() {
        $backup_file = sanitize_file_name(wp_unslash($_POST['backup_file'] ?? ''));

        if (empty($backup_file)) {
            $this->add_admin_notice('error', __('No backup file specified.', 'ofero-generator'));
            wp_safe_redirect(admin_url('admin.php?page=ofero-generator-settings'));
            exit;
        }

        $result = $this->file_manager->restore_backup($backup_file);

        if (is_wp_error($result)) {
            $this->add_admin_notice('error', $result->get_error_message());
        } else {
            $this->add_admin_notice('success', __('Backup restored successfully.', 'ofero-generator'));
        }

        wp_safe_redirect(admin_url('admin.php?page=ofero-generator-settings'));
        exit;
    }

    /**
     * Handle delete backup
     */
    private function handle_delete_backup() {
        $backup_file = sanitize_file_name(wp_unslash($_POST['backup_file'] ?? ''));

        if (empty($backup_file)) {
            $this->add_admin_notice('error', __('No backup file specified.', 'ofero-generator'));
            wp_safe_redirect(admin_url('admin.php?page=ofero-generator-settings'));
            exit;
        }

        $result = $this->file_manager->delete_backup($backup_file);

        if (is_wp_error($result)) {
            $this->add_admin_notice('error', $result->get_error_message());
        } else {
            $this->add_admin_notice('success', __('Backup deleted.', 'ofero-generator'));
        }

        wp_safe_redirect(admin_url('admin.php?page=ofero-generator-settings'));
        exit;
    }

    /**
     * Handle import
     */
    private function handle_import() {
        $import_url = esc_url_raw(wp_unslash($_POST['import_url'] ?? ''));

        if (empty($import_url)) {
            // Check for file upload
            if (!empty($_FILES['import_file']['tmp_name'])) {
                $tmp_name = sanitize_text_field(wp_unslash($_FILES['import_file']['tmp_name']));
                $content = '';
                if (is_uploaded_file($tmp_name)) {
                    global $wp_filesystem;
                    if (!$wp_filesystem) {
                        require_once ABSPATH . 'wp-admin/includes/file.php';
                        WP_Filesystem();
                    }
                    $content = $wp_filesystem->get_contents($tmp_name);
                }
                $data = json_decode((string) $content, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->add_admin_notice('error', __('Uploaded file contains invalid JSON.', 'ofero-generator'));
                    wp_safe_redirect(admin_url('admin.php?page=ofero-generator-settings'));
                    exit;
                }
            } else {
                $this->add_admin_notice('error', __('Please provide a URL or file to import.', 'ofero-generator'));
                wp_safe_redirect(admin_url('admin.php?page=ofero-generator-settings'));
                exit;
            }
        } else {
            $data = $this->file_manager->import_from_url($import_url);

            if (is_wp_error($data)) {
                $this->add_admin_notice('error', $data->get_error_message());
                wp_safe_redirect(admin_url('admin.php?page=ofero-generator-settings'));
                exit;
            }
        }

        // Validate imported data
        $validation = $this->validator->validate($data, 'basic');
        if (!$validation['valid']) {
            $error_messages = array_column($validation['errors'], 'message');
            $this->add_admin_notice('warning', __('Imported data has validation issues: ', 'ofero-generator') . implode(', ', $error_messages));
        }

        // Save imported data
        $result = $this->file_manager->save($data);

        if (is_wp_error($result)) {
            $this->add_admin_notice('error', $result->get_error_message());
        } else {
            $this->add_admin_notice('success', __('Data imported successfully.', 'ofero-generator'));
        }

        wp_safe_redirect(admin_url('admin.php?page=ofero-generator'));
        exit;
    }

    /**
     * Handle settings save
     */
    private function handle_settings() {
        update_option('ofero_generator_output_path', sanitize_text_field(wp_unslash($_POST['output_path'] ?? '.well-known/ofero.json')));
        update_option('ofero_generator_backup_enabled', isset($_POST['backup_enabled']));
        update_option('ofero_generator_auto_save', isset($_POST['auto_save']));

        $this->add_admin_notice('success', __('Settings saved.', 'ofero-generator'));

        wp_safe_redirect(admin_url('admin.php?page=ofero-generator-settings&saved=1'));
        exit;
    }

    /**
     * Handle emergency reset
     */
    private function handle_emergency_reset() {
        // Delete all plugin options
        delete_option('ofero_generator_translations_config');
        delete_option('ofero_generator_output_path');
        delete_option('ofero_generator_backup_enabled');
        delete_option('ofero_generator_auto_save');

        // Delete all transients
        delete_transient('ofero_generator_draft');
        delete_transient('ofero_generator_notice');

        // Note: We do NOT delete the ofero.json file or backups
        // This is intentional to preserve user data

        $this->add_admin_notice('success', __('Emergency reset completed successfully. All plugin settings have been reset to defaults.', 'ofero-generator'));

        wp_safe_redirect(admin_url('admin.php?page=ofero-generator-settings&reset=1'));
        exit;
    }

    /**
     * Collect form data from POST
     */
    private function collect_form_data() {
        $primary_language = sanitize_text_field(wp_unslash($_POST['language'] ?? 'en'));
        $enabled_languages = $this->collect_enabled_languages();

        // Collect translations for translatable fields
        $brand_name_translations = $this->collect_field_translations('organization_brandName', $enabled_languages);
        $description_translations = $this->collect_field_translations('organization_description', $enabled_languages);
        $keywords_translations = $this->collect_field_translations('keywords', $enabled_languages);

        $classification = $this->collect_business_classification();

        $data = array(
            'language' => $primary_language,
            'domain' => sanitize_text_field(wp_unslash($_POST['domain'] ?? '')),
            'canonicalUrl' => esc_url_raw(wp_unslash($_POST['canonicalUrl'] ?? '')),
            'metadata' => array(
                'version' => sanitize_text_field(wp_unslash($_POST['metadata_version'] ?? '2.0.0')),
                'schemaVersion' => defined('OFERO_GENERATOR_SCHEMA_VERSION') ? OFERO_GENERATOR_SCHEMA_VERSION : 'ofero-metadata-3.0',
                'lastUpdated' => current_time('c'),
                'createdAt' => sanitize_text_field(wp_unslash($_POST['metadata_createdAt'] ?? current_time('c')))
            ),
            'organization' => array(
                'legalName' => sanitize_text_field(wp_unslash($_POST['org_legalName'] ?? '')),
                'brandName' => $this->build_translatable_string(
                    sanitize_text_field(wp_unslash($_POST['org_brandName'] ?? '')),
                    $brand_name_translations
                ),
                'entityType' => sanitize_text_field(wp_unslash($_POST['org_entityType'] ?? 'company')),
                'legalForm' => sanitize_text_field(wp_unslash($_POST['org_legalForm'] ?? '')),
                // Schema requires organization.industry for companies; use the most general taxonomy level.
                'industry' => $classification['industry'][0] ?? '',
                'description' => $this->build_translatable_string(
                    sanitize_textarea_field(wp_unslash($_POST['org_description'] ?? '')),
                    $description_translations
                ),
                'website' => esc_url_raw(wp_unslash($_POST['org_website'] ?? '')),
                'contactEmail' => sanitize_email(wp_unslash($_POST['org_contactEmail'] ?? '')),
                'contactPhone' => sanitize_text_field(wp_unslash($_POST['org_contactPhone'] ?? '')),
                'identifiers' => array(
                    'global' => array(),
                    'primaryIncorporation' => array(
                        'country' => strtoupper(sanitize_text_field(wp_unslash($_POST['inc_country'] ?? ''))),
                        'registrationNumber' => sanitize_text_field(wp_unslash($_POST['inc_registrationNumber'] ?? '')),
                        'taxId' => sanitize_text_field(wp_unslash($_POST['inc_taxId'] ?? '')),
                        'vatNumber' => sanitize_text_field(wp_unslash($_POST['inc_vatNumber'] ?? ''))
                    ),
                    'perCountry' => array()
                )
            ),
            'businessClassification' => $classification,
            'locations' => $this->collect_locations(),
            'banking' => $this->collect_banking(),
            'wallets' => $this->collect_wallets(),
            'branding' => $this->collect_branding(),
            'catalog' => $this->collect_catalog(),
            'communications' => array(
                'social' => $this->collect_social(),
                'support' => $this->collect_support()
            )
        );

        // Add keywords if provided
        $keywords_default = sanitize_text_field(wp_unslash($_POST['keywords'] ?? ''));
        if (!empty($keywords_default) || !empty($keywords_translations)) {
            $data['keywords'] = $this->build_translatable_string($keywords_default, $keywords_translations);
        }

        // Store enabled languages for internal use (will be stripped on output)
        $data['_translations'] = array(
            'enabled_languages' => $enabled_languages
        );

        return $data;
    }

    /**
     * Collect enabled translation languages from POST
     */
    private function collect_enabled_languages() {
        if (!isset($_POST['translation_languages']) || !is_array($_POST['translation_languages'])) {
            return array();
        }

        $primary_language = sanitize_text_field(wp_unslash($_POST['language'] ?? 'en'));
        $enabled_languages = array_map('sanitize_text_field', wp_unslash((array) $_POST['translation_languages']));

        // Remove primary language from translation languages (if accidentally included)
        $enabled_languages = array_filter($enabled_languages, function($lang) use ($primary_language) {
            return $lang !== $primary_language;
        });

        return array_values($enabled_languages);
    }

    /**
     * Collect translations for a specific field
     */
    private function collect_field_translations($field_key, $enabled_languages) {
        $translations = array();

        foreach ($enabled_languages as $lang) {
            $post_key = 'translation_' . $field_key . '_' . $lang;
            if (isset($_POST[$post_key]) && !empty($_POST[$post_key])) {
                $translations[$lang] = sanitize_textarea_field(wp_unslash($_POST[$post_key]));
            }
        }

        return $translations;
    }

    /**
     * Build a TranslatableString structure
     * Always an object: the v2 schema rejects plain strings for translatable fields.
     */
    private function build_translatable_string($default_value, $translations) {
        $value = array('default' => $default_value);
        if (!empty($translations)) {
            $value['translations'] = $translations;
        }
        return $value;
    }

    /**
     * Collect businessClassification (including serviceArea) from POST
     */
    private function collect_business_classification() {
        $split = function ($raw) {
            $items = array_map('trim', explode(',', $raw));
            return array_values(array_unique(array_filter($items, 'strlen')));
        };

        $industry = array_values(array_filter(array_map(
            'sanitize_title',
            $split(sanitize_text_field(wp_unslash($_POST['bc_industry'] ?? '')))
        )));
        $products = $split(sanitize_text_field(wp_unslash($_POST['bc_primaryProducts'] ?? '')));

        $markets = isset($_POST['bc_targetMarket']) ? array_map('sanitize_text_field', wp_unslash((array) $_POST['bc_targetMarket'])) : array();
        $markets = array_values(array_intersect(array('B2C', 'B2B', 'B2G', 'C2C'), $markets));

        $status = sanitize_text_field(wp_unslash($_POST['bc_operationalStatus'] ?? ''));
        if (!in_array($status, array('active', 'suspended', 'closed', 'pending'), true)) {
            $status = '';
        }

        return array(
            'industry' => $industry,
            'primaryProducts' => $products,
            'targetMarket' => $markets,
            'serviceArea' => $this->collect_service_area($split),
            'operationalStatus' => $status
        );
    }

    /**
     * Collect businessClassification.serviceArea from POST
     */
    private function collect_service_area($split) {
        $modes = isset($_POST['sa_modes']) ? array_map('sanitize_text_field', wp_unslash((array) $_POST['sa_modes'])) : array();
        $modes = array_values(array_intersect(array('on-premises', 'at-customer', 'remote'), $modes));

        $countries = array_map('strtoupper', $split(sanitize_text_field(wp_unslash($_POST['sa_countries'] ?? ''))));
        $countries = array_values(array_unique(array_filter($countries, function ($code) {
            return (bool) preg_match('/^[A-Z]{2}$/', $code);
        })));

        // One region per line: "US-CA" (ISO 3166-2) or "US, Austin" (country, city/area)
        $regions = array();
        $lines = preg_split('/\r\n|\r|\n/', sanitize_textarea_field(wp_unslash($_POST['sa_regions'] ?? '')));
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^([A-Za-z]{2})-([A-Za-z0-9]{1,3})$/', $line, $m)) {
                $country = strtoupper($m[1]);
                $regions[] = array('country' => $country, 'subdivision' => $country . '-' . strtoupper($m[2]));
            } elseif (preg_match('/^([A-Za-z]{2})\s*,\s*(.+)$/', $line, $m)) {
                $regions[] = array('country' => strtoupper($m[1]), 'name' => trim($m[2]));
            }
        }
        foreach ($regions as $region) {
            if (!in_array($region['country'], $countries, true)) {
                $countries[] = $region['country'];
            }
        }

        $area = array(
            'modes' => $modes,
            'countries' => $countries,
            'regions' => $regions
        );
        if (!empty($_POST['sa_worldwide'])) {
            $area = array('modes' => $modes, 'worldwide' => true) + $area;
        }

        return $area;
    }

    /**
     * Collect locations from POST
     */
    private function collect_locations() {
        $locations = array();

        if (!isset($_POST['location_name']) || !is_array($_POST['location_name'])) {
            return $locations;
        }

        $count = count($_POST['location_name']);

        for ($i = 0; $i < $count; $i++) {
            if (empty($_POST['location_name'][$i])) {
                continue;
            }

            $type = sanitize_text_field(wp_unslash($_POST['location_type'][$i] ?? 'headquarters'));
            if (!in_array($type, array('headquarters', 'branch', 'international-branch', 'representative-office'), true)) {
                $type = 'branch';
            }
            $facility = sanitize_text_field(wp_unslash($_POST['location_facility'][$i] ?? ''));
            if (!in_array($facility, array('office', 'store', 'venue', 'workshop', 'warehouse', 'factory', 'distribution-center'), true)) {
                $facility = '';
            }
            $public_access = sanitize_text_field(wp_unslash($_POST['location_public_access'][$i] ?? ''));
            if (!in_array($public_access, array('walk-in', 'by-appointment', 'none'), true)) {
                $public_access = '';
            }

            $location = array(
                'name' => sanitize_text_field(wp_unslash($_POST['location_name'][$i] ?? '')),
                'type' => $type,
                'facility' => $facility,
                'publicAccess' => $public_access,
                'address' => array(
                    'street' => sanitize_text_field(wp_unslash($_POST['location_street'][$i] ?? '')),
                    'city' => sanitize_text_field(wp_unslash($_POST['location_city'][$i] ?? '')),
                    'region' => sanitize_text_field(wp_unslash($_POST['location_region'][$i] ?? '')),
                    'postalCode' => sanitize_text_field(wp_unslash($_POST['location_postal'][$i] ?? '')),
                    'country' => strtoupper(sanitize_text_field(wp_unslash($_POST['location_country'][$i] ?? '')))
                ),
                'phone' => sanitize_text_field(wp_unslash($_POST['location_phone'][$i] ?? '')),
                'email' => sanitize_email(wp_unslash($_POST['location_email'][$i] ?? ''))
            );

            // Collect location photos
            $photos_raw = sanitize_textarea_field(wp_unslash($_POST['location_photos'][$i] ?? ''));
            if (!empty($photos_raw)) {
                $photos = array_filter(array_map('esc_url_raw', array_map('trim', explode("\n", $photos_raw))));
                if (!empty($photos)) {
                    $location['photos'] = array_values($photos);
                }
            }

            // Collect regular business hours: one text field per day, e.g. "09:00-13:00, 14:00-18:00"
            $business_hours = array();
            foreach (Ofero_Opening_Hours::WEEKDAYS as $day) {
                $day_hours = Ofero_Opening_Hours::parse(
                    sanitize_text_field(wp_unslash($_POST['location_bh_' . $day][$i] ?? ''))
                );
                if ($day_hours !== null) {
                    $business_hours[$day] = $day_hours;
                }
            }
            if (!empty($business_hours)) {
                $timezone = sanitize_text_field(wp_unslash($_POST['location_bh_timezone'][$i] ?? ''));
                if ($timezone !== '') {
                    $business_hours['timezone'] = $timezone;
                }
                $location['businessHours'] = $business_hours;
            }

            // Collect special hours for this location
            $special_hours_raw = sanitize_textarea_field(wp_unslash($_POST['location_special_hours'][$i] ?? ''));
            if (!empty($special_hours_raw)) {
                $decoded = json_decode(wp_unslash($special_hours_raw), true);
                if (is_array($decoded) && !empty($decoded)) {
                    $special_hours = array();
                    foreach ($decoded as $entry) {
                        if (!isset($entry['name'], $entry['hours'])) {
                            continue;
                        }
                        // Text from the editor, or a list / 2.x string from the file on disk
                        $hours = Ofero_Opening_Hours::parse(
                            is_array($entry['hours'])
                                ? array_map('sanitize_text_field', $entry['hours'])
                                : sanitize_text_field($entry['hours'])
                        );
                        if ($hours === null) {
                            continue;
                        }
                        if (isset($entry['date'])) {
                            $special_hours[] = array(
                                'date'  => sanitize_text_field($entry['date']),
                                'name'  => sanitize_text_field($entry['name']),
                                'hours' => $hours
                            );
                        } elseif (isset($entry['from'], $entry['to'])) {
                            $special_hours[] = array(
                                'from'  => sanitize_text_field($entry['from']),
                                'to'    => sanitize_text_field($entry['to']),
                                'name'  => sanitize_text_field($entry['name']),
                                'hours' => $hours
                            );
                        }
                    }
                    if (!empty($special_hours)) {
                        $location['specialHours'] = $special_hours;
                    }
                }
            }

            // Collect contact persons for this location
            $contact_names = isset($_POST['location_contact_name'][$i])
                ? array_map('sanitize_text_field', wp_unslash((array) $_POST['location_contact_name'][$i]))
                : array();
            if (!empty($contact_names) && is_array($contact_names)) {
                $contacts = array();
                foreach ($contact_names as $ci => $contact_name) {
                    if (empty($contact_name)) {
                        continue;
                    }
                    $contact = array(
                        'name' => $contact_name,
                        'role' => sanitize_text_field(wp_unslash($_POST['location_contact_role'][$i][$ci] ?? '')),
                        'email' => sanitize_email(wp_unslash($_POST['location_contact_email'][$i][$ci] ?? '')),
                        'public' => !empty($_POST['location_contact_public'][$i][$ci])
                    );
                    $contact_phone = sanitize_text_field(wp_unslash($_POST['location_contact_phone'][$i][$ci] ?? ''));
                    if (!empty($contact_phone)) {
                        $contact['phone'] = $contact_phone;
                    }
                    $contact_photo = esc_url_raw(wp_unslash($_POST['location_contact_photo'][$i][$ci] ?? ''));
                    if (!empty($contact_photo)) {
                        $contact['photo'] = $contact_photo;
                    }
                    $contact_original = $this->decode_original(wp_unslash($_POST['location_contact_original'][$i][$ci] ?? ''));
                    $contacts[] = self::keep_unmanaged($contact_original, $contact, self::CONTACT_FIELDS);
                }
                if (!empty($contacts)) {
                    $location['contacts'] = $contacts;
                }
            }

            $original = $this->decode_original(wp_unslash($_POST['location_original'][$i] ?? ''));
            $locations[] = self::keep_unmanaged($original, $location, self::LOCATION_FIELDS);
        }

        return $locations;
    }

    /**
     * Collect banking from POST
     */
    private function collect_banking() {
        $banking = array();

        if (!isset($_POST['bank_name']) || !is_array($_POST['bank_name'])) {
            return $banking;
        }

        $count = count($_POST['bank_name']);

        for ($i = 0; $i < $count; $i++) {
            if (empty($_POST['bank_iban'][$i])) {
                continue;
            }

            $banking[] = array(
                'accountName' => sanitize_text_field(wp_unslash($_POST['bank_accountName'][$i] ?? '')),
                'bankName' => sanitize_text_field(wp_unslash($_POST['bank_name'][$i] ?? '')),
                'iban' => sanitize_text_field(wp_unslash($_POST['bank_iban'][$i] ?? '')),
                'bic' => sanitize_text_field(wp_unslash($_POST['bank_bic'][$i] ?? '')),
                'currency' => strtoupper(sanitize_text_field(wp_unslash($_POST['bank_currency'][$i] ?? '')))
            );
        }

        return $banking;
    }

    /**
     * Collect wallets from POST
     */
    private function collect_wallets() {
        $wallets = array();

        if (!isset($_POST['wallet_address']) || !is_array($_POST['wallet_address'])) {
            return $wallets;
        }

        $count = count($_POST['wallet_address']);

        for ($i = 0; $i < $count; $i++) {
            if (empty($_POST['wallet_address'][$i])) {
                continue;
            }

            $wallets[] = array(
                'blockchain' => sanitize_text_field(wp_unslash($_POST['wallet_blockchain'][$i] ?? '')),
                'network' => sanitize_text_field(wp_unslash($_POST['wallet_network'][$i] ?? 'mainnet')),
                'address' => sanitize_text_field(wp_unslash($_POST['wallet_address'][$i] ?? '')),
                'label' => sanitize_text_field(wp_unslash($_POST['wallet_label'][$i] ?? ''))
            );
        }

        return $wallets;
    }

    /**
     * Collect the Branding tab rows from POST and convert them to the schema `branding` object
     */
    private function collect_branding() {
        $assets = array();

        // guidelines, brandingKeywords etc. are not edited by the form; keep them from the saved file
        $existing = $this->file_manager ? ($this->file_manager->load()['branding'] ?? array()) : array();

        if (!isset($_POST['brand_url']) || !is_array($_POST['brand_url'])) {
            return Ofero_Branding_Mapper::rows_to_branding($assets, $existing);
        }

        $count = count($_POST['brand_url']);

        for ($i = 0; $i < $count; $i++) {
            if (empty($_POST['brand_url'][$i])) {
                continue;
            }

            $assets[] = array(
                'type' => sanitize_text_field(wp_unslash($_POST['brand_type'][$i] ?? 'logo')),
                'variant' => sanitize_text_field(wp_unslash($_POST['brand_variant'][$i] ?? 'primary')),
                'url' => esc_url_raw(wp_unslash($_POST['brand_url'][$i] ?? '')),
                'format' => sanitize_text_field(wp_unslash($_POST['brand_format'][$i] ?? ''))
            );
        }

        return Ofero_Branding_Mapper::rows_to_branding($assets, $existing);
    }

    /**
     * Collect social from POST
     */
    private function collect_social() {
        $social = array();

        if (!isset($_POST['social_platform']) || !is_array($_POST['social_platform'])) {
            return $social;
        }

        $count = count($_POST['social_platform']);

        for ($i = 0; $i < $count; $i++) {
            if (empty($_POST['social_url'][$i])) {
                continue;
            }

            $social[] = array(
                'platform' => sanitize_text_field(wp_unslash($_POST['social_platform'][$i] ?? '')),
                'url' => esc_url_raw(wp_unslash($_POST['social_url'][$i] ?? ''))
            );
        }

        return $social;
    }

    /**
     * Collect support channels from POST
     */
    private function collect_support() {
        $support = array();

        if (!isset($_POST['support_type']) || !is_array($_POST['support_type'])) {
            return $support;
        }

        $count = count($_POST['support_type']);

        for ($i = 0; $i < $count; $i++) {
            if (empty($_POST['support_contact'][$i])) {
                continue;
            }

            $support[] = array(
                'type' => sanitize_text_field(wp_unslash($_POST['support_type'][$i] ?? '')),
                'contact' => sanitize_text_field(wp_unslash($_POST['support_contact'][$i] ?? ''))
            );
        }

        return $support;
    }

    /**
     * Collect catalog from POST for v2.0 output.
     *
     * v2 forbids inline menus / products / services etc. The catalog block only
     * holds:
     *   - defaultCurrency, priceListUrl
     *   - wholesale: trade terms (only when "Offer wholesale" is checked)
     *   - feeds[]: external feed references (manual entries + WooCommerce REST endpoint)
     *   - signature[] (<=6) and highlights[] (<=6): tiny inline previews
     *
     * Existing v1 form fields (menu categories, daily menu, etc.) are NOT
     * written to ofero.json anymore. The menu/restaurant tabs in the UI become
     * lightweight feed-reference editors in v2 templates.
     */
    private function collect_catalog() {
        $catalog = array(
            'defaultCurrency' => strtoupper(sanitize_text_field(wp_unslash($_POST['menu_currency'] ?? 'USD'))),
        );

        $price_list_url = esc_url_raw(wp_unslash($_POST['catalog_price_list_url'] ?? ''));
        if (!empty($price_list_url)) {
            $catalog['priceListUrl'] = $price_list_url;
        }

        $wholesale = $this->collect_catalog_wholesale();
        if (!empty($wholesale)) {
            $catalog['wholesale'] = $wholesale;
        }

        $feeds = $this->collect_catalog_feeds();

        // WooCommerce sync: if active, persist selection and append a REST feed reference
        if (Ofero_WooCommerce_Sync::is_woocommerce_active()) {
            if (isset($_POST['selected_products']) && is_array($_POST['selected_products'])) {
                $selected_ids = array_map('intval', wp_unslash((array) $_POST['selected_products']));
                $woo_sync = new Ofero_WooCommerce_Sync();
                $woo_sync->save_selected_product_ids($selected_ids);
            }
            update_option('ofero_generator_catalog_auto_sync', isset($_POST['catalog_auto_sync']));
            $woo_sync = new Ofero_WooCommerce_Sync();
            $woo_feed = $woo_sync->generate_feed_reference();
            if ($woo_feed) {
                $feeds[] = $woo_feed;
            }
        }

        if (!empty($feeds)) {
            $catalog['feeds'] = array_slice($feeds, 0, 50);
        }

        $signature = $this->collect_catalog_preview_items('catalog_signature', 6);
        // Auto-populate from WooCommerce if user didn't enter any
        if (empty($signature) && Ofero_WooCommerce_Sync::is_woocommerce_active()) {
            $woo_sync = new Ofero_WooCommerce_Sync();
            $signature = $woo_sync->generate_signature_items(6);
        }
        if (!empty($signature)) {
            $catalog['signature'] = $signature;
        }

        $highlights = $this->collect_catalog_preview_items('catalog_highlights', 6);
        if (!empty($highlights)) {
            $catalog['highlights'] = $highlights;
        }

        return $catalog;
    }

    /**
     * Collect manual catalog.feeds[] entries from POST.
     * Each entry: {type, format, url, name?, language?, lastUpdated?, itemCount?, standard?}.
     */
    private function collect_catalog_feeds() {
        $feeds = array();
        if (!isset($_POST['catalog_feed_url']) || !is_array($_POST['catalog_feed_url'])) {
            return $feeds;
        }
        $valid_types = array('products','menu','services','packages','portfolio','reservations','rooms','other');
        $valid_formats = array('json','jsonl','xml','csv','rss','atom','schema.org-jsonld','google-merchant-xml','gtfs','ical','other');
        $count = count($_POST['catalog_feed_url']);
        for ($i = 0; $i < $count; $i++) {
            $url = esc_url_raw(wp_unslash($_POST['catalog_feed_url'][$i] ?? ''));
            if (empty($url)) {
                continue;
            }
            $type = sanitize_text_field(wp_unslash($_POST['catalog_feed_type'][$i] ?? 'other'));
            $format = sanitize_text_field(wp_unslash($_POST['catalog_feed_format'][$i] ?? 'json'));
            $entry = array(
                'type' => in_array($type, $valid_types, true) ? $type : 'other',
                'format' => in_array($format, $valid_formats, true) ? $format : 'json',
                'url' => $url,
            );
            $name = sanitize_text_field(wp_unslash($_POST['catalog_feed_name'][$i] ?? ''));
            if (!empty($name)) {
                $entry['name'] = array('default' => $name);
            }
            $lang = sanitize_text_field(wp_unslash($_POST['catalog_feed_language'][$i] ?? ''));
            if (!empty($lang) && preg_match('/^[a-z]{2}$/', $lang)) {
                $entry['language'] = $lang;
            }
            $standard = sanitize_text_field(wp_unslash($_POST['catalog_feed_standard'][$i] ?? ''));
            if (!empty($standard)) {
                $entry['standard'] = $standard;
            }
            $item_count = isset($_POST['catalog_feed_item_count'][$i])
                ? intval(wp_unslash($_POST['catalog_feed_item_count'][$i]))
                : 0;
            if ($item_count > 0) {
                $entry['itemCount'] = $item_count;
            }
            $audience = sanitize_text_field(wp_unslash($_POST['catalog_feed_audience'][$i] ?? ''));
            if (in_array($audience, array('retail', 'wholesale'), true)) {
                $entry['audience'] = $audience;
            }
            $feeds[] = $entry;
        }
        return $feeds;
    }

    /**
     * Collect catalog.wholesale from POST. Returns an empty array when
     * "Offer wholesale" is unchecked, so the key is omitted from ofero.json.
     */
    private function collect_catalog_wholesale() {
        if (empty($_POST['wholesale_enabled'])) {
            return array();
        }

        $wholesale = array();

        $valid_buyers = array('businesses', 'resellers', 'professionals');
        $buyers = isset($_POST['wholesale_buyers']) && is_array($_POST['wholesale_buyers'])
            ? array_map('sanitize_text_field', wp_unslash((array) $_POST['wholesale_buyers']))
            : array();
        $buyers = array_values(array_unique(array_intersect($buyers, $valid_buyers)));
        if (!empty($buyers)) {
            $wholesale['buyers'] = $buyers;
        }

        $eligibility = sanitize_text_field(wp_unslash($_POST['wholesale_eligibility'] ?? ''));
        if (!empty($eligibility)) {
            $wholesale['eligibility'] = array('default' => $eligibility);
        }

        $wholesale['accountRequired'] = !empty($_POST['wholesale_account_required']);

        $url_fields = array(
            'applicationUrl' => 'wholesale_application_url',
            'portalUrl'      => 'wholesale_portal_url',
            'priceListUrl'   => 'wholesale_price_list_url',
        );
        foreach ($url_fields as $key => $field) {
            $url = esc_url_raw(wp_unslash($_POST[$field] ?? ''), array('https'));
            if (!empty($url)) {
                $wholesale[$key] = $url;
            }
        }

        $amount = sanitize_text_field(wp_unslash($_POST['wholesale_min_order_amount'] ?? ''));
        if ($amount !== '' && is_numeric($amount) && (float) $amount >= 0) {
            $minimum_order = array('amount' => (float) $amount);
            $currency = strtoupper(sanitize_text_field(wp_unslash($_POST['wholesale_min_order_currency'] ?? '')));
            if (preg_match('/^[A-Z]{3}$/', $currency)) {
                $minimum_order['currency'] = $currency;
            }
            $wholesale['minimumOrder'] = $minimum_order;
        }

        $wholesale['volumePricing'] = !empty($_POST['wholesale_volume_pricing']);

        $email = sanitize_email(wp_unslash($_POST['wholesale_contact_email'] ?? ''));
        if (!empty($email)) {
            $wholesale['contactEmail'] = $email;
        }

        return $wholesale;
    }

    /**
     * Collect CatalogPreviewItem entries for catalog.signature[] or catalog.highlights[].
     * Hard-capped at $limit (schema enforces maxItems 6).
     */
    private function collect_catalog_preview_items($prefix, $limit) {
        $items = array();
        $name_key = $prefix . '_name';
        if (!isset($_POST[$name_key]) || !is_array($_POST[$name_key])) {
            return $items;
        }
        $count = min(count($_POST[$name_key]), $limit);
        for ($i = 0; $i < $count; $i++) {
            $name = sanitize_text_field(wp_unslash($_POST[$name_key][$i] ?? ''));
            if (empty($name)) {
                continue;
            }
            $entry = array('name' => array('default' => $name));
            $id = sanitize_text_field(wp_unslash($_POST[$prefix . '_id'][$i] ?? ''));
            if (!empty($id)) {
                $entry['id'] = $id;
            }
            $desc = sanitize_text_field(wp_unslash($_POST[$prefix . '_description'][$i] ?? ''));
            if (!empty($desc)) {
                $entry['description'] = array('default' => $desc);
            }
            $cat = sanitize_text_field(wp_unslash($_POST[$prefix . '_category'][$i] ?? ''));
            if (!empty($cat)) {
                $entry['category'] = $cat;
            }
            $image = esc_url_raw(wp_unslash($_POST[$prefix . '_image_url'][$i] ?? ''));
            if (!empty($image)) {
                $entry['imageUrl'] = $image;
            }
            $url = esc_url_raw(wp_unslash($_POST[$prefix . '_url'][$i] ?? ''));
            if (!empty($url)) {
                $entry['url'] = $url;
            }
            $price = sanitize_text_field(wp_unslash($_POST[$prefix . '_price_formatted'][$i] ?? ''));
            if (!empty($price)) {
                $entry['priceFormatted'] = $price;
            }
            $items[] = $entry;
        }
        return $items;
    }

    /**
     * AJAX: Save draft
     */
    public function ajax_save_draft() {
        check_ajax_referer('ofero_generator_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'ofero-generator'));
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- raw JSON payload, validated by json_decode() below and sanitized field by field on save.
        $data = json_decode(wp_unslash($_POST['data'] ?? '{}'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(__('Invalid JSON data.', 'ofero-generator'));
        }

        // Save as draft (transient)
        set_transient('ofero_generator_draft', $data, DAY_IN_SECONDS);

        wp_send_json_success(__('Draft saved.', 'ofero-generator'));
    }

    /**
     * AJAX: Validate
     */
    public function ajax_validate() {
        check_ajax_referer('ofero_generator_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'ofero-generator'));
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- raw JSON payload, validated by json_decode() below and sanitized field by field on save.
        $data = json_decode(wp_unslash($_POST['data'] ?? '{}'), true);
        $level = sanitize_text_field(wp_unslash($_POST['level'] ?? 'moderate'));

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(__('Invalid JSON data.', 'ofero-generator'));
        }

        $result = $this->validator->validate($data, $level);

        wp_send_json_success($result);
    }

    /**
     * AJAX: Export
     */
    public function ajax_export() {
        check_ajax_referer('ofero_generator_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'ofero-generator'));
        }

        $json = $this->file_manager->export();

        wp_send_json_success(array('content' => $json));
    }

    /**
     * AJAX: Import
     */
    public function ajax_import() {
        check_ajax_referer('ofero_generator_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'ofero-generator'));
        }

        $url = esc_url_raw(wp_unslash($_POST['url'] ?? ''));

        if (empty($url)) {
            wp_send_json_error(__('URL is required.', 'ofero-generator'));
        }

        $data = $this->file_manager->import_from_url($url);

        if (is_wp_error($data)) {
            wp_send_json_error($data->get_error_message());
        }

        wp_send_json_success($data);
    }

    /**
     * Add admin notice
     */
    private function add_admin_notice($type, $message) {
        set_transient('ofero_generator_notice', array(
            'type' => $type,
            'message' => $message
        ), 30);
    }
}
