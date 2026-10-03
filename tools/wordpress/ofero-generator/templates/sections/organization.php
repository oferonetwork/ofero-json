<?php
/**
 * Organization Section Template
 *
 * @package Ofero_Generator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// These templates are included from inside Ofero_Generator_Admin_Page::render(), so every
// variable below is a method local, not a global. PHPCS cannot see the enclosing scope.
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$org = $data['organization'] ?? array();
$identifiers = $org['identifiers'] ?? array();
$primaryInc = $identifiers['primaryIncorporation'] ?? array();
$classification = $data['businessClassification'] ?? array();
$serviceArea = $classification['serviceArea'] ?? array();

// brandName/description are TranslatableString objects in v2; older files may hold plain strings.
$ofero_text = function ($value) {
    if (is_array($value)) {
        return isset($value['default']) ? (string) $value['default'] : '';
    }
    return is_scalar($value) ? (string) $value : '';
};
$ofero_list = function ($value) {
    return is_array($value) ? implode(', ', array_map('strval', $value)) : '';
};

$saModes = $serviceArea['modes'] ?? array();
$saRegionLines = array();
foreach ($serviceArea['regions'] ?? array() as $region) {
    if (!empty($region['subdivision'])) {
        $saRegionLines[] = $region['subdivision'];
    } elseif (!empty($region['country']) && !empty($region['name'])) {
        $saRegionLines[] = $region['country'] . ', ' . $region['name'];
    }
}
?>
<div class="ofero-card">
    <h2><?php esc_html_e('Organization Details', 'ofero-generator'); ?></h2>

    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="org_legalName"><?php esc_html_e('Legal Name', 'ofero-generator'); ?> <span class="required">*</span></label>
            </th>
            <td>
                <input type="text" id="org_legalName" name="org_legalName"
                       value="<?php echo esc_attr($org['legalName'] ?? ''); ?>"
                       class="regular-text" required>
                <p class="description">
                    <?php esc_html_e('Official registered name of the organization', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="org_brandName"><?php esc_html_e('Brand Name', 'ofero-generator'); ?></label>
            </th>
            <td>
                <input type="text" id="org_brandName" name="org_brandName"
                       value="<?php echo esc_attr($ofero_text($org['brandName'] ?? '')); ?>"
                       class="regular-text">
                <p class="description">
                    <?php esc_html_e('Trading or brand name (if different from legal name)', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="org_entityType"><?php esc_html_e('Entity Type', 'ofero-generator'); ?> <span class="required">*</span></label>
            </th>
            <td>
                <select id="org_entityType" name="org_entityType" required>
                    <?php
                    $entityTypes = array(
                        'company' => __('Company', 'ofero-generator'),
                        'foundation' => __('Foundation', 'ofero-generator'),
                        'association' => __('Association', 'ofero-generator'),
                        'protocol' => __('Protocol (Web3)', 'ofero-generator'),
                        'store' => __('Store / E-commerce', 'ofero-generator'),
                        'ngo' => __('NGO', 'ofero-generator'),
                        'individual' => __('Individual / Freelancer', 'ofero-generator'),
                        'project' => __('Project', 'ofero-generator'),
                        'other' => __('Other', 'ofero-generator')
                    );
                    $currentType = $org['entityType'] ?? 'company';
                    foreach ($entityTypes as $value => $label):
                    ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($currentType, $value); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="org_legalForm"><?php esc_html_e('Legal Form', 'ofero-generator'); ?></label>
            </th>
            <td>
                <input type="text" id="org_legalForm" name="org_legalForm"
                       value="<?php echo esc_attr($org['legalForm'] ?? ''); ?>"
                       class="regular-text">
                <p class="description">
                    <?php esc_html_e('e.g., LLC, SRL, GmbH, Inc., Ltd.', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="org_description"><?php esc_html_e('Description', 'ofero-generator'); ?></label>
            </th>
            <td>
                <textarea id="org_description" name="org_description" rows="4" class="large-text"><?php echo esc_textarea($ofero_text($org['description'] ?? '')); ?></textarea>
                <p class="description">
                    <?php esc_html_e('Brief description of the organization', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="org_website"><?php esc_html_e('Website', 'ofero-generator'); ?></label>
            </th>
            <td>
                <input type="url" id="org_website" name="org_website"
                       value="<?php echo esc_url($org['website'] ?? ''); ?>"
                       class="regular-text">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="org_contactEmail"><?php esc_html_e('Contact Email', 'ofero-generator'); ?></label>
            </th>
            <td>
                <input type="email" id="org_contactEmail" name="org_contactEmail"
                       value="<?php echo esc_attr($org['contactEmail'] ?? ''); ?>"
                       class="regular-text">
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="org_contactPhone"><?php esc_html_e('Contact Phone', 'ofero-generator'); ?></label>
            </th>
            <td>
                <input type="tel" id="org_contactPhone" name="org_contactPhone"
                       value="<?php echo esc_attr($org['contactPhone'] ?? ''); ?>"
                       class="regular-text">
                <p class="description">
                    <?php esc_html_e('Include country code (e.g., +1 234 567 8900)', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
    </table>
</div>

<div class="ofero-card">
    <h2><?php esc_html_e('Primary Incorporation', 'ofero-generator'); ?></h2>

    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="inc_country"><?php esc_html_e('Country', 'ofero-generator'); ?></label>
            </th>
            <td>
                <input type="text" id="inc_country" name="inc_country"
                       value="<?php echo esc_attr($primaryInc['country'] ?? ''); ?>"
                       class="small-text" maxlength="2" pattern="[A-Za-z]{2}"
                       style="text-transform: uppercase;">
                <p class="description">
                    <?php esc_html_e('2-letter ISO country code (e.g., US, GB, DE)', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="inc_registrationNumber"><?php esc_html_e('Registration Number', 'ofero-generator'); ?></label>
            </th>
            <td>
                <input type="text" id="inc_registrationNumber" name="inc_registrationNumber"
                       value="<?php echo esc_attr($primaryInc['registrationNumber'] ?? ''); ?>"
                       class="regular-text">
                <p class="description">
                    <?php esc_html_e('Company registration number (e.g., 12-3456789, 123456789)', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="inc_taxId"><?php esc_html_e('Tax ID', 'ofero-generator'); ?></label>
            </th>
            <td>
                <input type="text" id="inc_taxId" name="inc_taxId"
                       value="<?php echo esc_attr($primaryInc['taxId'] ?? ''); ?>"
                       class="regular-text">
                <p class="description">
                    <?php esc_html_e('Tax identification number (e.g., 12-3456789, EIN, TIN)', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="inc_vatNumber"><?php esc_html_e('VAT Number', 'ofero-generator'); ?></label>
            </th>
            <td>
                <input type="text" id="inc_vatNumber" name="inc_vatNumber"
                       value="<?php echo esc_attr($primaryInc['vatNumber'] ?? ''); ?>"
                       class="regular-text">
                <p class="description">
                    <?php esc_html_e('VAT registration number (e.g., RO12345678)', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
    </table>
</div>

<div class="ofero-card">
    <h2><?php esc_html_e('Business Classification', 'ofero-generator'); ?></h2>

    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="bc_industry"><?php esc_html_e('Industry', 'ofero-generator'); ?></label>
            </th>
            <td>
                <input type="text" id="bc_industry" name="bc_industry"
                       value="<?php echo esc_attr($ofero_list($classification['industry'] ?? array())); ?>"
                       class="large-text" placeholder="creative-services, audio-production, commercial-audio">
                <p class="description">
                    <?php esc_html_e('Comma-separated path from general to specific, using IDs from the ofero.json industry taxonomy (ofero-json-industries.json). The first item is also written to organization.industry, which is required for companies.', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="bc_primaryProducts"><?php esc_html_e('Main Products / Services', 'ofero-generator'); ?></label>
            </th>
            <td>
                <input type="text" id="bc_primaryProducts" name="bc_primaryProducts"
                       value="<?php echo esc_attr($ofero_list($classification['primaryProducts'] ?? array())); ?>"
                       class="large-text">
                <p class="description">
                    <?php esc_html_e('Comma-separated, free text (e.g., radio ad spots, jingles, voice-over)', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e('Target Market', 'ofero-generator'); ?></th>
            <td>
                <?php foreach (array('B2C', 'B2B', 'B2G', 'C2C') as $market): ?>
                    <label style="margin-right: 12px;">
                        <input type="checkbox" name="bc_targetMarket[]" value="<?php echo esc_attr($market); ?>"
                               <?php checked(in_array($market, $classification['targetMarket'] ?? array(), true)); ?>>
                        <?php echo esc_html($market); ?>
                    </label>
                <?php endforeach; ?>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="bc_operationalStatus"><?php esc_html_e('Operational Status', 'ofero-generator'); ?></label>
            </th>
            <td>
                <select id="bc_operationalStatus" name="bc_operationalStatus">
                    <?php
                    $statuses = array(
                        '' => __('— Not specified —', 'ofero-generator'),
                        'active' => __('Active', 'ofero-generator'),
                        'pending' => __('Pending launch', 'ofero-generator'),
                        'suspended' => __('Temporarily suspended', 'ofero-generator'),
                        'closed' => __('Permanently closed', 'ofero-generator')
                    );
                    $currentStatus = $classification['operationalStatus'] ?? '';
                    foreach ($statuses as $value => $label):
                    ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($currentStatus, $value); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
    </table>
</div>

<div class="ofero-card">
    <h2><?php esc_html_e('Service Area', 'ofero-generator'); ?></h2>

    <p class="description">
        <?php esc_html_e('Where and how you serve customers. Fill this in if you have no public address (remote agency, online service) or if you serve customers beyond your locations. Leave it empty if customers simply visit your locations.', 'ofero-generator'); ?>
    </p>

    <table class="form-table">
        <tr>
            <th scope="row"><?php esc_html_e('How you serve customers', 'ofero-generator'); ?></th>
            <td>
                <?php
                $modes = array(
                    'on-premises' => __('At our location (customers come to us)', 'ofero-generator'),
                    'at-customer' => __('At the customer (we travel to them)', 'ofero-generator'),
                    'remote' => __('Remotely (online, phone, email, shipping)', 'ofero-generator')
                );
                foreach ($modes as $value => $label):
                ?>
                    <label style="display: block; margin-bottom: 4px;">
                        <input type="checkbox" name="sa_modes[]" value="<?php echo esc_attr($value); ?>"
                               <?php checked(in_array($value, $saModes, true)); ?>>
                        <?php echo esc_html($label); ?>
                    </label>
                <?php endforeach; ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php esc_html_e('Worldwide', 'ofero-generator'); ?></th>
            <td>
                <label>
                    <input type="checkbox" name="sa_worldwide" value="1" <?php checked(!empty($serviceArea['worldwide'])); ?>>
                    <?php esc_html_e('We serve customers in any country', 'ofero-generator'); ?>
                </label>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="sa_countries"><?php esc_html_e('Countries', 'ofero-generator'); ?></label>
            </th>
            <td>
                <input type="text" id="sa_countries" name="sa_countries"
                       value="<?php echo esc_attr($ofero_list($serviceArea['countries'] ?? array())); ?>"
                       class="regular-text" placeholder="US, CA" style="text-transform: uppercase;">
                <p class="description">
                    <?php esc_html_e('Comma-separated 2-letter ISO codes. Each country is served in full unless you list regions for it below.', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="sa_regions"><?php esc_html_e('Regions / Cities', 'ofero-generator'); ?></label>
            </th>
            <td>
                <textarea id="sa_regions" name="sa_regions" rows="4" class="regular-text"><?php echo esc_textarea(implode("\n", $saRegionLines)); ?></textarea>
                <p class="description">
                    <?php esc_html_e('Optional, one per line: an ISO 3166-2 code (e.g., US-CA) or "country, city" (e.g., US, Austin). Limits service in that country to the listed areas.', 'ofero-generator'); ?>
                </p>
            </td>
        </tr>
    </table>
</div>
