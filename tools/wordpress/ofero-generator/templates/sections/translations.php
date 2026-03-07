<?php
/**
 * Translations Section Template
 *
 * @package Ofero_Generator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Available languages (ISO 639-1 codes) - comprehensive list
$available_languages = array(
    // Major European Languages
    'en' => 'English',
    'ro' => 'Română',
    'de' => 'Deutsch',
    'fr' => 'Français',
    'es' => 'Español',
    'it' => 'Italiano',
    'pt' => 'Português',
    'nl' => 'Nederlands',
    'pl' => 'Polski',
    'cs' => 'Čeština',
    'sk' => 'Slovenčina',
    'hu' => 'Magyar',
    'bg' => 'Български',
    'hr' => 'Hrvatski',
    'sl' => 'Slovenščina',
    'sr' => 'Српски',
    'bs' => 'Bosanski',
    'mk' => 'Македонски',
    'sq' => 'Shqip',
    'el' => 'Ελληνικά',
    'ru' => 'Русский',
    'uk' => 'Українська',
    'be' => 'Беларуская',
    'lt' => 'Lietuvių',
    'lv' => 'Latviešu',
    'et' => 'Eesti',
    'fi' => 'Suomi',
    'sv' => 'Svenska',
    'no' => 'Norsk',
    'da' => 'Dansk',
    'is' => 'Íslenska',
    'ga' => 'Gaeilge',
    'cy' => 'Cymraeg',
    'mt' => 'Malti',
    'lb' => 'Lëtzebuergesch',
    'ca' => 'Català',
    'gl' => 'Galego',
    'eu' => 'Euskara',

    // Middle East & Central Asia
    'tr' => 'Türkçe',
    'ar' => 'العربية',
    'he' => 'עברית',
    'fa' => 'فارسی',
    'ur' => 'اردو',
    'az' => 'Azərbaycan',
    'ka' => 'ქართული',
    'hy' => 'Հայերեն',
    'kk' => 'Қазақша',
    'uz' => 'Oʻzbek',
    'tg' => 'Тоҷикӣ',
    'ky' => 'Кыргызча',
    'tk' => 'Türkmençe',

    // East Asia
    'zh' => '中文 (Chinese)',
    'ja' => '日本語 (Japanese)',
    'ko' => '한국어 (Korean)',
    'mn' => 'Монгол',

    // South & Southeast Asia
    'hi' => 'हिन्दी',
    'bn' => 'বাংলা',
    'pa' => 'ਪੰਜਾਬੀ',
    'gu' => 'ગુજરાતી',
    'mr' => 'मराठी',
    'ta' => 'தமிழ்',
    'te' => 'తెలుగు',
    'kn' => 'ಕನ್ನಡ',
    'ml' => 'മലയാളം',
    'si' => 'සිංහල',
    'ne' => 'नेपाली',
    'th' => 'ไทย',
    'vi' => 'Tiếng Việt',
    'id' => 'Bahasa Indonesia',
    'ms' => 'Bahasa Melayu',
    'tl' => 'Tagalog',
    'my' => 'မြန်မာဘာသာ',
    'km' => 'ភាសាខ្មែរ',
    'lo' => 'ລາວ',

    // Africa
    'sw' => 'Kiswahili',
    'am' => 'አማርኛ',
    'ha' => 'Hausa',
    'yo' => 'Yorùbá',
    'ig' => 'Igbo',
    'zu' => 'isiZulu',
    'xh' => 'isiXhosa',
    'af' => 'Afrikaans',
    'so' => 'Soomaali',
    'rw' => 'Ikinyarwanda',

    // Americas (Indigenous)
    'qu' => 'Quechua',
    'ay' => 'Aymar aru',
    'gn' => 'Avañeʼẽ',

    // Other
    'eo' => 'Esperanto',
    'la' => 'Latina',
);

// Sort languages alphabetically by name (keeping English first)
$english = array('en' => $available_languages['en']);
unset($available_languages['en']);
asort($available_languages);
$available_languages = $english + $available_languages;

// Get currently enabled languages from data
$enabled_languages = array();
if (!empty($data['_translations']['enabled_languages'])) {
    $enabled_languages = $data['_translations']['enabled_languages'];
}

// Helper function to get translation value
function ofero_get_translation($data, $field_path, $lang) {
    $parts = explode('.', $field_path);
    $value = $data;

    foreach ($parts as $part) {
        if (isset($value[$part])) {
            $value = $value[$part];
        } else {
            return '';
        }
    }

    // Check if it's a TranslatableString structure
    if (is_array($value) && isset($value['translations'][$lang])) {
        return $value['translations'][$lang];
    }

    return '';
}

// Helper function to get default value
function ofero_get_default($data, $field_path) {
    $parts = explode('.', $field_path);
    $value = $data;

    foreach ($parts as $part) {
        if (isset($value[$part])) {
            $value = $value[$part];
        } else {
            return '';
        }
    }

    // Check if it's a TranslatableString structure
    if (is_array($value) && isset($value['default'])) {
        return $value['default'];
    }

    // Plain string
    if (is_string($value)) {
        return $value;
    }

    return '';
}

// Translatable fields definition
$translatable_fields = array(
    'organization' => array(
        'label' => __('Organization', 'ofero-generator'),
        'fields' => array(
            'organization.brandName' => array(
                'label' => __('Brand Name', 'ofero-generator'),
                'type' => 'text',
            ),
            'organization.description' => array(
                'label' => __('Description', 'ofero-generator'),
                'type' => 'textarea',
            ),
        ),
    ),
    'general' => array(
        'label' => __('General', 'ofero-generator'),
        'fields' => array(
            'keywords' => array(
                'label' => __('Keywords', 'ofero-generator'),
                'type' => 'text',
                'description' => __('Comma-separated keywords for AI search indexing', 'ofero-generator'),
            ),
        ),
    ),
);
?>

<div class="ofero-card">
    <h2><?php esc_html_e('Translation Settings', 'ofero-generator'); ?></h2>

    <p class="description">
        <?php esc_html_e('Enable multi-language support for your ofero.json file. Select which languages you want to support, then provide translations for the translatable fields below.', 'ofero-generator'); ?>
    </p>

    <table class="form-table">
        <tr>
            <th scope="row">
                <label><?php esc_html_e('Primary Language', 'ofero-generator'); ?></label>
            </th>
            <td>
                <code><?php echo esc_html($data['language'] ?? 'en'); ?></code>
                <p class="description">
                    <?php esc_html_e('This is set in the Basic Info tab. All default values should be in this language.', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label><?php esc_html_e('Additional Languages', 'ofero-generator'); ?></label>
            </th>
            <td>
                <div class="ofero-language-search">
                    <input type="text"
                           id="ofero-language-filter"
                           placeholder="<?php esc_attr_e('Search languages...', 'ofero-generator'); ?>"
                           class="regular-text">
                    <div class="ofero-quick-select-buttons">
                        <span class="ofero-quick-label"><?php esc_html_e('Quick select:', 'ofero-generator'); ?></span>
                        <a href="#" class="button button-small ofero-quick-lang-select" data-langs="ro,de,fr,es,it,pt,nl,pl,hu,bg">
                            <?php esc_html_e('EU Common', 'ofero-generator'); ?>
                        </a>
                        <a href="#" class="button button-small ofero-quick-lang-select" data-langs="de,fr,es,it,pt,nl">
                            <?php esc_html_e('Western EU', 'ofero-generator'); ?>
                        </a>
                        <a href="#" class="button button-small ofero-quick-lang-select" data-langs="ro,hu,bg,pl,cs,sk,hr,sl">
                            <?php esc_html_e('Eastern EU', 'ofero-generator'); ?>
                        </a>
                        <a href="#" class="button button-small ofero-quick-lang-select" data-langs="sv,no,da,fi">
                            <?php esc_html_e('Nordic', 'ofero-generator'); ?>
                        </a>
                        <a href="#" id="ofero-select-all-langs" class="button button-small">
                            <?php esc_html_e('Select All Visible', 'ofero-generator'); ?>
                        </a>
                        <a href="#" id="ofero-deselect-all-langs" class="button button-small">
                            <?php esc_html_e('Clear All', 'ofero-generator'); ?>
                        </a>
                    </div>
                </div>
                <fieldset>
                    <div class="ofero-language-grid" id="ofero-language-list">
                        <?php foreach ($available_languages as $code => $name): ?>
                            <?php
                            // Skip primary language
                            if ($code === ($data['language'] ?? 'en')) continue;
                            ?>
                            <label class="ofero-language-checkbox" data-lang-name="<?php echo esc_attr(strtolower($name . ' ' . $code)); ?>">
                                <input type="checkbox"
                                       name="translation_languages[]"
                                       value="<?php echo esc_attr($code); ?>"
                                       <?php checked(in_array($code, $enabled_languages)); ?>
                                       class="ofero-lang-toggle"
                                       data-lang="<?php echo esc_attr($code); ?>">
                                <span class="ofero-lang-code"><?php echo esc_html($code); ?></span>
                                <span class="ofero-lang-name"><?php echo esc_html($name); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <p class="description">
                    <?php
                    printf(
                        esc_html__('Select the languages you want to provide translations for. %d languages available.', 'ofero-generator'),
                        count($available_languages) - 1
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>
</div>

<div class="ofero-card" id="ofero-translations-container">
    <h2><?php esc_html_e('Translations', 'ofero-generator'); ?></h2>

    <p class="description ofero-no-languages-message" <?php echo !empty($enabled_languages) ? 'style="display:none;"' : ''; ?>>
        <?php esc_html_e('Please select at least one additional language above to add translations.', 'ofero-generator'); ?>
    </p>

    <div class="ofero-translations-content" <?php echo empty($enabled_languages) ? 'style="display:none;"' : ''; ?>>
        <?php foreach ($translatable_fields as $section_key => $section): ?>
            <div class="ofero-translation-section">
                <h3><?php echo esc_html($section['label']); ?></h3>

                <?php foreach ($section['fields'] as $field_path => $field): ?>
                    <div class="ofero-translation-field">
                        <h4><?php echo esc_html($field['label']); ?></h4>

                        <?php if (!empty($field['description'])): ?>
                            <p class="description"><?php echo esc_html($field['description']); ?></p>
                        <?php endif; ?>

                        <!-- Default value (read-only reference) -->
                        <div class="ofero-translation-default">
                            <label><?php esc_html_e('Default value:', 'ofero-generator'); ?></label>
                            <div class="ofero-default-value">
                                <?php
                                $default_value = ofero_get_default($data, $field_path);
                                echo esc_html($default_value ?: __('(not set - fill in Organization tab first)', 'ofero-generator'));
                                ?>
                            </div>
                        </div>

                        <!-- Translation inputs for each enabled language -->
                        <div class="ofero-translation-inputs">
                            <?php foreach ($available_languages as $code => $name): ?>
                                <?php
                                // Skip primary language
                                if ($code === ($data['language'] ?? 'en')) continue;

                                $is_enabled = in_array($code, $enabled_languages);
                                $field_name = 'translation_' . str_replace('.', '_', $field_path) . '_' . $code;
                                $current_value = ofero_get_translation($data, $field_path, $code);
                                ?>
                                <div class="ofero-translation-row ofero-lang-row-<?php echo esc_attr($code); ?>"
                                     <?php echo !$is_enabled ? 'style="display:none;"' : ''; ?>>
                                    <label for="<?php echo esc_attr($field_name); ?>">
                                        <span class="ofero-lang-flag"><?php echo esc_html(strtoupper($code)); ?></span>
                                        <?php echo esc_html($name); ?>
                                    </label>

                                    <?php if ($field['type'] === 'textarea'): ?>
                                        <textarea id="<?php echo esc_attr($field_name); ?>"
                                                  name="<?php echo esc_attr($field_name); ?>"
                                                  rows="3"
                                                  class="large-text"
                                                  placeholder="<?php echo esc_attr(sprintf(__('Enter %s translation...', 'ofero-generator'), $name)); ?>"
                                        ><?php echo esc_textarea($current_value); ?></textarea>
                                    <?php else: ?>
                                        <input type="text"
                                               id="<?php echo esc_attr($field_name); ?>"
                                               name="<?php echo esc_attr($field_name); ?>"
                                               value="<?php echo esc_attr($current_value); ?>"
                                               class="regular-text"
                                               placeholder="<?php echo esc_attr(sprintf(__('Enter %s translation...', 'ofero-generator'), $name)); ?>">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.ofero-language-search {
    margin-bottom: 15px;
}

.ofero-language-search input {
    width: 100%;
    max-width: 300px;
    margin-bottom: 10px;
}

.ofero-quick-select-buttons {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
}

.ofero-quick-label {
    font-size: 12px;
    color: #666;
    font-weight: 500;
}

.ofero-quick-select-buttons .button-small {
    font-size: 11px;
    padding: 2px 8px;
    height: auto;
    line-height: 1.6;
}

.ofero-language-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 8px;
    margin-bottom: 10px;
    max-height: 300px;
    overflow-y: auto;
    padding: 10px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.ofero-language-checkbox {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.ofero-language-checkbox:hover {
    background: #f0f0f1;
    border-color: #2271b1;
}

.ofero-language-checkbox:has(input:checked) {
    background: #e7f3ff;
    border-color: #2271b1;
}

.ofero-lang-code {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 20px;
    background: #2271b1;
    color: #fff;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    border-radius: 3px;
}

.ofero-lang-name {
    flex: 1;
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ofero-translation-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #ddd;
}

.ofero-translation-section:last-child {
    border-bottom: none;
}

.ofero-translation-section h3 {
    margin-top: 0;
    color: #1d2327;
}

.ofero-translation-field {
    margin-bottom: 20px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 6px;
}

.ofero-translation-field h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #1d2327;
}

.ofero-translation-default {
    margin-bottom: 15px;
    padding: 10px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.ofero-translation-default label {
    display: block;
    font-size: 11px;
    text-transform: uppercase;
    color: #666;
    margin-bottom: 4px;
}

.ofero-default-value {
    color: #1d2327;
    font-style: italic;
}

.ofero-translation-inputs {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.ofero-translation-row {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.ofero-translation-row label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
    font-size: 13px;
}

.ofero-lang-flag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 20px;
    background: #2271b1;
    color: #fff;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    border-radius: 3px;
}

.ofero-translation-row input,
.ofero-translation-row textarea {
    width: 100%;
    max-width: 100%;
}

.ofero-no-languages-message {
    padding: 20px;
    text-align: center;
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 4px;
    color: #856404;
}

/* RTL language support indicator */
.ofero-language-checkbox[data-lang-name*="arabic"],
.ofero-language-checkbox[data-lang-name*="hebrew"],
.ofero-language-checkbox[data-lang-name*="urdu"],
.ofero-language-checkbox[data-lang-name*="farsi"],
.ofero-language-checkbox[data-lang-name*="persian"] {
    direction: ltr; /* Keep checkbox layout LTR */
}

.ofero-language-checkbox[data-lang-name*="arabic"] .ofero-lang-name,
.ofero-language-checkbox[data-lang-name*="hebrew"] .ofero-lang-name,
.ofero-language-checkbox[data-lang-name*="urdu"] .ofero-lang-name,
.ofero-language-checkbox[data-lang-name*="farsi"] .ofero-lang-name,
.ofero-language-checkbox[data-lang-name*="persian"] .ofero-lang-name {
    direction: rtl;
}
</style>
