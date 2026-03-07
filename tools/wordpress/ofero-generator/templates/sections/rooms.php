<?php
/**
 * Accommodation Details Section Template (Hotel, Hotel+Restaurant)
 *
 * @package Ofero_Generator
 */

if (!defined('ABSPATH')) {
    exit;
}

$ad       = $data['accommodationDetails'] ?? array();
$amenities = $ad['amenities'] ?? array();
$languages = $ad['languages'] ?? array();
?>

<div class="ofero-card">
    <h2><?php esc_html_e('Property Details', 'ofero-generator'); ?></h2>
    <p class="description">
        <?php esc_html_e('Basic static information about your property. For room types, rates and availability, use industry-standard systems (OTA, channel manager) and link to them below.', 'ofero-generator'); ?>
    </p>

    <table class="form-table">
        <tr>
            <th><label for="acc_property_type"><?php esc_html_e('Property Type', 'ofero-generator'); ?></label></th>
            <td>
                <select id="acc_property_type" name="acc_property_type">
                    <?php
                    $types = array(
                        ''           => __('— Select —', 'ofero-generator'),
                        'hotel'      => __('Hotel', 'ofero-generator'),
                        'motel'      => __('Motel', 'ofero-generator'),
                        'hostel'     => __('Hostel', 'ofero-generator'),
                        'apartment'  => __('Apartment / Aparthotel', 'ofero-generator'),
                        'resort'     => __('Resort', 'ofero-generator'),
                        'villa'      => __('Villa', 'ofero-generator'),
                        'guesthouse' => __('Guesthouse / Pensiune', 'ofero-generator'),
                        'bnb'        => __('Bed & Breakfast', 'ofero-generator'),
                        'campsite'   => __('Campsite', 'ofero-generator'),
                        'other'      => __('Other', 'ofero-generator'),
                    );
                    foreach ($types as $val => $lbl): ?>
                        <option value="<?php echo esc_attr($val); ?>" <?php selected($ad['propertyType'] ?? '', $val); ?>>
                            <?php echo esc_html($lbl); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="acc_star_rating"><?php esc_html_e('Star Rating', 'ofero-generator'); ?></label></th>
            <td>
                <select id="acc_star_rating" name="acc_star_rating">
                    <option value=""><?php esc_html_e('— Select —', 'ofero-generator'); ?></option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php selected($ad['starRating'] ?? '', $i); ?>>
                            <?php echo str_repeat('★', $i) . ' ' . sprintf(_n('%d star', '%d stars', $i, 'ofero-generator'), $i); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="acc_total_rooms"><?php esc_html_e('Total Rooms', 'ofero-generator'); ?></label></th>
            <td>
                <input type="number" id="acc_total_rooms" name="acc_total_rooms"
                       value="<?php echo esc_attr($ad['totalRooms'] ?? ''); ?>"
                       class="small-text" min="1">
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e('Check-in / Check-out', 'ofero-generator'); ?></th>
            <td>
                <label><?php esc_html_e('Check-in:', 'ofero-generator'); ?></label>
                <input type="time" name="acc_checkin" value="<?php echo esc_attr($ad['checkIn'] ?? '14:00'); ?>" style="margin: 0 16px 0 8px;">
                <label><?php esc_html_e('Check-out:', 'ofero-generator'); ?></label>
                <input type="time" name="acc_checkout" value="<?php echo esc_attr($ad['checkOut'] ?? '12:00'); ?>" style="margin-left: 8px;">
            </td>
        </tr>
        <tr>
            <th><label for="acc_languages"><?php esc_html_e('Staff Languages', 'ofero-generator'); ?></label></th>
            <td>
                <input type="text" id="acc_languages" name="acc_languages"
                       value="<?php echo esc_attr(implode(', ', $languages)); ?>"
                       class="regular-text" placeholder="en, ro, de, fr">
                <p class="description"><?php esc_html_e('Comma-separated ISO 639-1 language codes', 'ofero-generator'); ?></p>
            </td>
        </tr>
    </table>
</div>

