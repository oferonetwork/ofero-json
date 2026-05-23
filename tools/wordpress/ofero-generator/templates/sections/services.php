<?php
/**
 * Services Section Template (v2)
 *
 * v2 removes inline services from the schema. Use catalog.feeds[] with
 * type=services or type=packages. See the Catalog tab.
 *
 * @package Ofero_Generator
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="ofero-card">
    <h2><?php esc_html_e('Services (moved in v2)', 'ofero-generator'); ?></h2>

    <div class="notice notice-info inline" style="margin: 12px 0; padding: 12px 14px;">
        <p style="margin: 0 0 8px;">
            <strong><?php esc_html_e('Services and packages now live in external feeds.', 'ofero-generator'); ?></strong>
        </p>
        <p style="margin: 0 0 8px;">
            <?php esc_html_e('ofero.json v2 removed inline catalog.services[] and catalog.packages[]. Publish your service catalog as a JSON or JSON-LD file at a stable HTTPS URL on your site, then add it in the Catalog tab as a catalog.feeds[] entry with type=services or type=packages.', 'ofero-generator'); ?>
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
        <?php esc_html_e('You can keep up to 6 flagship services inline for landing-page rendering. Edit them in the Catalog tab under "Inline previews (signature & highlights)".', 'ofero-generator'); ?>
    </p>
</div>
