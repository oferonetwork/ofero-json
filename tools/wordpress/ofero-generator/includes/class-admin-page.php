<?php
/**
 * Admin Page Class
 *
 * Handles the WordPress admin interface for the generator.
 *
 * @package Ofero_Generator
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Ofero_Admin_Page {

    /**
     * File manager instance
     */
    private $file_manager;

    /**
     * Validator instance
     */
    private $validator;

    /**
     * Form handler instance
     */
    private $form_handler;

    /**
     * License verifier instance
     */
    private $license_verifier;

    /**
     * Constructor
     */
    public function __construct($file_manager, $validator, $form_handler, $license_verifier = null) {
        $this->file_manager = $file_manager;
        $this->validator = $validator;
        $this->form_handler = $form_handler;
        $this->license_verifier = $license_verifier;
    }

    /**
     * Add admin menu
     */
    public function add_menu() {
        // Main menu
        add_menu_page(
            __('Ofero.json', 'ofero-generator'),
            __('Ofero.json', 'ofero-generator'),
            'manage_options',
            'ofero-generator',
            array($this, 'render_main_page'),
            'dashicons-media-code',
            80
        );

        // Submenu - Editor
        add_submenu_page(
            'ofero-generator',
            __('Editor', 'ofero-generator'),
            __('Editor', 'ofero-generator'),
            'manage_options',
            'ofero-generator',
            array($this, 'render_main_page')
        );

        // Submenu - Preview
        add_submenu_page(
            'ofero-generator',
            __('Preview', 'ofero-generator'),
            __('Preview', 'ofero-generator'),
            'manage_options',
            'ofero-generator-preview',
            array($this, 'render_preview_page')
        );

        // Submenu - Settings
        add_submenu_page(
            'ofero-generator',
            __('Settings', 'ofero-generator'),
            __('Settings', 'ofero-generator'),
            'manage_options',
            'ofero-generator-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Render main editor page
     */
    public function render_main_page() {
        $data = $this->file_manager->load();
        $validation = $this->validator->get_summary($data);

        // Check for admin notices
        $notice = get_transient('ofero_generator_notice');
        if ($notice) {
            delete_transient('ofero_generator_notice');
        }

        // Get license verification status
        $license_badge = '';
        if ($this->license_verifier) {
            $license_badge = $this->license_verifier->get_status_badge();
        }

        include OFERO_GENERATOR_PATH . 'templates/admin-page.php';
    }

    /**
     * Render preview page
     */
    public function render_preview_page() {
        $data = $this->file_manager->load();
        $validation = $this->validator->validate($data, 'strict');

        ?>
        <div class="wrap ofero-generator-wrap">
            <h1><?php esc_html_e('ofero.json Preview', 'ofero-generator'); ?></h1>

            <div class="ofero-validation-banner <?php echo $validation['valid'] ? 'valid' : 'invalid'; ?>">
                <?php if ($validation['valid']): ?>
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php esc_html_e('Valid ofero.json', 'ofero-generator'); ?>
                <?php else: ?>
                    <span class="dashicons dashicons-warning"></span>
                    <?php echo sprintf(
                        esc_html__('%d validation error(s) found', 'ofero-generator'),
                        count($validation['errors'])
                    ); ?>
                <?php endif; ?>
            </div>

            <?php if (!$validation['valid']): ?>
                <div class="ofero-errors">
                    <h3><?php esc_html_e('Validation Errors', 'ofero-generator'); ?></h3>
                    <ul>
                        <?php foreach ($validation['errors'] as $error): ?>
                            <li>
                                <strong><?php echo esc_html($error['field']); ?>:</strong>
                                <?php echo esc_html($error['message']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="ofero-json-preview">
                <pre><?php echo esc_html(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></pre>
            </div>

            <div class="ofero-file-info">
                <p>
                    <strong><?php esc_html_e('File Location:', 'ofero-generator'); ?></strong>
                    <?php echo esc_html($this->file_manager->get_output_path()); ?>
                </p>
                <p>
                    <strong><?php esc_html_e('Full URL:', 'ofero-generator'); ?></strong>
                    <a href="<?php echo esc_url(site_url($this->file_manager->get_output_path())); ?>" target="_blank">
                        <?php echo esc_url(site_url($this->file_manager->get_output_path())); ?>
                    </a>
                </p>
                <p>
                    <strong><?php esc_html_e('Last Updated:', 'ofero-generator'); ?></strong>
                    <?php echo esc_html($data['metadata']['lastUpdated'] ?? __('Never', 'ofero-generator')); ?>
                </p>
            </div>

            <div class="ofero-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=ofero-generator')); ?>" class="button button-primary">
                    <?php esc_html_e('Edit', 'ofero-generator'); ?>
                </a>
                <button type="button" class="button" id="ofero-download">
                    <?php esc_html_e('Download', 'ofero-generator'); ?>
                </button>
                <button type="button" class="button" id="ofero-copy">
                    <?php esc_html_e('Copy to Clipboard', 'ofero-generator'); ?>
                </button>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#ofero-download').on('click', function() {
                var content = <?php echo json_encode(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>;
                var blob = new Blob([content], {type: 'application/json'});
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = 'ofero.json';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            });

            $('#ofero-copy').on('click', function() {
                var content = <?php echo json_encode(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?>;
                navigator.clipboard.writeText(content).then(function() {
                    alert('<?php esc_html_e('Copied to clipboard!', 'ofero-generator'); ?>');
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        $backups = $this->file_manager->get_backups();

        // Check for admin notices
        $notice = get_transient('ofero_generator_notice');
        if ($notice) {
            delete_transient('ofero_generator_notice');
        }

        ?>
        <div class="wrap ofero-generator-wrap">
            <h1><?php esc_html_e('Ofero Generator Settings', 'ofero-generator'); ?></h1>

            <?php if ($notice): ?>
                <div class="notice notice-<?php echo esc_attr($notice['type']); ?> is-dismissible">
                    <p><?php echo esc_html($notice['message']); ?></p>
                </div>
            <?php endif; ?>

            <div class="ofero-settings-grid">
                <!-- General Settings -->
                <div class="ofero-card">
                    <h2><?php esc_html_e('General Settings', 'ofero-generator'); ?></h2>

                    <form method="post" action="">
                        <?php wp_nonce_field('ofero_generator_save', 'ofero_generator_nonce'); ?>
                        <input type="hidden" name="ofero_generator_action" value="settings">

                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="output_path"><?php esc_html_e('Output Path', 'ofero-generator'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="output_path" name="output_path"
                                           value="<?php echo esc_attr(get_option('ofero_generator_output_path', '.well-known/ofero.json')); ?>"
                                           class="regular-text">
                                    <p class="description">
                                        <?php esc_html_e('Relative path from WordPress root.', 'ofero-generator'); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <?php esc_html_e('Automatic Backups', 'ofero-generator'); ?>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="backup_enabled" value="1"
                                               <?php checked(get_option('ofero_generator_backup_enabled', true)); ?>>
                                        <?php esc_html_e('Create backup before saving', 'ofero-generator'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <?php esc_html_e('Auto-save', 'ofero-generator'); ?>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="auto_save" value="1"
                                               <?php checked(get_option('ofero_generator_auto_save', true)); ?>>
                                        <?php esc_html_e('Enable draft auto-save', 'ofero-generator'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>

                        <?php submit_button(__('Save Settings', 'ofero-generator')); ?>
                    </form>
                </div>

                <!-- Import/Export -->
                <div class="ofero-card">
                    <h2><?php esc_html_e('Import / Export', 'ofero-generator'); ?></h2>

                    <h3><?php esc_html_e('Import from URL', 'ofero-generator'); ?></h3>
                    <form method="post" action="">
                        <?php wp_nonce_field('ofero_generator_save', 'ofero_generator_nonce'); ?>
                        <input type="hidden" name="ofero_generator_action" value="import">

                        <p>
                            <input type="url" name="import_url" placeholder="https://example.com/.well-known/ofero.json"
                                   class="regular-text">
                        </p>
                        <p>
                            <button type="submit" class="button"><?php esc_html_e('Import from URL', 'ofero-generator'); ?></button>
                        </p>
                    </form>

                    <h3><?php esc_html_e('Import from File', 'ofero-generator'); ?></h3>
                    <form method="post" action="" enctype="multipart/form-data">
                        <?php wp_nonce_field('ofero_generator_save', 'ofero_generator_nonce'); ?>
                        <input type="hidden" name="ofero_generator_action" value="import">

                        <p>
                            <input type="file" name="import_file" accept=".json">
                        </p>
                        <p>
                            <button type="submit" class="button"><?php esc_html_e('Import File', 'ofero-generator'); ?></button>
                        </p>
                    </form>

                    <h3><?php esc_html_e('Export', 'ofero-generator'); ?></h3>
                    <p>
                        <a href="<?php echo esc_url(site_url($this->file_manager->get_output_path())); ?>"
                           class="button" download="ofero.json">
                            <?php esc_html_e('Download Current ofero.json', 'ofero-generator'); ?>
                        </a>
                    </p>
                </div>

                <!-- Emergency Reset -->
                <div class="ofero-card">
                    <h2><?php esc_html_e('Emergency Reset', 'ofero-generator'); ?></h2>

                    <p class="description" style="color: #dc3545; margin-bottom: 20px;">
                        <strong><?php esc_html_e('⚠️ Warning:', 'ofero-generator'); ?></strong>
                        <?php esc_html_e('Use this if the plugin is not working correctly due to corrupted data. This will reset all plugin settings and clear cached data.', 'ofero-generator'); ?>
                    </p>

                    <form method="post" action="" onsubmit="return confirm('<?php esc_attr_e('⚠️ Are you sure you want to reset all plugin data? This cannot be undone. Your ofero.json file will NOT be deleted.', 'ofero-generator'); ?>')">
                        <?php wp_nonce_field('ofero_generator_save', 'ofero_generator_nonce'); ?>
                        <input type="hidden" name="ofero_generator_action" value="emergency_reset">

                        <p>
                            <strong><?php esc_html_e('This will reset:', 'ofero-generator'); ?></strong>
                        </p>
                        <ul style="list-style: disc; margin-left: 20px; margin-bottom: 15px;">
                            <li><?php esc_html_e('Translation configuration', 'ofero-generator'); ?></li>
                            <li><?php esc_html_e('All plugin settings', 'ofero-generator'); ?></li>
                            <li><?php esc_html_e('Cached data and transients', 'ofero-generator'); ?></li>
                        </ul>

                        <p>
                            <strong><?php esc_html_e('Will NOT delete:', 'ofero-generator'); ?></strong>
                        </p>
                        <ul style="list-style: disc; margin-left: 20px; margin-bottom: 20px;">
                            <li><?php esc_html_e('Your ofero.json file', 'ofero-generator'); ?></li>
                            <li><?php esc_html_e('Backup files', 'ofero-generator'); ?></li>
                        </ul>

                        <button type="submit" class="button button-large" style="background: #dc3545; color: white; border-color: #dc3545;">
                            <?php esc_html_e('🔧 Emergency Reset Plugin Data', 'ofero-generator'); ?>
                        </button>
                    </form>
                </div>

                <!-- Backups -->
                <div class="ofero-card ofero-card-full">
                    <h2><?php esc_html_e('Backups', 'ofero-generator'); ?></h2>

                    <?php if (empty($backups)): ?>
                        <p><?php esc_html_e('No backups available.', 'ofero-generator'); ?></p>
                    <?php else: ?>
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('File', 'ofero-generator'); ?></th>
                                    <th><?php esc_html_e('Date', 'ofero-generator'); ?></th>
                                    <th><?php esc_html_e('Size', 'ofero-generator'); ?></th>
                                    <th><?php esc_html_e('Actions', 'ofero-generator'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($backups as $backup): ?>
                                    <tr>
                                        <td><?php echo esc_html($backup['file']); ?></td>
                                        <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $backup['date'])); ?></td>
                                        <td><?php echo esc_html(size_format($backup['size'])); ?></td>
                                        <td>
                                            <form method="post" action="" style="display: inline;">
                                                <?php wp_nonce_field('ofero_generator_save', 'ofero_generator_nonce'); ?>
                                                <input type="hidden" name="ofero_generator_action" value="restore_backup">
                                                <input type="hidden" name="backup_file" value="<?php echo esc_attr($backup['file']); ?>">
                                                <button type="submit" class="button button-small"
                                                        onclick="return confirm('<?php esc_attr_e('Are you sure? This will overwrite the current ofero.json.', 'ofero-generator'); ?>')">
                                                    <?php esc_html_e('Restore', 'ofero-generator'); ?>
                                                </button>
                                            </form>

                                            <form method="post" action="" style="display: inline;">
                                                <?php wp_nonce_field('ofero_generator_save', 'ofero_generator_nonce'); ?>
                                                <input type="hidden" name="ofero_generator_action" value="delete_backup">
                                                <input type="hidden" name="backup_file" value="<?php echo esc_attr($backup['file']); ?>">
                                                <button type="submit" class="button button-small"
                                                        onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this backup?', 'ofero-generator'); ?>')">
                                                    <?php esc_html_e('Delete', 'ofero-generator'); ?>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}
