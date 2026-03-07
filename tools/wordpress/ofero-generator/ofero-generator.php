<?php
/**
 * Plugin Name: Ofero Generator
 * Plugin URI: https://ofero.me/ofero-json
 * Description: A complete admin interface for generating and managing ofero.json files with all sections, validation, and auto-save.
 * Version: 1.3.1
 * Author: Ofero Network
 * Author URI: https://ofero.network
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: ofero-generator
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('OFERO_GENERATOR_VERSION', '1.3.1');
define('OFERO_GENERATOR_PATH', plugin_dir_path(__FILE__));
define('OFERO_GENERATOR_URL', plugin_dir_url(__FILE__));
define('OFERO_GENERATOR_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once OFERO_GENERATOR_PATH . 'includes/class-admin-page.php';
require_once OFERO_GENERATOR_PATH . 'includes/class-form-handler.php';
require_once OFERO_GENERATOR_PATH . 'includes/class-validator.php';
require_once OFERO_GENERATOR_PATH . 'includes/class-file-manager.php';
require_once OFERO_GENERATOR_PATH . 'includes/class-license-verifier.php';
require_once OFERO_GENERATOR_PATH . 'includes/class-woocommerce-sync.php';

/**
 * Main plugin class
 */
class Ofero_Generator {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Admin page handler
     */
    private $admin_page;

    /**
     * Form handler
     */
    private $form_handler;

    /**
     * Validator
     */
    private $validator;

    /**
     * File manager
     */
    private $file_manager;

    /**
     * License verifier
     */
    private $license_verifier;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Initialize components
        $this->file_manager = new Ofero_File_Manager();
        $this->validator = new Ofero_Validator();
        $this->license_verifier = new Ofero_License_Verifier();
        $this->form_handler = new Ofero_Form_Handler($this->file_manager, $this->validator);
        $this->admin_page = new Ofero_Admin_Page($this->file_manager, $this->validator, $this->form_handler, $this->license_verifier);

