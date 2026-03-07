<?php
/**
 * Catalog Section Template
 *
 * @package Ofero_Generator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$woo_sync = new Ofero_WooCommerce_Sync();
$is_woo_active = Ofero_WooCommerce_Sync::is_woocommerce_active();
?>

<div class="ofero-card">
    <h2><?php esc_html_e('Product Catalog', 'ofero-generator'); ?></h2>

    <p class="description">
        <?php esc_html_e('Include your products in the ofero.json catalog. This makes your products discoverable by AI systems, search engines, and business partners.', 'ofero-generator'); ?>
    </p>

    <?php if ($is_woo_active): ?>
        <div class="ofero-catalog-options">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Catalog Type', 'ofero-generator'); ?>
                    </th>
                    <td>
                        <label>
                            <input type="radio" name="catalog_type" value="woocommerce" checked>
                            <?php esc_html_e('Sync from WooCommerce', 'ofero-generator'); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Automatically sync selected products from your WooCommerce store', 'ofero-generator'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Auto-Sync', 'ofero-generator'); ?>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="catalog_auto_sync" value="1"
                                   <?php checked(get_option('ofero_generator_catalog_auto_sync', false)); ?>>
                            <?php esc_html_e('Automatically update catalog when products change', 'ofero-generator'); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e('When enabled, the catalog will update automatically when you edit WooCommerce products', 'ofero-generator'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <h3><?php esc_html_e('Select Products', 'ofero-generator'); ?></h3>
        <p class="description">
            <?php esc_html_e('Choose which products to include in your ofero.json catalog. Only selected products will be visible to external systems.', 'ofero-generator'); ?>
        </p>

        <?php $woo_sync->render_product_selector(); ?>

    <?php else: ?>
        <div class="notice notice-info inline">
            <p>
                <strong><?php esc_html_e('WooCommerce Not Detected', 'ofero-generator'); ?></strong>
            </p>
            <p>
                <?php
                printf(
                    esc_html__('Install and activate %s to automatically sync your products to ofero.json catalog.', 'ofero-generator'),
                    '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>'
                );
                ?>
            </p>
            <p>
                <?php esc_html_e('Alternatively, you can add products manually using the JSON editor in the Preview tab.', 'ofero-generator'); ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<?php if ($is_woo_active): ?>
<div class="ofero-card">
    <h2><?php esc_html_e('Catalog Preview', 'ofero-generator'); ?></h2>

    <?php
    $catalog = $woo_sync->generate_catalog();
    if (!empty($catalog['items'])):
    ?>
        <p>
            <?php
            printf(
                esc_html__('Your catalog contains %d products. Last updated: %s', 'ofero-generator'),
                count($catalog['items']),
                !empty($catalog['lastUpdated']) ? esc_html($catalog['lastUpdated']) : esc_html__('Never', 'ofero-generator')
            );
            ?>
        </p>

        <details>
            <summary style="cursor: pointer; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                <?php esc_html_e('View JSON Preview', 'ofero-generator'); ?>
            </summary>
            <pre style="background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; margin-top: 10px;"><?php
                echo esc_html(json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            ?></pre>
        </details>
    <?php else: ?>
        <p><?php esc_html_e('No products selected. Select products above to generate catalog.', 'ofero-generator'); ?></p>
    <?php endif; ?>
</div>
<?php endif; ?>
