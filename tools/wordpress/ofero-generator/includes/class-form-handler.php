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

class Ofero_Form_Handler {

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
        if (!wp_verify_nonce($_POST['ofero_generator_nonce'] ?? '', 'ofero_generator_save')) {
            wp_die(__('Security check failed.', 'ofero-generator'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'ofero-generator'));
        }

        $action = sanitize_text_field($_POST['ofero_generator_action']);

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
        if (isset($_POST['ofero_business_type']) && in_array($_POST['ofero_business_type'], $allowed_types, true)) {
            update_option('ofero_generator_business_type', sanitize_text_field($_POST['ofero_business_type']));
        }

        // If this was only a business type change, redirect without saving ofero.json
        if (!empty($_POST['ofero_change_business_type'])) {
            wp_redirect(admin_url('admin.php?page=ofero-generator'));
            exit;
        }

        $data = $this->collect_form_data();
        $result = $this->file_manager->save($data);

        if (is_wp_error($result)) {
            $this->add_admin_notice('error', $result->get_error_message());
        } else {
            $this->add_admin_notice('success', __('ofero.json saved successfully.', 'ofero-generator'));
        }

        wp_redirect(admin_url('admin.php?page=ofero-generator&saved=1'));
        exit;
    }

    /**
     * Handle restore backup
     */
    private function handle_restore_backup() {
        $backup_file = sanitize_file_name($_POST['backup_file'] ?? '');

        if (empty($backup_file)) {
            $this->add_admin_notice('error', __('No backup file specified.', 'ofero-generator'));
            wp_redirect(admin_url('admin.php?page=ofero-generator-settings'));
            exit;
        }

        $result = $this->file_manager->restore_backup($backup_file);

        if (is_wp_error($result)) {
            $this->add_admin_notice('error', $result->get_error_message());
        } else {
            $this->add_admin_notice('success', __('Backup restored successfully.', 'ofero-generator'));
        }

        wp_redirect(admin_url('admin.php?page=ofero-generator-settings'));
        exit;
    }

    /**
     * Handle delete backup
     */
    private function handle_delete_backup() {
        $backup_file = sanitize_file_name($_POST['backup_file'] ?? '');

        if (empty($backup_file)) {
            $this->add_admin_notice('error', __('No backup file specified.', 'ofero-generator'));
            wp_redirect(admin_url('admin.php?page=ofero-generator-settings'));
            exit;
        }

        $result = $this->file_manager->delete_backup($backup_file);

        if (is_wp_error($result)) {
            $this->add_admin_notice('error', $result->get_error_message());
        } else {
            $this->add_admin_notice('success', __('Backup deleted.', 'ofero-generator'));
        }

        wp_redirect(admin_url('admin.php?page=ofero-generator-settings'));
        exit;
    }

    /**
     * Handle import
     */
    private function handle_import() {
        $import_url = esc_url_raw($_POST['import_url'] ?? '');

        if (empty($import_url)) {
            // Check for file upload
            if (!empty($_FILES['import_file']['tmp_name'])) {
                $content = file_get_contents($_FILES['import_file']['tmp_name']);
                $data = json_decode($content, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->add_admin_notice('error', __('Uploaded file contains invalid JSON.', 'ofero-generator'));
                    wp_redirect(admin_url('admin.php?page=ofero-generator-settings'));
                    exit;
                }
            } else {
                $this->add_admin_notice('error', __('Please provide a URL or file to import.', 'ofero-generator'));
                wp_redirect(admin_url('admin.php?page=ofero-generator-settings'));
                exit;
            }
        } else {
            $data = $this->file_manager->import_from_url($import_url);

            if (is_wp_error($data)) {
                $this->add_admin_notice('error', $data->get_error_message());
                wp_redirect(admin_url('admin.php?page=ofero-generator-settings'));
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

        wp_redirect(admin_url('admin.php?page=ofero-generator'));
        exit;
    }

    /**
     * Handle settings save
     */
    private function handle_settings() {
        update_option('ofero_generator_output_path', sanitize_text_field($_POST['output_path'] ?? '.well-known/ofero.json'));
        update_option('ofero_generator_backup_enabled', isset($_POST['backup_enabled']));
        update_option('ofero_generator_auto_save', isset($_POST['auto_save']));

        $this->add_admin_notice('success', __('Settings saved.', 'ofero-generator'));

        wp_redirect(admin_url('admin.php?page=ofero-generator-settings&saved=1'));
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

        wp_redirect(admin_url('admin.php?page=ofero-generator-settings&reset=1'));
        exit;
    }

    /**
     * Collect form data from POST
     */
    private function collect_form_data() {
        $primary_language = sanitize_text_field($_POST['language'] ?? 'en');
        $enabled_languages = $this->collect_enabled_languages();

        // Collect translations for translatable fields
        $brand_name_translations = $this->collect_field_translations('organization_brandName', $enabled_languages);
        $description_translations = $this->collect_field_translations('organization_description', $enabled_languages);
        $keywords_translations = $this->collect_field_translations('keywords', $enabled_languages);

        $data = array(
            'language' => $primary_language,
            'domain' => sanitize_text_field($_POST['domain'] ?? ''),
            'canonicalUrl' => esc_url_raw($_POST['canonicalUrl'] ?? ''),
            'metadata' => array(
                'version' => sanitize_text_field($_POST['metadata_version'] ?? '1.0.0'),
                'schemaVersion' => 'ofero-metadata-1.0',
                'lastUpdated' => current_time('c'),
                'createdAt' => sanitize_text_field($_POST['metadata_createdAt'] ?? current_time('c'))
            ),
            'organization' => array(
                'legalName' => sanitize_text_field($_POST['org_legalName'] ?? ''),
                'brandName' => $this->build_translatable_string(
                    sanitize_text_field($_POST['org_brandName'] ?? ''),
                    $brand_name_translations
                ),
                'entityType' => sanitize_text_field($_POST['org_entityType'] ?? 'company'),
                'legalForm' => sanitize_text_field($_POST['org_legalForm'] ?? ''),
                'description' => $this->build_translatable_string(
                    sanitize_textarea_field($_POST['org_description'] ?? ''),
                    $description_translations
                ),
                'website' => esc_url_raw($_POST['org_website'] ?? ''),
                'contactEmail' => sanitize_email($_POST['org_contactEmail'] ?? ''),
                'contactPhone' => sanitize_text_field($_POST['org_contactPhone'] ?? ''),
                'identifiers' => array(
                    'global' => array(),
                    'primaryIncorporation' => array(
                        'country' => strtoupper(sanitize_text_field($_POST['inc_country'] ?? '')),
                        'registrationNumber' => sanitize_text_field($_POST['inc_registrationNumber'] ?? ''),
                        'taxId' => sanitize_text_field($_POST['inc_taxId'] ?? ''),
                        'vatNumber' => sanitize_text_field($_POST['inc_vatNumber'] ?? '')
                    ),
                    'perCountry' => array()
                )
            ),
            'locations' => $this->collect_locations(),
            'banking' => $this->collect_banking(),
            'wallets' => $this->collect_wallets(),
            'brandAssets' => $this->collect_brand_assets(),
            'catalog' => $this->collect_catalog(),
            'communications' => array(
                'social' => $this->collect_social(),
                'support' => $this->collect_support()
            )
        );

        // Add keywords if provided
        $keywords_default = sanitize_text_field($_POST['keywords'] ?? '');
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

        $primary_language = sanitize_text_field($_POST['language'] ?? 'en');
        $enabled_languages = array_map('sanitize_text_field', $_POST['translation_languages']);

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
                $translations[$lang] = sanitize_textarea_field($_POST[$post_key]);
            }
        }

        return $translations;
    }

    /**
     * Build a TranslatableString structure
     * Returns plain string if no translations, or structured object if translations exist
     */
    private function build_translatable_string($default_value, $translations) {
        // If no translations, return plain string
        if (empty($translations)) {
            return $default_value;
        }

        // Build TranslatableString structure
        return array(
            'default' => $default_value,
            'translations' => $translations
        );
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

            $location = array(
                'name' => sanitize_text_field($_POST['location_name'][$i]),
                'type' => sanitize_text_field($_POST['location_type'][$i] ?? 'headquarters'),
                'address' => array(
                    'street' => sanitize_text_field($_POST['location_street'][$i] ?? ''),
                    'city' => sanitize_text_field($_POST['location_city'][$i] ?? ''),
                    'region' => sanitize_text_field($_POST['location_region'][$i] ?? ''),
                    'postalCode' => sanitize_text_field($_POST['location_postal'][$i] ?? ''),
                    'country' => strtoupper(sanitize_text_field($_POST['location_country'][$i] ?? ''))
                ),
                'phone' => sanitize_text_field($_POST['location_phone'][$i] ?? ''),
                'email' => sanitize_email($_POST['location_email'][$i] ?? '')
            );

            // Collect location photos
            $photos_raw = sanitize_textarea_field($_POST['location_photos'][$i] ?? '');
            if (!empty($photos_raw)) {
                $photos = array_filter(array_map('esc_url_raw', array_map('trim', explode("\n", $photos_raw))));
                if (!empty($photos)) {
                    $location['photos'] = array_values($photos);
                }
            }

            // Collect special hours for this location
            $special_hours_raw = sanitize_textarea_field($_POST['location_special_hours'][$i] ?? '');
            if (!empty($special_hours_raw)) {
                $decoded = json_decode(wp_unslash($special_hours_raw), true);
                if (is_array($decoded) && !empty($decoded)) {
                    $special_hours = array();
                    foreach ($decoded as $entry) {
                        if (isset($entry['name'], $entry['hours'])) {
                            if (isset($entry['date'])) {
                                $special_hours[] = array(
                                    'date'  => sanitize_text_field($entry['date']),
                                    'name'  => sanitize_text_field($entry['name']),
                                    'hours' => sanitize_text_field($entry['hours'])
                                );
                            } elseif (isset($entry['from'], $entry['to'])) {
                                $special_hours[] = array(
                                    'from'  => sanitize_text_field($entry['from']),
                                    'to'    => sanitize_text_field($entry['to']),
                                    'name'  => sanitize_text_field($entry['name']),
                                    'hours' => sanitize_text_field($entry['hours'])
                                );
                            }
                        }
                    }
                    if (!empty($special_hours)) {
                        $location['specialHours'] = $special_hours;
                    }
                }
            }

            // Collect contact persons for this location
            $contact_names = $_POST['location_contact_name'][$i] ?? array();
            if (!empty($contact_names) && is_array($contact_names)) {
                $contacts = array();
                foreach ($contact_names as $ci => $contact_name) {
                    if (empty($contact_name)) {
                        continue;
                    }
                    $contact = array(
                        'name' => sanitize_text_field($contact_name),
                        'role' => sanitize_text_field($_POST['location_contact_role'][$i][$ci] ?? ''),
                        'email' => sanitize_email($_POST['location_contact_email'][$i][$ci] ?? ''),
                        'public' => !empty($_POST['location_contact_public'][$i][$ci])
                    );
                    $contact_phone = sanitize_text_field($_POST['location_contact_phone'][$i][$ci] ?? '');
                    if (!empty($contact_phone)) {
                        $contact['phone'] = $contact_phone;
                    }
                    $contact_photo = esc_url_raw($_POST['location_contact_photo'][$i][$ci] ?? '');
                    if (!empty($contact_photo)) {
                        $contact['photo'] = $contact_photo;
                    }
                    $contacts[] = $contact;
                }
                if (!empty($contacts)) {
                    $location['contacts'] = $contacts;
                }
            }

            $locations[] = $location;
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
                'accountName' => sanitize_text_field($_POST['bank_accountName'][$i] ?? ''),
                'bankName' => sanitize_text_field($_POST['bank_name'][$i]),
                'iban' => sanitize_text_field($_POST['bank_iban'][$i]),
                'bic' => sanitize_text_field($_POST['bank_bic'][$i] ?? ''),
                'currency' => strtoupper(sanitize_text_field($_POST['bank_currency'][$i] ?? ''))
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
                'blockchain' => sanitize_text_field($_POST['wallet_blockchain'][$i] ?? ''),
                'network' => sanitize_text_field($_POST['wallet_network'][$i] ?? 'mainnet'),
                'address' => sanitize_text_field($_POST['wallet_address'][$i]),
                'label' => sanitize_text_field($_POST['wallet_label'][$i] ?? '')
            );
        }

        return $wallets;
    }

    /**
     * Collect brand assets from POST
     */
    private function collect_brand_assets() {
        $assets = array();

        if (!isset($_POST['brand_url']) || !is_array($_POST['brand_url'])) {
            return $assets;
        }

        $count = count($_POST['brand_url']);

        for ($i = 0; $i < $count; $i++) {
            if (empty($_POST['brand_url'][$i])) {
                continue;
            }

            $assets[] = array(
                'type' => sanitize_text_field($_POST['brand_type'][$i] ?? 'logo'),
                'variant' => sanitize_text_field($_POST['brand_variant'][$i] ?? 'primary'),
                'url' => esc_url_raw($_POST['brand_url'][$i]),
                'format' => sanitize_text_field($_POST['brand_format'][$i] ?? '')
            );
        }

        return $assets;
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
                'platform' => sanitize_text_field($_POST['social_platform'][$i]),
                'url' => esc_url_raw($_POST['social_url'][$i])
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
                'type' => sanitize_text_field($_POST['support_type'][$i]),
                'contact' => sanitize_text_field($_POST['support_contact'][$i])
            );
        }

        return $support;
    }

    /**
     * Collect catalog from POST (manual menu + WooCommerce fallback)
     */
    private function collect_catalog() {
        // WooCommerce sync takes priority if active
        if (Ofero_WooCommerce_Sync::is_woocommerce_active()) {
            if (isset($_POST['selected_products']) && is_array($_POST['selected_products'])) {
                $selected_ids = array_map('intval', $_POST['selected_products']);
                $woo_sync = new Ofero_WooCommerce_Sync();
                $woo_sync->save_selected_product_ids($selected_ids);
            }
            update_option('ofero_generator_catalog_auto_sync', isset($_POST['catalog_auto_sync']));
            $woo_sync = new Ofero_WooCommerce_Sync();
            return $woo_sync->generate_catalog();
        }

        // Manual menu
        $catalog = array(
            'defaultCurrency' => strtoupper(sanitize_text_field($_POST['menu_currency'] ?? 'USD')),
        );

        // Menu section
        $menu = array();

        $allergen_url = esc_url_raw($_POST['menu_allergen_url'] ?? '');
        if (!empty($allergen_url)) {
            $menu['allergenInfo'] = $allergen_url;
        }

        $dietary = isset($_POST['menu_dietary']) && is_array($_POST['menu_dietary'])
            ? array_map('sanitize_text_field', $_POST['menu_dietary'])
            : array();
        if (!empty($dietary)) {
            $menu['dietaryOptions'] = $dietary;
        }

        // Categories + items
        $categories = $this->collect_menu_categories();
        if (!empty($categories)) {
            $menu['categories'] = $categories;
        }

        if (!empty($menu)) {
            $catalog['menu'] = $menu;
        }

        // Daily menu
        $daily_menu = $this->collect_daily_menu();
        if (!empty($daily_menu)) {
            $catalog['dailyMenu'] = $daily_menu;
        }

        return $catalog;
    }

    /**
     * Collect menu categories and their items from POST
     */
    private function collect_menu_categories() {
        $categories = array();

        if (!isset($_POST['menu_cat_id']) || !is_array($_POST['menu_cat_id'])) {
            return $categories;
        }

        $count = count($_POST['menu_cat_id']);

        for ($catIdx = 0; $catIdx < $count; $catIdx++) {
            $cat_id = sanitize_text_field($_POST['menu_cat_id'][$catIdx] ?? '');
            if (empty($cat_id)) {
                continue;
            }

            $category = array(
                'id'        => $cat_id,
                'name'      => array('default' => sanitize_text_field($_POST['menu_cat_name'][$catIdx] ?? '')),
                'sortOrder' => intval($_POST['menu_cat_sort'][$catIdx] ?? ($catIdx + 1)),
            );

            $service_hours = sanitize_text_field($_POST['menu_cat_service_hours'][$catIdx] ?? '');
            if (!empty($service_hours)) {
                $category['serviceHours'] = $service_hours;
            }

            // Items belonging to this category
            $items = $this->collect_menu_items_for_category($catIdx);
            $category['items'] = $items;

            $categories[] = $category;
        }

        return $categories;
    }

    /**
     * Collect menu items for a specific category index
     */
    private function collect_menu_items_for_category($catIdx) {
        $items = array();

        if (!isset($_POST['menu_item_cat']) || !is_array($_POST['menu_item_cat'])) {
            return $items;
        }

        $total_items = count($_POST['menu_item_cat']);

        for ($i = 0; $i < $total_items; $i++) {
            if (intval($_POST['menu_item_cat'][$i]) !== $catIdx) {
                continue;
            }

            $item_id = sanitize_text_field($_POST['menu_item_id'][$i] ?? '');
            $item_name = sanitize_text_field($_POST['menu_item_name'][$i] ?? '');
            if (empty($item_id) && empty($item_name)) {
                continue;
            }

            $item = array(
                'id'    => $item_id,
                'name'  => array('default' => $item_name),
                'price' => floatval($_POST['menu_item_price'][$i] ?? 0),
            );

            $desc = sanitize_textarea_field($_POST['menu_item_desc'][$i] ?? '');
            if (!empty($desc)) {
                $item['description'] = array('default' => $desc);
            }

            $portion = sanitize_text_field($_POST['menu_item_portion'][$i] ?? '');
            if (!empty($portion)) {
                $item['portionSize'] = $portion;
            }

            $ingredients_raw = sanitize_textarea_field($_POST['menu_item_ingredients'][$i] ?? '');
            if (!empty($ingredients_raw)) {
                $item['ingredients'] = array_values(array_filter(array_map('trim', explode(',', $ingredients_raw))));
            }

            $image = esc_url_raw($_POST['menu_item_image'][$i] ?? '');
            if (!empty($image)) {
                $item['image'] = $image;
            }

            $prep = sanitize_text_field($_POST['menu_item_prep'][$i] ?? '');
            if (!empty($prep)) {
                $item['preparationTime'] = $prep;
            }

            $calories = intval($_POST['menu_item_calories'][$i] ?? 0);
            if ($calories > 0) {
                $item['calories'] = $calories;
            }

            $dietary_key = 'menu_item_dietary_' . $catIdx . '_' . $i;
            if (isset($_POST[$dietary_key]) && is_array($_POST[$dietary_key])) {
                $item['dietary'] = array_map('sanitize_text_field', $_POST[$dietary_key]);
            }

            $allergen_key = 'menu_item_allergens_' . $catIdx . '_' . $i;
            if (isset($_POST[$allergen_key]) && is_array($_POST[$allergen_key])) {
                $item['allergens'] = array_map('sanitize_text_field', $_POST[$allergen_key]);
            }

            $item['available'] = !empty($_POST['menu_item_available'][$i]);
            $item['popular']   = !empty($_POST['menu_item_popular'][$i]);

            $items[] = $item;
        }

        return $items;
    }

    /**
     * Collect daily menu from POST
     */
    private function collect_daily_menu() {
        $daily_menu = array();
        $week_days  = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');

        $week_of = sanitize_text_field($_POST['daily_menu_week_of'] ?? '');
        if (!empty($week_of)) {
            $daily_menu['weekOf'] = $week_of;
        }

        $note = sanitize_text_field($_POST['daily_menu_note'] ?? '');
        if (!empty($note)) {
            $daily_menu['note'] = array('default' => $note);
        }

        $schedule = array();

        foreach ($week_days as $day) {
            $names = $_POST['daily_' . $day . '_name'] ?? array();
            if (!is_array($names) || empty($names)) {
                continue;
            }

            $day_items = array();
            $count = count($names);

            for ($i = 0; $i < $count; $i++) {
                $name = sanitize_text_field($names[$i] ?? '');
                if (empty($name)) {
                    continue;
                }

                $item = array(
                    'name'  => array('default' => $name),
                    'price' => floatval($_POST['daily_' . $day . '_price'][$i] ?? 0),
                );

                $desc = sanitize_textarea_field($_POST['daily_' . $day . '_desc'][$i] ?? '');
                if (!empty($desc)) {
                    $item['description'] = array('default' => $desc);
                }

                $portion = sanitize_text_field($_POST['daily_' . $day . '_portion'][$i] ?? '');
                if (!empty($portion)) {
                    $item['portionSize'] = $portion;
                }

                $course = sanitize_text_field($_POST['daily_' . $day . '_course'][$i] ?? '');
                if (!empty($course)) {
                    $item['course'] = $course;
                }

                $ingredients_raw = sanitize_textarea_field($_POST['daily_' . $day . '_ingredients'][$i] ?? '');
                if (!empty($ingredients_raw)) {
                    $item['ingredients'] = array_values(array_filter(array_map('trim', explode(',', $ingredients_raw))));
                }

                $item['available'] = !empty($_POST['daily_' . $day . '_available'][$i]);

                $day_items[] = $item;
            }

            if (!empty($day_items)) {
                $schedule[$day] = $day_items;
            }
        }

        if (!empty($schedule)) {
            $daily_menu['schedule'] = $schedule;
        }

        return $daily_menu;
    }

    /**
     * AJAX: Save draft
     */
    public function ajax_save_draft() {
        check_ajax_referer('ofero_generator_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'ofero-generator'));
        }

        $data = json_decode(stripslashes($_POST['data'] ?? '{}'), true);

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

        $data = json_decode(stripslashes($_POST['data'] ?? '{}'), true);
        $level = sanitize_text_field($_POST['level'] ?? 'moderate');

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

        $url = esc_url_raw($_POST['url'] ?? '');

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
