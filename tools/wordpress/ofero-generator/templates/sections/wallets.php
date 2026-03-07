<?php
/**
 * Wallets Section Template
 *
 * @package Ofero_Generator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$wallets = $data['wallets'] ?? array();
?>
<div class="ofero-card">
    <h2><?php esc_html_e('Blockchain Wallets', 'ofero-generator'); ?></h2>
    <p class="description">
        <?php esc_html_e('Add cryptocurrency wallet addresses for Web3 payments and verification.', 'ofero-generator'); ?>
    </p>

    <div id="wallets-container">
        <?php if (!empty($wallets)): ?>
            <?php foreach ($wallets as $i => $wallet): ?>
                <div class="ofero-repeater-item" data-index="<?php echo $i; ?>">
                    <div class="ofero-repeater-header">
                        <span class="ofero-repeater-title">
                            <?php echo esc_html(ucfirst($wallet['blockchain'] ?? 'Wallet') . ' - ' . ($wallet['label'] ?? sprintf(__('Wallet %d', 'ofero-generator'), $i + 1))); ?>
                        </span>
                        <button type="button" class="button ofero-repeater-remove">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                    <div class="ofero-repeater-content">
                        <div class="ofero-field-row">
                            <div class="ofero-field">
                                <label><?php esc_html_e('Blockchain', 'ofero-generator'); ?></label>
                                <select name="wallet_blockchain[]">
                                    <?php
                                    $blockchains = array(
                                        'ethereum' => 'Ethereum',
                                        'multiversx' => 'MultiversX',
                                        'bitcoin' => 'Bitcoin',
                                        'polygon' => 'Polygon',
                                        'solana' => 'Solana',
                                        'avalanche' => 'Avalanche',
                                        'binance' => 'BNB Chain',
                                        'arbitrum' => 'Arbitrum',
                                        'optimism' => 'Optimism',
                                        'other' => __('Other', 'ofero-generator')
                                    );
                                    $currentChain = $wallet['blockchain'] ?? '';
                                    foreach ($blockchains as $value => $label):
                                    ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($currentChain, $value); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="ofero-field">
                                <label><?php esc_html_e('Network', 'ofero-generator'); ?></label>
                                <input type="text" name="wallet_network[]"
                                       value="<?php echo esc_attr($wallet['network'] ?? 'mainnet'); ?>"
                                       class="regular-text" placeholder="mainnet">
                            </div>
                            <div class="ofero-field">
                                <label><?php esc_html_e('Label', 'ofero-generator'); ?></label>
                                <input type="text" name="wallet_label[]"
                                       value="<?php echo esc_attr($wallet['label'] ?? ''); ?>"
                                       class="regular-text" placeholder="<?php esc_attr_e('Treasury, Payments, etc.', 'ofero-generator'); ?>">
                            </div>
                        </div>
                        <div class="ofero-field-row">
                            <div class="ofero-field ofero-field-wide">
                                <label><?php esc_html_e('Address', 'ofero-generator'); ?></label>
                                <input type="text" name="wallet_address[]"
                                       value="<?php echo esc_attr($wallet['address'] ?? ''); ?>"
                                       class="large-text" style="font-family: monospace; font-size: 12px;">
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <button type="button" class="button ofero-add-item" data-container="wallets-container" data-template="wallet-template">
        <span class="dashicons dashicons-plus-alt2"></span>
        <?php esc_html_e('Add Wallet', 'ofero-generator'); ?>
    </button>
</div>

<!-- Wallet Template -->
<script type="text/template" id="wallet-template">
    <div class="ofero-repeater-item">
        <div class="ofero-repeater-header">
            <span class="ofero-repeater-title"><?php esc_html_e('New Wallet', 'ofero-generator'); ?></span>
            <button type="button" class="button ofero-repeater-remove">
                <span class="dashicons dashicons-trash"></span>
            </button>
        </div>
        <div class="ofero-repeater-content">
            <div class="ofero-field-row">
                <div class="ofero-field">
                    <label><?php esc_html_e('Blockchain', 'ofero-generator'); ?></label>
                    <select name="wallet_blockchain[]">
                        <option value="ethereum">Ethereum</option>
                        <option value="multiversx">MultiversX</option>
                        <option value="bitcoin">Bitcoin</option>
                        <option value="polygon">Polygon</option>
                        <option value="solana">Solana</option>
                        <option value="avalanche">Avalanche</option>
                        <option value="binance">BNB Chain</option>
                        <option value="arbitrum">Arbitrum</option>
                        <option value="optimism">Optimism</option>
                        <option value="other"><?php esc_html_e('Other', 'ofero-generator'); ?></option>
                    </select>
                </div>
                <div class="ofero-field">
                    <label><?php esc_html_e('Network', 'ofero-generator'); ?></label>
                    <input type="text" name="wallet_network[]" class="regular-text" placeholder="mainnet" value="mainnet">
                </div>
                <div class="ofero-field">
                    <label><?php esc_html_e('Label', 'ofero-generator'); ?></label>
                    <input type="text" name="wallet_label[]" class="regular-text" placeholder="<?php esc_attr_e('Treasury, Payments, etc.', 'ofero-generator'); ?>">
                </div>
            </div>
            <div class="ofero-field-row">
                <div class="ofero-field ofero-field-wide">
                    <label><?php esc_html_e('Address', 'ofero-generator'); ?></label>
                    <input type="text" name="wallet_address[]" class="large-text" style="font-family: monospace; font-size: 12px;">
                </div>
            </div>
        </div>
    </div>
</script>
