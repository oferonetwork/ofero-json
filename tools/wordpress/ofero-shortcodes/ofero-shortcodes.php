<?php
/**
 * Plugin Name: Ofero Shortcodes
 * Plugin URI: https://ofero.me/ofero-json
 * Description: Display data from your ofero.json file using simple shortcodes. Compatible with Elementor, WPBakery, Gutenberg, and any theme.
 * Version: 2.0.0
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
define('OFERO_SHORTCODES_VERSION', '2.0.0');
define('OFERO_SHORTCODES_PATH', plugin_dir_path(__FILE__));
define('OFERO_SHORTCODES_URL', plugin_dir_url(__FILE__));

// Include the parser class
require_once OFERO_SHORTCODES_PATH . 'includes/class-ofero-parser.php';

/**
 * Register Elementor widgets.
 *
 * Uses the Elementor 3.5+ API (`elementor/widgets/register` + `$widgets_manager->register()`).
 * The old `widgets_registered` + `register_widget_type()` were deprecated in Elementor 3.5
 * and scheduled for removal in 4.3. We keep a compatibility branch for sites still on
 * Elementor < 3.5, but call the new API on every supported version (3.5 through 4.x).
 *
 * @param \Elementor\Widgets_Manager|null $widgets_manager Passed by the new hook; null on the legacy hook.
 */
