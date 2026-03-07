<?php
/**
 * File Manager Class
 *
 * Handles reading, writing, and managing ofero.json files.
 *
 * @package Ofero_Generator
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Ofero_File_Manager {

    /**
     * Get the output path for ofero.json
     */
    public function get_output_path() {
        return get_option('ofero_generator_output_path', '.well-known/ofero.json');
    }

    /**
     * Get the full file path
     */
    public function get_full_path() {
        return ABSPATH . $this->get_output_path();
    }

    /**
     * Check if ofero.json exists
     */
    public function file_exists() {
        return file_exists($this->get_full_path());
    }

    /**
     * Load ofero.json data
     */
    public function load() {
        $path = $this->get_full_path();

        if (!file_exists($path)) {
            return $this->get_default_data();
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->get_default_data();
        }

        // Restore internal translation config from database
        $translations_config = get_option('ofero_generator_translations_config', array());
        if (!empty($translations_config)) {
            $data['_translations'] = $translations_config;
        }

        return $data;
    }

    /**
     * Save ofero.json data
     */
    public function save($data) {
        $path = $this->get_full_path();
        $dir = dirname($path);

        // Create directory if it doesn't exist
        if (!file_exists($dir)) {
            if (!wp_mkdir_p($dir)) {
                return new WP_Error('dir_create_failed', __('Failed to create directory.', 'ofero-generator'));
            }
        }

        // Check if directory is writable
        if (!is_writable($dir)) {
            return new WP_Error('dir_not_writable', __('Directory is not writable.', 'ofero-generator'));
        }

        // Create backup if enabled
        if (get_option('ofero_generator_backup_enabled', true) && file_exists($path)) {
            $this->create_backup();
        }

        // Update metadata
        $data['metadata']['lastUpdated'] = current_time('c');
        if (empty($data['metadata']['createdAt'])) {
            $data['metadata']['createdAt'] = current_time('c');
        }

        // Store internal data separately (for plugin use, not in output file)
        $internal_data = isset($data['_translations']) ? $data['_translations'] : array();
        update_option('ofero_generator_translations_config', $internal_data);

        // Prepare data for output (remove internal fields)
        $output_data = $this->prepare_for_output($data);

        // Save file
        $json = json_encode($output_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Fix floating point precision issues in price amounts
        // PHP has a known issue with float precision, so we clean up the JSON output
        $json = preg_replace_callback(
            '/"amount":\s*(-?\d+\.\d{3,})/',
            function($matches) {
                return '"amount": ' . number_format((float)$matches[1], 2, '.', '');
            },
            $json
        );

        $result = file_put_contents($path, $json);

        if ($result === false) {
            return new WP_Error('save_failed', __('Failed to save file.', 'ofero-generator'));
        }

        return true;
    }

    /**
     * Prepare data for output (remove internal fields, clean up empty values)
     */
    private function prepare_for_output($data) {
        // Remove internal fields
        unset($data['_translations']);

        // Clean up empty arrays and values recursively
        $data = $this->clean_empty_values($data);

        return $data;
    }

    /**
     * Recursively clean empty values from array
     */
    private function clean_empty_values($data) {
        if (!is_array($data)) {
            return $data;
        }

        $cleaned = array();

        foreach ($data as $key => $value) {
            // Skip internal keys
            if (strpos($key, '_') === 0) {
                continue;
            }

            if (is_array($value)) {
                $cleaned_value = $this->clean_empty_values($value);
                // Keep non-empty arrays
                if (!empty($cleaned_value)) {
                    $cleaned[$key] = $cleaned_value;
                }
            } elseif ($value !== '' && $value !== null) {
                $cleaned[$key] = $value;
            }
        }

        return $cleaned;
    }

    /**
     * Create a backup of the current file
     */
    public function create_backup() {
        $path = $this->get_full_path();

        if (!file_exists($path)) {
            return false;
        }

        $backup_dir = dirname($path) . '/backups';
        if (!file_exists($backup_dir)) {
            wp_mkdir_p($backup_dir);
        }

        $backup_path = $backup_dir . '/ofero-' . date('Y-m-d-H-i-s') . '.json';
        return copy($path, $backup_path);
    }

    /**
     * Get list of available backups
     */
    public function get_backups() {
        $backup_dir = dirname($this->get_full_path()) . '/backups';

        if (!file_exists($backup_dir)) {
            return array();
        }

        $files = glob($backup_dir . '/ofero-*.json');
        $backups = array();

        foreach ($files as $file) {
            $backups[] = array(
                'file' => basename($file),
                'path' => $file,
                'date' => filemtime($file),
                'size' => filesize($file)
            );
        }

        // Sort by date descending
        usort($backups, function($a, $b) {
            return $b['date'] - $a['date'];
        });

        return $backups;
    }

    /**
     * Restore from backup
     */
    public function restore_backup($backup_file) {
        $backup_dir = dirname($this->get_full_path()) . '/backups';
        $backup_path = $backup_dir . '/' . sanitize_file_name($backup_file);

        if (!file_exists($backup_path)) {
            return new WP_Error('backup_not_found', __('Backup file not found.', 'ofero-generator'));
        }

        // Verify it's valid JSON
        $content = file_get_contents($backup_path);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('invalid_backup', __('Backup file contains invalid JSON.', 'ofero-generator'));
        }

        // Create backup of current file before restoring
        $this->create_backup();

        // Restore
        return $this->save($data);
    }

    /**
     * Delete a backup
     */
    public function delete_backup($backup_file) {
        $backup_dir = dirname($this->get_full_path()) . '/backups';
        $backup_path = $backup_dir . '/' . sanitize_file_name($backup_file);

        if (!file_exists($backup_path)) {
            return new WP_Error('backup_not_found', __('Backup file not found.', 'ofero-generator'));
        }

        return unlink($backup_path);
    }

    /**
     * Get default ofero.json structure
     */
    public function get_default_data() {
        $site_url = get_site_url();
        $parsed = parse_url($site_url);
        $domain = $parsed['host'] ?? 'example.com';

        return array(
            'language' => get_bloginfo('language') ? substr(get_bloginfo('language'), 0, 2) : 'en',
            'domain' => $domain,
            'canonicalUrl' => $site_url . '/.well-known/ofero.json',
            'metadata' => array(
                'version' => '1.0.0',
                'schemaVersion' => 'ofero-metadata-1.0',
                'lastUpdated' => current_time('c'),
                'createdAt' => current_time('c')
            ),
            'organization' => array(
                'legalName' => get_bloginfo('name'),
                'brandName' => get_bloginfo('name'),
                'entityType' => 'company',
                'legalForm' => '',
                'description' => get_bloginfo('description'),
                'website' => $site_url,
                'contactEmail' => get_bloginfo('admin_email'),
                'contactPhone' => '',
                'identifiers' => array(
                    'global' => array(),
                    'primaryIncorporation' => array(
                        'country' => '',
                        'registrationNumber' => '',
                        'taxId' => '',
                        'vatNumber' => ''
                    ),
                    'perCountry' => array()
                )
            ),
            'locations' => array(),
            'banking' => array(),
            'wallets' => array(),
            'brandAssets' => array(),
            'catalog' => array(),
            'communications' => array(
                'social' => array(),
                'support' => array()
            )
        );
    }

    /**
     * Import from URL
     */
    public function import_from_url($url) {
        $response = wp_remote_get($url, array('timeout' => 30));

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            return new WP_Error('fetch_failed', sprintf(__('Failed to fetch URL. HTTP code: %d', 'ofero-generator'), $code));
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('invalid_json', __('URL does not contain valid JSON.', 'ofero-generator'));
        }

        return $data;
    }

    /**
     * Export as downloadable file
     */
    public function export() {
        $data = $this->load();
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
