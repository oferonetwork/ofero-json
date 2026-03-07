<?php
/**
 * Plugin Name: Ofero Shortcodes
 * Plugin URI: https://ofero.me/ofero-json
 * Description: Display data from your ofero.json file using simple shortcodes. Compatible with Elementor, WPBakery, Gutenberg, and any theme.
 * Version: 1.3.0
 * Author: Ofero Network
 * Author URI: https://ofero.network
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: ofero-shortcodes
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('OFERO_SHORTCODES_VERSION', '1.3.0');
define('OFERO_SHORTCODES_PATH', plugin_dir_path(__FILE__));
define('OFERO_SHORTCODES_URL', plugin_dir_url(__FILE__));

// Include the parser class
require_once OFERO_SHORTCODES_PATH . 'includes/class-ofero-parser.php';

/**
 * Register Elementor Widgets
 */
function ofero_register_elementor_widgets() {
    // Check if Elementor is active
    if (!did_action('elementor/loaded')) {
        return;
    }

    // Include widget files
    require_once OFERO_SHORTCODES_PATH . 'includes/elementor/class-elementor-ofero-field-widget.php';
    require_once OFERO_SHORTCODES_PATH . 'includes/elementor/class-elementor-ofero-organization-widget.php';
    require_once OFERO_SHORTCODES_PATH . 'includes/elementor/class-elementor-ofero-location-widget.php';
    require_once OFERO_SHORTCODES_PATH . 'includes/elementor/class-elementor-ofero-social-widget.php';
    require_once OFERO_SHORTCODES_PATH . 'includes/elementor/class-elementor-ofero-banking-widget.php';

    // Register widgets
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Elementor_Ofero_Field_Widget());
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Elementor_Ofero_Organization_Widget());
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Elementor_Ofero_Location_Widget());
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Elementor_Ofero_Social_Widget());
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Elementor_Ofero_Banking_Widget());
}
add_action('elementor/widgets/widgets_registered', 'ofero_register_elementor_widgets');

/**
 * Add Elementor Widget Categories
 */
function ofero_add_elementor_widget_categories($elements_manager) {
    $elements_manager->add_category(
        'ofero',
        [
            'title' => __('Ofero', 'ofero-shortcodes'),
            'icon' => 'fa fa-plug',
        ]
    );
}
add_action('elementor/elements/categories_registered', 'ofero_add_elementor_widget_categories');

/**
 * Main plugin class
 */
class Ofero_Shortcodes {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Parser instance
     */
    private $parser;

    /**
     * Cached ofero.json data
     */
    private $cached_data = null;

