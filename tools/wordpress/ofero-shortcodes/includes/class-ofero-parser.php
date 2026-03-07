<?php
/**
 * Ofero Parser Class
 *
 * Handles parsing and field extraction from ofero.json data.
 *
 * @package Ofero_Shortcodes
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parser class for ofero.json field extraction
 */
class Ofero_Parser {

    /**
     * Get a nested field from data using dot notation
     *
     * Supports array indices: "locations.0.address.city"
     * Supports TranslatableString: returns 'default' value or translation for specified language
     *
     * @param array  $data  The ofero.json data array
     * @param string $path  Dot-notation path to the field
     * @param string $lang  Optional language code for TranslatableString fields
     * @return mixed|null   The field value or null if not found
     */
    public function get_field($data, $path, $lang = null) {
        if (empty($path) || !is_array($data)) {
            return null;
        }

        $keys = explode('.', $path);
        $value = $data;

        foreach ($keys as $key) {
            // Handle numeric keys for arrays
            if (is_numeric($key)) {
                $key = intval($key);
            }

            if (is_array($value) && array_key_exists($key, $value)) {
                $value = $value[$key];
            } else {
                return null;
            }
        }

        // Handle TranslatableString structure
        $value = $this->resolve_translatable($value, $lang);

        return $value;
    }

    /**
     * Resolve TranslatableString to actual value
     *
     * TranslatableString format:
     * {
     *   "default": "Default text",
     *   "translations": {
     *     "ro": "Text în română",
     *     "de": "Text auf Deutsch"
     *   }
     * }
     *
     * @param mixed  $value The value to check/resolve
     * @param string $lang  Optional language code
     * @return mixed        The resolved value
     */
    public function resolve_translatable($value, $lang = null) {
        // Check if this is a TranslatableString structure
        if (!is_array($value) || !isset($value['default'])) {
            return $value;
        }

        // If it has 'default' key and optionally 'translations', it's a TranslatableString
        if (!array_key_exists('translations', $value) && !is_string($value['default'])) {
            // Not a TranslatableString, just an array with 'default' key
            return $value;
        }

        // If language specified and translation exists, return translation
        if ($lang && isset($value['translations'][$lang])) {
            return $value['translations'][$lang];
        }

        // Return default value
        return $value['default'];
    }

    /**
     * Get field with automatic language detection from WordPress
     *
     * @param array  $data  The ofero.json data array
     * @param string $path  Dot-notation path to the field
     * @return mixed|null   The field value in current language or default
     */
    public function get_field_localized($data, $path) {
        // Try to get current language from WordPress
        $lang = $this->get_current_language();
        return $this->get_field($data, $path, $lang);
    }

    /**
     * Get current language from WordPress or multilingual plugins
     *
     * @return string|null Language code or null
     */
    private function get_current_language() {
        // WPML
        if (defined('ICL_LANGUAGE_CODE')) {
            return ICL_LANGUAGE_CODE;
        }

        // Polylang
        if (function_exists('pll_current_language')) {
            return pll_current_language();
        }

        // TranslatePress
        global $TRP_LANGUAGE;
        if (!empty($TRP_LANGUAGE)) {
            return substr($TRP_LANGUAGE, 0, 2);
        }

        // WordPress default locale
        $locale = get_locale();
        if ($locale) {
            return substr($locale, 0, 2);
        }

        return null;
    }

    /**
     * Check if a field exists in the data
     *
     * @param array  $data  The ofero.json data array
     * @param string $path  Dot-notation path to the field
     * @return bool         Whether the field exists
     */
    public function has_field($data, $path) {
        return $this->get_field($data, $path) !== null;
    }

    /**
     * Get multiple fields at once
     *
     * @param array $data   The ofero.json data array
     * @param array $paths  Array of dot-notation paths
     * @return array        Associative array of path => value
     */
    public function get_fields($data, $paths) {
        $result = array();

        foreach ($paths as $path) {
            $result[$path] = $this->get_field($data, $path);
        }

        return $result;
    }

