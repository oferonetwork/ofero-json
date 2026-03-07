<?php
/**
 * WooCommerce Sync Class
 *
 * Handles synchronization between WooCommerce products and ofero.json catalog.
 *
 * @package Ofero_Generator
 * @since 1.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Ofero_WooCommerce_Sync {

    /**
     * Check if WooCommerce is active
     */
    public static function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }

    /**
     * Get all WooCommerce products
     */
    public function get_products($args = array()) {
        if (!self::is_woocommerce_active()) {
            return array();
        }

        $defaults = array(
            'limit' => -1,
            'status' => 'publish',
            'orderby' => 'name',
            'order' => 'ASC',
        );

        $args = wp_parse_args($args, $defaults);
        $products = wc_get_products($args);

        return $products;
    }

    /**
     * Get selected product IDs from settings
     */
    public function get_selected_product_ids() {
        return get_option('ofero_generator_selected_products', array());
    }

    /**
     * Save selected product IDs
     */
    public function save_selected_product_ids($product_ids) {
        update_option('ofero_generator_selected_products', $product_ids);
    }

    /**
     * Convert WooCommerce product to ofero.json format
     */
    public function convert_product_to_ofero($product) {
        if (!$product) {
            return null;
        }

        $ofero_product = array(
            'id' => strval($product->get_id()),
            'name' => $product->get_name(),
            'description' => wp_strip_all_tags($product->get_description() ?: $product->get_short_description()),
        );

        // Price
        $regular_price = $product->get_regular_price();
        $sale_price = $product->get_sale_price();

        if ($regular_price) {
            // Use bc_math for precise decimal handling, fallback to sprintf
            $amount = function_exists('bcadd')
                ? (float) bcadd($regular_price, '0', 2)
                : (float) sprintf('%.2f', $regular_price);

            $ofero_product['price'] = array(
                'amount' => $amount,
                'currency' => get_woocommerce_currency(),
            );

            if ($sale_price) {
                $sale_amount = function_exists('bcadd')
                    ? (float) bcadd($sale_price, '0', 2)
                    : (float) sprintf('%.2f', $sale_price);

                $ofero_product['salePrice'] = array(
                    'amount' => $sale_amount,
                    'currency' => get_woocommerce_currency(),
                );
            }
        }

        // Images
        $image_id = $product->get_image_id();
        if ($image_id) {
            $image_url = wp_get_attachment_image_url($image_id, 'full');
            if ($image_url) {
                $ofero_product['images'] = array($image_url);
            }
        }

        // Categories
        $categories = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'names'));
        if (!empty($categories) && !is_wp_error($categories)) {
            $ofero_product['categories'] = $categories;
        }

        // Tags as keywords
        $tags = wp_get_post_terms($product->get_id(), 'product_tag', array('fields' => 'names'));
        if (!empty($tags) && !is_wp_error($tags)) {
            $ofero_product['keywords'] = $tags;
        }

        // URL
        $ofero_product['url'] = get_permalink($product->get_id());

        // SKU
        if ($product->get_sku()) {
            $ofero_product['sku'] = $product->get_sku();
        }

        // Stock status
        if ($product->is_in_stock()) {
            $ofero_product['availability'] = 'in_stock';
        } else {
            $ofero_product['availability'] = 'out_of_stock';
        }

        // Product type specific data
        if ($product->is_type('variable')) {
            $variations = $product->get_available_variations();
            if (!empty($variations)) {
                $ofero_product['hasVariants'] = true;
                $ofero_product['variantCount'] = count($variations);
            }
        }

        return $ofero_product;
    }

    /**
     * Generate catalog from selected WooCommerce products
     */
    public function generate_catalog() {
        if (!self::is_woocommerce_active()) {
            return array();
        }

        $selected_ids = $this->get_selected_product_ids();

        if (empty($selected_ids)) {
            return array();
        }

        $catalog = array(
            'type' => 'products',
            'lastUpdated' => current_time('c'),
            'items' => array(),
        );

        foreach ($selected_ids as $product_id) {
            $product = wc_get_product($product_id);
            if ($product) {
                $ofero_product = $this->convert_product_to_ofero($product);
                if ($ofero_product) {
                    $catalog['items'][] = $ofero_product;
                }
            }
        }

        return $catalog;
    }

    /**
     * Get catalog statistics
     */
    public function get_catalog_stats() {
        $selected_ids = $this->get_selected_product_ids();
        $total_products = 0;
        $total_value = 0;
        $currency = get_woocommerce_currency();

        if (self::is_woocommerce_active()) {
            $total_products = count(wc_get_products(array('limit' => -1, 'status' => 'publish')));
        }

        foreach ($selected_ids as $product_id) {
            $product = wc_get_product($product_id);
            if ($product) {
                $price = $product->get_sale_price() ?: $product->get_regular_price();
                if ($price) {
                    $total_value += floatval($price);
                }
            }
        }

        return array(
            'total_products' => $total_products,
            'selected_products' => count($selected_ids),
            'total_value' => $total_value,
            'currency' => $currency,
        );
    }

    /**
     * Render product selector UI
     */
    public function render_product_selector() {
        if (!self::is_woocommerce_active()) {
            echo '<div class="notice notice-warning">';
            echo '<p>' . esc_html__('WooCommerce is not active. Install and activate WooCommerce to sync products.', 'ofero-generator') . '</p>';
            echo '</div>';
            return;
        }

        $products = $this->get_products();
        $selected_ids = $this->get_selected_product_ids();
        $stats = $this->get_catalog_stats();

        ?>
        <div class="ofero-woo-sync">
            <div class="ofero-stats">
                <div class="stat-box">
                    <span class="stat-label"><?php esc_html_e('Total Products', 'ofero-generator'); ?></span>
                    <span class="stat-value"><?php echo esc_html($stats['total_products']); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php esc_html_e('Selected for Catalog', 'ofero-generator'); ?></span>
                    <span class="stat-value"><?php echo esc_html($stats['selected_products']); ?></span>
                </div>
                <div class="stat-box">
                    <span class="stat-label"><?php esc_html_e('Total Value', 'ofero-generator'); ?></span>
                    <span class="stat-value">
                        <?php echo esc_html(number_format($stats['total_value'], 2) . ' ' . $stats['currency']); ?>
                    </span>
                </div>
            </div>

            <div class="ofero-product-filter">
                <input type="text" id="ofero-product-search" placeholder="<?php esc_attr_e('Search products...', 'ofero-generator'); ?>" class="regular-text">
                <button type="button" id="ofero-select-all-products" class="button">
                    <?php esc_html_e('Select All', 'ofero-generator'); ?>
                </button>
                <button type="button" id="ofero-deselect-all-products" class="button">
                    <?php esc_html_e('Deselect All', 'ofero-generator'); ?>
                </button>
            </div>

            <div class="ofero-product-list">
                <?php if (empty($products)): ?>
                    <p><?php esc_html_e('No products found. Create products in WooCommerce first.', 'ofero-generator'); ?></p>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <?php
                        $product_id = $product->get_id();
                        $is_selected = in_array($product_id, $selected_ids);
                        $image_url = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
                        $price = $product->get_price_html();
                        ?>
                        <label class="ofero-product-item" data-product-name="<?php echo esc_attr(strtolower($product->get_name())); ?>">
                            <input type="checkbox"
                                   name="selected_products[]"
                                   value="<?php echo esc_attr($product_id); ?>"
                                   <?php checked($is_selected); ?>>

                            <div class="product-image">
                                <?php if ($image_url): ?>
                                    <img src="<?php echo esc_url($image_url); ?>" alt="">
                                <?php else: ?>
                                    <div class="no-image">📦</div>
                                <?php endif; ?>
                            </div>

                            <div class="product-info">
                                <div class="product-name"><?php echo esc_html($product->get_name()); ?></div>
                                <div class="product-meta">
                                    <span class="product-price"><?php echo wp_kses_post($price); ?></span>
                                    <?php if ($product->get_sku()): ?>
                                        <span class="product-sku">SKU: <?php echo esc_html($product->get_sku()); ?></span>
                                    <?php endif; ?>
                                    <?php if (!$product->is_in_stock()): ?>
                                        <span class="product-stock out-of-stock"><?php esc_html_e('Out of Stock', 'ofero-generator'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <style>
        .ofero-woo-sync {
            margin-top: 20px;
        }

        .ofero-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid #e9ecef;
        }

        .stat-label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            display: block;
            font-size: 24px;
            font-weight: 600;
            color: #2271b1;
        }

        .ofero-product-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .ofero-product-filter input {
            flex: 1;
        }

        .ofero-product-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
            max-height: 600px;
            overflow-y: auto;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .ofero-product-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: white;
            border: 2px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .ofero-product-item:hover {
            border-color: #2271b1;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .ofero-product-item:has(input:checked) {
            border-color: #2271b1;
            background: #e7f3ff;
        }

        .product-image {
            flex-shrink: 0;
            width: 60px;
            height: 60px;
            border-radius: 6px;
            overflow: hidden;
            background: #f0f0f1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .no-image {
            font-size: 24px;
        }

        .product-info {
            flex: 1;
            min-width: 0;
        }

        .product-name {
            font-weight: 600;
            margin-bottom: 5px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .product-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            font-size: 12px;
            color: #666;
        }

        .product-price {
            font-weight: 600;
            color: #2271b1;
        }

        .product-stock.out-of-stock {
            color: #dc3545;
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Product search
            $('#ofero-product-search').on('input', function() {
                var query = $(this).val().toLowerCase();
                $('.ofero-product-item').each(function() {
                    var name = $(this).data('product-name');
                    if (name.indexOf(query) > -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Select all visible
            $('#ofero-select-all-products').on('click', function() {
                $('.ofero-product-item:visible input[type="checkbox"]').prop('checked', true);
            });

            // Deselect all
            $('#ofero-deselect-all-products').on('click', function() {
                $('.ofero-product-item input[type="checkbox"]').prop('checked', false);
            });
        });
        </script>
        <?php
    }
}