    /**
     * Cache duration in seconds (1 hour)
     */
    const CACHE_DURATION = 3600;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->parser = new Ofero_Parser();
        $this->register_shortcodes();
        $this->register_admin_menu();
    }

    /**
     * Register all shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('ofero', array($this, 'ofero_shortcode'));
        add_shortcode('ofero_organization', array($this, 'organization_shortcode'));
        add_shortcode('ofero_location', array($this, 'location_shortcode'));
        add_shortcode('ofero_social', array($this, 'social_shortcode'));
        add_shortcode('ofero_banking', array($this, 'banking_shortcode'));
        add_shortcode('ofero_logo', array($this, 'logo_shortcode'));
        add_shortcode('ofero_hours', array($this, 'hours_shortcode'));
        add_shortcode('ofero_map', array($this, 'map_shortcode'));
        add_shortcode('ofero_team', array($this, 'team_shortcode'));
        add_shortcode('ofero_certificates', array($this, 'certificates_shortcode'));
        add_shortcode('ofero_promo', array($this, 'promo_shortcode'));
        add_shortcode('ofero_contact_form', array($this, 'contact_form_shortcode'));
    }

    /**
     * Register admin menu
     */
    private function register_admin_menu() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_options_page(
            'Ofero Shortcodes',
            'Ofero Shortcodes',
            'manage_options',
            'ofero-shortcodes',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('ofero_shortcodes_settings', 'ofero_json_path');
        register_setting('ofero_shortcodes_settings', 'ofero_json_url');
        register_setting('ofero_shortcodes_settings', 'ofero_cache_enabled');
    }

    /**
     * Render admin settings page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>Ofero Shortcodes Settings</h1>

            <form method="post" action="options.php">
                <?php settings_fields('ofero_shortcodes_settings'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="ofero_json_path">ofero.json Path</label>
                        </th>
                        <td>
                            <input type="text" id="ofero_json_path" name="ofero_json_path"
                                   value="<?php echo esc_attr(get_option('ofero_json_path', '.well-known/ofero.json')); ?>"
                                   class="regular-text">
                            <p class="description">
                                Relative path from WordPress root. Default: <code>.well-known/ofero.json</code>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="ofero_json_url">External URL (optional)</label>
                        </th>
                        <td>
                            <input type="url" id="ofero_json_url" name="ofero_json_url"
                                   value="<?php echo esc_attr(get_option('ofero_json_url', '')); ?>"
                                   class="regular-text">
                            <p class="description">
                                If set, loads ofero.json from this URL instead of local file.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="ofero_cache_enabled">Enable Cache</label>
                        </th>
                        <td>
                            <input type="checkbox" id="ofero_cache_enabled" name="ofero_cache_enabled"
                                   value="1" <?php checked(get_option('ofero_cache_enabled', true)); ?>>
                            <p class="description">
                                Cache ofero.json data for 1 hour to improve performance.
                            </p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <hr>

            <h2>Elementor Integration</h2>
            <?php if (did_action('elementor/loaded')): ?>
                <p style="color: green;">&#10003; <strong>Elementor is active!</strong> You can use Ofero widgets directly in Elementor:</p>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><strong>Ofero Field</strong> - Display any field from ofero.json</li>
                    <li><strong>Ofero Organization</strong> - Display organization information card</li>
                    <li><strong>Ofero Location</strong> - Display location information</li>
                    <li><strong>Ofero Social Media</strong> - Display social media links</li>
                    <li><strong>Ofero Banking</strong> - Display banking information</li>
                </ul>
                <p>All widgets are available in the <strong>"Ofero"</strong> category in Elementor's widget panel.</p>
            <?php else: ?>
                <p style="color: orange;">&#9888; Elementor is not active. Install and activate Elementor to use native Ofero widgets with drag & drop interface.</p>
            <?php endif; ?>

            <hr>

            <h2>Available Shortcodes</h2>

            <h3>Basic Shortcode</h3>
            <p>Use <code>[ofero field="path.to.field"]</code> to display any field from your ofero.json.</p>

            <table class="widefat" style="max-width: 800px;">
                <thead>
                    <tr>
                        <th>Shortcode</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>[ofero field="organization.legalName"]</code></td>
                        <td>Display legal name</td>
                    </tr>
                    <tr>
                        <td><code>[ofero field="organization.brandName"]</code></td>
                        <td>Display brand name</td>
                    </tr>
                    <tr>
                        <td><code>[ofero field="organization.contactEmail"]</code></td>
                        <td>Display contact email</td>
                    </tr>
                    <tr>
                        <td><code>[ofero field="organization.contactPhone"]</code></td>
                        <td>Display contact phone</td>
                    </tr>
                    <tr>
                        <td><code>[ofero field="locations.0.address.city"]</code></td>
                        <td>Display first location's city</td>
                    </tr>
                    <tr>
                        <td><code>[ofero field="banking.0.iban"]</code></td>
                        <td>Display first bank IBAN</td>
                    </tr>
                </tbody>
            </table>

            <h3>Organization Shortcode</h3>
            <p>Use <code>[ofero_organization]</code> to display a formatted organization card.</p>
            <pre>[ofero_organization show="name,email,phone,website" class="my-custom-class"]</pre>

            <h3>Location Shortcode</h3>
            <p>Use <code>[ofero_location]</code> to display location information.</p>
            <pre>[ofero_location index="0" show="name,address,phone,hours"]</pre>

            <h3>Social Shortcode</h3>
            <p>Use <code>[ofero_social]</code> to display social media links.</p>
            <pre>[ofero_social icons="true" class="social-icons"]</pre>

            <h3>Banking Shortcode</h3>
            <p>Use <code>[ofero_banking]</code> to display banking information.</p>
            <pre>[ofero_banking index="0" show="bank,iban,bic"]</pre>

            <h3>Logo Shortcode</h3>
            <p>Use <code>[ofero_logo]</code> to display your logo from brand assets.</p>
            <pre>[ofero_logo variant="light" width="200px"]</pre>
            <p class="description">Attributes: <code>variant</code> (light/dark/color), <code>width</code>, <code>height</code>, <code>alt</code></p>

            <h3>Business Hours Shortcode</h3>
            <p>Use <code>[ofero_hours]</code> to display business hours.</p>
            <pre>[ofero_hours location="0" format="table"]</pre>
            <p class="description">Format: <code>table</code> or <code>list</code></p>

            <h3>Map Shortcode</h3>
            <p>Use <code>[ofero_map]</code> to embed a Google Map for your location.</p>
            <pre>[ofero_map location="0" width="100%" height="400px" zoom="15"]</pre>
            <p class="description">Note: Requires Google Maps API key for full functionality.</p>

            <h3>Team Shortcode</h3>
            <p>Use <code>[ofero_team]</code> to display team members.</p>
            <pre>[ofero_team type="leadership" show="photo,name,role,bio" columns="3"]</pre>
            <p class="description">Types: <code>leadership</code>, <code>advisors</code>, <code>investors</code></p>

            <h3>Certificates Shortcode</h3>
            <p>Use <code>[ofero_certificates]</code> to display certifications.</p>
            <pre>[ofero_certificates show="name,issuer,date,link" columns="2"]</pre>
            <p class="description">Fields: <code>name</code>, <code>issuer</code>, <code>date</code>, <code>expiry</code>, <code>number</code>, <code>link</code></p>

            <h3>Promo Codes Shortcode</h3>
            <p>Use <code>[ofero_promo]</code> to display promotional codes.</p>
            <pre>[ofero_promo show="code,description,discount,expiry" active_only="true"]</pre>
            <p class="description">Automatically filters expired and inactive codes when <code>active_only="true"</code></p>

            <h3>Contact Form Shortcode</h3>
            <p>Use <code>[ofero_contact_form]</code> to display a contact form with auto-populated recipient email.</p>
            <pre>[ofero_contact_form fields="name,email,phone,subject,message" submit_text="Send Message"]</pre>
            <p class="description">Emails are sent to the contact email from your ofero.json file.</p>

            <hr>

            <h2>Current ofero.json Status</h2>
            <?php
            $data = $this->get_ofero_data();
            if ($data) {
                echo '<p style="color: green;">&#10003; ofero.json loaded successfully.</p>';
                echo '<p><strong>Organization:</strong> ' . esc_html($data['organization']['legalName'] ?? 'Not set') . '</p>';
                echo '<p><strong>Domain:</strong> ' . esc_html($data['domain'] ?? 'Not set') . '</p>';
                echo '<p><strong>Last Updated:</strong> ' . esc_html($data['metadata']['lastUpdated'] ?? 'Not set') . '</p>';
            } else {
                echo '<p style="color: red;">&#10007; ofero.json not found or invalid.</p>';
            }
            ?>

            <p>
                <a href="<?php echo esc_url(admin_url('options-general.php?page=ofero-shortcodes&clear_cache=1')); ?>"
                   class="button">Clear Cache</a>
            </p>
        </div>
        <?php

        // Handle cache clearing
        if (isset($_GET['clear_cache'])) {
            delete_transient('ofero_json_data');
            echo '<div class="notice notice-success"><p>Cache cleared successfully.</p></div>';
        }
    }

    /**
     * Get ofero.json data (with caching)
     */
    public function get_ofero_data() {
        // Check cache first
        if (get_option('ofero_cache_enabled', true)) {
            $cached = get_transient('ofero_json_data');
            if ($cached !== false) {
                return $cached;
            }
        }

        $data = null;

        // Try external URL first
        $external_url = get_option('ofero_json_url', '');
        if (!empty($external_url)) {
            $response = wp_remote_get($external_url, array('timeout' => 10));
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);
            }
        }

        // Fall back to local file
        if (!$data) {
            $path = get_option('ofero_json_path', '.well-known/ofero.json');
            $full_path = ABSPATH . $path;

            if (file_exists($full_path)) {
                $content = file_get_contents($full_path);
                $data = json_decode($content, true);
            }
        }

        // Cache the result
        if ($data && get_option('ofero_cache_enabled', true)) {
            set_transient('ofero_json_data', $data, self::CACHE_DURATION);
        }

        return $data;
    }

    /**
     * Main ofero shortcode handler
     */
    public function ofero_shortcode($atts) {
        $atts = shortcode_atts(array(
            'field' => '',
            'default' => '',
            'format' => 'text',
            'link' => 'false',
        ), $atts, 'ofero');

        if (empty($atts['field'])) {
            return '';
        }

        $data = $this->get_ofero_data();
        if (!$data) {
            return esc_html($atts['default']);
        }

        $value = $this->parser->get_field($data, $atts['field']);

        if ($value === null) {
            return esc_html($atts['default']);
        }

        // Handle arrays
        if (is_array($value)) {
            $value = implode(', ', array_filter($value, 'is_string'));
        }

        // Format output
        $output = esc_html($value);

        // Auto-link emails and URLs
        if ($atts['link'] === 'true') {
            if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $output = '<a href="mailto:' . esc_attr($value) . '">' . $output . '</a>';
            } elseif (filter_var($value, FILTER_VALIDATE_URL)) {
                $output = '<a href="' . esc_url($value) . '" target="_blank" rel="noopener">' . $output . '</a>';
            } elseif (preg_match('/^\+?[\d\s\-()]+$/', $value)) {
                $tel = preg_replace('/[^\d+]/', '', $value);
                $output = '<a href="tel:' . esc_attr($tel) . '">' . $output . '</a>';
            }
        }

        return $output;
    }

    /**
     * Organization shortcode handler
     */
    public function organization_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show' => 'name,description,email,phone,website',
            'class' => 'ofero-organization',
        ), $atts, 'ofero_organization');

        $data = $this->get_ofero_data();
        if (!$data || !isset($data['organization'])) {
            return '';
        }

        $org = $data['organization'];
        $fields = array_map('trim', explode(',', $atts['show']));

        $output = '<div class="' . esc_attr($atts['class']) . '">';

        foreach ($fields as $field) {
            switch ($field) {
                case 'name':
                    if (!empty($org['brandName']) || !empty($org['legalName'])) {
                        $name = !empty($org['brandName']) ? $org['brandName'] : $org['legalName'];
                        $output .= '<div class="ofero-org-name">' . esc_html($name) . '</div>';
                    }
                    break;

                case 'legal_name':
                    if (!empty($org['legalName'])) {
                        $output .= '<div class="ofero-org-legal-name">' . esc_html($org['legalName']) . '</div>';
                    }
                    break;

                case 'description':
                    if (!empty($org['description'])) {
                        $output .= '<div class="ofero-org-description">' . esc_html($org['description']) . '</div>';
                    }
                    break;

                case 'email':
                    if (!empty($org['contactEmail'])) {
                        $output .= '<div class="ofero-org-email">';
                        $output .= '<a href="mailto:' . esc_attr($org['contactEmail']) . '">';
                        $output .= esc_html($org['contactEmail']);
                        $output .= '</a></div>';
                    }
                    break;

                case 'phone':
                    if (!empty($org['contactPhone'])) {
                        $tel = preg_replace('/[^\d+]/', '', $org['contactPhone']);
                        $output .= '<div class="ofero-org-phone">';
                        $output .= '<a href="tel:' . esc_attr($tel) . '">';
                        $output .= esc_html($org['contactPhone']);
                        $output .= '</a></div>';
                    }
                    break;

                case 'website':
                    if (!empty($org['website'])) {
                        $output .= '<div class="ofero-org-website">';
                        $output .= '<a href="' . esc_url($org['website']) . '" target="_blank" rel="noopener">';
                        $output .= esc_html(preg_replace('#^https?://#', '', $org['website']));
                        $output .= '</a></div>';
                    }
                    break;
            }
        }

        $output .= '</div>';
        return $output;
    }

    /**
     * Location shortcode handler
     */
    public function location_shortcode($atts) {
        $atts = shortcode_atts(array(
            'index' => '0',
            'show' => 'name,address,phone,email',
            'class' => 'ofero-location',
            'photo_size' => '300',
        ), $atts, 'ofero_location');

        $data = $this->get_ofero_data();
        if (!$data || !isset($data['locations'])) {
            return '';
        }

        $index = intval($atts['index']);
        if (!isset($data['locations'][$index])) {
            return '';
        }

        $loc = $data['locations'][$index];
        $fields = array_map('trim', explode(',', $atts['show']));

        $output = '<div class="' . esc_attr($atts['class']) . '">';

        foreach ($fields as $field) {
            switch ($field) {
                case 'name':
                    if (!empty($loc['name'])) {
                        $output .= '<div class="ofero-loc-name">' . esc_html($loc['name']) . '</div>';
                    }
                    break;

                case 'type':
                    if (!empty($loc['type'])) {
                        $output .= '<div class="ofero-loc-type">' . esc_html(ucfirst($loc['type'])) . '</div>';
                    }
                    break;

                case 'address':
                    if (!empty($loc['address'])) {
                        $addr = $loc['address'];
                        $parts = array_filter(array(
                            $addr['street'] ?? '',
                            $addr['city'] ?? '',
                            $addr['postalCode'] ?? '',
                            $addr['country'] ?? ''
                        ));
                        if (!empty($parts)) {
                            $output .= '<div class="ofero-loc-address">' . esc_html(implode(', ', $parts)) . '</div>';
                        }
                    }
                    break;

                case 'phone':
                    if (!empty($loc['phone'])) {
                        $tel = preg_replace('/[^\d+]/', '', $loc['phone']);
                        $output .= '<div class="ofero-loc-phone">';
                        $output .= '<a href="tel:' . esc_attr($tel) . '">' . esc_html($loc['phone']) . '</a>';
                        $output .= '</div>';
                    }
                    break;

                case 'email':
                    if (!empty($loc['email'])) {
                        $output .= '<div class="ofero-loc-email">';
                        $output .= '<a href="mailto:' . esc_attr($loc['email']) . '">' . esc_html($loc['email']) . '</a>';
                        $output .= '</div>';
                    }
                    break;

                case 'hours':
                    if (!empty($loc['hours'])) {
                        $output .= '<div class="ofero-loc-hours">';
                        if (is_array($loc['hours'])) {
                            foreach ($loc['hours'] as $day => $time) {
                                $output .= '<div>' . esc_html(ucfirst($day) . ': ' . $time) . '</div>';
                            }
                        } else {
                            $output .= esc_html($loc['hours']);
                        }
                        $output .= '</div>';
                    }
                    break;

                case 'photos':
                    if (!empty($loc['photos']) && is_array($loc['photos'])) {
                        $size = intval($atts['photo_size']);
                        $output .= '<div class="ofero-loc-photos">';
                        foreach ($loc['photos'] as $photo_url) {
                            $output .= '<img src="' . esc_url($photo_url) . '" alt="' . esc_attr($loc['name'] ?? '') . '" loading="lazy"';
                            if ($size > 0) {
                                $output .= ' style="max-width:' . $size . 'px;"';
                            }
                            $output .= '>';
                        }
                        $output .= '</div>';
                    }
                    break;

                case 'contacts':
                    if (!empty($loc['contacts']) && is_array($loc['contacts'])) {
                        $output .= '<div class="ofero-loc-contacts">';
                        foreach ($loc['contacts'] as $contact) {
                            if (isset($contact['public']) && !$contact['public']) {
                                continue;
                            }
                            $output .= '<div class="ofero-loc-contact">';
                            if (!empty($contact['photo'])) {
                                $output .= '<img src="' . esc_url($contact['photo']) . '" alt="' . esc_attr($contact['name'] ?? '') . '" class="ofero-contact-photo" loading="lazy">';
                            }
                            if (!empty($contact['name'])) {
                                $output .= '<div class="ofero-contact-name">' . esc_html($contact['name']) . '</div>';
                            }
                            if (!empty($contact['role'])) {
                                $output .= '<div class="ofero-contact-role">' . esc_html($contact['role']) . '</div>';
                            }
                            if (!empty($contact['phone'])) {
                                $tel = preg_replace('/[^\d+]/', '', $contact['phone']);
                                $output .= '<div class="ofero-contact-phone"><a href="tel:' . esc_attr($tel) . '">' . esc_html($contact['phone']) . '</a></div>';
                            }
                            if (!empty($contact['email'])) {
                                $output .= '<div class="ofero-contact-email"><a href="mailto:' . esc_attr($contact['email']) . '">' . esc_html($contact['email']) . '</a></div>';
                            }
                            $output .= '</div>';
                        }
                        $output .= '</div>';
                    }
                    break;
            }
        }

        $output .= '</div>';
        return $output;
    }

    /**
     * Social shortcode handler
     */
    public function social_shortcode($atts) {
        $atts = shortcode_atts(array(
            'icons' => 'false',
            'platforms' => '',
            'class' => 'ofero-social',
        ), $atts, 'ofero_social');

        $data = $this->get_ofero_data();
        if (!$data || !isset($data['communications']['social'])) {
            return '';
        }

        $social = $data['communications']['social'];
        $filter_platforms = !empty($atts['platforms']) ? array_map('trim', explode(',', $atts['platforms'])) : array();

        $output = '<div class="' . esc_attr($atts['class']) . '">';

        foreach ($social as $item) {
            $platform = $item['platform'] ?? '';
            $url = $item['url'] ?? '';

            if (empty($platform) || empty($url)) {
                continue;
            }

            if (!empty($filter_platforms) && !in_array($platform, $filter_platforms)) {
                continue;
            }

            $output .= '<a href="' . esc_url($url) . '" class="ofero-social-link ofero-social-' . esc_attr($platform) . '" target="_blank" rel="noopener">';

            if ($atts['icons'] === 'true') {
                $output .= '<span class="ofero-social-icon ofero-icon-' . esc_attr($platform) . '"></span>';
            }

            $output .= '<span class="ofero-social-name">' . esc_html(ucfirst($platform)) . '</span>';
            $output .= '</a>';
        }

        $output .= '</div>';
        return $output;
    }

    /**
     * Banking shortcode handler
     */
    public function banking_shortcode($atts) {
        $atts = shortcode_atts(array(
            'index' => '0',
            'show' => 'account,bank,iban,bic,currency',
            'class' => 'ofero-banking',
        ), $atts, 'ofero_banking');

        $data = $this->get_ofero_data();
        if (!$data || !isset($data['banking'])) {
            return '';
        }

        $index = intval($atts['index']);
        if (!isset($data['banking'][$index])) {
            return '';
        }

        $bank = $data['banking'][$index];
        $fields = array_map('trim', explode(',', $atts['show']));

        $output = '<div class="' . esc_attr($atts['class']) . '">';

        foreach ($fields as $field) {
            switch ($field) {
                case 'account':
                    if (!empty($bank['accountName'])) {
                        $output .= '<div class="ofero-bank-account">';
                        $output .= '<span class="ofero-label">Account:</span> ';
                        $output .= esc_html($bank['accountName']);
                        $output .= '</div>';
                    }
                    break;

                case 'bank':
                    if (!empty($bank['bankName'])) {
                        $output .= '<div class="ofero-bank-name">';
                        $output .= '<span class="ofero-label">Bank:</span> ';
                        $output .= esc_html($bank['bankName']);
                        $output .= '</div>';
                    }
                    break;

                case 'iban':
                    if (!empty($bank['iban'])) {
                        $output .= '<div class="ofero-bank-iban">';
                        $output .= '<span class="ofero-label">IBAN:</span> ';
                        $output .= '<code>' . esc_html($bank['iban']) . '</code>';
                        $output .= '</div>';
                    }
                    break;

                case 'bic':
                    if (!empty($bank['bic'])) {
                        $output .= '<div class="ofero-bank-bic">';
                        $output .= '<span class="ofero-label">BIC/SWIFT:</span> ';
                        $output .= '<code>' . esc_html($bank['bic']) . '</code>';
                        $output .= '</div>';
                    }
                    break;

                case 'currency':
                    if (!empty($bank['currency'])) {
                        $output .= '<div class="ofero-bank-currency">';
                        $output .= '<span class="ofero-label">Currency:</span> ';
                        $output .= esc_html($bank['currency']);
                        $output .= '</div>';
                    }
                    break;
            }
        }

        $output .= '</div>';
        return $output;
    }

    /**
     * Logo shortcode handler
     */
    public function logo_shortcode($atts) {
        $atts = shortcode_atts(array(
            'type' => 'primary',
            'format' => 'png',
            'variant' => '',
            'class' => 'ofero-logo',
            'width' => '',
            'height' => '',
            'alt' => '',
        ), $atts, 'ofero_logo');

        $data = $this->get_ofero_data();
        if (!$data || !isset($data['brandAssets'])) {
            return '';
        }

        $logo_url = '';
        $logo_alt = $atts['alt'];

        // Try to find matching logo
        if (!empty($data['brandAssets']['logos']['vector'])) {
            foreach ($data['brandAssets']['logos']['vector'] as $logo) {
                if (!empty($atts['variant']) && isset($logo['colorVariant']) && $logo['colorVariant'] === $atts['variant']) {
                    $logo_url = $logo['url'] ?? '';
                    $logo_alt = $logo['alt'] ?? $logo_alt;
                    break;
                } elseif (isset($logo['primary']) && $logo['primary']) {
                    $logo_url = $logo['url'] ?? '';
                    $logo_alt = $logo['alt'] ?? $logo_alt;
                }
            }
        }

        // Fallback to raster logos
        if (empty($logo_url) && !empty($data['brandAssets']['logos']['raster'])) {
            foreach ($data['brandAssets']['logos']['raster'] as $logo) {
                if ($logo['type'] === $atts['format']) {
                    $logo_url = $logo['url'] ?? '';
                    break;
                }
            }
        }

        if (empty($logo_url)) {
            return '';
        }

        $style = '';
        if (!empty($atts['width'])) {
            $style .= 'width: ' . esc_attr($atts['width']) . ';';
        }
        if (!empty($atts['height'])) {
            $style .= 'height: ' . esc_attr($atts['height']) . ';';
        }

        $output = '<img src="' . esc_url($logo_url) . '" ';
        $output .= 'class="' . esc_attr($atts['class']) . '" ';
        if (!empty($logo_alt)) {
            $output .= 'alt="' . esc_attr($logo_alt) . '" ';
        }
        if (!empty($style)) {
            $output .= 'style="' . $style . '" ';
        }
        $output .= '/>';

        return $output;
    }

    /**
     * Business hours shortcode handler
     */
    public function hours_shortcode($atts) {
        $atts = shortcode_atts(array(
            'location' => '0',
            'class' => 'ofero-hours',
            'format' => 'table',
        ), $atts, 'ofero_hours');

        $data = $this->get_ofero_data();
        if (!$data || !isset($data['locations'])) {
            return '';
        }

        $index = intval($atts['location']);
        if (!isset($data['locations'][$index]) || empty($data['locations'][$index]['hours'])) {
            return '';
        }

        $hours = $data['locations'][$index]['hours'];

        if ($atts['format'] === 'table') {
            $output = '<table class="' . esc_attr($atts['class']) . '">';
            foreach ($hours as $day => $time) {
                $output .= '<tr>';
                $output .= '<td class="ofero-hours-day">' . esc_html(ucfirst($day)) . '</td>';
                $output .= '<td class="ofero-hours-time">' . esc_html($time) . '</td>';
                $output .= '</tr>';
            }
            $output .= '</table>';
        } else {
            $output = '<div class="' . esc_attr($atts['class']) . '">';
            foreach ($hours as $day => $time) {
                $output .= '<div class="ofero-hours-row">';
                $output .= '<span class="ofero-hours-day">' . esc_html(ucfirst($day)) . ':</span> ';
                $output .= '<span class="ofero-hours-time">' . esc_html($time) . '</span>';
                $output .= '</div>';
            }
            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Map shortcode handler
     */
    public function map_shortcode($atts) {
        $atts = shortcode_atts(array(
            'location' => '0',
            'width' => '100%',
            'height' => '400px',
            'zoom' => '15',
            'class' => 'ofero-map',
        ), $atts, 'ofero_map');

        $data = $this->get_ofero_data();
        if (!$data || !isset($data['locations'])) {
            return '';
        }

        $index = intval($atts['location']);
        if (!isset($data['locations'][$index]) || empty($data['locations'][$index]['address'])) {
            return '';
        }

        $address = $data['locations'][$index]['address'];

        // Build address string
        $address_parts = array_filter(array(
            $address['street'] ?? '',
            $address['city'] ?? '',
            $address['postalCode'] ?? '',
            $address['country'] ?? ''
        ));

        if (empty($address_parts)) {
            return '';
        }

        $address_string = implode(', ', $address_parts);
        $encoded_address = urlencode($address_string);

        // Use Google Maps embed
        $output = '<div class="' . esc_attr($atts['class']) . '">';
        $output .= '<iframe ';
        $output .= 'width="' . esc_attr($atts['width']) . '" ';
        $output .= 'height="' . esc_attr($atts['height']) . '" ';
        $output .= 'style="border:0" ';
        $output .= 'loading="lazy" ';
        $output .= 'allowfullscreen ';
        $output .= 'src="https://www.google.com/maps/embed/v1/place?key=&q=' . $encoded_address . '&zoom=' . esc_attr($atts['zoom']) . '">';
        $output .= '</iframe>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Team shortcode handler
     */
    public function team_shortcode($atts) {
        $atts = shortcode_atts(array(
            'type' => 'leadership',
            'show' => 'photo,name,role,bio',
            'class' => 'ofero-team',
            'columns' => '3',
        ), $atts, 'ofero_team');

        $data = $this->get_ofero_data();
        if (!$data || !isset($data['team'])) {
            return '';
        }

        $team_data = [];
        switch ($atts['type']) {
            case 'leadership':
                $team_data = $data['team']['leadership'] ?? [];
                break;
            case 'advisors':
                $team_data = $data['team']['advisors'] ?? [];
                break;
            case 'investors':
                $team_data = $data['team']['investors'] ?? [];
                break;
            default:
                $team_data = $data['team']['leadership'] ?? [];
        }

        if (empty($team_data)) {
            return '';
        }

        $fields = array_map('trim', explode(',', $atts['show']));
        $columns = intval($atts['columns']);

        $output = '<div class="' . esc_attr($atts['class']) . '" style="display: grid; grid-template-columns: repeat(' . $columns . ', 1fr); gap: 1.5em;">';

        foreach ($team_data as $member) {
            $output .= '<div class="ofero-team-member">';

            foreach ($fields as $field) {
                switch ($field) {
                    case 'photo':
                        if (!empty($member['photo'])) {
                            $output .= '<div class="ofero-team-photo">';
                            $output .= '<img src="' . esc_url($member['photo']) . '" alt="' . esc_attr($member['name'] ?? '') . '">';
                            $output .= '</div>';
                        }
                        break;

                    case 'name':
                        if (!empty($member['name'])) {
                            $output .= '<h4 class="ofero-team-name">' . esc_html($member['name']) . '</h4>';
                        }
                        break;

                    case 'role':
                        if (!empty($member['role'])) {
                            $output .= '<p class="ofero-team-role">' . esc_html($member['role']) . '</p>';
                        }
                        break;

                    case 'bio':
                        if (!empty($member['bio'])) {
                            $output .= '<p class="ofero-team-bio">' . esc_html($member['bio']) . '</p>';
                        }
                        break;

                    case 'social':
                        if (!empty($member['social'])) {
                            $output .= '<div class="ofero-team-social">';
                            foreach ($member['social'] as $platform => $url) {
                                $output .= '<a href="' . esc_url($url) . '" target="_blank" rel="noopener">';
                                $output .= esc_html(ucfirst($platform));
                                $output .= '</a> ';
                            }
                            $output .= '</div>';
                        }
                        break;
                }
            }

            $output .= '</div>';
        }

        $output .= '</div>';
        return $output;
    }

    /**
     * Certificates shortcode handler
     */
    public function certificates_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show' => 'name,issuer,date',
            'class' => 'ofero-certificates',
            'columns' => '2',
        ), $atts, 'ofero_certificates');

        $data = $this->get_ofero_data();
        if (!$data || !isset($data['certificates']) || empty($data['certificates'])) {
            return '';
        }

        $fields = array_map('trim', explode(',', $atts['show']));
        $columns = intval($atts['columns']);

        $output = '<div class="' . esc_attr($atts['class']) . '" style="display: grid; grid-template-columns: repeat(' . $columns . ', 1fr); gap: 1em;">';

        foreach ($data['certificates'] as $cert) {
            $output .= '<div class="ofero-certificate-item">';

            foreach ($fields as $field) {
                switch ($field) {
                    case 'name':
                        if (!empty($cert['name'])) {
                            $output .= '<h4 class="ofero-cert-name">' . esc_html($cert['name']) . '</h4>';
                        }
                        break;

                    case 'issuer':
                        if (!empty($cert['issuer'])) {
                            $output .= '<p class="ofero-cert-issuer">';
                            $output .= '<strong>Issued by:</strong> ' . esc_html($cert['issuer']);
                            $output .= '</p>';
                        }
                        break;

                    case 'date':
                        if (!empty($cert['issueDate'])) {
                            $output .= '<p class="ofero-cert-date">';
                            $output .= '<strong>Date:</strong> ' . esc_html($cert['issueDate']);
                            $output .= '</p>';
                        }
                        break;

                    case 'expiry':
                        if (!empty($cert['expiryDate'])) {
                            $output .= '<p class="ofero-cert-expiry">';
                            $output .= '<strong>Expires:</strong> ' . esc_html($cert['expiryDate']);
                            $output .= '</p>';
                        }
                        break;

                    case 'number':
                        if (!empty($cert['certificateNumber'])) {
                            $output .= '<p class="ofero-cert-number">';
                            $output .= '<strong>Number:</strong> <code>' . esc_html($cert['certificateNumber']) . '</code>';
                            $output .= '</p>';
                        }
                        break;

                    case 'link':
                        if (!empty($cert['verificationUrl'])) {
                            $output .= '<p class="ofero-cert-link">';
                            $output .= '<a href="' . esc_url($cert['verificationUrl']) . '" target="_blank" rel="noopener">Verify Certificate</a>';
                            $output .= '</p>';
                        }
                        break;
                }
            }

            $output .= '</div>';
        }

        $output .= '</div>';
        return $output;
    }

    /**
     * Promo code shortcode handler
     */
    public function promo_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show' => 'code,description,discount',
            'class' => 'ofero-promo',
            'active_only' => 'true',
        ), $atts, 'ofero_promo');

        $data = $this->get_ofero_data();
        if (!$data || !isset($data['promoCodes']) || empty($data['promoCodes'])) {
            return '';
        }

        $fields = array_map('trim', explode(',', $atts['show']));
        $active_only = $atts['active_only'] === 'true';

        $output = '<div class="' . esc_attr($atts['class']) . '">';

        foreach ($data['promoCodes'] as $promo) {
            // Skip inactive promos if active_only is true
            if ($active_only && isset($promo['active']) && !$promo['active']) {
                continue;
            }

            // Check expiry date
            if ($active_only && !empty($promo['validUntil'])) {
                $expiry = strtotime($promo['validUntil']);
                if ($expiry && $expiry < time()) {
                    continue;
                }
            }

            $output .= '<div class="ofero-promo-item">';

            foreach ($fields as $field) {
                switch ($field) {
                    case 'code':
                        if (!empty($promo['code'])) {
                            $output .= '<div class="ofero-promo-code">';
                            $output .= '<strong>Code:</strong> <code>' . esc_html($promo['code']) . '</code>';
                            $output .= '</div>';
                        }
                        break;

                    case 'description':
                        if (!empty($promo['description'])) {
                            $output .= '<p class="ofero-promo-description">' . esc_html($promo['description']) . '</p>';
                        }
                        break;

                    case 'discount':
                        if (!empty($promo['discountPercentage'])) {
                            $output .= '<p class="ofero-promo-discount">';
                            $output .= '<strong>' . esc_html($promo['discountPercentage']) . '%</strong> OFF';
                            $output .= '</p>';
                        } elseif (!empty($promo['discountAmount'])) {
                            $output .= '<p class="ofero-promo-discount">';
                            $output .= '<strong>' . esc_html($promo['discountAmount']) . ' ' . esc_html($promo['currency'] ?? '') . '</strong> OFF';
                            $output .= '</p>';
                        }
                        break;

                    case 'expiry':
                        if (!empty($promo['validUntil'])) {
                            $output .= '<p class="ofero-promo-expiry">';
                            $output .= '<small>Valid until: ' . esc_html($promo['validUntil']) . '</small>';
                            $output .= '</p>';
                        }
                        break;

                    case 'terms':
                        if (!empty($promo['terms'])) {
                            $output .= '<p class="ofero-promo-terms">';
                            $output .= '<small>' . esc_html($promo['terms']) . '</small>';
                            $output .= '</p>';
                        }
                        break;
                }
            }

            $output .= '</div>';
        }

        $output .= '</div>';
        return $output;
    }

    /**
     * Contact form shortcode handler
     */
    public function contact_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'class' => 'ofero-contact-form',
            'submit_text' => 'Send Message',
            'fields' => 'name,email,phone,message',
        ), $atts, 'ofero_contact_form');

        $data = $this->get_ofero_data();
        $org = $data['organization'] ?? [];

        $fields = array_map('trim', explode(',', $atts['fields']));

        $output = '<form class="' . esc_attr($atts['class']) . '" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        $output .= '<input type="hidden" name="action" value="ofero_contact_form">';
        $output .= wp_nonce_field('ofero_contact_form', 'ofero_contact_nonce', true, false);

        foreach ($fields as $field) {
            switch ($field) {
                case 'name':
                    $output .= '<div class="ofero-form-group">';
                    $output .= '<label for="ofero_name">Name <span class="required">*</span></label>';
                    $output .= '<input type="text" id="ofero_name" name="ofero_name" required>';
                    $output .= '</div>';
                    break;

                case 'email':
                    $output .= '<div class="ofero-form-group">';
                    $output .= '<label for="ofero_email">Email <span class="required">*</span></label>';
                    $output .= '<input type="email" id="ofero_email" name="ofero_email" required>';
                    $output .= '</div>';
                    break;

                case 'phone':
                    $output .= '<div class="ofero-form-group">';
                    $output .= '<label for="ofero_phone">Phone</label>';
                    $output .= '<input type="tel" id="ofero_phone" name="ofero_phone">';
                    $output .= '</div>';
                    break;

                case 'subject':
                    $output .= '<div class="ofero-form-group">';
                    $output .= '<label for="ofero_subject">Subject</label>';
                    $output .= '<input type="text" id="ofero_subject" name="ofero_subject">';
                    $output .= '</div>';
                    break;

                case 'message':
                    $output .= '<div class="ofero-form-group">';
                    $output .= '<label for="ofero_message">Message <span class="required">*</span></label>';
                    $output .= '<textarea id="ofero_message" name="ofero_message" rows="5" required></textarea>';
                    $output .= '</div>';
                    break;
            }
        }

        $output .= '<div class="ofero-form-group">';
        $output .= '<button type="submit" class="ofero-submit-btn">' . esc_html($atts['submit_text']) . '</button>';
        $output .= '</div>';

        // Hidden field with contact email from ofero.json
        if (!empty($org['contactEmail'])) {
            $output .= '<input type="hidden" name="ofero_recipient" value="' . esc_attr($org['contactEmail']) . '">';
        }

        $output .= '</form>';
        return $output;
    }
}