function ofero_register_elementor_widgets($widgets_manager = null) {
    if (!did_action('elementor/loaded')) {
        return;
    }

    require_once OFERO_SHORTCODES_PATH . 'includes/elementor/class-elementor-ofero-field-widget.php';
    require_once OFERO_SHORTCODES_PATH . 'includes/elementor/class-elementor-ofero-organization-widget.php';
    require_once OFERO_SHORTCODES_PATH . 'includes/elementor/class-elementor-ofero-location-widget.php';
    require_once OFERO_SHORTCODES_PATH . 'includes/elementor/class-elementor-ofero-social-widget.php';
    require_once OFERO_SHORTCODES_PATH . 'includes/elementor/class-elementor-ofero-banking-widget.php';

    $widgets = array(
        new Elementor_Ofero_Field_Widget(),
        new Elementor_Ofero_Organization_Widget(),
        new Elementor_Ofero_Location_Widget(),
        new Elementor_Ofero_Social_Widget(),
        new Elementor_Ofero_Banking_Widget(),
    );

    if ($widgets_manager && method_exists($widgets_manager, 'register')) {
        // Elementor 3.5+ / 4.x
        foreach ($widgets as $widget) {
            $widgets_manager->register($widget);
        }
        return;
    }

    // Elementor < 3.5 fallback
    $legacy_manager = \Elementor\Plugin::instance()->widgets_manager;
    if (method_exists($legacy_manager, 'register_widget_type')) {
        foreach ($widgets as $widget) {
            $legacy_manager->register_widget_type($widget);
        }
    }
}
add_action('elementor/widgets/register', 'ofero_register_elementor_widgets');
// Fallback for Elementor < 3.5 (the new hook does not fire there)
if (defined('ELEMENTOR_VERSION') && version_compare(ELEMENTOR_VERSION, '3.5.0', '<')) {
    add_action('elementor/widgets/widgets_registered', 'ofero_register_elementor_widgets');
}

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
        add_shortcode('ofero_feed', array($this, 'feed_shortcode'));
    }

    /**
     * Register admin menu
     */
    private function register_admin_menu() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_notices', array($this, 'admin_notices'));
    }

    /**
     * Show admin notices for: file too large (>200 KB), v1 schema, or inline catalog.
     * These help the site owner notice misconfigurations that hurt performance.
     */
    public function admin_notices() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $size = get_transient('ofero_size_warning');
        if ($size) {
            $kb = round(((int) $size) / 1024);
            echo '<div class="notice notice-warning"><p><strong>Ofero Shortcodes:</strong> your ofero.json is ' . esc_html((string) $kb) . ' KB. v2 recommends &lt; 100 KB. Move catalog data to <code>catalog.feeds[]</code> — see <a href="https://github.com/oferonetwork/ofero-json/blob/main/docs/MIGRATION-v1-to-v2.md" target="_blank" rel="noopener">MIGRATION-v1-to-v2.md</a>.</p></div>';
        }

        $data = $this->get_ofero_data();
        if (!$data) return;

        $schema_version = $data['metadata']['schemaVersion'] ?? '';
        if ($schema_version === 'ofero-metadata-1.0') {
            echo '<div class="notice notice-error"><p><strong>Ofero Shortcodes:</strong> your ofero.json declares <code>ofero-metadata-1.0</code>. This plugin is v2-only and may not render catalog data correctly. Upgrade your file per <a href="https://github.com/oferonetwork/ofero-json/blob/main/docs/MIGRATION-v1-to-v2.md" target="_blank" rel="noopener">MIGRATION-v1-to-v2.md</a>.</p></div>';
        }

        $v1_keys = $this->parser->v1_catalog_keys($data);
        if (!empty($v1_keys)) {
            echo '<div class="notice notice-warning"><p><strong>Ofero Shortcodes:</strong> your ofero.json still contains v1 inline catalog fields (<code>' . esc_html(implode(', ', $v1_keys)) . '</code>). Move them into <code>catalog.feeds[]</code>.</p></div>';
        }
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

            <h3>External Feed Shortcode (v2)</h3>
            <p>Use <code>[ofero_feed]</code> to render an external feed declared in <code>catalog.feeds[]</code>. This is the only shortcode that fetches an external URL — all other shortcodes read only ofero.json itself.</p>
            <pre>[ofero_feed type="menu" limit="20"]</pre>
            <p class="description">
                Supported <code>type</code> values: <code>menu</code>, <code>products</code>, <code>services</code>, <code>packages</code>, <code>portfolio</code>, <code>reservations</code>, <code>rooms</code>, <code>other</code>.
                Recognized feed formats: Schema.org Menu JSON-LD, Google Merchant XML, generic JSON.
                Feed body is cached separately under its own ETag/Last-Modified.
            </p>

            <hr>

            <h2>Current ofero.json Status</h2>
            <?php
            $data = $this->get_ofero_data();
            if ($data) {
                $schema_version = $data['metadata']['schemaVersion'] ?? '';
                $version_ok = $schema_version === 'ofero-metadata-2.0';
                echo '<p style="color: green;">&#10003; ofero.json loaded successfully.</p>';
                echo '<p><strong>Organization:</strong> ' . esc_html($data['organization']['legalName'] ?? 'Not set') . '</p>';
                echo '<p><strong>Domain:</strong> ' . esc_html($data['domain'] ?? 'Not set') . '</p>';
                echo '<p><strong>Schema version:</strong> ' . esc_html($schema_version) . ($version_ok ? ' <span style="color:green">(v2 OK)</span>' : ' <span style="color:#d63638">(plugin requires v2 — upgrade your file)</span>') . '</p>';
                echo '<p><strong>Last Updated:</strong> ' . esc_html($data['metadata']['lastUpdated'] ?? 'Not set') . '</p>';
                if (isset($data['catalog']['feeds']) && is_array($data['catalog']['feeds'])) {
                    echo '<p><strong>catalog.feeds[]:</strong> ' . count($data['catalog']['feeds']) . ' feed(s) declared.</p>';
                }
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

        // Handle cache clearing — delete all ofero_json_* and ofero_feed_* transients
        if (isset($_GET['clear_cache'])) {
            global $wpdb;
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ofero_json_%' OR option_name LIKE '_transient_timeout_ofero_json_%' OR option_name LIKE '_transient_ofero_feed_%' OR option_name LIKE '_transient_timeout_ofero_feed_%'");
            delete_transient('ofero_json_data');
            delete_transient('ofero_size_warning');
            echo '<div class="notice notice-success"><p>Cache cleared successfully.</p></div>';
        }
    }

    /**
     * Size threshold above which the file triggers an admin warning (200 KB).
     */
    const SIZE_WARNING_BYTES = 204800;

    /**
     * Build a cache key qualified by the source URL/path AND its Last-Modified marker.
     * When the source changes, the marker changes and the old cache entries fall out
     * naturally — no manual invalidation needed.
     */
    private function cache_key($suffix, $marker) {
        return 'ofero_json_' . md5(($marker ?? '') . '|' . $suffix);
    }

    /**
     * Resolve the source identity (URL or local path) and a Last-Modified marker.
     * The marker is used to key all caches so updates auto-invalidate.
     *
     * @return array{0:string,1:string,2:string} [type ('url'|'file'|'none'), source, marker]
     */
    private function resolve_source() {
        $external_url = get_option('ofero_json_url', '');
        if (!empty($external_url)) {
            return array('url', $external_url, '');
        }
        $path = get_option('ofero_json_path', '.well-known/ofero.json');
        $full_path = ABSPATH . $path;
        if (file_exists($full_path)) {
            return array('file', $full_path, (string) filemtime($full_path));
        }
        return array('none', '', '');
    }

    /**
     * Fetch raw ofero.json data with cache.
     *
     * Caches the parsed array under a key qualified by source + Last-Modified header
     * (for URLs) or filemtime (for files), so updates auto-invalidate. Also records
     * the byte size so the admin page can warn if the file is too large for v2.
     */
    public function get_ofero_data() {
        list($type, $source, $file_marker) = $this->resolve_source();
        if ($type === 'none') {
            return null;
        }

        $cache_enabled = (bool) get_option('ofero_cache_enabled', true);

        // For URLs, we don't know Last-Modified until we ask. We try a cheap HEAD
        // first; if that gives us a marker, we can serve a fully cached parsed array.
        $marker = $file_marker;
        if ($type === 'url' && $cache_enabled) {
            $head = wp_remote_head($source, array('timeout' => 5));
            if (!is_wp_error($head)) {
                $lm = wp_remote_retrieve_header($head, 'last-modified');
                $etag = wp_remote_retrieve_header($head, 'etag');
                $marker = $lm ?: $etag ?: '';
                if ($marker !== '') {
                    $cached = get_transient($this->cache_key($source, $marker));
                    if ($cached !== false) {
                        return $cached;
                    }
                }
            }
        } elseif ($type === 'file' && $cache_enabled) {
            $cached = get_transient($this->cache_key($source, $marker));
            if ($cached !== false) {
                return $cached;
            }
        }

        // Cache miss — fetch the body
        $body = null;
        if ($type === 'url') {
            $response = wp_remote_get($source, array('timeout' => 10));
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $body = wp_remote_retrieve_body($response);
                if ($marker === '') {
                    $lm = wp_remote_retrieve_header($response, 'last-modified');
                    $etag = wp_remote_retrieve_header($response, 'etag');
                    $marker = $lm ?: $etag ?: '';
                }
            }
        } else {
            $body = file_get_contents($source);
        }

        if ($body === null || $body === false) {
            return null;
        }

        $size = strlen($body);
        if ($size > self::SIZE_WARNING_BYTES) {
            set_transient('ofero_size_warning', $size, DAY_IN_SECONDS);
        } else {
            delete_transient('ofero_size_warning');
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            return null;
        }

        if ($cache_enabled && $marker !== '') {
            set_transient($this->cache_key($source, $marker), $data, self::CACHE_DURATION);
        }

        return $data;
    }

    /**
     * Get a single top-level section. Cached separately so a shortcode that only
     * needs `organization` does not pay the cost of (de)serializing the full tree.
     *
     * @param string $section Top-level key (e.g., "organization", "locations")
     * @return mixed|null
     */
    public function get_ofero_section($section) {
        if (empty($section)) {
            return null;
        }

        list($type, $source, $marker) = $this->resolve_source();
        if ($type === 'none') {
            return null;
        }

        $cache_enabled = (bool) get_option('ofero_cache_enabled', true);
        if ($cache_enabled && $marker !== '') {
            $cached = get_transient($this->cache_key($source . '|section|' . $section, $marker));
            if ($cached !== false) {
                return $cached === '__OFERO_NULL__' ? null : $cached;
            }
        }

        $data = $this->get_ofero_data();
        $value = $this->parser->get_section($data, $section);

        if ($cache_enabled && $marker !== '') {
            set_transient(
                $this->cache_key($source . '|section|' . $section, $marker),
                $value === null ? '__OFERO_NULL__' : $value,
                self::CACHE_DURATION
            );
        }

        return $value;
    }

    /**
     * Fetch one external catalog feed (URL from catalog.feeds[]).
     * Cached separately under its own ETag/Last-Modified so feed updates do not
     * invalidate the ofero.json cache and vice versa.
     *
     * @param string $url Feed URL (HTTPS)
     * @return string|null Raw body, or null on error
     */
    public function fetch_feed($url) {
        if (empty($url) || strpos($url, 'https://') !== 0) {
            return null;
        }

        // Cheap HEAD to get the cache marker
        $marker = '';
        $head = wp_remote_head($url, array('timeout' => 5));
        if (!is_wp_error($head)) {
            $lm = wp_remote_retrieve_header($head, 'last-modified');
            $etag = wp_remote_retrieve_header($head, 'etag');
            $marker = $lm ?: $etag ?: '';
        }

        $key = 'ofero_feed_' . md5($url . '|' . $marker);
        if ($marker !== '') {
            $cached = get_transient($key);
            if ($cached !== false) {
                return $cached;
            }
        }

        $response = wp_remote_get($url, array('timeout' => 10));
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return null;
        }
        $body = wp_remote_retrieve_body($response);
        if ($body === '' || $body === false) {
            return null;
        }

        if ($marker === '') {
            $lm = wp_remote_retrieve_header($response, 'last-modified');
            $etag = wp_remote_retrieve_header($response, 'etag');
            $marker = $lm ?: $etag ?: '';
            $key = 'ofero_feed_' . md5($url . '|' . $marker);
        }

        if ($marker !== '') {
            set_transient($key, $body, self::CACHE_DURATION);
        }

        return $body;
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

        $org = $this->get_ofero_section('organization');
        if (!is_array($org)) {
            return '';
        }
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

        $locations = $this->get_ofero_section('locations');
        if (!is_array($locations)) {
            return '';
        }

        $index = intval($atts['index']);
        if (!isset($locations[$index])) {
            return '';
        }

        $loc = $locations[$index];
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

        $communications = $this->get_ofero_section('communications');
        if (!is_array($communications) || !isset($communications['social'])) {
            return '';
        }

        $social = $communications['social'];
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

        $banking = $this->get_ofero_section('banking');
        if (!is_array($banking)) {
            return '';
        }

        $index = intval($atts['index']);
        if (!isset($banking[$index])) {
            return '';
        }

        $bank = $banking[$index];
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

        $brand = $this->get_ofero_section('brandAssets');
        if (!is_array($brand)) {
            return '';
        }

        $logo_url = '';
        $logo_alt = $atts['alt'];

        // Try to find matching logo
        if (!empty($brand['logos']['vector'])) {
            foreach ($brand['logos']['vector'] as $logo) {
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
        if (empty($logo_url) && !empty($brand['logos']['raster'])) {
            foreach ($brand['logos']['raster'] as $logo) {
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

        $locations = $this->get_ofero_section('locations');
        if (!is_array($locations)) {
            return '';
        }

        $index = intval($atts['location']);
        if (!isset($locations[$index]) || empty($locations[$index]['hours'])) {
            return '';
        }

        $hours = $locations[$index]['hours'];

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

        $locations = $this->get_ofero_section('locations');
        if (!is_array($locations)) {
            return '';
        }

        $index = intval($atts['location']);
        if (!isset($locations[$index]) || empty($locations[$index]['address'])) {
            return '';
        }

        $address = $locations[$index]['address'];

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

        $team = $this->get_ofero_section('team');
        if (!is_array($team)) {
            return '';
        }
        $data = array('team' => $team); // local alias for the rest of the handler

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

        $certs = $this->get_ofero_section('certificates');
        if (!is_array($certs) || empty($certs)) {
            return '';
        }
        $data = array('certificates' => $certs);

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

        $promos = $this->get_ofero_section('promoCodes');
        if (!is_array($promos) || empty($promos)) {
            return '';
        }
        $data = array('promoCodes' => $promos);

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
     * External feed shortcode handler — [ofero_feed type="menu" limit="20"]
     *
     * Explicit, opt-in: only this shortcode ever fetches a catalog.feeds[] URL.
     * Other shortcodes never touch external feeds. The fetched body is cached
     * separately under its own ETag/Last-Modified marker.
     *
     * Supported attributes:
     *   type     Feed type to render (matches catalog.feeds[].type). Required.
     *   limit    Max items to render (default 20).
     *   class    Wrapper CSS class (default "ofero-feed").
     */
    public function feed_shortcode($atts) {
        $atts = shortcode_atts(array(
            'type' => '',
            'limit' => '20',
            'class' => 'ofero-feed',
        ), $atts, 'ofero_feed');

        if (empty($atts['type'])) {
            return '';
        }

        $catalog = $this->get_ofero_section('catalog');
        if (!is_array($catalog) || empty($catalog['feeds']) || !is_array($catalog['feeds'])) {
            return '';
        }

        $feed = null;
        foreach ($catalog['feeds'] as $f) {
            if (isset($f['type']) && $f['type'] === $atts['type']) {
                $feed = $f;
                break;
            }
        }
        if (!$feed || empty($feed['url'])) {
            return '';
        }

        $body = $this->fetch_feed($feed['url']);
        if ($body === null) {
            return '';
        }

        $limit = max(1, intval($atts['limit']));
        $items = $this->extract_feed_items($body, $feed['format'] ?? 'json', $limit);
        if (empty($items)) {
            return '';
        }

        $output = '<ul class="' . esc_attr($atts['class']) . ' ofero-feed-' . esc_attr($atts['type']) . '">';
        foreach ($items as $item) {
            $name = $item['name'] ?? '';
            $price = $item['price'] ?? '';
            $output .= '<li class="ofero-feed-item">';
            $output .= '<span class="ofero-feed-item-name">' . esc_html($name) . '</span>';
            if ($price !== '') {
                $output .= ' <span class="ofero-feed-item-price">' . esc_html($price) . '</span>';
            }
            $output .= '</li>';
        }
        $output .= '</ul>';

        return $output;
    }

    /**
     * Extract a list of {name, price} items from a feed body, depending on format.
     * Handles JSON, Schema.org Menu JSON-LD, and Google Merchant XML at a basic level.
     * Returns at most $limit items.
     */
    private function extract_feed_items($body, $format, $limit) {
        $items = array();

        if ($format === 'schema.org-jsonld' || $format === 'json' || $format === 'jsonl') {
            $decoded = json_decode($body, true);
            if (!is_array($decoded)) {
                return array();
            }
            // Schema.org Menu
            if (isset($decoded['@type']) && $decoded['@type'] === 'Menu' && isset($decoded['hasMenuSection'])) {
                foreach ((array) $decoded['hasMenuSection'] as $section) {
                    if (!isset($section['hasMenuItem'])) continue;
                    foreach ((array) $section['hasMenuItem'] as $mi) {
                        $items[] = array(
                            'name' => $mi['name'] ?? '',
                            'price' => isset($mi['offers']['price']) ? trim(($mi['offers']['price']) . ' ' . ($mi['offers']['priceCurrency'] ?? '')) : '',
                        );
                        if (count($items) >= $limit) return $items;
                    }
                }
                return $items;
            }
            // Generic JSON: array at root, or {items: [...]}
            $list = $decoded;
            if (isset($decoded['items']) && is_array($decoded['items'])) {
                $list = $decoded['items'];
            }
            if (!isset($list[0])) {
                return array();
            }
            foreach ($list as $entry) {
                if (!is_array($entry)) continue;
                $items[] = array(
                    'name' => $entry['name'] ?? ($entry['title'] ?? ''),
                    'price' => $entry['priceFormatted'] ?? (isset($entry['price']) ? (string) $entry['price'] : ''),
                );
                if (count($items) >= $limit) return $items;
            }
            return $items;
        }

        if ($format === 'google-merchant-xml' || $format === 'xml' || $format === 'rss' || $format === 'atom') {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($body);
            if (!$xml) {
                return array();
            }
            // Google Merchant: <item><title/><g:price/></item> inside <channel>
            $entries = $xml->channel->item ?? $xml->item ?? $xml->entry ?? null;
            if (!$entries) {
                return array();
            }
            foreach ($entries as $entry) {
                $name = (string) ($entry->title ?? '');
                $price = '';
                $g = $entry->children('http://base.google.com/ns/1.0');
                if ($g && isset($g->price)) {
                    $price = (string) $g->price;
                }
                $items[] = array('name' => $name, 'price' => $price);
                if (count($items) >= $limit) return $items;
            }
            return $items;
        }

        return array();
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

        $org = $this->get_ofero_section('organization') ?? [];

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
