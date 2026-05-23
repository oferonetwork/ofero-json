<?php
/**
 * Menu Section Template (v2)
 *
 * v2 removes inline menus from the schema — full menu data must live in an
 * external feed referenced from catalog.feeds[]. This tab is kept as a
 * redirect/explainer so existing users land somewhere meaningful when they
 * click "Menu".
 *
 * @package Ofero_Generator
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="ofero-card">
    <h2><?php esc_html_e('Menu (moved in v2)', 'ofero-generator'); ?></h2>

    <div class="notice notice-info inline" style="margin: 12px 0; padding: 12px 14px;">
        <p style="margin: 0 0 8px;">
            <strong><?php esc_html_e('Menus now live in external feeds.', 'ofero-generator'); ?></strong>
        </p>
        <p style="margin: 0 0 8px;">
            <?php esc_html_e('ofero.json v2 removed the inline menu editor (catalog.menu, catalog.dailyMenu, menu categories with items). A full restaurant menu can be hundreds of items, which would push ofero.json over the recommended 100 KB size and slow down every consumer that re-fetches the file.', 'ofero-generator'); ?>
        </p>
        <p style="margin: 0 0 8px;">
            <?php esc_html_e('Instead, publish your menu as a Schema.org Menu JSON-LD file at a stable HTTPS URL on your site (e.g., https://example.com/feeds/menu.jsonld), then add it as a catalog.feeds[] entry in the Catalog tab.', 'ofero-generator'); ?>
        </p>
        <p style="margin: 0;">
            <a class="button button-primary" href="#" onclick="document.querySelector('[data-section=&quot;catalog&quot;]')?.click(); return false;">
                <?php esc_html_e('Go to Catalog tab', 'ofero-generator'); ?>
            </a>
            <a class="button" href="https://github.com/oferonetwork/ofero-json/blob/main/docs/MIGRATION-v1-to-v2.md" target="_blank" rel="noopener">
                <?php esc_html_e('Read migration guide', 'ofero-generator'); ?>
            </a>
        </p>
    </div>

    <h3 style="margin-top:24px;"><?php esc_html_e('Inline previews', 'ofero-generator'); ?></h3>
    <p class="description">
        <?php esc_html_e('You can still keep up to 6 signature dishes inline for landing-page rendering. Edit them in the Catalog tab under "Inline previews (signature & highlights)".', 'ofero-generator'); ?>
    </p>
</div>
