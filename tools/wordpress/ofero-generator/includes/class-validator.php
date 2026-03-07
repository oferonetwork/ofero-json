<?php
/**
 * Validator Class
 *
 * Validates ofero.json data against the standard.
 *
 * @package Ofero_Generator
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Ofero_Validator {

    /**
     * Valid entity types
     */
    const ENTITY_TYPES = array(
        'company',
        'foundation',
        'association',
        'protocol',
        'store',
        'ngo',
        'individual',
        'project',
        'other'
    );

    /**
     * Valid location types
     */
    const LOCATION_TYPES = array(
        'headquarters',
        'branch',
        'store',
        'warehouse',
        'office',
        'factory',
        'distribution_center'
    );

    /**
     * Valid social platforms
     */
    const SOCIAL_PLATFORMS = array(
        'facebook',
        'instagram',
        'x',
        'twitter',
        'linkedin',
        'youtube',
        'tiktok',
        'discord',
        'telegram',
        'github',
        'reddit',
        'pinterest',
        'snapchat',
        'whatsapp',
        'other'
    );

    /**
     * Validate ofero.json data
     *
     * @param array  $data  The data to validate
     * @param string $level Validation level: basic, moderate, strict
     * @return array        Array with 'valid' boolean and 'errors' array
     */
    public function validate($data, $level = 'moderate') {
        $errors = array();

        // Basic validation (required fields only)
        $errors = array_merge($errors, $this->validate_basic($data));

        if ($level === 'basic') {
            return array(
                'valid' => empty($errors),
                'errors' => $errors
            );
        }

        // Moderate validation (format checks)
        $errors = array_merge($errors, $this->validate_moderate($data));

        if ($level === 'moderate') {
            return array(
                'valid' => empty($errors),
                'errors' => $errors
            );
        }

        // Strict validation (full validation)
        $errors = array_merge($errors, $this->validate_strict($data));

        return array(
            'valid' => empty($errors),
            'errors' => $errors
        );
    }

    /**
     * Basic validation - required fields only
     */
    private function validate_basic($data) {
        $errors = array();

        // Root required fields
        if (empty($data['language'])) {
            $errors[] = array(
                'field' => 'language',
                'message' => __('Language is required.', 'ofero-generator')
            );
        }

        if (empty($data['domain'])) {
            $errors[] = array(
                'field' => 'domain',
                'message' => __('Domain is required.', 'ofero-generator')
            );
        }

        if (empty($data['canonicalUrl'])) {
            $errors[] = array(
                'field' => 'canonicalUrl',
                'message' => __('Canonical URL is required.', 'ofero-generator')
            );
        }

        // Metadata
        if (!isset($data['metadata']) || !is_array($data['metadata'])) {
            $errors[] = array(
                'field' => 'metadata',
                'message' => __('Metadata section is required.', 'ofero-generator')
            );
        } else {
            if (empty($data['metadata']['version'])) {
                $errors[] = array(
                    'field' => 'metadata.version',
                    'message' => __('Version is required.', 'ofero-generator')
                );
            }
            if (empty($data['metadata']['schemaVersion'])) {
                $errors[] = array(
                    'field' => 'metadata.schemaVersion',
                    'message' => __('Schema version is required.', 'ofero-generator')
                );
            }
        }

        // Organization
        if (!isset($data['organization']) || !is_array($data['organization'])) {
            $errors[] = array(
                'field' => 'organization',
                'message' => __('Organization section is required.', 'ofero-generator')
            );
        } else {
            if (empty($data['organization']['legalName'])) {
                $errors[] = array(
                    'field' => 'organization.legalName',
                    'message' => __('Legal name is required.', 'ofero-generator')
                );
            }
            if (empty($data['organization']['entityType'])) {
                $errors[] = array(
                    'field' => 'organization.entityType',
                    'message' => __('Entity type is required.', 'ofero-generator')
                );
            }
        }

        return $errors;
    }

    /**
     * Moderate validation - format checks
     */
    private function validate_moderate($data) {
        $errors = array();

        // Language code format (2 letters)
        if (!empty($data['language']) && !preg_match('/^[a-z]{2}$/i', $data['language'])) {
            $errors[] = array(
                'field' => 'language',
                'message' => __('Language must be a 2-letter ISO code (e.g., "en").', 'ofero-generator')
            );
        }

        // Canonical URL format
        if (!empty($data['canonicalUrl']) && !filter_var($data['canonicalUrl'], FILTER_VALIDATE_URL)) {
            $errors[] = array(
                'field' => 'canonicalUrl',
                'message' => __('Canonical URL must be a valid URL.', 'ofero-generator')
            );
        }

        // Entity type validation
        if (!empty($data['organization']['entityType']) && !in_array($data['organization']['entityType'], self::ENTITY_TYPES)) {
            $errors[] = array(
                'field' => 'organization.entityType',
                'message' => sprintf(
                    __('Invalid entity type. Must be one of: %s', 'ofero-generator'),
                    implode(', ', self::ENTITY_TYPES)
                )
            );
        }

        // Email format
        if (!empty($data['organization']['contactEmail']) && !is_email($data['organization']['contactEmail'])) {
            $errors[] = array(
                'field' => 'organization.contactEmail',
                'message' => __('Contact email must be a valid email address.', 'ofero-generator')
            );
        }

        // Website URL
        if (!empty($data['organization']['website']) && !filter_var($data['organization']['website'], FILTER_VALIDATE_URL)) {
            $errors[] = array(
                'field' => 'organization.website',
                'message' => __('Website must be a valid URL.', 'ofero-generator')
            );
        }

        // Locations validation
        if (!empty($data['locations']) && is_array($data['locations'])) {
            foreach ($data['locations'] as $i => $location) {
                if (!empty($location['type']) && !in_array($location['type'], self::LOCATION_TYPES)) {
                    $errors[] = array(
                        'field' => "locations.{$i}.type",
                        'message' => sprintf(
                            __('Invalid location type at index %d.', 'ofero-generator'),
                            $i
                        )
                    );
                }

                if (!empty($location['email']) && !is_email($location['email'])) {
                    $errors[] = array(
                        'field' => "locations.{$i}.email",
                        'message' => sprintf(
                            __('Invalid email at location index %d.', 'ofero-generator'),
                            $i
                        )
                    );
                }
            }
        }

        // Social platforms validation
        if (!empty($data['communications']['social']) && is_array($data['communications']['social'])) {
            foreach ($data['communications']['social'] as $i => $social) {
                if (!empty($social['url']) && !filter_var($social['url'], FILTER_VALIDATE_URL)) {
                    $errors[] = array(
                        'field' => "communications.social.{$i}.url",
                        'message' => sprintf(
                            __('Invalid URL at social index %d.', 'ofero-generator'),
                            $i
                        )
                    );
                }
            }
        }

        return $errors;
    }

    /**
     * Strict validation - full validation
     */
    private function validate_strict($data) {
        $errors = array();

        // Country code validation (ISO 3166-1 alpha-2)
        if (!empty($data['organization']['identifiers']['primaryIncorporation']['country'])) {
            $country = $data['organization']['identifiers']['primaryIncorporation']['country'];
            if (!preg_match('/^[A-Z]{2}$/', $country)) {
                $errors[] = array(
                    'field' => 'organization.identifiers.primaryIncorporation.country',
                    'message' => __('Country must be a 2-letter ISO code (e.g., "US", "RO").', 'ofero-generator')
                );
            }
        }

        // IBAN validation
        if (!empty($data['banking']) && is_array($data['banking'])) {
            foreach ($data['banking'] as $i => $account) {
                if (!empty($account['iban'])) {
                    $iban = preg_replace('/\s+/', '', $account['iban']);
                    if (!$this->validate_iban($iban)) {
                        $errors[] = array(
                            'field' => "banking.{$i}.iban",
                            'message' => sprintf(
                                __('Invalid IBAN format at banking index %d.', 'ofero-generator'),
                                $i
                            )
                        );
                    }
                }

                // Currency code (ISO 4217)
                if (!empty($account['currency']) && !preg_match('/^[A-Z]{3}$/', $account['currency'])) {
                    $errors[] = array(
                        'field' => "banking.{$i}.currency",
                        'message' => sprintf(
                            __('Currency must be a 3-letter ISO code (e.g., "EUR") at index %d.', 'ofero-generator'),
                            $i
                        )
                    );
                }
            }
        }

        // Brand asset URL validation
        if (!empty($data['brandAssets']) && is_array($data['brandAssets'])) {
            foreach ($data['brandAssets'] as $i => $asset) {
                if (!empty($asset['url']) && !filter_var($asset['url'], FILTER_VALIDATE_URL)) {
                    $errors[] = array(
                        'field' => "brandAssets.{$i}.url",
                        'message' => sprintf(
                            __('Invalid URL at brand asset index %d.', 'ofero-generator'),
                            $i
                        )
                    );
                }
            }
        }

        // Wallet address validation (basic format check)
        if (!empty($data['wallets']) && is_array($data['wallets'])) {
            foreach ($data['wallets'] as $i => $wallet) {
                if (!empty($wallet['address']) && strlen($wallet['address']) < 10) {
                    $errors[] = array(
                        'field' => "wallets.{$i}.address",
                        'message' => sprintf(
                            __('Wallet address seems too short at index %d.', 'ofero-generator'),
                            $i
                        )
                    );
                }
            }
        }

        return $errors;
    }

    /**
     * Basic IBAN validation
     */
    private function validate_iban($iban) {
        $iban = strtoupper(preg_replace('/[^A-Z0-9]/', '', $iban));

        if (strlen($iban) < 15 || strlen($iban) > 34) {
            return false;
        }

        // Check country code
        if (!preg_match('/^[A-Z]{2}/', $iban)) {
            return false;
        }

        return true;
    }

    /**
     * Get validation summary
     */
    public function get_summary($data) {
        $result = $this->validate($data, 'strict');

        $summary = array(
            'total_errors' => count($result['errors']),
            'is_valid' => $result['valid'],
            'sections' => array(
                'basic' => array('valid' => true, 'errors' => 0),
                'organization' => array('valid' => true, 'errors' => 0),
                'locations' => array('valid' => true, 'errors' => 0),
                'banking' => array('valid' => true, 'errors' => 0),
                'wallets' => array('valid' => true, 'errors' => 0),
                'branding' => array('valid' => true, 'errors' => 0),
                'communications' => array('valid' => true, 'errors' => 0)
            )
        );

        foreach ($result['errors'] as $error) {
            $field = $error['field'];

            if (strpos($field, 'organization') === 0) {
                $summary['sections']['organization']['errors']++;
                $summary['sections']['organization']['valid'] = false;
            } elseif (strpos($field, 'locations') === 0) {
                $summary['sections']['locations']['errors']++;
                $summary['sections']['locations']['valid'] = false;
            } elseif (strpos($field, 'banking') === 0) {
                $summary['sections']['banking']['errors']++;
                $summary['sections']['banking']['valid'] = false;
            } elseif (strpos($field, 'wallets') === 0) {
                $summary['sections']['wallets']['errors']++;
                $summary['sections']['wallets']['valid'] = false;
            } elseif (strpos($field, 'brandAssets') === 0) {
                $summary['sections']['branding']['errors']++;
                $summary['sections']['branding']['valid'] = false;
            } elseif (strpos($field, 'communications') === 0) {
                $summary['sections']['communications']['errors']++;
                $summary['sections']['communications']['valid'] = false;
            } else {
                $summary['sections']['basic']['errors']++;
                $summary['sections']['basic']['valid'] = false;
            }
        }

        return $summary;
    }
}
