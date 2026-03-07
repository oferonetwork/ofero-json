<?php
/**
 * Admin Page Template
 *
 * @package Ofero_Generator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap ofero-generator-wrap">
    <div class="ofero-header">
        <h1><?php esc_html_e('Ofero.json Editor', 'ofero-generator'); ?></h1>

        <?php if (!empty($license_badge)): ?>
            <div class="ofero-license-section">
                <?php echo $license_badge; ?>
                <button type="button" class="button button-small ofero-refresh-license" title="<?php esc_attr_e('Refresh license status', 'ofero-generator'); ?>">
                    <span class="dashicons dashicons-update"></span>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($notice): ?>
        <div class="notice notice-<?php echo esc_attr($notice['type']); ?> is-dismissible">
            <p><?php echo esc_html($notice['message']); ?></p>
        </div>
    <?php endif; ?>

    <!-- Validation Summary -->
    <div class="ofero-validation-summary">
        <div class="ofero-validation-banner <?php echo $validation['is_valid'] ? 'valid' : 'invalid'; ?>">
            <?php if ($validation['is_valid']): ?>
                <span class="dashicons dashicons-yes-alt"></span>
                <?php esc_html_e('ofero.json is valid', 'ofero-generator'); ?>
            <?php else: ?>
                <span class="dashicons dashicons-warning"></span>
                <?php echo sprintf(
                    esc_html__('%d validation issue(s)', 'ofero-generator'),
                    $validation['total_errors']
                ); ?>
            <?php endif; ?>
        </div>
    </div>

    <form method="post" action="" id="ofero-editor-form">
        <?php wp_nonce_field('ofero_generator_save', 'ofero_generator_nonce'); ?>
        <input type="hidden" name="ofero_generator_action" value="save">
        <input type="hidden" name="metadata_createdAt" value="<?php echo esc_attr($data['metadata']['createdAt'] ?? ''); ?>">

        <?php
        $businessType = get_option('ofero_generator_business_type', 'general');
        $showMenu       = in_array($businessType, ['restaurant', 'hotel_restaurant']);
        $showRestaurant = in_array($businessType, ['restaurant', 'hotel_restaurant']);
        $showRooms      = in_array($businessType, ['hotel', 'hotel_restaurant']);
        $showCatalog    = $businessType === 'online_store';
        $showServices   = in_array($businessType, ['clinic', 'auto_service', 'services']);
        ?>

        <!-- Tab Navigation -->
        <nav class="nav-tab-wrapper ofero-tabs" data-business-type="<?php echo esc_attr($businessType); ?>">
            <a href="#tab-basic" class="nav-tab nav-tab-active" data-tab="basic">
                <?php esc_html_e('Basic Info', 'ofero-generator'); ?>
                <?php if (!$validation['sections']['basic']['valid']): ?>
                    <span class="ofero-tab-error"><?php echo $validation['sections']['basic']['errors']; ?></span>
                <?php endif; ?>
            </a>
            <a href="#tab-organization" class="nav-tab" data-tab="organization">
                <?php esc_html_e('Organization', 'ofero-generator'); ?>
                <?php if (!$validation['sections']['organization']['valid']): ?>
                    <span class="ofero-tab-error"><?php echo $validation['sections']['organization']['errors']; ?></span>
                <?php endif; ?>
            </a>
            <a href="#tab-locations" class="nav-tab" data-tab="locations">
                <?php esc_html_e('Locations', 'ofero-generator'); ?>
                <?php if (!$validation['sections']['locations']['valid']): ?>
                    <span class="ofero-tab-error"><?php echo $validation['sections']['locations']['errors']; ?></span>
                <?php endif; ?>
            </a>
            <a href="#tab-banking" class="nav-tab" data-tab="banking">
                <?php esc_html_e('Banking', 'ofero-generator'); ?>
                <?php if (!$validation['sections']['banking']['valid']): ?>
                    <span class="ofero-tab-error"><?php echo $validation['sections']['banking']['errors']; ?></span>
                <?php endif; ?>
            </a>
            <a href="#tab-wallets" class="nav-tab" data-tab="wallets">
                <?php esc_html_e('Wallets', 'ofero-generator'); ?>
                <?php if (!$validation['sections']['wallets']['valid']): ?>
                    <span class="ofero-tab-error"><?php echo $validation['sections']['wallets']['errors']; ?></span>
                <?php endif; ?>
            </a>
            <a href="#tab-branding" class="nav-tab" data-tab="branding">
                <?php esc_html_e('Branding', 'ofero-generator'); ?>
                <?php if (!$validation['sections']['branding']['valid']): ?>
                    <span class="ofero-tab-error"><?php echo $validation['sections']['branding']['errors']; ?></span>
                <?php endif; ?>
            </a>
            <a href="#tab-communications" class="nav-tab" data-tab="communications">
                <?php esc_html_e('Communications', 'ofero-generator'); ?>
                <?php if (!$validation['sections']['communications']['valid']): ?>
                    <span class="ofero-tab-error"><?php echo $validation['sections']['communications']['errors']; ?></span>
                <?php endif; ?>
            </a>

            <?php if ($showMenu): ?>
            <a href="#tab-menu" class="nav-tab ofero-conditional-tab" data-tab="menu" data-requires="restaurant,hotel_restaurant">
                <?php esc_html_e('Menu', 'ofero-generator'); ?>
            </a>
            <?php endif; ?>

            <?php if ($showRestaurant): ?>
            <a href="#tab-restaurant" class="nav-tab ofero-conditional-tab" data-tab="restaurant" data-requires="restaurant,hotel_restaurant">
                <?php esc_html_e('Restaurant Details', 'ofero-generator'); ?>
            </a>
            <?php endif; ?>

            <?php if ($showRooms): ?>
            <a href="#tab-rooms" class="nav-tab ofero-conditional-tab" data-tab="rooms" data-requires="hotel,hotel_restaurant">
                <?php esc_html_e('Rooms', 'ofero-generator'); ?>
            </a>
            <?php endif; ?>

            <?php if ($showCatalog): ?>
            <a href="#tab-catalog" class="nav-tab ofero-conditional-tab" data-tab="catalog" data-requires="online_store">
                <?php esc_html_e('Products / Store', 'ofero-generator'); ?>
                <?php if (Ofero_WooCommerce_Sync::is_woocommerce_active()): ?>
                    <span class="dashicons dashicons-products" style="font-size: 14px; margin-left: 4px;"></span>
                <?php endif; ?>
            </a>
            <?php endif; ?>

            <?php if ($showServices): ?>
            <a href="#tab-services" class="nav-tab ofero-conditional-tab" data-tab="services" data-requires="clinic,auto_service,services">
                <?php esc_html_e('Services', 'ofero-generator'); ?>
            </a>
            <?php endif; ?>

            <a href="#tab-translations" class="nav-tab" data-tab="translations">
                <?php esc_html_e('Translations', 'ofero-generator'); ?>
            </a>
        </nav>

        <!-- Tab: Basic Info -->
        <div id="tab-basic" class="ofero-tab-content active">
            <?php include OFERO_GENERATOR_PATH . 'templates/sections/basic.php'; ?>
        </div>

        <!-- Tab: Organization -->
        <div id="tab-organization" class="ofero-tab-content">
            <?php include OFERO_GENERATOR_PATH . 'templates/sections/organization.php'; ?>
        </div>

        <!-- Tab: Locations -->
        <div id="tab-locations" class="ofero-tab-content">
            <?php include OFERO_GENERATOR_PATH . 'templates/sections/locations.php'; ?>
        </div>

        <!-- Tab: Banking -->
        <div id="tab-banking" class="ofero-tab-content">
            <?php include OFERO_GENERATOR_PATH . 'templates/sections/banking.php'; ?>
        </div>

        <!-- Tab: Wallets -->
        <div id="tab-wallets" class="ofero-tab-content">
            <?php include OFERO_GENERATOR_PATH . 'templates/sections/wallets.php'; ?>
        </div>

        <!-- Tab: Branding -->
        <div id="tab-branding" class="ofero-tab-content">
            <?php include OFERO_GENERATOR_PATH . 'templates/sections/branding.php'; ?>
        </div>

        <!-- Tab: Communications -->
        <div id="tab-communications" class="ofero-tab-content">
            <?php include OFERO_GENERATOR_PATH . 'templates/sections/communications.php'; ?>
        </div>

        <!-- Tab: Menu (restaurant, hotel_restaurant) -->
        <?php if ($showMenu): ?>
        <div id="tab-menu" class="ofero-tab-content">
            <?php include OFERO_GENERATOR_PATH . 'templates/sections/menu.php'; ?>
        </div>
        <?php endif; ?>

        <!-- Tab: Restaurant Details (restaurant, hotel_restaurant) -->
        <?php if ($showRestaurant): ?>
        <div id="tab-restaurant" class="ofero-tab-content">
            <?php include OFERO_GENERATOR_PATH . 'templates/sections/restaurant.php'; ?>
        </div>
        <?php endif; ?>

        <!-- Tab: Rooms (hotel, hotel_restaurant) -->
        <?php if ($showRooms): ?>
        <div id="tab-rooms" class="ofero-tab-content">
            <?php include OFERO_GENERATOR_PATH . 'templates/sections/rooms.php'; ?>
        </div>
        <?php endif; ?>

        <!-- Tab: Products / Store (online_store) -->
        <?php if ($showCatalog): ?>
        <div id="tab-catalog" class="ofero-tab-content">
            <?php include OFERO_GENERATOR_PATH . 'templates/sections/catalog.php'; ?>
        </div>
        <?php endif; ?>

        <!-- Tab: Services (clinic, auto_service, services) -->
        <?php if ($showServices): ?>
        <div id="tab-services" class="ofero-tab-content">
            <?php include OFERO_GENERATOR_PATH . 'templates/sections/services.php'; ?>
        </div>
        <?php endif; ?>

        <!-- Tab: Translations -->
        <div id="tab-translations" class="ofero-tab-content">
            <?php include OFERO_GENERATOR_PATH . 'templates/sections/translations.php'; ?>
        </div>

        <!-- Submit Buttons -->
        <div class="ofero-submit-wrapper">
            <button type="submit" class="button button-primary button-large">
                <?php esc_html_e('Save ofero.json', 'ofero-generator'); ?>
            </button>
            <a href="<?php echo esc_url(admin_url('admin.php?page=ofero-generator-preview')); ?>" class="button button-large">
                <?php esc_html_e('Preview', 'ofero-generator'); ?>
            </a>
            <?php if (is_plugin_active('ofero-shortcodes/ofero-shortcodes.php')): ?>
            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=ofero-generator&clear_shortcode_cache=1'), 'ofero_clear_cache')); ?>" class="button button-large">
                <?php esc_html_e('Clear Shortcode Cache', 'ofero-generator'); ?>
            </a>
            <?php endif; ?>
            <span class="ofero-autosave-status"></span>
        </div>
    </form>
</div>
