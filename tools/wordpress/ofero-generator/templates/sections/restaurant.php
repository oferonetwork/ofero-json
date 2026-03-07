<?php
/**
 * Restaurant Details Section Template
 *
 * @package Ofero_Generator
 */

if (!defined('ABSPATH')) {
    exit;
}

$rd           = $data['restaurantDetails'] ?? array();
$serviceTypes = $rd['serviceTypes'] ?? array();
$reservations = $rd['reservations'] ?? array();
$parking      = $rd['parking'] ?? array();
$delivery     = $rd['deliveryPlatforms'] ?? array();
$amenities    = $rd['amenities'] ?? array();
$cuisine      = $rd['cuisine'] ?? array();
?>

<div class="ofero-card">
    <h2><?php esc_html_e('Seating Capacity', 'ofero-generator'); ?></h2>
    <table class="form-table">
        <tr>
            <th><label for="rd_seating_total"><?php esc_html_e('Total Seats', 'ofero-generator'); ?></label></th>
            <td>
                <input type="number" id="rd_seating_total" name="rd_seating_total"
                       value="<?php echo esc_attr($rd['seatingCapacity'] ?? ''); ?>"
                       class="small-text" min="0">
            </td>
        </tr>
        <tr>
            <th><label for="rd_seating_indoor"><?php esc_html_e('Indoor Seats', 'ofero-generator'); ?></label></th>
            <td>
                <input type="number" id="rd_seating_indoor" name="rd_seating_indoor"
                       value="<?php echo esc_attr($rd['indoorSeats'] ?? ''); ?>"
                       class="small-text" min="0">
            </td>
        </tr>
        <tr>
            <th><label for="rd_seating_outdoor"><?php esc_html_e('Outdoor / Terrace Seats', 'ofero-generator'); ?></label></th>
            <td>
                <input type="number" id="rd_seating_outdoor" name="rd_seating_outdoor"
                       value="<?php echo esc_attr($rd['outdoorSeats'] ?? ''); ?>"
                       class="small-text" min="0">
            </td>
        </tr>
    </table>
</div>

<div class="ofero-card">
    <h2><?php esc_html_e('Service Types', 'ofero-generator'); ?></h2>
    <table class="form-table">
        <tr>
            <th><?php esc_html_e('Available Services', 'ofero-generator'); ?></th>
            <td>
                <?php
                $services = array(
                    'dineIn'      => __('Dine-in', 'ofero-generator'),
                    'takeaway'    => __('Takeaway / To-go', 'ofero-generator'),
                    'delivery'    => __('Home Delivery', 'ofero-generator'),
                    'catering'    => __('Catering', 'ofero-generator'),
                    'driveThrough'=> __('Drive-through', 'ofero-generator'),
                );
                foreach ($services as $key => $label): ?>
                    <label style="display: block; margin-bottom: 6px;">
                        <input type="checkbox" name="rd_service_<?php echo esc_attr($key); ?>" value="1"
                               <?php checked(!empty($serviceTypes[$key])); ?>>
                        <?php echo esc_html($label); ?>
                    </label>
                <?php endforeach; ?>
            </td>
        </tr>
    </table>
</div>

