<?php
/**
 * Services Section Template (Clinic, Auto Service, Services generic)
 *
 * @package Ofero_Generator
 */

if (!defined('ABSPATH')) {
    exit;
}

$businessType = get_option('ofero_generator_business_type', 'services');
$services     = $data['catalog']['services'] ?? array();

$typeLabels = array(
    'clinic'      => __('medical services', 'ofero-generator'),
    'auto_service' => __('auto services', 'ofero-generator'),
    'services'    => __('services', 'ofero-generator'),
);
$typeLabel = $typeLabels[$businessType] ?? __('services', 'ofero-generator');
?>

<div class="ofero-card">
    <h2><?php esc_html_e('Services', 'ofero-generator'); ?></h2>
    <p class="description">
        <?php printf(
            esc_html__('Add your %s with descriptions and pricing.', 'ofero-generator'),
            esc_html($typeLabel)
        ); ?>
    </p>

    <div id="services-container">
        <?php foreach ($services as $i => $service): ?>
        <div class="ofero-repeater-item ofero-service-item">
            <div class="ofero-repeater-header">
                <span class="ofero-repeater-title">
                    <?php echo esc_html(is_array($service['name']) ? ($service['name']['default'] ?? '') : ($service['name'] ?? '')); ?>
                </span>
                <button type="button" class="button button-small button-link-delete ofero-repeater-remove">
                    <?php esc_html_e('Remove', 'ofero-generator'); ?>
                </button>
            </div>
            <div class="ofero-repeater-content">
                <table class="form-table">
                    <tr>
                        <th style="width: 160px;"><label><?php esc_html_e('Service ID', 'ofero-generator'); ?></label></th>
                        <td><input type="text" name="svc_id[]" value="<?php echo esc_attr($service['id'] ?? ''); ?>" class="regular-text" placeholder="e.g., oil-change"></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Name', 'ofero-generator'); ?></th>
                        <td><input type="text" name="svc_name[]" value="<?php echo esc_attr(is_array($service['name']) ? ($service['name']['default'] ?? '') : ($service['name'] ?? '')); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Description', 'ofero-generator'); ?></th>
                        <td><textarea name="svc_desc[]" rows="2" class="large-text"><?php echo esc_textarea(is_array($service['description']) ? ($service['description']['default'] ?? '') : ($service['description'] ?? '')); ?></textarea></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Category', 'ofero-generator'); ?></th>
                        <td><input type="text" name="svc_category[]" value="<?php echo esc_attr($service['category'] ?? ''); ?>" class="regular-text" placeholder="<?php esc_attr_e('e.g., Consultation, Maintenance', 'ofero-generator'); ?>"></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Pricing Type', 'ofero-generator'); ?></th>
                        <td>
                            <select name="svc_pricing_type[]">
                                <?php
                                $pricingTypes = array(
                                    'fixed'   => __('Fixed price', 'ofero-generator'),
                                    'hourly'  => __('Hourly rate', 'ofero-generator'),
                                    'from'    => __('Starting from', 'ofero-generator'),
                                    'quote'   => __('By quote', 'ofero-generator'),
                                    'free'    => __('Free', 'ofero-generator'),
                                );
                                $currentPricingType = $service['pricingType'] ?? 'fixed';
                                foreach ($pricingTypes as $pv => $pl): ?>
                                    <option value="<?php echo esc_attr($pv); ?>" <?php selected($currentPricingType, $pv); ?>>
                                        <?php echo esc_html($pl); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Price', 'ofero-generator'); ?></th>
                        <td>
                            <input type="number" name="svc_price[]" value="<?php echo esc_attr($service['price'] ?? ''); ?>" class="small-text" min="0" step="0.01">
                            <p class="description"><?php esc_html_e('Leave empty for quote-based services', 'ofero-generator'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Duration', 'ofero-generator'); ?></th>
                        <td>
                            <input type="text" name="svc_duration[]" value="<?php echo esc_attr($service['duration'] ?? ''); ?>" class="small-text" placeholder="30 min">
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Available', 'ofero-generator'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="svc_available[]" value="1" <?php checked($service['available'] ?? true); ?>>
                                <?php esc_html_e('Service currently available', 'ofero-generator'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <button type="button" class="button button-secondary" id="ofero-add-service" style="margin-top: 16px;">
        + <?php esc_html_e('Add Service', 'ofero-generator'); ?>
    </button>
</div>