        // Register hooks
        $this->register_hooks();
    }

    /**
     * Register WordPress hooks
     */
    private function register_hooks() {
        // Admin hooks
        add_action('admin_menu', array($this->admin_page, 'add_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('admin_init', array($this->form_handler, 'handle_form_submission'));
        add_action('admin_init', array($this, 'handle_clear_shortcode_cache'));

        // AJAX handlers
        add_action('wp_ajax_ofero_save_draft', array($this->form_handler, 'ajax_save_draft'));
        add_action('wp_ajax_ofero_validate', array($this->form_handler, 'ajax_validate'));
        add_action('wp_ajax_ofero_export', array($this->form_handler, 'ajax_export'));
        add_action('wp_ajax_ofero_import', array($this->form_handler, 'ajax_import'));
        add_action('wp_ajax_ofero_refresh_license', array($this, 'ajax_refresh_license'));

        // Activation/Deactivation
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'ofero-generator') === false) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'ofero-generator-admin',
            OFERO_GENERATOR_URL . 'assets/css/admin.css',
            array(),
            OFERO_GENERATOR_VERSION
        );

        // Enqueue JavaScript
        wp_enqueue_script(
            'ofero-generator-admin',
            OFERO_GENERATOR_URL . 'assets/js/admin.js',
            array('jquery'),
            OFERO_GENERATOR_VERSION,
            true
        );

        // Localize script
        wp_localize_script('ofero-generator-admin', 'oferoGenerator', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ofero_generator_nonce'),
            'strings' => array(
                'saving'          => __('Saving...', 'ofero-generator'),
                'saved'           => __('Saved!', 'ofero-generator'),
                'error'           => __('Error saving', 'ofero-generator'),
                'validating'      => __('Validating...', 'ofero-generator'),
                'valid'           => __('Valid!', 'ofero-generator'),
                'invalid'         => __('Validation failed', 'ofero-generator'),
                'confirmDelete'   => __('Are you sure you want to delete this item?', 'ofero-generator'),
                // Menu tab
                'newCategory'     => __('New Category', 'ofero-generator'),
                'removeCategory'  => __('Remove Category', 'ofero-generator'),
                'categoryId'      => __('Category ID', 'ofero-generator'),
                'categoryName'    => __('Category Name', 'ofero-generator'),
                'sortOrder'       => __('Sort Order', 'ofero-generator'),
                'itemsInCategory' => __('Items in this category', 'ofero-generator'),
                'addItem'         => __('Add Item', 'ofero-generator'),
                'newItem'         => __('New Item', 'ofero-generator'),
                'removeItem'      => __('Remove Item', 'ofero-generator'),
                'itemId'          => __('Item ID', 'ofero-generator'),
                'name'            => __('Name', 'ofero-generator'),
                'description'     => __('Description', 'ofero-generator'),
                'price'           => __('Price', 'ofero-generator'),
                'imageUrl'        => __('Image URL', 'ofero-generator'),
                'prepTime'        => __('Prep Time', 'ofero-generator'),
                'available'       => __('Available', 'ofero-generator'),
                'itemAvailable'   => __('Item is currently available', 'ofero-generator'),
                'popular'         => __('Popular', 'ofero-generator'),
                'markPopular'     => __('Mark as popular / featured', 'ofero-generator'),
                'collapse'        => __('Collapse', 'ofero-generator'),
                'expand'          => __('Expand', 'ofero-generator'),
                // Restaurant tab
                'platformName'    => __('Platform name (e.g., Glovo)', 'ofero-generator'),
                'remove'          => __('Remove', 'ofero-generator'),
                // Services tab
                'newService'      => __('New Service', 'ofero-generator'),
                'serviceId'       => __('Service ID', 'ofero-generator'),
                'category'        => __('Category', 'ofero-generator'),
                'duration'        => __('Duration', 'ofero-generator'),
                'serviceAvailable'=> __('Service currently available', 'ofero-generator'),
            )
        ));

        // Media uploader for logos
        wp_enqueue_media();
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create .well-known directory if it doesn't exist
        $well_known_dir = ABSPATH . '.well-known';
        if (!file_exists($well_known_dir)) {
            wp_mkdir_p($well_known_dir);
        }

        // Set default options
        add_option('ofero_generator_output_path', '.well-known/ofero.json');
        add_option('ofero_generator_backup_enabled', true);
        add_option('ofero_generator_auto_save', true);

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Cleanup if needed
        flush_rewrite_rules();
    }

    /**
     * Get file manager
     */
    public function get_file_manager() {
        return $this->file_manager;
    }

    /**
     * Get validator
     */
    public function get_validator() {
        return $this->validator;
    }

    /**
     * Get license verifier
     */
    public function get_license_verifier() {
        return $this->license_verifier;
    }

    /**
     * Handle clear shortcode cache request
     */
    public function handle_clear_shortcode_cache() {
        if (!isset($_GET['clear_shortcode_cache']) || $_GET['page'] !== 'ofero-generator') {
            return;
        }

        if (!wp_verify_nonce($_GET['_wpnonce'] ?? '', 'ofero_clear_cache')) {
            wp_die(__('Security check failed.', 'ofero-generator'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'ofero-generator'));
        }

        delete_transient('ofero_json_data');

        set_transient('ofero_generator_notice', array(
            'type' => 'success',
            'message' => __('Shortcode cache cleared successfully.', 'ofero-generator')
        ), 30);

        wp_redirect(admin_url('admin.php?page=ofero-generator'));
        exit;
    }

    /**
     * AJAX handler for refreshing license status
     */
    public function ajax_refresh_license() {
        check_ajax_referer('ofero_generator_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'ofero-generator')));
        }

        // Force refresh from API
        $result = $this->license_verifier->verify(true);

        wp_send_json_success(array(
            'license' => $result,
            'badge_html' => $this->license_verifier->get_status_badge($result),
        ));
    }
}

// Initialize plugin
add_action('plugins_loaded', function() {
    Ofero_Generator::get_instance();
});

/**
 * Get plugin instance
 */
function ofero_generator() {
    return Ofero_Generator::get_instance();
}
