<?php
/**
 * Locations Section Template
 *
 * @package Ofero_Generator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$locations = $data['locations'] ?? array();
?>
<div class="ofero-card">
    <h2><?php esc_html_e('Locations', 'ofero-generator'); ?></h2>
    <p class="description">
        <?php esc_html_e('Add physical locations for your organization (headquarters, branches, stores, etc.)', 'ofero-generator'); ?>
    </p>

    <div id="locations-container">
        <?php if (!empty($locations)): ?>
            <?php foreach ($locations as $i => $location): ?>
                <div class="ofero-repeater-item" data-index="<?php echo $i; ?>">
                    <div class="ofero-repeater-header">
                        <span class="ofero-repeater-title">
                            <?php echo esc_html($location['name'] ?? sprintf(__('Location %d', 'ofero-generator'), $i + 1)); ?>
                        </span>
                        <button type="button" class="button ofero-repeater-remove">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                    <div class="ofero-repeater-content">

                        <!-- Address fields -->
                        <div class="ofero-field-row">
                            <div class="ofero-field">
                                <label><?php esc_html_e('Name', 'ofero-generator'); ?></label>
                                <input type="text" name="location_name[]"
                                       value="<?php echo esc_attr($location['name'] ?? ''); ?>"
                                       class="regular-text">
                            </div>
                            <div class="ofero-field">
                                <label><?php esc_html_e('Type', 'ofero-generator'); ?></label>
                                <select name="location_type[]">
                                    <?php
                                    $locationTypes = array(
                                        'headquarters' => __('Headquarters', 'ofero-generator'),
                                        'branch' => __('Branch', 'ofero-generator'),
                                        'store' => __('Store', 'ofero-generator'),
                                        'warehouse' => __('Warehouse', 'ofero-generator'),
                                        'office' => __('Office', 'ofero-generator'),
                                        'factory' => __('Factory', 'ofero-generator'),
                                        'distribution_center' => __('Distribution Center', 'ofero-generator')
                                    );
                                    $currentType = $location['type'] ?? 'headquarters';
                                    foreach ($locationTypes as $value => $label):
                                    ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($currentType, $value); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="ofero-field-row">
                            <div class="ofero-field ofero-field-wide">
                                <label><?php esc_html_e('Street Address', 'ofero-generator'); ?></label>
                                <input type="text" name="location_street[]"
                                       value="<?php echo esc_attr($location['address']['street'] ?? ''); ?>"
                                       class="large-text">
                            </div>
                        </div>
                        <div class="ofero-field-row">
                            <div class="ofero-field">
                                <label><?php esc_html_e('City', 'ofero-generator'); ?></label>
                                <input type="text" name="location_city[]"
                                       value="<?php echo esc_attr($location['address']['city'] ?? ''); ?>"
                                       class="regular-text">
                            </div>
                            <div class="ofero-field">
                                <label><?php esc_html_e('Region / State', 'ofero-generator'); ?></label>
                                <input type="text" name="location_region[]"
                                       value="<?php echo esc_attr($location['address']['region'] ?? ''); ?>"
                                       class="regular-text"
                                       placeholder="<?php esc_attr_e('State, province, or region', 'ofero-generator'); ?>">
                            </div>
                        </div>
                        <div class="ofero-field-row">
                            <div class="ofero-field">
                                <label><?php esc_html_e('Postal Code', 'ofero-generator'); ?></label>
                                <input type="text" name="location_postal[]"
                                       value="<?php echo esc_attr($location['address']['postalCode'] ?? ''); ?>"
                                       class="regular-text">
                            </div>
                            <div class="ofero-field">
                                <label><?php esc_html_e('Country', 'ofero-generator'); ?></label>
                                <input type="text" name="location_country[]"
                                       value="<?php echo esc_attr($location['address']['country'] ?? ''); ?>"
                                       class="small-text" maxlength="2" style="text-transform: uppercase;">
                                <p class="description">
                                    <?php esc_html_e('2-letter ISO code (e.g., US, GB, RO)', 'ofero-generator'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="ofero-field-row">
                            <div class="ofero-field">
                                <label><?php esc_html_e('Phone', 'ofero-generator'); ?></label>
                                <input type="tel" name="location_phone[]"
                                       value="<?php echo esc_attr($location['phone'] ?? ''); ?>"
                                       class="regular-text">
                            </div>
                            <div class="ofero-field">
                                <label><?php esc_html_e('Email', 'ofero-generator'); ?></label>
                                <input type="email" name="location_email[]"
                                       value="<?php echo esc_attr($location['email'] ?? ''); ?>"
                                       class="regular-text">
                            </div>
                        </div>
                        <div class="ofero-field-row">
                            <div class="ofero-field ofero-field-wide">
                                <label><?php esc_html_e('Location Photos (URLs)', 'ofero-generator'); ?></label>
                                <textarea name="location_photos[]" class="large-text" rows="3"
                                          placeholder="<?php esc_attr_e('One HTTPS URL per line, e.g. https://example.com/photo1.jpg', 'ofero-generator'); ?>"><?php echo esc_textarea(implode("\n", $location['photos'] ?? [])); ?></textarea>
                                <p class="description"><?php esc_html_e('Photos of this location (storefronts, interiors, etc.). One URL per line.', 'ofero-generator'); ?></p>
                            </div>
                        </div>

                        <!-- Special Hours -->
                        <div style="margin-top: 20px; border: 1px solid #c3d0e8; border-radius: 4px; background: #f0f4fb; padding: 14px 16px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                <div style="font-weight: 600; color: #1d2327; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                                    <span class="dashicons dashicons-calendar-alt" style="color: #7986cb; font-size: 16px; width: 16px; height: 16px;"></span>
                                    <?php esc_html_e('Special Hours', 'ofero-generator'); ?>
                                    <span style="font-weight: 400; color: #646970; font-size: 12px;"><?php esc_html_e('(optional — overrides regular hours for holidays or seasonal schedules)', 'ofero-generator'); ?></span>
                                </div>
                                <button type="button" class="button ofero-add-special-hour" data-location-index="<?php echo $i; ?>"
                                        style="border-color: #7986cb; color: #7986cb; background: #fff;">
                                    <span class="dashicons dashicons-plus-alt2" style="margin-top: 3px;"></span>
                                    <?php esc_html_e('Add Entry', 'ofero-generator'); ?>
                                </button>
                            </div>
                            <div class="location-special-hours-container" data-location-index="<?php echo $i; ?>">
                                <?php if (empty($location['specialHours'])): ?>
                                <div class="ofero-special-hours-empty" style="text-align: center; padding: 12px 0 6px; color: #8c8f94; font-size: 12px; font-style: italic;">
                                    <?php esc_html_e('No special hours added yet.', 'ofero-generator'); ?>
                                </div>
                                <?php else: ?>
                                <?php foreach ($location['specialHours'] as $si => $entry): ?>
                                <div class="ofero-special-hour-item" style="border: 1px solid #c3d0e8; border-left: 3px solid #7986cb; padding: 10px 12px; margin-bottom: 8px; background: #fff; border-radius: 3px;">
                                    <div style="display: flex; justify-content: flex-end; margin-bottom: 6px;">
                                        <button type="button" class="button-link ofero-remove-special-hour" style="color: #b32d2e; text-decoration: none; font-size: 12px;">
                                            <span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span>
                                            <?php esc_html_e('Remove', 'ofero-generator'); ?>
                                        </button>
                                    </div>
                                    <div class="ofero-field-row">
                                        <div class="ofero-field">
                                            <label><?php esc_html_e('Type', 'ofero-generator'); ?></label>
                                            <select class="ofero-special-hour-type">
                                                <option value="date" <?php selected(!isset($entry['from'])); ?>><?php esc_html_e('Single day', 'ofero-generator'); ?></option>
                                                <option value="range" <?php selected(isset($entry['from'])); ?>><?php esc_html_e('Date range', 'ofero-generator'); ?></option>
                                            </select>
                                        </div>
                                        <div class="ofero-field ofero-special-hour-date-field" <?php echo isset($entry['from']) ? 'style="display:none;"' : ''; ?>>
                                            <label><?php esc_html_e('Date', 'ofero-generator'); ?></label>
                                            <input type="date" class="ofero-special-hour-date regular-text"
                                                   value="<?php echo esc_attr($entry['date'] ?? ''); ?>">
                                        </div>
                                        <div class="ofero-field ofero-special-hour-from-field" <?php echo !isset($entry['from']) ? 'style="display:none;"' : ''; ?>>
                                            <label><?php esc_html_e('From', 'ofero-generator'); ?></label>
                                            <input type="date" class="ofero-special-hour-from regular-text"
                                                   value="<?php echo esc_attr($entry['from'] ?? ''); ?>">
                                        </div>
                                        <div class="ofero-field ofero-special-hour-to-field" <?php echo !isset($entry['from']) ? 'style="display:none;"' : ''; ?>>
                                            <label><?php esc_html_e('To', 'ofero-generator'); ?></label>
                                            <input type="date" class="ofero-special-hour-to regular-text"
                                                   value="<?php echo esc_attr($entry['to'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="ofero-field-row">
                                        <div class="ofero-field">
                                            <label><?php esc_html_e('Name / Label', 'ofero-generator'); ?></label>
                                            <input type="text" class="ofero-special-hour-name regular-text"
                                                   value="<?php echo esc_attr($entry['name'] ?? ''); ?>"
                                                   placeholder="<?php esc_attr_e('e.g. Christmas Day, Summer Schedule', 'ofero-generator'); ?>">
                                        </div>
                                        <div class="ofero-field">
                                            <label><?php esc_html_e('Hours', 'ofero-generator'); ?></label>
                                            <input type="text" class="ofero-special-hour-hours regular-text"
                                                   value="<?php echo esc_attr($entry['hours'] ?? ''); ?>"
                                                   placeholder="<?php esc_attr_e('e.g. 10:00-14:00 or Closed', 'ofero-generator'); ?>">
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="location_special_hours[]"
                                   class="ofero-special-hours-json"
                                   value="<?php echo esc_attr(wp_json_encode($location['specialHours'] ?? [])); ?>">
                        </div>

                        <!-- Contact Persons -->
                        <div style="margin-top: 20px; border: 1px solid #c3d0e8; border-radius: 4px; background: #f0f4fb; padding: 14px 16px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                <div style="font-weight: 600; color: #1d2327; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                                    <span class="dashicons dashicons-id" style="color: #7986cb; font-size: 16px; width: 16px; height: 16px;"></span>
                                    <?php esc_html_e('Contact Persons', 'ofero-generator'); ?>
                                    <span style="font-weight: 400; color: #646970; font-size: 12px;"><?php esc_html_e('(optional — who to contact at this location)', 'ofero-generator'); ?></span>
                                </div>
                                <button type="button" class="button ofero-add-contact" data-location-index="<?php echo $i; ?>"
                                        style="border-color: #7986cb; color: #7986cb; background: #fff;">
                                    <span class="dashicons dashicons-plus-alt2" style="margin-top: 3px;"></span>
                                    <?php esc_html_e('Add Contact Person', 'ofero-generator'); ?>
                                </button>
                            </div>
                            <div class="location-contacts-container" data-location-index="<?php echo $i; ?>">
                                <?php if (empty($location['contacts'])): ?>
                                <div class="ofero-contacts-empty" style="text-align: center; padding: 16px 0 8px; color: #8c8f94; font-size: 12px; font-style: italic;">
                                    <span class="dashicons dashicons-groups" style="font-size: 24px; width: 24px; height: 24px; display: block; margin: 0 auto 6px; color: #c3d0e8;"></span>
                                    <?php esc_html_e('No contact persons added yet. Use the button above to add one.', 'ofero-generator'); ?>
                                </div>
                                <?php else: ?>
                                <?php foreach ($location['contacts'] as $ci => $contact): ?>
                                <div class="ofero-contact-item" style="border: 1px solid #c3d0e8; border-left: 3px solid #7986cb; padding: 12px; margin-bottom: 8px; background: #fff; border-radius: 3px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                        <strong style="font-size: 12px; color: #7986cb;">
                                            <span class="dashicons dashicons-admin-users" style="font-size: 13px; width: 13px; height: 13px; vertical-align: middle;"></span>
                                            <?php echo esc_html(!empty($contact['name']) ? $contact['name'] : __('Contact Person', 'ofero-generator')); ?>
                                        </strong>
                                        <button type="button" class="button-link ofero-remove-contact" style="color: #b32d2e; text-decoration: none;">
                                            <span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span>
                                            <?php esc_html_e('Remove', 'ofero-generator'); ?>
                                        </button>
                                    </div>
                                    <div class="ofero-field-row">
                                        <div class="ofero-field">
                                            <label><?php esc_html_e('Name', 'ofero-generator'); ?></label>
                                            <input type="text" name="location_contact_name[<?php echo $i; ?>][]"
                                                   value="<?php echo esc_attr($contact['name'] ?? ''); ?>"
                                                   class="regular-text">
                                        </div>
                                        <div class="ofero-field">
                                            <label><?php esc_html_e('Role', 'ofero-generator'); ?></label>
                                            <input type="text" name="location_contact_role[<?php echo $i; ?>][]"
                                                   value="<?php echo esc_attr($contact['role'] ?? ''); ?>"
                                                   class="regular-text" placeholder="<?php esc_attr_e('e.g. Store Manager', 'ofero-generator'); ?>">
                                        </div>
                                    </div>
                                    <div class="ofero-field-row">
                                        <div class="ofero-field">
                                            <label><?php esc_html_e('Email', 'ofero-generator'); ?></label>
                                            <input type="email" name="location_contact_email[<?php echo $i; ?>][]"
                                                   value="<?php echo esc_attr($contact['email'] ?? ''); ?>"
                                                   class="regular-text">
                                        </div>
                                        <div class="ofero-field">
                                            <label><?php esc_html_e('Phone', 'ofero-generator'); ?></label>
                                            <input type="tel" name="location_contact_phone[<?php echo $i; ?>][]"
                                                   value="<?php echo esc_attr($contact['phone'] ?? ''); ?>"
                                                   class="regular-text" placeholder="+1234567890">
                                        </div>
                                    </div>
                                    <div class="ofero-field-row">
                                        <div class="ofero-field ofero-field-wide">
                                            <label><?php esc_html_e('Photo URL', 'ofero-generator'); ?></label>
                                            <input type="url" name="location_contact_photo[<?php echo $i; ?>][]"
                                                   value="<?php echo esc_attr($contact['photo'] ?? ''); ?>"
                                                   class="large-text" placeholder="https://example.com/photo.jpg">
                                        </div>
                                    </div>
                                    <div style="margin-top: 8px;">
                                        <label style="font-size: 12px; color: #646970;">
                                            <input type="checkbox" name="location_contact_public[<?php echo $i; ?>][]"
                                                   value="1" <?php checked(isset($contact['public']) ? $contact['public'] : true); ?>>
                                            <?php esc_html_e('Show this contact publicly', 'ofero-generator'); ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <button type="button" class="button ofero-add-item" data-container="locations-container" data-template="location-template">
        <span class="dashicons dashicons-plus-alt2"></span>
        <?php esc_html_e('Add Location', 'ofero-generator'); ?>
    </button>
</div>

<!-- Location Template (for new locations added via JS) -->
<script type="text/template" id="location-template">
    <div class="ofero-repeater-item">
        <div class="ofero-repeater-header">
            <span class="ofero-repeater-title"><?php esc_html_e('New Location', 'ofero-generator'); ?></span>
            <button type="button" class="button ofero-repeater-remove">
                <span class="dashicons dashicons-trash"></span>
            </button>
        </div>
        <div class="ofero-repeater-content">
            <div class="ofero-field-row">
                <div class="ofero-field">
                    <label><?php esc_html_e('Name', 'ofero-generator'); ?></label>
                    <input type="text" name="location_name[]" class="regular-text">
                </div>
                <div class="ofero-field">
                    <label><?php esc_html_e('Type', 'ofero-generator'); ?></label>
                    <select name="location_type[]">
                        <option value="headquarters"><?php esc_html_e('Headquarters', 'ofero-generator'); ?></option>
                        <option value="branch"><?php esc_html_e('Branch', 'ofero-generator'); ?></option>
                        <option value="store"><?php esc_html_e('Store', 'ofero-generator'); ?></option>
                        <option value="warehouse"><?php esc_html_e('Warehouse', 'ofero-generator'); ?></option>
                        <option value="office"><?php esc_html_e('Office', 'ofero-generator'); ?></option>
                        <option value="factory"><?php esc_html_e('Factory', 'ofero-generator'); ?></option>
                        <option value="distribution_center"><?php esc_html_e('Distribution Center', 'ofero-generator'); ?></option>
                    </select>
                </div>
            </div>
            <div class="ofero-field-row">
                <div class="ofero-field ofero-field-wide">
                    <label><?php esc_html_e('Street Address', 'ofero-generator'); ?></label>
                    <input type="text" name="location_street[]" class="large-text">
                </div>
            </div>
            <div class="ofero-field-row">
                <div class="ofero-field">
                    <label><?php esc_html_e('City', 'ofero-generator'); ?></label>
                    <input type="text" name="location_city[]" class="regular-text">
                </div>
                <div class="ofero-field">
                    <label><?php esc_html_e('Region / State', 'ofero-generator'); ?></label>
                    <input type="text" name="location_region[]" class="regular-text"
                           placeholder="<?php esc_attr_e('State, province, or region', 'ofero-generator'); ?>">
                </div>
            </div>
            <div class="ofero-field-row">
                <div class="ofero-field">
                    <label><?php esc_html_e('Postal Code', 'ofero-generator'); ?></label>
                    <input type="text" name="location_postal[]" class="regular-text">
                </div>
                <div class="ofero-field">
                    <label><?php esc_html_e('Country', 'ofero-generator'); ?></label>
                    <input type="text" name="location_country[]" class="small-text" maxlength="2" style="text-transform: uppercase;">
                    <p class="description">
                        <?php esc_html_e('2-letter ISO code (e.g., US, GB, RO)', 'ofero-generator'); ?>
                    </p>
                </div>
            </div>
            <div class="ofero-field-row">
                <div class="ofero-field">
                    <label><?php esc_html_e('Phone', 'ofero-generator'); ?></label>
                    <input type="tel" name="location_phone[]" class="regular-text">
                </div>
                <div class="ofero-field">
                    <label><?php esc_html_e('Email', 'ofero-generator'); ?></label>
                    <input type="email" name="location_email[]" class="regular-text">
                </div>
            </div>
            <div class="ofero-field-row">
                <div class="ofero-field ofero-field-wide">
                    <label><?php esc_html_e('Location Photos (URLs)', 'ofero-generator'); ?></label>
                    <textarea name="location_photos[]" class="large-text" rows="3"
                              placeholder="<?php esc_attr_e('One HTTPS URL per line, e.g. https://example.com/photo1.jpg', 'ofero-generator'); ?>"></textarea>
                    <p class="description"><?php esc_html_e('Photos of this location (storefronts, interiors, etc.). One URL per line.', 'ofero-generator'); ?></p>
                </div>
            </div>
            <div style="margin-top: 20px; border: 1px solid #c3d0e8; border-radius: 4px; background: #f0f4fb; padding: 14px 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <div style="font-weight: 600; color: #1d2327; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                        <span class="dashicons dashicons-calendar-alt" style="color: #7986cb; font-size: 16px; width: 16px; height: 16px;"></span>
                        <?php esc_html_e('Special Hours', 'ofero-generator'); ?>
                        <span style="font-weight: 400; color: #646970; font-size: 12px;"><?php esc_html_e('(optional)', 'ofero-generator'); ?></span>
                    </div>
                    <button type="button" class="button ofero-add-special-hour" data-location-index="__INDEX__"
                            style="border-color: #7986cb; color: #7986cb; background: #fff;">
                        <span class="dashicons dashicons-plus-alt2" style="margin-top: 3px;"></span>
                        <?php esc_html_e('Add Entry', 'ofero-generator'); ?>
                    </button>
                </div>
                <div class="location-special-hours-container" data-location-index="__INDEX__">
                    <div class="ofero-special-hours-empty" style="text-align: center; padding: 12px 0 6px; color: #8c8f94; font-size: 12px; font-style: italic;">
                        <?php esc_html_e('No special hours added yet.', 'ofero-generator'); ?>
                    </div>
                </div>
                <input type="hidden" name="location_special_hours[]" class="ofero-special-hours-json" value="[]">
            </div>
            <div style="margin-top: 20px; border: 1px solid #c3d0e8; border-radius: 4px; background: #f0f4fb; padding: 14px 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <div style="font-weight: 600; color: #1d2327; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                        <span class="dashicons dashicons-id" style="color: #7986cb; font-size: 16px; width: 16px; height: 16px;"></span>
                        <?php esc_html_e('Contact Persons', 'ofero-generator'); ?>
                        <span style="font-weight: 400; color: #646970; font-size: 12px;"><?php esc_html_e('(optional — who to contact at this location)', 'ofero-generator'); ?></span>
                    </div>
                    <button type="button" class="button ofero-add-contact" data-location-index="__INDEX__"
                            style="border-color: #7986cb; color: #7986cb; background: #fff;">
                        <span class="dashicons dashicons-plus-alt2" style="margin-top: 3px;"></span>
                        <?php esc_html_e('Add Contact Person', 'ofero-generator'); ?>
                    </button>
                </div>
                <div class="location-contacts-container" data-location-index="__INDEX__">
                    <div class="ofero-contacts-empty" style="text-align: center; padding: 16px 0 8px; color: #8c8f94; font-size: 12px; font-style: italic;">
                        <span class="dashicons dashicons-groups" style="font-size: 24px; width: 24px; height: 24px; display: block; margin: 0 auto 6px; color: #c3d0e8;"></span>
                        <?php esc_html_e('No contact persons added yet. Use the button above to add one.', 'ofero-generator'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>

<!-- Contact Person Template -->
<script type="text/template" id="contact-person-template">
    <div class="ofero-contact-item" style="border: 1px solid #c3d0e8; border-left: 3px solid #7986cb; padding: 12px; margin-bottom: 8px; background: #fff; border-radius: 3px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <strong style="font-size: 12px; color: #7986cb;">
                <span class="dashicons dashicons-admin-users" style="font-size: 13px; width: 13px; height: 13px; vertical-align: middle;"></span>
                <?php esc_html_e('Contact Person', 'ofero-generator'); ?>
            </strong>
            <button type="button" class="button-link ofero-remove-contact" style="color: #b32d2e; text-decoration: none;">
                <span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span>
                <?php esc_html_e('Remove', 'ofero-generator'); ?>
            </button>
        </div>
        <div class="ofero-field-row">
            <div class="ofero-field">
                <label><?php esc_html_e('Name', 'ofero-generator'); ?></label>
                <input type="text" name="location_contact_name[__LOC_INDEX__][]" class="regular-text">
            </div>
            <div class="ofero-field">
                <label><?php esc_html_e('Role', 'ofero-generator'); ?></label>
                <input type="text" name="location_contact_role[__LOC_INDEX__][]" class="regular-text" placeholder="<?php esc_attr_e('e.g. Store Manager', 'ofero-generator'); ?>">
            </div>
        </div>
        <div class="ofero-field-row">
            <div class="ofero-field">
                <label><?php esc_html_e('Email', 'ofero-generator'); ?></label>
                <input type="email" name="location_contact_email[__LOC_INDEX__][]" class="regular-text">
            </div>
            <div class="ofero-field">
                <label><?php esc_html_e('Phone', 'ofero-generator'); ?></label>
                <input type="tel" name="location_contact_phone[__LOC_INDEX__][]" class="regular-text" placeholder="+1234567890">
            </div>
        </div>
        <div class="ofero-field-row">
            <div class="ofero-field ofero-field-wide">
                <label><?php esc_html_e('Photo URL', 'ofero-generator'); ?></label>
                <input type="url" name="location_contact_photo[__LOC_INDEX__][]" class="large-text" placeholder="https://example.com/photo.jpg">
            </div>
        </div>
        <div style="margin-top: 8px;">
            <label style="font-size: 12px; color: #646970;">
                <input type="checkbox" name="location_contact_public[__LOC_INDEX__][]" value="1" checked>
                <?php esc_html_e('Show this contact publicly', 'ofero-generator'); ?>
            </label>
        </div>
    </div>
</script>

<script>
(function($) {
    // Track the next location index for new locations added dynamically
    var nextLocationIndex = <?php echo count($locations); ?>;

    // Special hours entry template
    function specialHourItemHTML() {
        return '<div class="ofero-special-hour-item" style="border: 1px solid #c3d0e8; border-left: 3px solid #7986cb; padding: 10px 12px; margin-bottom: 8px; background: #fff; border-radius: 3px;">' +
            '<div style="display: flex; justify-content: flex-end; margin-bottom: 6px;">' +
                '<button type="button" class="button-link ofero-remove-special-hour" style="color: #b32d2e; text-decoration: none; font-size: 12px;">' +
                    '<span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span> <?php esc_html_e('Remove', 'ofero-generator'); ?>' +
                '</button>' +
            '</div>' +
            '<div class="ofero-field-row">' +
                '<div class="ofero-field">' +
                    '<label><?php esc_html_e('Type', 'ofero-generator'); ?></label>' +
                    '<select class="ofero-special-hour-type">' +
                        '<option value="date"><?php esc_html_e('Single day', 'ofero-generator'); ?></option>' +
                        '<option value="range"><?php esc_html_e('Date range', 'ofero-generator'); ?></option>' +
                    '</select>' +
                '</div>' +
                '<div class="ofero-field ofero-special-hour-date-field">' +
                    '<label><?php esc_html_e('Date', 'ofero-generator'); ?></label>' +
                    '<input type="date" class="ofero-special-hour-date regular-text">' +
                '</div>' +
                '<div class="ofero-field ofero-special-hour-from-field" style="display:none;">' +
                    '<label><?php esc_html_e('From', 'ofero-generator'); ?></label>' +
                    '<input type="date" class="ofero-special-hour-from regular-text">' +
                '</div>' +
                '<div class="ofero-field ofero-special-hour-to-field" style="display:none;">' +
                    '<label><?php esc_html_e('To', 'ofero-generator'); ?></label>' +
                    '<input type="date" class="ofero-special-hour-to regular-text">' +
                '</div>' +
            '</div>' +
            '<div class="ofero-field-row">' +
                '<div class="ofero-field">' +
                    '<label><?php esc_html_e('Name / Label', 'ofero-generator'); ?></label>' +
                    '<input type="text" class="ofero-special-hour-name regular-text" placeholder="<?php esc_attr_e('e.g. Christmas Day, Summer Schedule', 'ofero-generator'); ?>">' +
                '</div>' +
                '<div class="ofero-field">' +
                    '<label><?php esc_html_e('Hours', 'ofero-generator'); ?></label>' +
                    '<input type="text" class="ofero-special-hour-hours regular-text" placeholder="<?php esc_attr_e('e.g. 10:00-14:00 or Closed', 'ofero-generator'); ?>">' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    // Toggle single-day / date-range fields
    $(document).on('change', '.ofero-special-hour-type', function() {
        var item = $(this).closest('.ofero-special-hour-item');
        var isRange = $(this).val() === 'range';
        item.find('.ofero-special-hour-date-field').toggle(!isRange);
        item.find('.ofero-special-hour-from-field, .ofero-special-hour-to-field').toggle(isRange);
    });

    // Add special hour entry
    $(document).on('click', '.ofero-add-special-hour', function() {
        var locIndex = $(this).data('location-index');
        var container = $('.location-special-hours-container[data-location-index="' + locIndex + '"]');
        container.find('.ofero-special-hours-empty').remove();
        container.append(specialHourItemHTML());
        syncSpecialHoursJSON(container);
    });

    // Remove special hour entry
    $(document).on('click', '.ofero-remove-special-hour', function() {
        var container = $(this).closest('.location-special-hours-container');
        $(this).closest('.ofero-special-hour-item').remove();
        if (container.find('.ofero-special-hour-item').length === 0) {
            container.append('<div class="ofero-special-hours-empty" style="text-align: center; padding: 12px 0 6px; color: #8c8f94; font-size: 12px; font-style: italic;"><?php esc_html_e('No special hours added yet.', 'ofero-generator'); ?></div>');
        }
        syncSpecialHoursJSON(container);
    });

    // Sync all special hour entries to the hidden JSON field on any change
    $(document).on('change input', '.ofero-special-hour-item input, .ofero-special-hour-item select', function() {
        var container = $(this).closest('.location-special-hours-container');
        syncSpecialHoursJSON(container);
    });

    function syncSpecialHoursJSON(container) {
        var entries = [];
        container.find('.ofero-special-hour-item').each(function() {
            var type  = $(this).find('.ofero-special-hour-type').val();
            var name  = $(this).find('.ofero-special-hour-name').val().trim();
            var hours = $(this).find('.ofero-special-hour-hours').val().trim();
            if (!name || !hours) return;
            if (type === 'range') {
                var from = $(this).find('.ofero-special-hour-from').val();
                var to   = $(this).find('.ofero-special-hour-to').val();
                if (from && to) entries.push({ from: from, to: to, name: name, hours: hours });
            } else {
                var date = $(this).find('.ofero-special-hour-date').val();
                if (date) entries.push({ date: date, name: name, hours: hours });
            }
        });
        var locIndex = container.data('location-index');
        // Find the hidden JSON field that belongs to the same repeater item
        container.closest('.ofero-repeater-content').find('.ofero-special-hours-json').val(JSON.stringify(entries));
    }

    // Add contact person to a location
    $(document).on('click', '.ofero-add-contact', function() {
        var locIndex = $(this).data('location-index');
        var container = $('.location-contacts-container[data-location-index="' + locIndex + '"]');
        var template = $('#contact-person-template').html();
        template = template.replace(/__LOC_INDEX__/g, locIndex);
        container.find('.ofero-contacts-empty').remove();
        container.append(template);
    });

    // Remove contact person
    $(document).on('click', '.ofero-remove-contact', function() {
        $(this).closest('.ofero-contact-item').remove();
    });

    // When a new location is added via the main "Add Location" button,
    // assign a unique index to its contact container and button
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if ($(node).hasClass('ofero-repeater-item')) {
                    var idx = nextLocationIndex++;
                    $(node).find('[data-location-index="__INDEX__"]').attr('data-location-index', idx);
                    $(node).find('input[name*="__INDEX__"], textarea[name*="__INDEX__"]').each(function() {
                        $(this).attr('name', $(this).attr('name').replace(/__INDEX__/g, idx));
                    });
                }
            });
        });
    });

    var container = document.getElementById('locations-container');
    if (container) {
        observer.observe(container, { childList: true });
    }
})(jQuery);
</script>
