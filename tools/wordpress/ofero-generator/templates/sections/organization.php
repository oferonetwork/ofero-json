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

$org = $data['organization'] ?? array();
$identifiers = $org['identifiers'] ?? array();
$primaryInc = $identifiers['primaryIncorporation'] ?? array();
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
                       value="<?php echo esc_attr($org['brandName'] ?? ''); ?>"
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
                <textarea id="org_description" name="org_description" rows="4" class="large-text"><?php echo esc_textarea($org['description'] ?? ''); ?></textarea>
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
