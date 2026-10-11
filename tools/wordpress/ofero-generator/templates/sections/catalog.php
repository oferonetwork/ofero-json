<?php
/**
 * Catalog Section Template (v2)
 *
 * v2 forbids inline catalog data. This editor only manages:
 *   - defaultCurrency, priceListUrl
 *   - catalog.wholesale (trade terms: buyers, account, minimum order, ...)
 *   - catalog.feeds[] (external feed references — manual entries plus the
 *     WooCommerce REST endpoint when WooCommerce is active)
 *   - catalog.signature[] and catalog.highlights[] (tiny inline previews, <=6 each)
 *
 * @package Ofero_Generator
 */

if (!defined('ABSPATH')) {
    exit;
}

// These templates are included from inside Ofero_Generator_Admin_Page::render(), so every
// variable below is a method local, not a global. PHPCS cannot see the enclosing scope.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$existing = is_array($existing ?? null) ? $existing : array();
$catalog = is_array($existing['catalog'] ?? null) ? $existing['catalog'] : array();
$feeds = isset($catalog['feeds']) && is_array($catalog['feeds']) ? $catalog['feeds'] : array();
$signature = isset($catalog['signature']) && is_array($catalog['signature']) ? $catalog['signature'] : array();
$highlights = isset($catalog['highlights']) && is_array($catalog['highlights']) ? $catalog['highlights'] : array();
$default_currency = $catalog['defaultCurrency'] ?? 'USD';
$price_list_url = $catalog['priceListUrl'] ?? '';
$wholesale = is_array($catalog['wholesale'] ?? null) ? $catalog['wholesale'] : array();
$wholesale_buyers = is_array($wholesale['buyers'] ?? null) ? $wholesale['buyers'] : array();
$wholesale_eligibility = isset($wholesale['eligibility']) && is_array($wholesale['eligibility']) ? ($wholesale['eligibility']['default'] ?? '') : '';
$wholesale_min_order = is_array($wholesale['minimumOrder'] ?? null) ? $wholesale['minimumOrder'] : array();

$woo_active = Ofero_WooCommerce_Sync::is_woocommerce_active();
$woo_feed_url = $woo_active ? Ofero_WooCommerce_Sync::get_feed_url() : '';

$feed_types = array('products','menu','services','packages','portfolio','reservations','rooms','other');
$feed_formats = array('json','jsonl','xml','csv','rss','atom','schema.org-jsonld','google-merchant-xml','gtfs','ical','other');
$feed_audiences = array(
    ''          => __('All buyers', 'ofero-generator'),
    'retail'    => __('Retail', 'ofero-generator'),
    'wholesale' => __('Wholesale', 'ofero-generator'),
);
$wholesale_buyer_options = array(
    'businesses'    => __('Any registered business', 'ofero-generator'),
    'resellers'     => __('Resellers (shops, distributors)', 'ofero-generator'),
    'professionals' => __('Professionals buying for their own work (contractors, salons, clinics)', 'ofero-generator'),
);

$preview_text = function ($entry) {
    if (!is_array($entry)) return '';
    if (isset($entry['default'])) return (string) $entry['default'];
    return is_scalar($entry) ? (string) $entry : '';
};
?>

