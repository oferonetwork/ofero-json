<?php
/**
 * Opening hours helpers
 *
 * Since ofero.json 3.0 each businessHours day and specialHours[].hours is a list of
 * "HH:MM-HH:MM" intervals (several = split schedule, [] = closed). The editor shows a
 * day as text ("09:00-13:00, 14:00-18:00" or "Closed"); these helpers convert between
 * the two and read the single strings written by 2.x ("09:00-18:00", "Closed").
 *
 * @package Ofero_Generator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Ofero_Opening_Hours {

    const WEEKDAYS = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');

    const RANGE_PATTERN = '/^([01][0-9]|2[0-3]):[0-5][0-9]-(([01][0-9]|2[0-3]):[0-5][0-9]|24:00)$/';

    /**
     * Translated weekday labels, keyed by schema key
     */
    public static function weekday_labels() {
        return array(
            'monday' => __('Monday', 'ofero-generator'),
            'tuesday' => __('Tuesday', 'ofero-generator'),
            'wednesday' => __('Wednesday', 'ofero-generator'),
            'thursday' => __('Thursday', 'ofero-generator'),
            'friday' => __('Friday', 'ofero-generator'),
            'saturday' => __('Saturday', 'ofero-generator'),
            'sunday' => __('Sunday', 'ofero-generator')
        );
    }

    /**
     * Editor text for one day: "09:00-13:00, 14:00-18:00", "Closed" or "" (not stated)
     *
     * @param mixed $value Interval list (3.x) or string (2.x)
     * @return string
     */
    public static function to_text($value) {
        if (is_array($value)) {
            return empty($value) ? 'Closed' : implode(', ', array_map('strval', $value));
        }
        return is_string($value) ? trim($value) : '';
    }

    /**
     * Interval list for one day, from editor text, a 2.x string or a 3.x list.
     * Returns null when nothing is stated. Intervals that are not HH:MM-HH:MM are dropped;
     * the editor checks them before submit.
     *
     * @param mixed $value
     * @return array|null
     */
    public static function parse($value) {
        if (is_array($value)) {
            $parts = $value;
        } else {
            $text = is_string($value) ? trim($value) : '';
            if ($text === '') {
                return null;
            }
            if (strcasecmp($text, 'closed') === 0) {
                return array();
            }
            $parts = preg_split('/[,;]/', $text);
        }

        $ranges = array();
        foreach ($parts as $part) {
            $range = self::normalize_range((string) $part);
            if ($range !== '' && preg_match(self::RANGE_PATTERN, $range)) {
                $ranges[] = $range;
            }
        }
        if (empty($ranges) && !empty($parts)) {
            return null; // nothing usable: unknown, never "closed"
        }
        return array_values(array_unique($ranges));
    }

    /**
     * "9:00 - 13:00" -> "09:00-13:00"
     */
    public static function normalize_range($range) {
        $range = trim($range);
        $range = preg_replace('/\s*(-|\x{2013})\s*/u', '-', $range);
        return preg_replace('/(^|-)(\d):/', '${1}0${2}:', $range);
    }

    /**
     * Problems with one day's intervals: format, order, overlap, only the last one past midnight.
     *
     * @param mixed $value
     * @return string[] Human-readable problems, empty when the day is valid
     */
    public static function problems($value) {
        if (!is_array($value)) {
            return array(__('must be a list of HH:MM-HH:MM intervals (ofero.json 3.0)', 'ofero-generator'));
        }

        $problems = array();
        $previous_end = -1;
        $count = count($value);
        foreach (array_values($value) as $k => $range) {
            if (!is_string($range) || !preg_match(self::RANGE_PATTERN, $range)) {
                /* translators: %s: the invalid opening interval */
                $problems[] = sprintf(__('"%s" is not an HH:MM-HH:MM interval', 'ofero-generator'), is_scalar($range) ? $range : '');
                continue;
            }
            list($start, $end) = array_map(array(__CLASS__, 'minutes'), explode('-', $range));
            if ($end <= $start) {
                $end += 24 * 60;
            }
            if ($start < $previous_end) {
                /* translators: %s: an opening interval */
                $problems[] = sprintf(__('"%s" overlaps or comes before the previous interval', 'ofero-generator'), $range);
            } elseif ($end > 24 * 60 && $k < $count - 1) {
                /* translators: %s: an opening interval */
                $problems[] = sprintf(__('"%s" runs past midnight, so it must be the last interval', 'ofero-generator'), $range);
                $end = -1;
            }
            $previous_end = $end;
        }
        return $problems;
    }

    private static function minutes($time) {
        list($h, $m) = array_map('intval', explode(':', $time));
        return $h * 60 + $m;
    }
}