// Initialize plugin
add_action('plugins_loaded', function() {
    Ofero_Shortcodes::get_instance();
});

/**
 * Handle contact form submission
 */
add_action('admin_post_nopriv_ofero_contact_form', 'ofero_handle_contact_form');
add_action('admin_post_ofero_contact_form', 'ofero_handle_contact_form');

function ofero_handle_contact_form() {
    // Verify nonce
    if (!isset($_POST['ofero_contact_nonce']) || !wp_verify_nonce($_POST['ofero_contact_nonce'], 'ofero_contact_form')) {
        wp_die('Security check failed');
    }

    // Get form data
    $name = sanitize_text_field($_POST['ofero_name'] ?? '');
    $email = sanitize_email($_POST['ofero_email'] ?? '');
    $phone = sanitize_text_field($_POST['ofero_phone'] ?? '');
    $subject = sanitize_text_field($_POST['ofero_subject'] ?? 'Contact Form Submission');
    $message = sanitize_textarea_field($_POST['ofero_message'] ?? '');
    $recipient = sanitize_email($_POST['ofero_recipient'] ?? get_option('admin_email'));

    // Validate required fields
    if (empty($name) || empty($email) || empty($message)) {
        wp_die('Please fill in all required fields');
    }

    // Prepare email
    $to = $recipient;
    $email_subject = '[Contact Form] ' . $subject;
    $email_body = "Name: $name\n";
    $email_body .= "Email: $email\n";
    if (!empty($phone)) {
        $email_body .= "Phone: $phone\n";
    }
    $email_body .= "\nMessage:\n$message\n";

    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        'Reply-To: ' . $name . ' <' . $email . '>'
    );

    // Send email
    $sent = wp_mail($to, $email_subject, $email_body, $headers);

    // Redirect back
    $redirect = wp_get_referer() ? wp_get_referer() : home_url();
    if ($sent) {
        $redirect = add_query_arg('contact', 'success', $redirect);
    } else {
        $redirect = add_query_arg('contact', 'error', $redirect);
    }

    wp_redirect($redirect);
    exit;
}

