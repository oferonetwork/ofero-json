<?php
/**
 * Communications Section Template
 *
 * @package Ofero_Generator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$communications = $data['communications'] ?? array();
$social = $communications['social'] ?? array();
$support = $communications['support'] ?? array();
?>
<div class="ofero-card">
    <h2><?php esc_html_e('Social Media', 'ofero-generator'); ?></h2>
    <p class="description">
        <?php esc_html_e('Add complete URLs to your social media profiles. Always use the full link (e.g., https://facebook.com/yourpage, https://instagram.com/username).', 'ofero-generator'); ?>
    </p>
    <div class="ofero-info-box" style="background: #e7f3ff; border-left: 4px solid #2271b1; padding: 12px; margin-bottom: 15px;">
        <strong><?php esc_html_e('Important:', 'ofero-generator'); ?></strong>
        <ul style="margin: 8px 0 0 20px;">
            <li><?php esc_html_e('Facebook: Use full URL (e.g., https://facebook.com/yourpage or https://facebook.com/profile.php?id=123456)', 'ofero-generator'); ?></li>
            <li><?php esc_html_e('Instagram: Use full URL (e.g., https://instagram.com/username)', 'ofero-generator'); ?></li>
            <li><?php esc_html_e('WhatsApp: Use wa.me link (e.g., https://wa.me/1234567890) - just the phone number with country code', 'ofero-generator'); ?></li>
            <li><?php esc_html_e('X (Twitter): Use full URL (e.g., https://x.com/username or https://twitter.com/username)', 'ofero-generator'); ?></li>
            <li><?php esc_html_e('LinkedIn: Use full URL (e.g., https://linkedin.com/company/name)', 'ofero-generator'); ?></li>
        </ul>
    </div>

    <div id="social-container">
        <?php if (!empty($social)): ?>
            <?php foreach ($social as $i => $item): ?>
                <div class="ofero-repeater-item ofero-repeater-inline" data-index="<?php echo $i; ?>">
                    <div class="ofero-field">
                        <select name="social_platform[]">
                            <?php
                            $platforms = array(
                                'facebook' => 'Facebook',
                                'instagram' => 'Instagram',
                                'x' => 'X (Twitter)',
                                'linkedin' => 'LinkedIn',
                                'youtube' => 'YouTube',
                                'tiktok' => 'TikTok',
                                'discord' => 'Discord',
                                'telegram' => 'Telegram',
                                'github' => 'GitHub',
                                'reddit' => 'Reddit',
                                'pinterest' => 'Pinterest',
                                'snapchat' => 'Snapchat',
                                'whatsapp' => 'WhatsApp',
                                'other' => __('Other', 'ofero-generator')
                            );
                            $currentPlatform = $item['platform'] ?? '';
                            foreach ($platforms as $value => $label):
                            ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($currentPlatform, $value); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="ofero-field ofero-field-wide">
                        <input type="url" name="social_url[]"
                               value="<?php echo esc_url($item['url'] ?? ''); ?>"
                               class="regular-text" placeholder="<?php esc_attr_e('https://platform.com/yourprofile', 'ofero-generator'); ?>">
                    </div>
                    <button type="button" class="button ofero-repeater-remove">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <button type="button" class="button ofero-add-item" data-container="social-container" data-template="social-template">
        <span class="dashicons dashicons-plus-alt2"></span>
        <?php esc_html_e('Add Social Link', 'ofero-generator'); ?>
    </button>
</div>

<div class="ofero-card">
    <h2><?php esc_html_e('Support Channels', 'ofero-generator'); ?></h2>
    <p class="description">
        <?php esc_html_e('Add customer support contact methods.', 'ofero-generator'); ?>
    </p>

    <div id="support-container">
        <?php if (!empty($support)): ?>
            <?php foreach ($support as $i => $item): ?>
                <div class="ofero-repeater-item ofero-repeater-inline" data-index="<?php echo $i; ?>">
                    <div class="ofero-field">
                        <select name="support_type[]">
                            <?php
                            $supportTypes = array(
                                'email' => __('Email', 'ofero-generator'),
                                'phone' => __('Phone', 'ofero-generator'),
                                'chat' => __('Live Chat', 'ofero-generator'),
                                'ticket' => __('Ticket System', 'ofero-generator'),
                                'forum' => __('Forum', 'ofero-generator'),
                                'faq' => __('FAQ', 'ofero-generator'),
                                'other' => __('Other', 'ofero-generator')
                            );
                            $currentType = $item['type'] ?? 'email';
                            foreach ($supportTypes as $value => $label):
                            ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($currentType, $value); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="ofero-field ofero-field-wide">
                        <input type="text" name="support_contact[]"
                               value="<?php echo esc_attr($item['contact'] ?? ''); ?>"
                               class="regular-text" placeholder="<?php esc_attr_e('Contact info or URL', 'ofero-generator'); ?>">
                    </div>
                    <button type="button" class="button ofero-repeater-remove">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <button type="button" class="button ofero-add-item" data-container="support-container" data-template="support-template">
        <span class="dashicons dashicons-plus-alt2"></span>
        <?php esc_html_e('Add Support Channel', 'ofero-generator'); ?>
    </button>
</div>

<!-- Social Template -->
<script type="text/template" id="social-template">
    <div class="ofero-repeater-item ofero-repeater-inline">
        <div class="ofero-field">
            <select name="social_platform[]">
                <option value="facebook">Facebook</option>
                <option value="instagram">Instagram</option>
                <option value="x">X (Twitter)</option>
                <option value="linkedin">LinkedIn</option>
                <option value="youtube">YouTube</option>
                <option value="tiktok">TikTok</option>
                <option value="discord">Discord</option>
                <option value="telegram">Telegram</option>
                <option value="github">GitHub</option>
                <option value="reddit">Reddit</option>
                <option value="pinterest">Pinterest</option>
                <option value="snapchat">Snapchat</option>
                <option value="whatsapp">WhatsApp</option>
                <option value="other"><?php esc_html_e('Other', 'ofero-generator'); ?></option>
            </select>
        </div>
        <div class="ofero-field ofero-field-wide">
            <input type="url" name="social_url[]" class="regular-text" placeholder="<?php esc_attr_e('https://platform.com/yourprofile', 'ofero-generator'); ?>">
        </div>
        <button type="button" class="button ofero-repeater-remove">
            <span class="dashicons dashicons-trash"></span>
        </button>
    </div>
</script>

<!-- Support Template -->
<script type="text/template" id="support-template">
    <div class="ofero-repeater-item ofero-repeater-inline">
        <div class="ofero-field">
            <select name="support_type[]">
                <option value="email"><?php esc_html_e('Email', 'ofero-generator'); ?></option>
                <option value="phone"><?php esc_html_e('Phone', 'ofero-generator'); ?></option>
                <option value="chat"><?php esc_html_e('Live Chat', 'ofero-generator'); ?></option>
                <option value="ticket"><?php esc_html_e('Ticket System', 'ofero-generator'); ?></option>
                <option value="forum"><?php esc_html_e('Forum', 'ofero-generator'); ?></option>
                <option value="faq"><?php esc_html_e('FAQ', 'ofero-generator'); ?></option>
                <option value="other"><?php esc_html_e('Other', 'ofero-generator'); ?></option>
            </select>
        </div>
        <div class="ofero-field ofero-field-wide">
            <input type="text" name="support_contact[]" class="regular-text" placeholder="<?php esc_attr_e('Contact info or URL', 'ofero-generator'); ?>">
        </div>
        <button type="button" class="button ofero-repeater-remove">
            <span class="dashicons dashicons-trash"></span>
        </button>
    </div>
</script>
