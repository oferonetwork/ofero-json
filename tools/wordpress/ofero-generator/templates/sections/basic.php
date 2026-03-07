<?php
/**
 * Basic Info Section Template
 *
 * @package Ofero_Generator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="ofero-card">
    <h2><?php esc_html_e('Business Type', 'ofero-generator'); ?></h2>
    <p class="description" style="margin-bottom: 16px;">
        <?php esc_html_e('Select your business type. This customizes the editor to show only the relevant sections for your type of business.', 'ofero-generator'); ?>
    </p>

    <?php
    $businessType = get_option('ofero_generator_business_type', 'general');
    $businessTypes = array(
        'general'           => array('label' => __('General / Company', 'ofero-generator'),           'icon' => '🏢'),
        'restaurant'        => array('label' => __('Restaurant / Café / Bar', 'ofero-generator'),     'icon' => '🍽️'),
        'hotel'             => array('label' => __('Hotel', 'ofero-generator'),                       'icon' => '🏨'),
        'hotel_restaurant'  => array('label' => __('Hotel + Restaurant', 'ofero-generator'),          'icon' => '🏨🍽️'),
        'online_store'      => array('label' => __('Online Store', 'ofero-generator'),                'icon' => '🛒'),
        'clinic'            => array('label' => __('Clinic / Medical', 'ofero-generator'),            'icon' => '🏥'),
        'auto_service'      => array('label' => __('Auto Service', 'ofero-generator'),                'icon' => '🔧'),
        'services'          => array('label' => __('Services (generic)', 'ofero-generator'),          'icon' => '⚙️'),
    );
    ?>

    <div class="ofero-business-type-grid">
        <?php foreach ($businessTypes as $value => $info): ?>
            <label class="ofero-business-type-card <?php echo $businessType === $value ? 'selected' : ''; ?>">
                <input type="radio" name="ofero_business_type" value="<?php echo esc_attr($value); ?>"
                       <?php checked($businessType, $value); ?> class="ofero-business-type-radio">
                <span class="ofero-business-type-icon"><?php echo $info['icon']; ?></span>
                <span class="ofero-business-type-label"><?php echo esc_html($info['label']); ?></span>
            </label>
        <?php endforeach; ?>
    </div>

    <style>
        .ofero-business-type-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
            margin-top: 8px;
        }
        .ofero-business-type-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 16px 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: border-color .15s, background .15s;
            background: #fff;
        }
        .ofero-business-type-card:hover {
            border-color: #2271b1;
            background: #f0f6fc;
        }
        .ofero-business-type-card.selected,
        .ofero-business-type-card input:checked ~ * {
            border-color: #2271b1;
            background: #f0f6fc;
        }
        .ofero-business-type-card input[type="radio"] {
            display: none;
        }
        .ofero-business-type-icon {
            font-size: 28px;
            line-height: 1;
        }
        .ofero-business-type-label {
            font-size: 13px;
            font-weight: 500;
            color: #1d2327;
            line-height: 1.3;
        }
    </style>
</div>

<div class="ofero-card">
    <h2><?php esc_html_e('Basic Information', 'ofero-generator'); ?></h2>

    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="language"><?php esc_html_e('Language Code', 'ofero-generator'); ?> <span class="required">*</span></label>
            </th>
            <td>
                <input type="text" id="language" name="language"
                       value="<?php echo esc_attr($data['language'] ?? 'en'); ?>"
                       class="small-text" maxlength="2" pattern="[a-z]{2}" required>
                <p class="description">
                    <?php esc_html_e('2-letter ISO code (e.g., "en", "ro", "de")', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="domain"><?php esc_html_e('Domain', 'ofero-generator'); ?> <span class="required">*</span></label>
            </th>
            <td>
                <input type="text" id="domain" name="domain"
                       value="<?php echo esc_attr($data['domain'] ?? ''); ?>"
                       class="regular-text" required>
                <p class="description">
                    <?php esc_html_e('Your domain without protocol (e.g., "example.com")', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="canonicalUrl"><?php esc_html_e('Canonical URL', 'ofero-generator'); ?> <span class="required">*</span></label>
            </th>
            <td>
                <input type="url" id="canonicalUrl" name="canonicalUrl"
                       value="<?php echo esc_url($data['canonicalUrl'] ?? ''); ?>"
                       class="large-text" required>
                <p class="description">
                    <?php esc_html_e('Full URL to your ofero.json file', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="metadata_version"><?php esc_html_e('Version', 'ofero-generator'); ?> <span class="required">*</span></label>
            </th>
            <td>
                <input type="text" id="metadata_version" name="metadata_version"
                       value="<?php echo esc_attr($data['metadata']['version'] ?? '1.0.0'); ?>"
                       class="small-text" required>
                <p class="description">
                    <?php esc_html_e('Semantic version of your ofero.json (e.g., "1.0.0")', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="keywords"><?php esc_html_e('Keywords', 'ofero-generator'); ?></label>
            </th>
            <td>
                <input type="hidden" id="keywords" name="keywords"
                       value="<?php echo esc_attr(is_array($data['keywords'] ?? null) ? ($data['keywords']['default'] ?? '') : ($data['keywords'] ?? '')); ?>">
                <div class="ofero-tags-field" id="keywords-tags-field">
                    <div class="ofero-tags-list" id="keywords-tags-list"></div>
                    <input type="text" class="ofero-tags-input" id="keywords-tags-input"
                           placeholder="<?php esc_attr_e('Type a keyword and press Enter or comma...', 'ofero-generator'); ?>">
                </div>
                <p class="description" style="margin-top: 8px;">
                    <?php esc_html_e('Type a keyword and press Enter or comma to add it. Click × to remove. Duplicates are ignored automatically.', 'ofero-generator'); ?>
                    <br><strong><?php esc_html_e('Example:', 'ofero-generator'); ?></strong>
                    <code>laptop</code> → <code>laptops</code> → <code>california laptop</code> → <code>california laptops</code>
                </p>
                <p class="description" style="margin-top: 6px;">
                    <strong><?php esc_html_e('Tips for effective keywords:', 'ofero-generator'); ?></strong>
                    <?php esc_html_e('Always put your most important keyword first — it carries the highest weight. The weight decreases with each position: with 1 keyword it gets 100 pts, with 2 keywords the first gets ~55 pts and the second ~45 pts, with 3 keywords it\'s ~40, ~35, ~25 pts, and so on. Only add keywords that describe your business as a whole, not individual products (those belong in the Catalog section). For service businesses and brands, 8–12 focused keywords work best. For retail and e-commerce stores with multiple product categories, up to 20 keywords may be appropriate. Every extra keyword reduces the weight of all others, so only add what is truly relevant.', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
    </table>
</div>

<div class="ofero-card">
    <h2><?php esc_html_e('Metadata', 'ofero-generator'); ?></h2>

    <table class="form-table">
        <tr>
            <th scope="row"><?php esc_html_e('Schema Version', 'ofero-generator'); ?></th>
            <td>
                <code>ofero-metadata-1.0</code>
                <p class="description">
                    <?php esc_html_e('This is set automatically.', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e('Created At', 'ofero-generator'); ?></th>
            <td>
                <code><?php echo esc_html($data['metadata']['createdAt'] ?? __('Will be set on first save', 'ofero-generator')); ?></code>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e('Last Updated', 'ofero-generator'); ?></th>
            <td>
                <code><?php echo esc_html($data['metadata']['lastUpdated'] ?? __('Will be set on save', 'ofero-generator')); ?></code>
                <p class="description">
                    <?php esc_html_e('This is updated automatically on each save.', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
    </table>
</div>
