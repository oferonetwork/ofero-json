<?php
/**
 * Branding Section Template
 *
 * @package Ofero_Generator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$brandAssets = $data['brandAssets'] ?? array();
?>
<div class="ofero-card">
    <h2><?php esc_html_e('Brand Assets', 'ofero-generator'); ?></h2>
    <p class="description">
        <?php esc_html_e('Add logos, icons, and other brand assets.', 'ofero-generator'); ?>
    </p>
    <div class="ofero-info-box" style="background: #e7f3ff; border-left: 4px solid #2271b1; padding: 12px; margin-bottom: 15px;">
        <strong><?php esc_html_e('Logo Variants:', 'ofero-generator'); ?></strong>
        <ul style="margin: 8px 0 0 20px;">
            <li><?php esc_html_e('Light: Logo optimized for dark backgrounds (light-colored logo for dark themes)', 'ofero-generator'); ?></li>
            <li><?php esc_html_e('Dark: Logo optimized for light backgrounds (dark-colored logo for light themes)', 'ofero-generator'); ?></li>
            <li><?php esc_html_e('Monochrome: Single-color version', 'ofero-generator'); ?></li>
            <li><?php esc_html_e('Full Color: Complete color version (default)', 'ofero-generator'); ?></li>
        </ul>
    </div>

    <div id="branding-container">
        <?php if (!empty($brandAssets)): ?>
            <?php foreach ($brandAssets as $i => $asset): ?>
                <div class="ofero-repeater-item" data-index="<?php echo $i; ?>">
                    <div class="ofero-repeater-header">
                        <span class="ofero-repeater-title">
                            <?php echo esc_html(ucfirst($asset['type'] ?? 'Asset') . ' - ' . ucfirst($asset['variant'] ?? 'Primary')); ?>
                        </span>
                        <button type="button" class="button ofero-repeater-remove">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                    <div class="ofero-repeater-content">
                        <div class="ofero-field-row">
                            <div class="ofero-field">
                                <label><?php esc_html_e('Type', 'ofero-generator'); ?></label>
                                <select name="brand_type[]">
                                    <?php
                                    $assetTypes = array(
                                        'logo' => __('Logo', 'ofero-generator'),
                                        'icon' => __('Icon', 'ofero-generator'),
                                        'banner' => __('Banner', 'ofero-generator'),
                                        'favicon' => __('Favicon', 'ofero-generator'),
                                        'avatar' => __('Avatar', 'ofero-generator'),
                                        'cover' => __('Cover Image', 'ofero-generator')
                                    );
                                    $currentType = $asset['type'] ?? 'logo';
                                    foreach ($assetTypes as $value => $label):
                                    ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($currentType, $value); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="ofero-field">
                                <label><?php esc_html_e('Variant', 'ofero-generator'); ?></label>
                                <select name="brand_variant[]">
                                    <?php
                                    $variants = array(
                                        'primary' => __('Primary', 'ofero-generator'),
                                        'dark' => __('Dark (for light backgrounds)', 'ofero-generator'),
                                        'light' => __('Light (for dark backgrounds)', 'ofero-generator'),
                                        'monochrome' => __('Monochrome', 'ofero-generator'),
                                        'full-color' => __('Full Color', 'ofero-generator'),
                                        'transparent' => __('Transparent', 'ofero-generator')
                                    );
                                    $currentVariant = $asset['variant'] ?? 'primary';
                                    foreach ($variants as $value => $label):
                                    ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($currentVariant, $value); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="ofero-field">
                                <label><?php esc_html_e('Format', 'ofero-generator'); ?></label>
                                <input type="text" name="brand_format[]"
                                       value="<?php echo esc_attr($asset['format'] ?? ''); ?>"
                                       class="small-text" placeholder="png, svg, jpg">
                            </div>
                        </div>
                        <div class="ofero-field-row">
                            <div class="ofero-field ofero-field-wide">
                                <label><?php esc_html_e('URL', 'ofero-generator'); ?></label>
                                <div class="ofero-media-field">
                                    <input type="url" name="brand_url[]"
                                           value="<?php echo esc_url($asset['url'] ?? ''); ?>"
                                           class="large-text ofero-media-url">
                                    <button type="button" class="button ofero-media-select">
                                        <?php esc_html_e('Select', 'ofero-generator'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($asset['url'])): ?>
                            <div class="ofero-asset-preview">
                                <img src="<?php echo esc_url($asset['url']); ?>" alt="" style="max-width: 150px; max-height: 100px;">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <button type="button" class="button ofero-add-item" data-container="branding-container" data-template="branding-template">
        <span class="dashicons dashicons-plus-alt2"></span>
        <?php esc_html_e('Add Brand Asset', 'ofero-generator'); ?>
    </button>
</div>

<!-- Branding Template -->
<script type="text/template" id="branding-template">
    <div class="ofero-repeater-item">
        <div class="ofero-repeater-header">
            <span class="ofero-repeater-title"><?php esc_html_e('New Brand Asset', 'ofero-generator'); ?></span>
            <button type="button" class="button ofero-repeater-remove">
                <span class="dashicons dashicons-trash"></span>
            </button>
        </div>
        <div class="ofero-repeater-content">
            <div class="ofero-field-row">
                <div class="ofero-field">
                    <label><?php esc_html_e('Type', 'ofero-generator'); ?></label>
                    <select name="brand_type[]">
                        <option value="logo"><?php esc_html_e('Logo', 'ofero-generator'); ?></option>
                        <option value="icon"><?php esc_html_e('Icon', 'ofero-generator'); ?></option>
                        <option value="banner"><?php esc_html_e('Banner', 'ofero-generator'); ?></option>
                        <option value="favicon"><?php esc_html_e('Favicon', 'ofero-generator'); ?></option>
                        <option value="avatar"><?php esc_html_e('Avatar', 'ofero-generator'); ?></option>
                        <option value="cover"><?php esc_html_e('Cover Image', 'ofero-generator'); ?></option>
                    </select>
                </div>
                <div class="ofero-field">
                    <label><?php esc_html_e('Variant', 'ofero-generator'); ?></label>
                    <select name="brand_variant[]">
                        <option value="primary"><?php esc_html_e('Primary', 'ofero-generator'); ?></option>
                        <option value="dark"><?php esc_html_e('Dark (for light backgrounds)', 'ofero-generator'); ?></option>
                        <option value="light"><?php esc_html_e('Light (for dark backgrounds)', 'ofero-generator'); ?></option>
                        <option value="monochrome"><?php esc_html_e('Monochrome', 'ofero-generator'); ?></option>
                        <option value="full-color"><?php esc_html_e('Full Color', 'ofero-generator'); ?></option>
                        <option value="transparent"><?php esc_html_e('Transparent', 'ofero-generator'); ?></option>
                    </select>
                </div>
                <div class="ofero-field">
                    <label><?php esc_html_e('Format', 'ofero-generator'); ?></label>
                    <input type="text" name="brand_format[]" class="small-text" placeholder="png, svg, jpg">
                </div>
            </div>
            <div class="ofero-field-row">
                <div class="ofero-field ofero-field-wide">
                    <label><?php esc_html_e('URL', 'ofero-generator'); ?></label>
                    <div class="ofero-media-field">
                        <input type="url" name="brand_url[]" class="large-text ofero-media-url">
                        <button type="button" class="button ofero-media-select">
                            <?php esc_html_e('Select', 'ofero-generator'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>