<div class="ofero-card">
    <h2><?php esc_html_e('Amenities', 'ofero-generator'); ?></h2>
    <?php
    $allAmenities = array(
        'pool'               => __('Outdoor Pool', 'ofero-generator'),
        'indoor-pool'        => __('Indoor Pool', 'ofero-generator'),
        'spa'                => __('Spa', 'ofero-generator'),
        'gym'                => __('Gym / Fitness', 'ofero-generator'),
        'sauna'              => __('Sauna', 'ofero-generator'),
        'jacuzzi'            => __('Jacuzzi', 'ofero-generator'),
        'restaurant'         => __('Restaurant', 'ofero-generator'),
        'bar'                => __('Bar', 'ofero-generator'),
        'room-service'       => __('Room Service', 'ofero-generator'),
        'breakfast-included' => __('Breakfast Included', 'ofero-generator'),
        'parking'            => __('Free Parking', 'ofero-generator'),
        'valet-parking'      => __('Valet Parking', 'ofero-generator'),
        'ev-charging'        => __('EV Charging', 'ofero-generator'),
        'wifi'               => __('Free WiFi', 'ofero-generator'),
        'business-center'    => __('Business Center', 'ofero-generator'),
        'conference-rooms'   => __('Conference Rooms', 'ofero-generator'),
        'airport-shuttle'    => __('Airport Shuttle', 'ofero-generator'),
        'concierge'          => __('Concierge', 'ofero-generator'),
        '24h-reception'      => __('24h Reception', 'ofero-generator'),
        'pet-friendly'       => __('Pet Friendly', 'ofero-generator'),
        'wheelchair-accessible' => __('Wheelchair Accessible', 'ofero-generator'),
        'kids-club'          => __('Kids Club', 'ofero-generator'),
        'beach-access'       => __('Beach Access', 'ofero-generator'),
        'ski-in-ski-out'     => __('Ski-in / Ski-out', 'ofero-generator'),
        'garden'             => __('Garden', 'ofero-generator'),
        'terrace'            => __('Terrace', 'ofero-generator'),
    );
    ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 8px; margin-top: 8px;">
        <?php foreach ($allAmenities as $value => $label): ?>
            <label>
                <input type="checkbox" name="acc_amenities[]" value="<?php echo esc_attr($value); ?>"
                       <?php checked(in_array($value, $amenities)); ?>>
                <?php echo esc_html($label); ?>
            </label>
        <?php endforeach; ?>
    </div>
</div>

<div class="ofero-card">
    <h2><?php esc_html_e('Booking & External Links', 'ofero-generator'); ?></h2>
    <p class="description">
        <?php esc_html_e('Link to external systems for room types, rates and availability. These systems handle the complex data — ofero.json just references them.', 'ofero-generator'); ?>
    </p>

    <table class="form-table">
        <tr>
            <th><label for="acc_booking_url"><?php esc_html_e('Direct Booking URL', 'ofero-generator'); ?></label></th>
            <td>
                <input type="url" id="acc_booking_url" name="acc_booking_url"
                       value="<?php echo esc_url($ad['bookingUrl'] ?? ''); ?>"
                       class="regular-text" placeholder="https://yourhotel.com/book">
                <p class="description"><?php esc_html_e('Booking page on your own website', 'ofero-generator'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="acc_ratings_url"><?php esc_html_e('Ratings Page', 'ofero-generator'); ?></label></th>
            <td>
                <input type="url" id="acc_ratings_url" name="acc_ratings_url"
                       value="<?php echo esc_url($ad['ratingsUrl'] ?? ''); ?>"
                       class="regular-text" placeholder="https://booking.com/hotel/...">
                <p class="description"><?php esc_html_e('Your Booking.com, TripAdvisor or Google page. Do not embed scores — link here instead.', 'ofero-generator'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="acc_schema_url"><?php esc_html_e('Schema.org JSON-LD URL', 'ofero-generator'); ?></label></th>
            <td>
                <input type="url" id="acc_schema_url" name="acc_schema_url"
                       value="<?php echo esc_url($ad['schemaOrgUrl'] ?? ''); ?>"
                       class="regular-text" placeholder="https://yourhotel.com/schema.json">
                <p class="description">
                    <?php esc_html_e('URL to your Schema.org LodgingBusiness / HotelRoom JSON-LD file. Used by Google Hotels for rich results.', 'ofero-generator'); ?>
                    <a href="https://schema.org/Hotel" target="_blank"><?php esc_html_e('Schema.org/Hotel ↗', 'ofero-generator'); ?></a>
                </p>
            </td>
        </tr>
        <tr>
            <th><label for="acc_ota_feed"><?php esc_html_e('OTA Feed URL', 'ofero-generator'); ?></label></th>
            <td>
                <input type="url" id="acc_ota_feed" name="acc_ota_feed"
                       value="<?php echo esc_url($ad['otaFeedUrl'] ?? ''); ?>"
                       class="regular-text" placeholder="https://yourhotel.com/ota-feed.xml">
                <p class="description">
                    <?php esc_html_e('OpenTravel Alliance (OTA) XML feed — the standard used by Booking.com, Expedia and all channel managers for room types, rates and availability.', 'ofero-generator'); ?>
                    <a href="https://opentravel.org" target="_blank"><?php esc_html_e('opentravel.org ↗', 'ofero-generator'); ?></a>
                </p>
            </td>
        </tr>
        <tr>
            <th><label for="acc_channel_manager_id"><?php esc_html_e('Channel Manager Property ID', 'ofero-generator'); ?></label></th>
            <td>
                <input type="text" id="acc_channel_manager_id" name="acc_channel_manager_id"
                       value="<?php echo esc_attr($ad['channelManagerId'] ?? ''); ?>"
                       class="regular-text" placeholder="e.g., PROP-12345">
                <p class="description"><?php esc_html_e('Your property ID in SiteMinder, Myallocator, or other channel manager', 'ofero-generator'); ?></p>
            </td>
        </tr>
    </table>
</div>
