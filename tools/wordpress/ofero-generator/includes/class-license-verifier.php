<?php
/**
 * License Verifier Class
 *
 * Handles verification of domain license against Ofero Network registry.
 *
 * @package Ofero_Generator
 * @since 1.1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * License Verifier class
 */
class Ofero_License_Verifier {

    /**
     * API base URL
     */
    const API_URL = 'https://licence.ofero.network/api/v1/registry';

    /**
     * Cache duration in seconds (5 minutes)
     */
    const CACHE_DURATION = 300;

    /**
     * Transient key prefix
     */
    const CACHE_PREFIX = 'ofero_license_';

    /**
     * Verify the current domain's license
     *
     * @param bool $force_refresh Force a fresh API call
     * @return array License verification result
     */
    public function verify($force_refresh = false) {
        $domain = $this->get_current_domain();

        // Check cache first
        if (!$force_refresh) {
            $cached = $this->get_cached_result($domain);
            if ($cached !== false) {
                return $cached;
            }
        }

        // Make API call
        $result = $this->call_api($domain);

        // Cache the result
        $this->cache_result($domain, $result);

        return $result;
    }

    /**
     * Get the current domain
     *
     * @return string Normalized domain
     */
    public function get_current_domain() {
        $domain = parse_url(home_url(), PHP_URL_HOST);
        $domain = preg_replace('/^www\./', '', $domain);
        return strtolower($domain);
    }

    /**
     * Call the Ofero Registry API
     *
     * @param string $domain Domain to verify
     * @return array API response
     */
    private function call_api($domain) {
        $url = self::API_URL . '/verify/' . urlencode($domain);

        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'headers' => array(
                'Accept' => 'application/json',
                'User-Agent' => 'Ofero-WordPress-Plugin/1.0',
            ),
        ));

        // Handle connection errors
        if (is_wp_error($response)) {
            return array(
                'valid' => false,
                'error' => 'connection_error',
                'message' => $response->get_error_message(),
                'cached' => false,
                'checked_at' => current_time('c'),
            );
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        // Handle non-200 responses
        if ($status_code !== 200) {
            return array(
                'valid' => false,
                'error' => 'api_error',
                'message' => sprintf(__('API returned status code %d', 'ofero-generator'), $status_code),
                'cached' => false,
                'checked_at' => current_time('c'),
            );
        }

        // Parse JSON response
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return array(
                'valid' => false,
                'error' => 'parse_error',
                'message' => __('Failed to parse API response', 'ofero-generator'),
                'cached' => false,
                'checked_at' => current_time('c'),
            );
        }

        // Add metadata
        $data['cached'] = false;
        $data['checked_at'] = current_time('c');

        return $data;
    }

    /**
     * Get cached result
     *
     * @param string $domain Domain to check
     * @return array|false Cached result or false
     */
    private function get_cached_result($domain) {
        $cache_key = self::CACHE_PREFIX . md5($domain);
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            $cached['cached'] = true;
            return $cached;
        }

        return false;
    }

    /**
     * Cache verification result
     *
     * @param string $domain Domain
     * @param array $result Result to cache
     */
    private function cache_result($domain, $result) {
        $cache_key = self::CACHE_PREFIX . md5($domain);
        set_transient($cache_key, $result, self::CACHE_DURATION);
    }

    /**
     * Clear cached result for domain
     *
     * @param string|null $domain Domain to clear (null for current domain)
     */
    public function clear_cache($domain = null) {
        if ($domain === null) {
            $domain = $this->get_current_domain();
        }
        $cache_key = self::CACHE_PREFIX . md5($domain);
        delete_transient($cache_key);
    }

    /**
     * Get license status badge HTML
     *
     * @param array|null $result Verification result (or null to verify now)
     * @return string HTML for the badge
     */
    public function get_status_badge($result = null) {
        if ($result === null) {
            $result = $this->verify();
        }

        if ($result['valid']) {
            $tier = $result['license']['tier'] ?? 'basic';
            $lifetime = $result['license']['lifetime'] ?? false;

            $tier_label = ucfirst($tier);
            if ($lifetime) {
                $tier_label .= ' (Lifetime)';
            }

            return sprintf(
                '<div class="ofero-license-badge ofero-license-valid">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <div class="ofero-license-info">
                        <strong>%s</strong>
                        <span>%s License - %s</span>
                    </div>
                </div>',
                esc_html__('Verified by Ofero Network', 'ofero-generator'),
                esc_html($tier_label),
                esc_html($result['organization']['name'] ?? $result['domain'])
            );
        }

        // Handle different error types
        $error = $result['error'] ?? 'unknown';
        $message = $result['message'] ?? __('Verification failed', 'ofero-generator');

        switch ($error) {
            case 'not_registered':
                $title = __('Not Registered', 'ofero-generator');
                $description = __('This domain is not registered in the Ofero Network.', 'ofero-generator');
                break;

            case 'license_expired':
                $title = __('License Expired', 'ofero-generator');
                $description = __('Your Ofero Network license has expired.', 'ofero-generator');
                break;

            case 'connection_error':
                $title = __('Connection Error', 'ofero-generator');
                $description = $message;
                break;

            default:
                $title = __('Verification Error', 'ofero-generator');
                $description = $message;
        }

        return sprintf(
            '<div class="ofero-license-badge ofero-license-invalid">
                <span class="dashicons dashicons-warning"></span>
                <div class="ofero-license-info">
                    <strong>%s</strong>
                    <span>%s</span>
                </div>
            </div>',
            esc_html($title),
            esc_html($description)
        );
    }

    /**
     * Get detailed license information
     *
     * @return array Detailed license info
     */
    public function get_license_details() {
        $result = $this->verify();

        return array(
            'domain' => $this->get_current_domain(),
            'is_valid' => $result['valid'] ?? false,
            'tier' => $result['license']['tier'] ?? null,
            'lifetime' => $result['license']['lifetime'] ?? false,
            'expires_at' => $result['license']['expires_at'] ?? null,
            'organization' => $result['organization'] ?? null,
            'error' => $result['error'] ?? null,
            'message' => $result['message'] ?? null,
            'cached' => $result['cached'] ?? false,
            'checked_at' => $result['checked_at'] ?? null,
        );
    }

    /**
     * Check if license allows specific feature
     *
     * @param string $feature Feature to check
     * @return bool Whether feature is allowed
     */
    public function has_feature($feature) {
        $result = $this->verify();

        if (!$result['valid']) {
            return false;
        }

        $tier = $result['license']['tier'] ?? 'basic';

        // Feature matrix
        $features = array(
            'enterprise' => array('multi_domain', 'priority_support', 'api_access', 'custom_integrations', 'white_label'),
            'business' => array('single_domain', 'standard_support', 'full_features'),
            'basic' => array('single_domain', 'community_support', 'core_features'),
        );

        // Enterprise has all features
        if ($tier === 'enterprise') {
            return true;
        }

        // Check tier features
        $tier_features = $features[$tier] ?? array();
        return in_array($feature, $tier_features);
    }
}
