<?php
/**
 * Branding Mapper
 *
 * Converts between the Branding tab's flat rows (type, variant, format, url) and the
 * schema's `branding` object (logos.vector/raster, icons, coverImage).
 *
 * Before 2.1.0 the plugin wrote the flat rows to a top-level `brandAssets` key, which
 * was never part of the schema. Those files are still read and converted on next save.
 *
 * @package Ofero_Generator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Ofero_Branding_Mapper {

    /**
     * Row variant => LogoAsset.colorVariant
     */
    const VARIANT_TO_COLOR = array(
        'primary' => 'color',
        'full-color' => 'color',
        'dark' => 'dark',
        'light' => 'light',
        'monochrome' => 'monochrome'
    );

    /**
     * Build the schema `branding` object from form rows.
     *
     * @param array $rows     Rows of array('type', 'variant', 'format', 'url')
     * @param array $existing Current `branding` object; keys the form does not edit are kept
     * @return array
     */
    public static function rows_to_branding($rows, $existing = array()) {
        $branding = is_array($existing) ? $existing : array();
        unset($branding['logos'], $branding['icons'], $branding['coverImage']);

        $logos = array('vector' => array(), 'raster' => array());
        $icons = array('favicon' => array(), 'appIcons' => array());
        $cover = array();
        $has_primary = false;

        foreach ($rows as $row) {
            $url = $row['url'] ?? '';
            if ($url === '') {
                continue;
            }
            $type = $row['type'] ?? 'logo';
            $variant = $row['variant'] ?? 'primary';
            $format = self::detect_format($row['format'] ?? '', $url);

            switch ($type) {
                case 'favicon':
                    $icons['favicon'][$format === 'ico' ? 'ico' : 'png32x32'] = $url;
                    break;

                case 'icon':
                    $icons['appIcons'][] = array('url' => $url);
                    break;

                case 'cover':
                case 'banner':
                    if (empty($cover)) {
                        $cover = array('url' => $url);
                    }
                    break;

                default: // logo, avatar
                    $logo = array(
                        'type' => in_array($format, array('svg', 'pdf', 'png', 'jpeg', 'jpg'), true) ? $format : 'png',
                        'url' => $url
                    );
                    // The schema expects a single primary logo; the first one marked wins
                    if ($variant === 'primary' && !$has_primary) {
                        $logo['primary'] = true;
                        $has_primary = true;
                    }
                    if (isset(self::VARIANT_TO_COLOR[$variant])) {
                        $logo['colorVariant'] = self::VARIANT_TO_COLOR[$variant];
                    }
                    if ($type === 'avatar') {
                        $logo['aspectRatio'] = 'square';
                    }
                    $bucket = in_array($logo['type'], array('svg', 'pdf'), true) ? 'vector' : 'raster';
                    $logos[$bucket][] = $logo;
            }
        }

        $branding = array_merge(
            array(
                'logos' => $logos,
                'icons' => $icons,
                'coverImage' => $cover
            ),
            $branding
        );

        return $branding;
    }

    /**
     * Turn saved data into form rows. Reads `branding` (schema) and falls back to the
     * legacy `brandAssets` rows written by plugin versions before 2.1.0.
     *
     * @param array $data Full ofero.json data
     * @return array Rows of array('type', 'variant', 'format', 'url')
     */
    public static function data_to_rows($data) {
        $branding = $data['branding'] ?? array();
        $rows = array();

        foreach (array('vector', 'raster') as $bucket) {
            foreach ($branding['logos'][$bucket] ?? array() as $logo) {
                if (empty($logo['url'])) {
                    continue;
                }
                $color = $logo['colorVariant'] ?? 'color';
                if (!empty($logo['primary'])) {
                    $variant = 'primary';
                } elseif (in_array($color, array('dark', 'light', 'monochrome'), true)) {
                    $variant = $color;
                } else {
                    $variant = 'full-color';
                }
                $rows[] = array(
                    'type' => 'logo',
                    'variant' => $variant,
                    'format' => $logo['type'] ?? '',
                    'url' => $logo['url']
                );
            }
        }

        $favicon = $branding['icons']['favicon'] ?? array();
        foreach (array('ico' => 'ico', 'png32x32' => 'png', 'png192x192' => 'png') as $key => $format) {
            if (!empty($favicon[$key])) {
                $rows[] = array('type' => 'favicon', 'variant' => 'primary', 'format' => $format, 'url' => $favicon[$key]);
            }
        }
        foreach ($branding['icons']['appIcons'] ?? array() as $icon) {
            if (!empty($icon['url'])) {
                $rows[] = array('type' => 'icon', 'variant' => 'primary', 'format' => self::detect_format('', $icon['url']), 'url' => $icon['url']);
            }
        }
        if (!empty($branding['coverImage']['url'])) {
            $url = $branding['coverImage']['url'];
            $rows[] = array('type' => 'cover', 'variant' => 'primary', 'format' => self::detect_format('', $url), 'url' => $url);
        }

        // Legacy flat list
        if (empty($rows) && !empty($data['brandAssets']) && is_array($data['brandAssets']) && isset($data['brandAssets'][0])) {
            foreach ($data['brandAssets'] as $asset) {
                if (is_array($asset) && !empty($asset['url'])) {
                    $rows[] = array(
                        'type' => $asset['type'] ?? 'logo',
                        'variant' => $asset['variant'] ?? 'primary',
                        'format' => $asset['format'] ?? '',
                        'url' => $asset['url']
                    );
                }
            }
        }

        return $rows;
    }

    /**
     * File format from the explicit value or the URL extension.
     */
    private static function detect_format($format, $url) {
        $format = strtolower(trim((string) $format));
        if ($format === '') {
            $path = (string) wp_parse_url($url, PHP_URL_PATH);
            $format = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        }
        return $format === 'jpeg' ? 'jpg' : $format;
    }
}