<div class="ofero-card">
    <h2><?php esc_html_e('Reservations', 'ofero-generator'); ?></h2>
    <table class="form-table">
        <tr>
            <th><?php esc_html_e('Policy', 'ofero-generator'); ?></th>
            <td>
                <label style="display: block; margin-bottom: 6px;">
                    <input type="checkbox" name="rd_reservations_required" value="1"
                           <?php checked(!empty($reservations['required'])); ?>>
                    <?php esc_html_e('Reservations required (walk-in not accepted)', 'ofero-generator'); ?>
                </label>
                <label style="display: block;">
                    <input type="checkbox" name="rd_reservations_recommended" value="1"
                           <?php checked(!empty($reservations['recommended'])); ?>>
                    <?php esc_html_e('Reservations recommended', 'ofero-generator'); ?>
                </label>
            </td>
        </tr>
        <tr>
            <th><label for="rd_booking_url"><?php esc_html_e('Booking URL', 'ofero-generator'); ?></label></th>
            <td>
                <input type="url" id="rd_booking_url" name="rd_booking_url"
                       value="<?php echo esc_url($reservations['bookingUrl'] ?? ''); ?>"
                       class="regular-text" placeholder="https://yoursite.com/book">
            </td>
        </tr>
        <tr>
            <th><label for="rd_reservation_phone"><?php esc_html_e('Reservation Phone', 'ofero-generator'); ?></label></th>
            <td>
                <input type="tel" id="rd_reservation_phone" name="rd_reservation_phone"
                       value="<?php echo esc_attr($reservations['phone'] ?? ''); ?>"
                       class="regular-text" placeholder="+1 234 567 8900">
            </td>
        </tr>
        <tr>
            <th><label for="rd_advance_notice"><?php esc_html_e('Advance Notice', 'ofero-generator'); ?></label></th>
            <td>
                <input type="text" id="rd_advance_notice" name="rd_advance_notice"
                       value="<?php echo esc_attr($reservations['advanceNotice'] ?? ''); ?>"
                       class="small-text" placeholder="24h">
                <p class="description"><?php esc_html_e('How far in advance to book (e.g., 24h, 48h, 1 week)', 'ofero-generator'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="rd_max_group"><?php esc_html_e('Max Group Size', 'ofero-generator'); ?></label></th>
            <td>
                <input type="number" id="rd_max_group" name="rd_max_group"
                       value="<?php echo esc_attr($reservations['maxGroupSize'] ?? ''); ?>"
                       class="small-text" min="1">
            </td>
        </tr>
        <tr>
            <th><label for="rd_availability_api"><?php esc_html_e('Real-time Availability API', 'ofero-generator'); ?></label></th>
            <td>
                <input type="url" id="rd_availability_api" name="rd_availability_api"
                       value="<?php echo esc_url($data['apiEndpoints']['availability'] ?? ''); ?>"
                       class="regular-text" placeholder="https://yoursite.com/api/availability">
                <p class="description"><?php esc_html_e('Optional: API endpoint that returns live occupancy / table availability data', 'ofero-generator'); ?></p>
            </td>
        </tr>
    </table>
</div>

<div class="ofero-card">
    <h2><?php esc_html_e('Delivery Platforms', 'ofero-generator'); ?></h2>
    <p class="description"><?php esc_html_e('Add platforms where customers can order from you (Glovo, Tazz, Bolt Food, Uber Eats, DoorDash, etc.)', 'ofero-generator'); ?></p>

    <div id="delivery-platforms-container">
        <?php foreach ($delivery as $i => $platform): ?>
        <div class="ofero-repeater-item" style="display: flex; gap: 12px; align-items: center; margin-bottom: 8px;">
            <input type="text" name="rd_delivery_name[]"
                   value="<?php echo esc_attr($platform['name'] ?? ''); ?>"
                   class="regular-text" placeholder="<?php esc_attr_e('Platform name (e.g., Glovo)', 'ofero-generator'); ?>">
            <input type="url" name="rd_delivery_url[]"
                   value="<?php echo esc_url($platform['url'] ?? ''); ?>"
                   class="regular-text" placeholder="https://...">
            <button type="button" class="button button-small button-link-delete ofero-repeater-remove">
                <?php esc_html_e('Remove', 'ofero-generator'); ?>
            </button>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="button" id="ofero-add-delivery-platform" style="margin-top: 8px;">
        + <?php esc_html_e('Add Platform', 'ofero-generator'); ?>
    </button>
</div>

<div class="ofero-card">
    <h2><?php esc_html_e('Parking', 'ofero-generator'); ?></h2>
    <table class="form-table">
        <tr>
            <th><?php esc_html_e('Parking', 'ofero-generator'); ?></th>
            <td>
                <label style="display: block; margin-bottom: 6px;">
                    <input type="checkbox" name="rd_parking_available" value="1"
                           <?php checked(!empty($parking['available'])); ?>>
                    <?php esc_html_e('Parking available nearby', 'ofero-generator'); ?>
                </label>
                <label style="display: block; margin-bottom: 6px;">
                    <input type="checkbox" name="rd_parking_free" value="1"
                           <?php checked(!empty($parking['free'])); ?>>
                    <?php esc_html_e('Free parking', 'ofero-generator'); ?>
                </label>
            </td>
        </tr>
        <tr>
            <th><label for="rd_parking_spaces"><?php esc_html_e('Parking Spaces', 'ofero-generator'); ?></label></th>
            <td>
                <input type="number" id="rd_parking_spaces" name="rd_parking_spaces"
                       value="<?php echo esc_attr($parking['spaces'] ?? ''); ?>"
                       class="small-text" min="0">
            </td>
        </tr>
        <tr>
            <th><label for="rd_parking_notes"><?php esc_html_e('Parking Notes', 'ofero-generator'); ?></label></th>
            <td>
                <input type="text" id="rd_parking_notes" name="rd_parking_notes"
                       value="<?php echo esc_attr($parking['notes'] ?? ''); ?>"
                       class="large-text" placeholder="<?php esc_attr_e('e.g., Parking in the mall, first 2h free', 'ofero-generator'); ?>">
            </td>
        </tr>
    </table>
</div>

<div class="ofero-card">
    <h2><?php esc_html_e('Cuisine & Details', 'ofero-generator'); ?></h2>
    <table class="form-table">
        <tr>
            <th><label for="rd_cuisine"><?php esc_html_e('Cuisine Types', 'ofero-generator'); ?></label></th>
            <td>
                <input type="text" id="rd_cuisine" name="rd_cuisine"
                       value="<?php echo esc_attr(implode(', ', $cuisine)); ?>"
                       class="large-text" placeholder="italian, pizza, mediterranean">
                <p class="description"><?php esc_html_e('Comma-separated cuisine types', 'ofero-generator'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="rd_price_range"><?php esc_html_e('Price Range', 'ofero-generator'); ?></label></th>
            <td>
                <select id="rd_price_range" name="rd_price_range">
                    <option value=""><?php esc_html_e('— Select —', 'ofero-generator'); ?></option>
                    <?php
                    $ranges = array(
                        '$'    => __('$ — Budget-friendly', 'ofero-generator'),
                        '$$'   => __('$$ — Moderate', 'ofero-generator'),
                        '$$$'  => __('$$$ — Upscale', 'ofero-generator'),
                        '$$$$' => __('$$$$ — Fine dining', 'ofero-generator'),
                    );
                    foreach ($ranges as $val => $lbl): ?>
                        <option value="<?php echo esc_attr($val); ?>" <?php selected($rd['priceRange'] ?? '', $val); ?>>
                            <?php echo esc_html($lbl); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="rd_avg_check"><?php esc_html_e('Avg. Check / Person', 'ofero-generator'); ?></label></th>
            <td>
                <input type="number" id="rd_avg_check" name="rd_avg_check"
                       value="<?php echo esc_attr($rd['averageCheckPerPerson'] ?? ''); ?>"
                       class="small-text" min="0" step="0.01">
                <p class="description"><?php esc_html_e('Average spend per person in your default currency', 'ofero-generator'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="rd_ratings_url"><?php esc_html_e('Ratings Page URL', 'ofero-generator'); ?></label></th>
            <td>
                <input type="url" id="rd_ratings_url" name="rd_ratings_url"
                       value="<?php echo esc_url($rd['ratingsUrl'] ?? ''); ?>"
                       class="regular-text" placeholder="https://g.page/your-restaurant">
                <p class="description"><?php esc_html_e('Link to your Google Business, TripAdvisor, or other ratings page', 'ofero-generator'); ?></p>
            </td>
        </tr>
    </table>
</div>

<div class="ofero-card">
    <h2><?php esc_html_e('Amenities', 'ofero-generator'); ?></h2>
    <?php
    $allAmenities = array(
        'wifi'             => __('WiFi', 'ofero-generator'),
        'air-conditioning' => __('Air Conditioning', 'ofero-generator'),
        'heating'          => __('Heating', 'ofero-generator'),
        'terrace'          => __('Terrace', 'ofero-generator'),
        'garden'           => __('Garden', 'ofero-generator'),
        'live-music'       => __('Live Music', 'ofero-generator'),
        'tv'               => __('TV', 'ofero-generator'),
        'bar'              => __('Bar', 'ofero-generator'),
        'kids-menu'        => __('Kids Menu', 'ofero-generator'),
        'kids-play-area'   => __('Kids Play Area', 'ofero-generator'),
        'wheelchair-accessible' => __('Wheelchair Accessible', 'ofero-generator'),
        'pet-friendly'     => __('Pet Friendly', 'ofero-generator'),
        'smoking-area'     => __('Smoking Area', 'ofero-generator'),
        'non-smoking'      => __('Non-smoking', 'ofero-generator'),
        'valet-parking'    => __('Valet Parking', 'ofero-generator'),
        'private-events'   => __('Private Events', 'ofero-generator'),
        'gift-cards'       => __('Gift Cards', 'ofero-generator'),
    );
    ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 8px; margin-top: 8px;">
        <?php foreach ($allAmenities as $value => $label): ?>
            <label>
                <input type="checkbox" name="rd_amenities[]" value="<?php echo esc_attr($value); ?>"
                       <?php checked(in_array($value, $amenities)); ?>>
                <?php echo esc_html($label); ?>
            </label>
        <?php endforeach; ?>
    </div>
</div>
