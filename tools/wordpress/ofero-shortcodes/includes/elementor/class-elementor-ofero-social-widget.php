<?php
/**
 * Elementor Ofero Social Widget
 *
 * @package Ofero_Shortcodes
 */

if (!defined('ABSPATH')) {
    exit;
}

class Elementor_Ofero_Social_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'ofero_social';
    }

    public function get_title() {
        return __('Ofero Social Media', 'ofero-shortcodes');
    }

    public function get_icon() {
        return 'eicon-social-icons';
    }

    public function get_categories() {
        return ['ofero'];
    }

    public function get_keywords() {
        return ['ofero', 'social', 'facebook', 'instagram', 'twitter', 'linkedin'];
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
            'show_icons',
            [
                'label' => __('Show Icons', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'ofero-shortcodes'),
                'label_off' => __('No', 'ofero-shortcodes'),
                'return_value' => 'true',
                'default' => 'true',
            ]
        );

        $this->add_control(
            'platforms',
            [
                'label' => __('Filter Platforms (Optional)', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __('facebook,instagram,twitter', 'ofero-shortcodes'),
                'description' => __('Leave empty to show all platforms. Enter comma-separated platform names to filter.', 'ofero-shortcodes'),
            ]
        );

        $this->add_control(
            'custom_class',
            [
                'label' => __('Custom CSS Class', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'ofero-social',
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
            'link_color',
            [
                'label' => __('Link Color', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ofero-social-link' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'link_hover_color',
            [
                'label' => __('Link Hover Color', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ofero-social-link:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => __('Icon Size', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ofero-social-icon' => 'font-size: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'spacing',
            [
                'label' => __('Spacing', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .ofero-social' => 'gap: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $icons = $settings['show_icons'] === 'true' ? 'true' : 'false';
        $platforms = !empty($settings['platforms']) ? $settings['platforms'] : '';
        $custom_class = !empty($settings['custom_class']) ? $settings['custom_class'] : 'ofero-social';

        $shortcode = '[ofero_social icons="' . $icons . '" class="' . esc_attr($custom_class) . '"';
        if (!empty($platforms)) {
            $shortcode .= ' platforms="' . esc_attr($platforms) . '"';
        }
        $shortcode .= ']';

        echo do_shortcode($shortcode);
    }

    protected function content_template() {
        ?>
        <#
        var customClass = settings.custom_class || 'ofero-social';
        #>
        <div class="{{ customClass }}">
            <p><?php _e('Preview: Social media links will be displayed here.', 'ofero-shortcodes'); ?></p>
        </div>
        <?php
    }
}