<div class="ofero-card">
    <h2><?php esc_html_e('Catalog (v2)', 'ofero-generator'); ?></h2>

    <p class="description">
        <?php esc_html_e('In ofero.json v2, catalog data (menus, products, services) lives in external feeds referenced by URL — not inline in this file. This keeps ofero.json small (target < 100 KB) and lets catalog data update independently of identity data. Use Schema.org Menu/Product JSON-LD or Google Merchant XML when possible.', 'ofero-generator'); ?>
    </p>

    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="menu_currency"><?php esc_html_e('Default currency', 'ofero-generator'); ?></label>
            </th>
            <td>
                <input type="text" id="menu_currency" name="menu_currency"
                       value="<?php echo esc_attr($default_currency); ?>"
                       maxlength="3" pattern="[A-Z]{3}" placeholder="USD" class="regular-text">
                <p class="description"><?php esc_html_e('ISO 4217 currency code (e.g., USD, EUR, GBP).', 'ofero-generator'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="catalog_price_list_url"><?php esc_html_e('Price list URL (optional)', 'ofero-generator'); ?></label>
            </th>
            <td>
                <input type="url" id="catalog_price_list_url" name="catalog_price_list_url"
                       value="<?php echo esc_attr($price_list_url); ?>"
                       class="regular-text" placeholder="https://example.com/pricing.pdf">
                <p class="description"><?php esc_html_e('Public price list. Trade prices go in the Wholesale card below.', 'ofero-generator'); ?></p>
            </td>
        </tr>
    </table>
</div>

<div class="ofero-card">
    <h2><?php esc_html_e('Wholesale (catalog.wholesale)', 'ofero-generator'); ?></h2>
    <p class="description">
        <?php esc_html_e('Fill this in only if you sell in bulk to other businesses. Per-product case prices and quantity tiers belong in a feed marked "Wholesale" below, not here.', 'ofero-generator'); ?>
    </p>

    <table class="form-table">
        <tr>
            <th scope="row"><?php esc_html_e('Offer wholesale', 'ofero-generator'); ?></th>
            <td>
                <label>
                    <input type="checkbox" name="wholesale_enabled" value="1" <?php checked(!empty($wholesale)); ?>>
                    <?php esc_html_e('We sell at wholesale / trade terms', 'ofero-generator'); ?>
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e('Who can buy', 'ofero-generator'); ?></th>
            <td>
                <?php foreach ($wholesale_buyer_options as $value => $label): ?>
                    <label style="display:block;">
                        <input type="checkbox" name="wholesale_buyers[]" value="<?php echo esc_attr($value); ?>" <?php checked(in_array($value, $wholesale_buyers, true)); ?>>
                        <?php echo esc_html($label); ?>
                    </label>
                <?php endforeach; ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="wholesale_eligibility"><?php esc_html_e('Conditions', 'ofero-generator'); ?></label></th>
            <td>
                <input type="text" id="wholesale_eligibility" name="wholesale_eligibility"
                       value="<?php echo esc_attr($wholesale_eligibility); ?>" class="large-text"
                       placeholder="<?php esc_attr_e('Registered business with a VAT number', 'ofero-generator'); ?>">
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e('Trade account', 'ofero-generator'); ?></th>
            <td>
                <label>
                    <input type="checkbox" name="wholesale_account_required" value="1" <?php checked(!empty($wholesale['accountRequired'])); ?>>
                    <?php esc_html_e('Prices or ordering only after an approved trade account', 'ofero-generator'); ?>
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="wholesale_application_url"><?php esc_html_e('Apply for an account URL', 'ofero-generator'); ?></label></th>
            <td>
                <input type="url" id="wholesale_application_url" name="wholesale_application_url"
                       value="<?php echo esc_attr($wholesale['applicationUrl'] ?? ''); ?>" class="regular-text"
                       placeholder="https://example.com/trade/apply">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="wholesale_portal_url"><?php esc_html_e('Trade portal / login URL', 'ofero-generator'); ?></label></th>
            <td>
                <input type="url" id="wholesale_portal_url" name="wholesale_portal_url"
                       value="<?php echo esc_attr($wholesale['portalUrl'] ?? ''); ?>" class="regular-text"
                       placeholder="https://example.com/trade/login">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="wholesale_price_list_url"><?php esc_html_e('Trade price list URL', 'ofero-generator'); ?></label></th>
            <td>
                <input type="url" id="wholesale_price_list_url" name="wholesale_price_list_url"
                       value="<?php echo esc_attr($wholesale['priceListUrl'] ?? ''); ?>" class="regular-text"
                       placeholder="https://example.com/trade/price-list.pdf">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="wholesale_min_order_amount"><?php esc_html_e('Minimum order value', 'ofero-generator'); ?></label></th>
            <td>
                <input type="number" min="0" step="0.01" id="wholesale_min_order_amount" name="wholesale_min_order_amount"
                       value="<?php echo esc_attr($wholesale_min_order['amount'] ?? ''); ?>" style="width:120px">
                <input type="text" name="wholesale_min_order_currency"
                       value="<?php echo esc_attr($wholesale_min_order['currency'] ?? ''); ?>"
                       maxlength="3" pattern="[A-Z]{3}" placeholder="<?php echo esc_attr($default_currency); ?>" style="width:70px">
                <p class="description"><?php esc_html_e('Leave the currency empty to use the default currency.', 'ofero-generator'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e('Volume pricing', 'ofero-generator'); ?></th>
            <td>
                <label>
                    <input type="checkbox" name="wholesale_volume_pricing" value="1" <?php checked(!empty($wholesale['volumePricing'])); ?>>
                    <?php esc_html_e('Unit prices drop with quantity', 'ofero-generator'); ?>
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="wholesale_contact_email"><?php esc_html_e('Trade enquiries email', 'ofero-generator'); ?></label></th>
            <td>
                <input type="email" id="wholesale_contact_email" name="wholesale_contact_email"
                       value="<?php echo esc_attr($wholesale['contactEmail'] ?? ''); ?>" class="regular-text"
                       placeholder="trade@example.com">
            </td>
        </tr>
    </table>
</div>

<div class="ofero-card">
    <h2><?php esc_html_e('External feeds (catalog.feeds[])', 'ofero-generator'); ?></h2>
    <p class="description">
        <?php esc_html_e('Each entry points consumers to an external feed (one URL per type). Up to 50 entries.', 'ofero-generator'); ?>
    </p>

    <table class="widefat ofero-feeds-table">
        <thead>
            <tr>
                <th><?php esc_html_e('Type', 'ofero-generator'); ?></th>
                <th><?php esc_html_e('Format', 'ofero-generator'); ?></th>
                <th><?php esc_html_e('URL', 'ofero-generator'); ?></th>
                <th><?php esc_html_e('Name', 'ofero-generator'); ?></th>
                <th><?php esc_html_e('Standard', 'ofero-generator'); ?></th>
                <th><?php esc_html_e('Lang', 'ofero-generator'); ?></th>
                <th><?php esc_html_e('Items', 'ofero-generator'); ?></th>
                <th><?php esc_html_e('Audience', 'ofero-generator'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody id="catalog-feeds-rows">
            <?php
            // Render existing rows plus one empty row for adding more
            $rows = $feeds;
            $rows[] = array(); // empty trailing row for new entries
            foreach ($rows as $f):
                $name_preview = isset($f['name']) ? $preview_text($f['name']) : '';
            ?>
                <tr class="catalog-feed-row">
                    <td>
                        <select name="catalog_feed_type[]">
                            <?php foreach ($feed_types as $t): ?>
                                <option value="<?php echo esc_attr($t); ?>" <?php selected(($f['type'] ?? 'other'), $t); ?>><?php echo esc_html($t); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="catalog_feed_format[]">
                            <?php foreach ($feed_formats as $fmt): ?>
                                <option value="<?php echo esc_attr($fmt); ?>" <?php selected(($f['format'] ?? 'json'), $fmt); ?>><?php echo esc_html($fmt); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input type="url" name="catalog_feed_url[]" value="<?php echo esc_attr($f['url'] ?? ''); ?>"
                               placeholder="https://example.com/feeds/menu.jsonld" style="width:100%">
                    </td>
                    <td>
                        <input type="text" name="catalog_feed_name[]" value="<?php echo esc_attr($name_preview); ?>"
                               placeholder="<?php esc_attr_e('Main menu', 'ofero-generator'); ?>" style="width:100%">
                    </td>
                    <td>
                        <input type="text" name="catalog_feed_standard[]" value="<?php echo esc_attr($f['standard'] ?? ''); ?>"
                               placeholder="schema.org/Menu" style="width:100%">
                    </td>
                    <td>
                        <input type="text" name="catalog_feed_language[]" value="<?php echo esc_attr($f['language'] ?? ''); ?>"
                               maxlength="2" pattern="[a-z]{2}" placeholder="en" style="width:60px">
                    </td>
                    <td>
                        <input type="number" min="0" name="catalog_feed_item_count[]" value="<?php echo esc_attr($f['itemCount'] ?? ''); ?>"
                               style="width:80px">
                    </td>
                    <td>
                        <select name="catalog_feed_audience[]">
                            <?php foreach ($feed_audiences as $value => $label): ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected(($f['audience'] ?? ''), $value); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <button type="button" class="button-link delete catalog-feed-remove" aria-label="<?php esc_attr_e('Remove', 'ofero-generator'); ?>">&times;</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p>
        <button type="button" class="button" id="catalog-feed-add">+ <?php esc_html_e('Add feed', 'ofero-generator'); ?></button>
    </p>

    <p class="description">
        <strong><?php esc_html_e('Recommended formats:', 'ofero-generator'); ?></strong>
        <?php esc_html_e('menus → Schema.org Menu (schema.org-jsonld); products → Google Merchant (google-merchant-xml) or Schema.org Product (schema.org-jsonld); generic → JSON Feed.', 'ofero-generator'); ?>
    </p>
</div>

<?php if ($woo_active): ?>
<div class="ofero-card">
    <h2><?php esc_html_e('WooCommerce products feed', 'ofero-generator'); ?></h2>
    <p class="description">
        <?php
        printf(
            /* translators: %s is the REST URL the plugin exposes */
            wp_kses_post(__('Selected WooCommerce products are exposed as a JSON feed at <code>%s</code>. The plugin automatically adds this URL to <code>catalog.feeds[]</code> on save — no manual entry needed.', 'ofero-generator')),
            esc_html($woo_feed_url)
        );
        ?>
    </p>

    <table class="form-table">
        <tr>
            <th scope="row"><?php esc_html_e('Auto-update on product changes', 'ofero-generator'); ?></th>
            <td>
                <label>
                    <input type="checkbox" name="catalog_auto_sync" value="1"
                           <?php checked(get_option('ofero_generator_catalog_auto_sync', false)); ?>>
                    <?php esc_html_e('Refresh the feed automatically when WooCommerce products change', 'ofero-generator'); ?>
                </label>
            </td>
        </tr>
    </table>

    <h3><?php esc_html_e('Select products for the feed', 'ofero-generator'); ?></h3>
    <p class="description">
        <?php esc_html_e('Only checked products are served at the REST endpoint above.', 'ofero-generator'); ?>
    </p>
    <?php $woo_sync = new Ofero_WooCommerce_Sync(); $woo_sync->render_product_selector(); ?>
</div>
<?php endif; ?>

<div class="ofero-card">
    <h2><?php esc_html_e('Inline previews (signature & highlights)', 'ofero-generator'); ?></h2>
    <p class="description">
        <?php esc_html_e('Tiny, hard-capped previews shown by consumers (e.g., WordPress shortcodes, AI summaries) without having to fetch the external feed. Signature = flagship items (top dishes, hero services). Highlights = portfolio teasers. Max 6 each.', 'ofero-generator'); ?>
    </p>

    <h3><?php esc_html_e('Signature items (max 6)', 'ofero-generator'); ?></h3>
    <?php
    $sig_rows = $signature;
    while (count($sig_rows) < 3) $sig_rows[] = array(); // 3 empty rows by default
    $sig_rows = array_slice($sig_rows, 0, 6);
    foreach ($sig_rows as $s):
        $sname = isset($s['name']) ? $preview_text($s['name']) : '';
        $sdesc = isset($s['description']) ? $preview_text($s['description']) : '';
    ?>
        <div class="ofero-preview-row" style="display:grid;grid-template-columns:1fr 1fr 1fr 80px;gap:8px;margin-bottom:6px;">
            <input type="text" name="catalog_signature_name[]" value="<?php echo esc_attr($sname); ?>" placeholder="<?php esc_attr_e('Name', 'ofero-generator'); ?>">
            <input type="text" name="catalog_signature_category[]" value="<?php echo esc_attr($s['category'] ?? ''); ?>" placeholder="<?php esc_attr_e('Category', 'ofero-generator'); ?>">
            <input type="url" name="catalog_signature_image_url[]" value="<?php echo esc_attr($s['imageUrl'] ?? ''); ?>" placeholder="https://… image">
            <input type="text" name="catalog_signature_price_formatted[]" value="<?php echo esc_attr($s['priceFormatted'] ?? ''); ?>" placeholder="$12">
            <input type="hidden" name="catalog_signature_id[]" value="<?php echo esc_attr($s['id'] ?? ''); ?>">
            <input type="hidden" name="catalog_signature_description[]" value="<?php echo esc_attr($sdesc); ?>">
            <input type="hidden" name="catalog_signature_url[]" value="<?php echo esc_attr($s['url'] ?? ''); ?>">
        </div>
    <?php endforeach; ?>

    <h3 style="margin-top:24px;"><?php esc_html_e('Highlight items (max 6)', 'ofero-generator'); ?></h3>
    <?php
    $hl_rows = $highlights;
    while (count($hl_rows) < 3) $hl_rows[] = array();
    $hl_rows = array_slice($hl_rows, 0, 6);
    foreach ($hl_rows as $h):
        $hname = isset($h['name']) ? $preview_text($h['name']) : '';
        $hdesc = isset($h['description']) ? $preview_text($h['description']) : '';
    ?>
        <div class="ofero-preview-row" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:8px;margin-bottom:6px;">
            <input type="text" name="catalog_highlights_name[]" value="<?php echo esc_attr($hname); ?>" placeholder="<?php esc_attr_e('Project name', 'ofero-generator'); ?>">
            <input type="text" name="catalog_highlights_category[]" value="<?php echo esc_attr($h['category'] ?? ''); ?>" placeholder="<?php esc_attr_e('Category', 'ofero-generator'); ?>">
            <input type="url" name="catalog_highlights_image_url[]" value="<?php echo esc_attr($h['imageUrl'] ?? ''); ?>" placeholder="https://… image">
            <input type="url" name="catalog_highlights_url[]" value="<?php echo esc_attr($h['url'] ?? ''); ?>" placeholder="https://… project page">
            <input type="hidden" name="catalog_highlights_id[]" value="<?php echo esc_attr($h['id'] ?? ''); ?>">
            <input type="hidden" name="catalog_highlights_description[]" value="<?php echo esc_attr($hdesc); ?>">
            <input type="hidden" name="catalog_highlights_price_formatted[]" value="">
        </div>
    <?php endforeach; ?>
</div>

<script>
(function () {
    var addBtn = document.getElementById('catalog-feed-add');
    var rows = document.getElementById('catalog-feeds-rows');
    if (!addBtn || !rows) return;

    addBtn.addEventListener('click', function () {
        var lastRow = rows.querySelector('.catalog-feed-row:last-child');
        if (!lastRow) return;
        var clone = lastRow.cloneNode(true);
        clone.querySelectorAll('input').forEach(function (i) { i.value = ''; });
        clone.querySelectorAll('select').forEach(function (s) { s.selectedIndex = 0; });
        rows.appendChild(clone);
    });

    rows.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('catalog-feed-remove')) {
            var row = e.target.closest('.catalog-feed-row');
            if (row && rows.querySelectorAll('.catalog-feed-row').length > 1) {
                row.remove();
            }
        }
    });
})();
</script>