    /**
     * Flatten nested array to dot-notation keys
     *
     * @param array  $array  The array to flatten
     * @param string $prefix Current key prefix
     * @return array         Flattened array with dot-notation keys
     */
    public function flatten($array, $prefix = '') {
        $result = array();

        foreach ($array as $key => $value) {
            $new_key = $prefix === '' ? $key : $prefix . '.' . $key;

            if (is_array($value) && !$this->is_sequential_array($value)) {
                $result = array_merge($result, $this->flatten($value, $new_key));
            } else {
                $result[$new_key] = $value;
            }
        }

        return $result;
    }

    /**
     * Check if array is sequential (numeric keys starting from 0)
     *
     * @param array $array The array to check
     * @return bool        Whether array is sequential
     */
    private function is_sequential_array($array) {
        if (!is_array($array) || empty($array)) {
            return false;
        }

        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * Get all available field paths from ofero.json data
     *
     * @param array $data The ofero.json data array
     * @return array      List of all available dot-notation paths
     */
    public function get_available_fields($data) {
        $flattened = $this->flatten($data);
        return array_keys($flattened);
    }

    /**
     * Search for fields matching a pattern
     *
     * @param array  $data    The ofero.json data array
     * @param string $pattern Pattern to match (supports * wildcard)
     * @return array          Matching paths and their values
     */
    public function search_fields($data, $pattern) {
        $all_fields = $this->flatten($data);
        $results = array();

        // Convert pattern to regex
        $regex = '/^' . str_replace(
            array('\*', '\?'),
            array('.*', '.'),
            preg_quote($pattern, '/')
        ) . '$/i';

        foreach ($all_fields as $path => $value) {
            if (preg_match($regex, $path)) {
                $results[$path] = $value;
            }
        }

        return $results;
    }

    /**
     * Validate ofero.json structure
     *
     * @param array $data The ofero.json data array
     * @return array      Array of validation errors (empty if valid)
     */
    public function validate($data) {
        $errors = array();

        // Required root fields
        $required_root = array('language', 'domain', 'canonicalUrl', 'metadata', 'organization');
        foreach ($required_root as $field) {
            if (!isset($data[$field])) {
                $errors[] = "Missing required field: {$field}";
            }
        }

        // Required metadata fields
        if (isset($data['metadata'])) {
            $required_meta = array('version', 'schemaVersion');
            foreach ($required_meta as $field) {
                if (!isset($data['metadata'][$field])) {
                    $errors[] = "Missing required metadata field: {$field}";
                }
            }
        }

        // Required organization fields
        if (isset($data['organization'])) {
            $required_org = array('legalName', 'entityType');
            foreach ($required_org as $field) {
                if (empty($data['organization'][$field])) {
                    $errors[] = "Missing required organization field: {$field}";
                }
            }

            // Valid entity types
            $valid_types = array('company', 'foundation', 'association', 'protocol', 'store', 'ngo', 'individual', 'project', 'other');
            if (isset($data['organization']['entityType']) && !in_array($data['organization']['entityType'], $valid_types)) {
                $errors[] = "Invalid entity type: {$data['organization']['entityType']}";
            }
        }

        return $errors;
    }

    /**
     * Format a field value for display
     *
     * @param mixed  $value  The value to format
     * @param string $format The format type (text, html, json)
     * @return string        Formatted value
     */
    public function format_value($value, $format = 'text') {
        if ($value === null) {
            return '';
        }

        switch ($format) {
            case 'json':
                return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            case 'html':
                if (is_array($value)) {
                    return '<pre>' . esc_html(json_encode($value, JSON_PRETTY_PRINT)) . '</pre>';
                }
                return nl2br(esc_html($value));

            case 'text':
            default:
                if (is_array($value)) {
                    return implode(', ', array_filter($value, 'is_string'));
                }
                return (string) $value;
        }
    }
}
