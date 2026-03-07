<?php
/**
 * Elementor Ofero Organization Widget
 *
 * @package Ofero_Shortcodes
 */

if (!defined('ABSPATH')) {
    exit;
}

class Elementor_Ofero_Organization_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'ofero_organization';
    }

    public function get_title() {
        return __('Ofero Organization', 'ofero-shortcodes');
    }

    public function get_icon() {
        return 'eicon-info-circle';
    }

    public function get_categories() {
        return ['ofero'];
    }

    public function get_keywords() {
        return ['ofero', 'organization', 'company', 'business'];
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
            'show_fields',
            [
                'label' => __('Show Fields', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => [
                    'name' => __('Brand Name', 'ofero-shortcodes'),
                    'legal_name' => __('Legal Name', 'ofero-shortcodes'),
                    'description' => __('Description', 'ofero-shortcodes'),
                    'email' => __('Email', 'ofero-shortcodes'),
                    'phone' => __('Phone', 'ofero-shortcodes'),
                    'website' => __('Website', 'ofero-shortcodes'),
                ],
                'default' => ['name', 'email', 'phone', 'website'],
            ]
        );

        $this->add_control(
            'custom_class',
            [
                'label' => __('Custom CSS Class', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'ofero-organization',
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
                    '{{WRAPPER}} .ofero-organization' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'selector' => '{{WRAPPER}} .ofero-organization',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $show_fields = !empty($settings['show_fields']) ? implode(',', $settings['show_fields']) : 'name,email,phone,website';
        $custom_class = !empty($settings['custom_class']) ? $settings['custom_class'] : 'ofero-organization';

        echo do_shortcode('[ofero_organization show="' . esc_attr($show_fields) . '" class="' . esc_attr($custom_class) . '"]');
    }

    protected function content_template() {
        ?>
        <#
        var showFields = settings.show_fields.join(',');
        var customClass = settings.custom_class || 'ofero-organization';
        #>
        <div class="{{ customClass }}">
            <p><?php _e('Preview: Organization information will be displayed here.', 'ofero-shortcodes'); ?></p>
        </div>
        <?php
    }
}
