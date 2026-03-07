<?php
/**
 * Uninstall Script
 *
 * This file runs when the plugin is deleted via WordPress admin.
 * It cleans up all plugin data from the database.
 *
 * @package Ofero_Generator
 * @since 1.0.0
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Delete all plugin options from database
 */
delete_option('ofero_generator_translations_config');
delete_option('ofero_generator_output_path');
delete_option('ofero_generator_backup_enabled');
delete_option('ofero_generator_auto_save');

/**
 * Delete all transients
 */
delete_transient('ofero_generator_draft');
delete_transient('ofero_generator_notice');

/**
 * Optional: Delete ofero.json file
 *
 * Uncomment the lines below if you want to delete the ofero.json file
 * when the plugin is uninstalled. By default, we keep the file so users
 * don't lose their data if they accidentally uninstall the plugin.
 */
/*
$file_path = ABSPATH . get_option('ofero_generator_output_path', '.well-known/ofero.json');
if (file_exists($file_path)) {
    unlink($file_path);
}
*/

/**
 * Optional: Delete backup files
 *
 * Uncomment to delete all backup files when uninstalling
 */
/*
$backup_dir = WP_CONTENT_DIR . '/ofero-backups';
if (is_dir($backup_dir)) {
    $files = glob($backup_dir . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    rmdir($backup_dir);
}
*/
