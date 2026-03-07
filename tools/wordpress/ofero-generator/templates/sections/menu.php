<?php
/**
 * Menu Section Template (Restaurant / Hotel+Restaurant)
 *
 * @package Ofero_Generator
 */

if (!defined('ABSPATH')) {
    exit;
}

$menu       = $data['catalog']['menu'] ?? array();
$categories = $menu['categories'] ?? array();
$currency   = $data['catalog']['defaultCurrency'] ?? 'USD';
?>

<div class="ofero-card">
    <h2><?php esc_html_e('Menu', 'ofero-generator'); ?></h2>
    <p class="description">
        <?php esc_html_e('Add your menu categories and items. Each item can have variants (sizes) and add-ons.', 'ofero-generator'); ?>
    </p>

    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="menu_currency"><?php esc_html_e('Currency', 'ofero-generator'); ?></label>
            </th>
            <td>
                <input type="text" id="menu_currency" name="menu_currency"
                       value="<?php echo esc_attr($currency); ?>"
                       class="small-text" maxlength="3" placeholder="USD">
                <p class="description"><?php esc_html_e('ISO 4217 currency code (e.g., USD, EUR, RON)', 'ofero-generator'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="menu_allergen_url"><?php esc_html_e('Allergen Info URL', 'ofero-generator'); ?></label>
            </th>
            <td>
                <input type="url" id="menu_allergen_url" name="menu_allergen_url"
                       value="<?php echo esc_url($menu['allergenInfo'] ?? ''); ?>"
                       class="regular-text" placeholder="https://yoursite.com/allergens">
                <p class="description"><?php esc_html_e('Link to your allergen information page', 'ofero-generator'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e('Dietary Options', 'ofero-generator'); ?></th>
            <td>
                <?php
                $dietaryOptions = $menu['dietaryOptions'] ?? array();
                $allDietary = array(
                    'vegetarian' => __('Vegetarian', 'ofero-generator'),
                    'vegan'      => __('Vegan', 'ofero-generator'),
                    'gluten-free'=> __('Gluten-free', 'ofero-generator'),
                    'halal'      => __('Halal', 'ofero-generator'),
                    'kosher'     => __('Kosher', 'ofero-generator'),
                    'dairy-free' => __('Dairy-free', 'ofero-generator'),
                    'nut-free'   => __('Nut-free', 'ofero-generator'),
                    'organic'    => __('Organic', 'ofero-generator'),
                    'keto'       => __('Keto', 'ofero-generator'),
                    'paleo'      => __('Paleo', 'ofero-generator'),
                );
                foreach ($allDietary as $value => $label): ?>
                    <label style="margin-right: 16px; display: inline-block;">
                        <input type="checkbox" name="menu_dietary[]" value="<?php echo esc_attr($value); ?>"
                               <?php checked(in_array($value, $dietaryOptions)); ?>>
                        <?php echo esc_html($label); ?>
                    </label>
                <?php endforeach; ?>
            </td>
        </tr>
    </table>
</div>

<div class="ofero-card">
    <h2><?php esc_html_e('Menu Categories', 'ofero-generator'); ?></h2>
    <p class="description">
        <?php esc_html_e('Add categories (e.g., Appetizers, Pizza, Pasta, Desserts, Drinks). Each category contains its menu items.', 'ofero-generator'); ?>
    </p>

    <div id="menu-categories-container">
        <?php foreach ($categories as $catIndex => $category): ?>
        <div class="ofero-menu-category ofero-repeater-item" data-cat-index="<?php echo $catIndex; ?>">
            <div class="ofero-repeater-header">
                <span class="ofero-repeater-title">
                    <?php echo esc_html(is_array($category['name']) ? ($category['name']['default'] ?? '') : ($category['name'] ?? '')); ?>
                </span>
                <button type="button" class="button button-small ofero-menu-category-toggle">
                    <?php esc_html_e('Collapse', 'ofero-generator'); ?>
                </button>
                <button type="button" class="button button-small button-link-delete ofero-repeater-remove">
                    <?php esc_html_e('Remove Category', 'ofero-generator'); ?>
                </button>
            </div>
            <div class="ofero-menu-category-body ofero-repeater-content">
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e('Category ID', 'ofero-generator'); ?></th>
                        <td>
                            <input type="text" name="menu_cat_id[]"
                                   value="<?php echo esc_attr($category['id'] ?? ''); ?>"
                                   class="regular-text" placeholder="e.g., pizza, desserts">
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Category Name', 'ofero-generator'); ?></th>
                        <td>
                            <input type="text" name="menu_cat_name[]"
                                   value="<?php echo esc_attr(is_array($category['name']) ? ($category['name']['default'] ?? '') : ($category['name'] ?? '')); ?>"
                                   class="regular-text" placeholder="e.g., Pizza">
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Sort Order', 'ofero-generator'); ?></th>
                        <td>
                            <input type="number" name="menu_cat_sort[]"
                                   value="<?php echo esc_attr($category['sortOrder'] ?? ($catIndex + 1)); ?>"
                                   class="small-text" min="1">
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Service Hours', 'ofero-generator'); ?></th>
                        <td>
                            <input type="text" name="menu_cat_service_hours[]"
                                   value="<?php echo esc_attr($category['serviceHours'] ?? ''); ?>"
                                   class="small-text" placeholder="08:00-12:00">
                            <p class="description"><?php esc_html_e('Hours when this category is served (HH:MM-HH:MM). Leave empty if available all day.', 'ofero-generator'); ?></p>
                        </td>
                    </tr>
                </table>

                <h4 style="margin: 16px 0 8px; padding-left: 12px;">
                    <?php esc_html_e('Items in this category', 'ofero-generator'); ?>
                </h4>

                <div class="ofero-menu-items-container" id="menu-items-<?php echo $catIndex; ?>">
                    <?php foreach (($category['items'] ?? array()) as $itemIndex => $item): ?>
                    <div class="ofero-menu-item ofero-repeater-item">
                        <div class="ofero-repeater-header" style="background: #f0f0f0;">
                            <span class="ofero-repeater-title">
                                <?php echo esc_html(is_array($item['name']) ? ($item['name']['default'] ?? '') : ($item['name'] ?? '')); ?>
                            </span>
                            <button type="button" class="button button-small ofero-menu-item-toggle">
                                <?php esc_html_e('Collapse', 'ofero-generator'); ?>
                            </button>
                            <button type="button" class="button button-small button-link-delete ofero-repeater-remove">
                                <?php esc_html_e('Remove Item', 'ofero-generator'); ?>
                            </button>
                        </div>
                        <div class="ofero-menu-item-body ofero-repeater-content" style="padding: 12px;">
                            <input type="hidden" name="menu_item_cat[]" value="<?php echo $catIndex; ?>">
                            <table class="form-table" style="margin: 0;">
                                <tr>
                                    <th style="width: 140px;"><?php esc_html_e('Item ID', 'ofero-generator'); ?></th>
                                    <td><input type="text" name="menu_item_id[]" value="<?php echo esc_attr($item['id'] ?? ''); ?>" class="regular-text" placeholder="e.g., margherita"></td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Name', 'ofero-generator'); ?></th>
                                    <td><input type="text" name="menu_item_name[]" value="<?php echo esc_attr(is_array($item['name']) ? ($item['name']['default'] ?? '') : ($item['name'] ?? '')); ?>" class="regular-text"></td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Description', 'ofero-generator'); ?></th>
                                    <td><textarea name="menu_item_desc[]" rows="2" class="large-text"><?php echo esc_textarea(is_array($item['description']) ? ($item['description']['default'] ?? '') : ($item['description'] ?? '')); ?></textarea></td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Portion Size', 'ofero-generator'); ?></th>
                                    <td><input type="text" name="menu_item_portion[]" value="<?php echo esc_attr($item['portionSize'] ?? ''); ?>" class="small-text" placeholder="220g">
                                    <p class="description"><?php esc_html_e('Weight or portion size (e.g., 220g, 350ml, 2-3 persons)', 'ofero-generator'); ?></p></td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Ingredients', 'ofero-generator'); ?></th>
                                    <td><textarea name="menu_item_ingredients[]" rows="2" class="large-text" placeholder="egg, bacon, toast, pickle"><?php echo esc_textarea(implode(', ', $item['ingredients'] ?? array())); ?></textarea>
                                    <p class="description"><?php esc_html_e('Comma-separated list of ingredients', 'ofero-generator'); ?></p></td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Price', 'ofero-generator'); ?></th>
                                    <td><input type="number" name="menu_item_price[]" value="<?php echo esc_attr($item['price'] ?? ''); ?>" class="small-text" min="0" step="0.01"></td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Image URL', 'ofero-generator'); ?></th>
                                    <td><input type="url" name="menu_item_image[]" value="<?php echo esc_url($item['image'] ?? ''); ?>" class="regular-text" placeholder="https://..."></td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Prep Time', 'ofero-generator'); ?></th>
                                    <td><input type="text" name="menu_item_prep[]" value="<?php echo esc_attr($item['preparationTime'] ?? ''); ?>" class="small-text" placeholder="15 min"></td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Calories', 'ofero-generator'); ?></th>
                                    <td><input type="number" name="menu_item_calories[]" value="<?php echo esc_attr($item['calories'] ?? ''); ?>" class="small-text" min="0" placeholder="kcal"></td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Dietary', 'ofero-generator'); ?></th>
                                    <td>
                                        <?php
                                        $itemDietary = $item['dietary'] ?? array();
                                        $dietaryLabels = array(
                                            'vegetarian'       => 'Vegetarian',
                                            'vegan'            => 'Vegan',
                                            'gluten-free'      => 'Gluten-free',
                                            'halal'            => 'Halal',
                                            'kosher'           => 'Kosher',
                                            'dairy-free'       => 'Dairy-free',
                                            'spicy'            => 'Spicy',
                                            'contains-alcohol' => 'Contains alcohol',
                                        );
                                        foreach ($dietaryLabels as $dv => $dl): ?>
                                            <label style="margin-right: 12px;">
                                                <input type="checkbox" name="menu_item_dietary_<?php echo $catIndex; ?>_<?php echo $itemIndex; ?>[]"
                                                       value="<?php echo esc_attr($dv); ?>"
                                                       data-item-dietary="true"
                                                       <?php checked(in_array($dv, $itemDietary)); ?>>
                                                <?php echo esc_html($dl); ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Allergens', 'ofero-generator'); ?></th>
                                    <td>
                                        <?php
                                        $itemAllergens = $item['allergens'] ?? array();
                                        $allergenList = array(
                                            'gluten'     => 'Gluten',
                                            'dairy'      => 'Dairy',
                                            'eggs'       => 'Eggs',
                                            'fish'       => 'Fish',
                                            'shellfish'  => 'Shellfish',
                                            'tree-nuts'  => 'Tree nuts',
                                            'peanuts'    => 'Peanuts',
                                            'soy'        => 'Soy',
                                            'sesame'     => 'Sesame',
                                            'sulfites'   => 'Sulfites',
                                        );
                                        foreach ($allergenList as $av => $al): ?>
                                            <label style="margin-right: 12px;">
                                                <input type="checkbox" name="menu_item_allergens_<?php echo $catIndex; ?>_<?php echo $itemIndex; ?>[]"
                                                       value="<?php echo esc_attr($av); ?>"
                                                       data-item-allergen="true"
                                                       <?php checked(in_array($av, $itemAllergens)); ?>>
                                                <?php echo esc_html($al); ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Available', 'ofero-generator'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="menu_item_available[]" value="1"
                                                   <?php checked($item['available'] ?? true); ?>>
                                            <?php esc_html_e('Item is currently available', 'ofero-generator'); ?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e('Popular', 'ofero-generator'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="menu_item_popular[]" value="1"
                                                   <?php checked($item['popular'] ?? false); ?>>
                                            <?php esc_html_e('Mark as popular / featured', 'ofero-generator'); ?>
                                        </label>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="button ofero-add-menu-item" style="margin: 8px 0 0 12px;"
                        data-cat-index="<?php echo $catIndex; ?>">
                    + <?php esc_html_e('Add Item', 'ofero-generator'); ?>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <button type="button" class="button button-secondary" id="ofero-add-menu-category" style="margin-top: 16px;">
        + <?php esc_html_e('Add Category', 'ofero-generator'); ?>
    </button>
</div>

<?php
$dailyMenu  = $data['catalog']['dailyMenu'] ?? array();
$schedule   = $dailyMenu['schedule'] ?? array();
$weekDays   = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
$courseOptions = array(
    ''        => __('— none —', 'ofero-generator'),
    'starter' => __('Starter', 'ofero-generator'),
    'soup'    => __('Soup', 'ofero-generator'),
    'main'    => __('Main', 'ofero-generator'),
    'dessert' => __('Dessert', 'ofero-generator'),
    'drink'   => __('Drink', 'ofero-generator'),
    'side'    => __('Side', 'ofero-generator'),
    'other'   => __('Other', 'ofero-generator'),
);
?>

<div class="ofero-card">
    <h2><?php esc_html_e('Daily Menu', 'ofero-generator'); ?></h2>
    <p class="description">
        <?php esc_html_e('Weekly rotating specials. Items here may have different portion sizes or prices than the regular menu. Leave days empty if not applicable.', 'ofero-generator'); ?>
    </p>

    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="daily_menu_week_of"><?php esc_html_e('Week of (Monday)', 'ofero-generator'); ?></label>
            </th>
            <td>
                <input type="date" id="daily_menu_week_of" name="daily_menu_week_of"
                       value="<?php echo esc_attr($dailyMenu['weekOf'] ?? ''); ?>"
                       class="regular-text">
                <p class="description"><?php esc_html_e('The Monday date this weekly menu applies to (YYYY-MM-DD)', 'ofero-generator'); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="daily_menu_note"><?php esc_html_e('Note', 'ofero-generator'); ?></label>
            </th>
            <td>
                <input type="text" id="daily_menu_note" name="daily_menu_note"
                       value="<?php echo esc_attr(is_array($dailyMenu['note']) ? ($dailyMenu['note']['default'] ?? '') : ($dailyMenu['note'] ?? '')); ?>"
                       class="large-text" placeholder="Menu changes every Monday. Available while supplies last.">
            </td>
        </tr>
    </table>

    <?php foreach ($weekDays as $day): ?>
    <div class="ofero-card" style="margin-top: 16px; background: #f9f9f9;">
        <h3 style="text-transform: capitalize; margin-top: 0;"><?php echo esc_html(ucfirst($day)); ?></h3>

        <div class="ofero-daily-items-container" id="daily-items-<?php echo $day; ?>">
            <?php foreach (($schedule[$day] ?? array()) as $idx => $item): ?>
            <div class="ofero-daily-item ofero-repeater-item" style="background: #fff; border: 1px solid #ddd; padding: 12px; margin-bottom: 8px;">
                <div class="ofero-repeater-header" style="background: #e8e8e8;">
                    <span class="ofero-repeater-title">
                        <?php echo esc_html(is_array($item['name']) ? ($item['name']['default'] ?? '') : ($item['name'] ?? '')); ?>
                    </span>
                    <button type="button" class="button button-small button-link-delete ofero-repeater-remove">
                        <?php esc_html_e('Remove', 'ofero-generator'); ?>
                    </button>
                </div>
                <div class="ofero-repeater-content">
                    <table class="form-table" style="margin: 0;">
                        <tr>
                            <th style="width: 140px;"><?php esc_html_e('Name', 'ofero-generator'); ?></th>
                            <td><input type="text" name="daily_<?php echo $day; ?>_name[]"
                                       value="<?php echo esc_attr(is_array($item['name']) ? ($item['name']['default'] ?? '') : ($item['name'] ?? '')); ?>"
                                       class="regular-text"></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Description', 'ofero-generator'); ?></th>
                            <td><textarea name="daily_<?php echo $day; ?>_desc[]" rows="2" class="large-text"><?php echo esc_textarea(is_array($item['description']) ? ($item['description']['default'] ?? '') : ($item['description'] ?? '')); ?></textarea></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Portion Size', 'ofero-generator'); ?></th>
                            <td><input type="text" name="daily_<?php echo $day; ?>_portion[]"
                                       value="<?php echo esc_attr($item['portionSize'] ?? ''); ?>"
                                       class="small-text" placeholder="320g"></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Price', 'ofero-generator'); ?></th>
                            <td><input type="number" name="daily_<?php echo $day; ?>_price[]"
                                       value="<?php echo esc_attr($item['price'] ?? ''); ?>"
                                       class="small-text" min="0" step="0.01"></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Course', 'ofero-generator'); ?></th>
                            <td>
                                <select name="daily_<?php echo $day; ?>_course[]">
                                    <?php foreach ($courseOptions as $val => $label): ?>
                                        <option value="<?php echo esc_attr($val); ?>"
                                                <?php selected($item['course'] ?? '', $val); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Ingredients', 'ofero-generator'); ?></th>
                            <td><textarea name="daily_<?php echo $day; ?>_ingredients[]" rows="2" class="large-text" placeholder="egg, bacon, toast"><?php echo esc_textarea(implode(', ', $item['ingredients'] ?? array())); ?></textarea>
                            <p class="description"><?php esc_html_e('Comma-separated', 'ofero-generator'); ?></p></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Available', 'ofero-generator'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="daily_<?php echo $day; ?>_available[]" value="1"
                                           <?php checked($item['available'] ?? true); ?>>
                                    <?php esc_html_e('Available today', 'ofero-generator'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="button ofero-add-daily-item" style="margin-top: 8px;"
                data-day="<?php echo esc_attr($day); ?>">
            + <?php echo esc_html(sprintf(__('Add %s item', 'ofero-generator'), ucfirst($day))); ?>
        </button>
    </div>
    <?php endforeach; ?>
</div>