// Add basic CSS
add_action('wp_head', function() {
    ?>
    <style>
        .ofero-organization,
        .ofero-location,
        .ofero-banking {
            margin-bottom: 1em;
        }
        .ofero-social {
            display: flex;
            gap: 1em;
            flex-wrap: wrap;
        }
        .ofero-social-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5em;
            text-decoration: none;
        }
        .ofero-label {
            font-weight: 600;
        }
        .ofero-bank-iban code,
        .ofero-bank-bic code {
            background: #f5f5f5;
            padding: 0.2em 0.5em;
            border-radius: 3px;
            font-family: monospace;
        }
        .ofero-logo {
            max-width: 100%;
            height: auto;
        }
        .ofero-hours {
            margin-bottom: 1em;
        }
        .ofero-hours table {
            width: 100%;
            border-collapse: collapse;
        }
        .ofero-hours td {
            padding: 0.5em;
            border-bottom: 1px solid #eee;
        }
        .ofero-hours-day {
            font-weight: 600;
        }
        .ofero-hours-row {
            padding: 0.25em 0;
        }
        .ofero-map {
            margin-bottom: 1em;
        }
        .ofero-map iframe {
            width: 100%;
            display: block;
        }
        .ofero-team-member {
            text-align: center;
            padding: 1em;
            border: 1px solid #eee;
            border-radius: 8px;
        }
        .ofero-team-photo img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1em;
        }
        .ofero-team-name {
            margin: 0.5em 0 0.25em;
            font-size: 1.2em;
        }
        .ofero-team-role {
            color: #666;
            font-size: 0.9em;
            margin: 0 0 0.5em;
        }
        .ofero-team-bio {
            font-size: 0.9em;
            line-height: 1.5;
        }
        .ofero-team-social a {
            margin: 0 0.5em;
        }
        .ofero-certificate-item {
            padding: 1em;
            border: 1px solid #eee;
            border-radius: 8px;
            background: #f9f9f9;
        }
        .ofero-cert-name {
            margin: 0 0 0.5em;
        }
        .ofero-promo-item {
            padding: 1em;
            margin-bottom: 1em;
            border: 2px dashed #4CAF50;
            border-radius: 8px;
            background: #f1f8f4;
        }
        .ofero-promo-code code {
            background: #4CAF50;
            color: white;
            padding: 0.5em 1em;
            border-radius: 4px;
            font-size: 1.2em;
            font-weight: bold;
        }
        .ofero-promo-discount {
            font-size: 1.5em;
            color: #4CAF50;
            margin: 0.5em 0;
        }
        .ofero-contact-form {
            max-width: 600px;
        }
        .ofero-form-group {
            margin-bottom: 1em;
        }
        .ofero-form-group label {
            display: block;
            margin-bottom: 0.5em;
            font-weight: 600;
        }
        .ofero-form-group input,
        .ofero-form-group textarea {
            width: 100%;
            padding: 0.75em;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
        }
        .ofero-form-group .required {
            color: red;
        }
        .ofero-submit-btn {
            background: #0073aa;
            color: white;
            padding: 0.75em 1.5em;
            border: none;
            border-radius: 4px;
            font-size: 1em;
            cursor: pointer;
        }
        .ofero-submit-btn:hover {
            background: #005177;
        }
    </style>
    <?php
});
