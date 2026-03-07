<?php
/**
 * Elementor Ofero Location Widget
 *
 * @package Ofero_Shortcodes
 */

if (!defined('ABSPATH')) {
    exit;
}

class Elementor_Ofero_Location_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'ofero_location';
    }

    public function get_title() {
        return __('Ofero Location', 'ofero-shortcodes');
    }

    public function get_icon() {
        return 'eicon-map-pin';
    }

    public function get_categories() {
        return ['ofero'];
    }

    public function get_keywords() {
        return ['ofero', 'location', 'address', 'place'];
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
            'location_index',
            [
                'label' => __('Location Index', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
                'description' => __('Select which location to display (0 = first location)', 'ofero-shortcodes'),
            ]
        );

        $this->add_control(
            'show_fields',
            [
                'label' => __('Show Fields', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => [
                    'name' => __('Name', 'ofero-shortcodes'),
                    'type' => __('Type', 'ofero-shortcodes'),
                    'address' => __('Address', 'ofero-shortcodes'),
                    'phone' => __('Phone', 'ofero-shortcodes'),
                    'email' => __('Email', 'ofero-shortcodes'),
                    'hours' => __('Business Hours', 'ofero-shortcodes'),
                ],
                'default' => ['name', 'address', 'phone', 'email'],
            ]
        );

        $this->add_control(
            'custom_class',
            [
                'label' => __('Custom CSS Class', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'ofero-location',
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
                    '{{WRAPPER}} .ofero-location' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'selector' => '{{WRAPPER}} .ofero-location',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $index = isset($settings['location_index']) ? intval($settings['location_index']) : 0;
        $show_fields = !empty($settings['show_fields']) ? implode(',', $settings['show_fields']) : 'name,address,phone,email';
        $custom_class = !empty($settings['custom_class']) ? $settings['custom_class'] : 'ofero-location';

        echo do_shortcode('[ofero_location index="' . $index . '" show="' . esc_attr($show_fields) . '" class="' . esc_attr($custom_class) . '"]');
    }

    protected function content_template() {
        ?>
        <#
        var showFields = settings.show_fields.join(',');
        var customClass = settings.custom_class || 'ofero-location';
        #>
        <div class="{{ customClass }}">
            <p><?php _e('Preview: Location information will be displayed here.', 'ofero-shortcodes'); ?></p>
        </div>
        <?php
    }
}
