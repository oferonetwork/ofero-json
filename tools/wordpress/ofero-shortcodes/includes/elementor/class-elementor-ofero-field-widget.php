<?php
/**
 * Elementor Ofero Field Widget
 *
 * @package Ofero_Shortcodes
 */

if (!defined('ABSPATH')) {
    exit;
}

class Elementor_Ofero_Field_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'ofero_field';
    }

    public function get_title() {
        return __('Ofero Field', 'ofero-shortcodes');
    }

    public function get_icon() {
        return 'eicon-database';
    }

    public function get_categories() {
        return ['ofero'];
    }

    public function get_keywords() {
        return ['ofero', 'field', 'data', 'custom'];
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'ofero-shortcodes'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'field_path',
            [
                'label' => __('Field Path', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __('organization.legalName', 'ofero-shortcodes'),
                'description' => __('Enter the dot-notation path to the field (e.g., organization.legalName, locations.0.address.city)', 'ofero-shortcodes'),
            ]
        );

        $this->add_control(
            'common_fields',
            [
                'label' => __('Or Select Common Field', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '' => __('Custom (use field path above)', 'ofero-shortcodes'),
                    'organization.legalName' => __('Legal Name', 'ofero-shortcodes'),
                    'organization.brandName' => __('Brand Name', 'ofero-shortcodes'),
                    'organization.description' => __('Description', 'ofero-shortcodes'),
                    'organization.contactEmail' => __('Contact Email', 'ofero-shortcodes'),
                    'organization.contactPhone' => __('Contact Phone', 'ofero-shortcodes'),
                    'organization.website' => __('Website', 'ofero-shortcodes'),
                    'domain' => __('Domain', 'ofero-shortcodes'),
                    'locations.0.name' => __('First Location Name', 'ofero-shortcodes'),
                    'locations.0.address.city' => __('First Location City', 'ofero-shortcodes'),
                    'locations.0.phone' => __('First Location Phone', 'ofero-shortcodes'),
                ],
                'default' => '',
            ]
        );

        $this->add_control(
            'default_value',
            [
                'label' => __('Default Value', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('Text to display if field is empty', 'ofero-shortcodes'),
            ]
        );

        $this->add_control(
            'auto_link',
            [
                'label' => __('Auto Link', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'ofero-shortcodes'),
                'label_off' => __('No', 'ofero-shortcodes'),
                'return_value' => 'true',
                'default' => 'false',
                'description' => __('Automatically convert emails and URLs to links', 'ofero-shortcodes'),
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'ofero-shortcodes'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __('Text Color', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'selector' => '{{WRAPPER}}',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        // Use common field if selected, otherwise use custom field path
        $field = !empty($settings['common_fields']) ? $settings['common_fields'] : $settings['field_path'];
        $default = !empty($settings['default_value']) ? $settings['default_value'] : '';
        $link = $settings['auto_link'] === 'true' ? 'true' : 'false';

        if (empty($field)) {
            echo '<p>' . __('Please select a field or enter a field path.', 'ofero-shortcodes') . '</p>';
            return;
        }

        echo do_shortcode('[ofero field="' . esc_attr($field) . '" default="' . esc_attr($default) . '" link="' . $link . '"]');
    }

    protected function content_template() {
        ?>
        <#
        var field = settings.common_fields || settings.field_path || 'field.path';
        #>
        <p><?php _e('Preview: Field value will be displayed here.', 'ofero-shortcodes'); ?></p>
        <p><small><?php _e('Field:', 'ofero-shortcodes'); ?> {{ field }}</small></p>
        <?php
    }
}
