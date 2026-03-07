<?php
/**
 * Banking Section Template
 *
 * @package Ofero_Generator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$banking = $data['banking'] ?? array();
?>
<div class="ofero-card">
    <h2><?php esc_html_e('Banking Information', 'ofero-generator'); ?></h2>
    <p class="description">
        <?php esc_html_e('Add bank accounts for receiving payments.', 'ofero-generator'); ?>
    </p>

    <div id="banking-container">
        <?php if (!empty($banking)): ?>
            <?php foreach ($banking as $i => $account): ?>
                <div class="ofero-repeater-item" data-index="<?php echo $i; ?>">
                    <div class="ofero-repeater-header">
                        <span class="ofero-repeater-title">
                            <?php echo esc_html($account['bankName'] ?? sprintf(__('Account %d', 'ofero-generator'), $i + 1)); ?>
                        </span>
                        <button type="button" class="button ofero-repeater-remove">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                    <div class="ofero-repeater-content">
                        <div class="ofero-field-row">
                            <div class="ofero-field">
                                <label><?php esc_html_e('Account Name', 'ofero-generator'); ?></label>
                                <input type="text" name="bank_accountName[]"
                                       value="<?php echo esc_attr($account['accountName'] ?? ''); ?>"
                                       class="regular-text">
                            </div>
                            <div class="ofero-field">
                                <label><?php esc_html_e('Bank Name', 'ofero-generator'); ?></label>
                                <input type="text" name="bank_name[]"
                                       value="<?php echo esc_attr($account['bankName'] ?? ''); ?>"
                                       class="regular-text">
                            </div>
                        </div>
                        <div class="ofero-field-row">
                            <div class="ofero-field ofero-field-wide">
                                <label><?php esc_html_e('IBAN', 'ofero-generator'); ?></label>
                                <input type="text" name="bank_iban[]"
                                       value="<?php echo esc_attr($account['iban'] ?? ''); ?>"
                                       class="large-text" style="font-family: monospace;">
                            </div>
                        </div>
                        <div class="ofero-field-row">
                            <div class="ofero-field">
                                <label><?php esc_html_e('BIC/SWIFT', 'ofero-generator'); ?></label>
                                <input type="text" name="bank_bic[]"
                                       value="<?php echo esc_attr($account['bic'] ?? ''); ?>"
                                       class="regular-text" style="font-family: monospace; text-transform: uppercase;">
                            </div>
                            <div class="ofero-field">
                                <label><?php esc_html_e('Currency', 'ofero-generator'); ?></label>
                                <input type="text" name="bank_currency[]"
                                       value="<?php echo esc_attr($account['currency'] ?? ''); ?>"
                                       class="small-text" maxlength="3" style="text-transform: uppercase;"
                                       placeholder="EUR">
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <button type="button" class="button ofero-add-item" data-container="banking-container" data-template="banking-template">
        <span class="dashicons dashicons-plus-alt2"></span>
        <?php esc_html_e('Add Bank Account', 'ofero-generator'); ?>
    </button>
</div>

<!-- Banking Template -->
<script type="text/template" id="banking-template">
    <div class="ofero-repeater-item">
        <div class="ofero-repeater-header">
            <span class="ofero-repeater-title"><?php esc_html_e('New Bank Account', 'ofero-generator'); ?></span>
            <button type="button" class="button ofero-repeater-remove">
                <span class="dashicons dashicons-trash"></span>
            </button>
        </div>
        <div class="ofero-repeater-content">
            <div class="ofero-field-row">
                <div class="ofero-field">
                    <label><?php esc_html_e('Account Name', 'ofero-generator'); ?></label>
                    <input type="text" name="bank_accountName[]" class="regular-text">
                </div>
                <div class="ofero-field">
                    <label><?php esc_html_e('Bank Name', 'ofero-generator'); ?></label>
                    <input type="text" name="bank_name[]" class="regular-text">
                </div>
            </div>
            <div class="ofero-field-row">
                <div class="ofero-field ofero-field-wide">
                    <label><?php esc_html_e('IBAN', 'ofero-generator'); ?></label>
                    <input type="text" name="bank_iban[]" class="large-text" style="font-family: monospace;">
                </div>
            </div>
            <div class="ofero-field-row">
                <div class="ofero-field">
                    <label><?php esc_html_e('BIC/SWIFT', 'ofero-generator'); ?></label>
                    <input type="text" name="bank_bic[]" class="regular-text" style="font-family: monospace; text-transform: uppercase;">
                </div>
                <div class="ofero-field">
                    <label><?php esc_html_e('Currency', 'ofero-generator'); ?></label>
                    <input type="text" name="bank_currency[]" class="small-text" maxlength="3" style="text-transform: uppercase;" placeholder="EUR">
                </div>
            </div>
        </div>
    </div>
</script>
