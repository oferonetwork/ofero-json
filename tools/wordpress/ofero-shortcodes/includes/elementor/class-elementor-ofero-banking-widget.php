<?php
/**
 * Elementor Ofero Banking Widget
 *
 * @package Ofero_Shortcodes
 */

if (!defined('ABSPATH')) {
    exit;
}

class Elementor_Ofero_Banking_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'ofero_banking';
    }

    public function get_title() {
        return __('Ofero Banking', 'ofero-shortcodes');
    }

    public function get_icon() {
        return 'eicon-price-table';
    }

    public function get_categories() {
        return ['ofero'];
    }

    public function get_keywords() {
        return ['ofero', 'banking', 'iban', 'account', 'payment'];
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
            'banking_index',
            [
                'label' => __('Bank Account Index', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
                'description' => __('Select which bank account to display (0 = first account)', 'ofero-shortcodes'),
            ]
        );

        $this->add_control(
            'show_fields',
            [
                'label' => __('Show Fields', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => [
                    'account' => __('Account Name', 'ofero-shortcodes'),
                    'bank' => __('Bank Name', 'ofero-shortcodes'),
                    'iban' => __('IBAN', 'ofero-shortcodes'),
                    'bic' => __('BIC/SWIFT', 'ofero-shortcodes'),
                    'currency' => __('Currency', 'ofero-shortcodes'),
                ],
                'default' => ['account', 'bank', 'iban', 'bic', 'currency'],
            ]
        );

        $this->add_control(
            'custom_class',
            [
                'label' => __('Custom CSS Class', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'ofero-banking',
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
                    '{{WRAPPER}} .ofero-banking' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'label_color',
            [
                'label' => __('Label Color', 'ofero-shortcodes'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ofero-label' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'selector' => '{{WRAPPER}} .ofero-banking',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $index = isset($settings['banking_index']) ? intval($settings['banking_index']) : 0;
        $show_fields = !empty($settings['show_fields']) ? implode(',', $settings['show_fields']) : 'account,bank,iban,bic,currency';
        $custom_class = !empty($settings['custom_class']) ? $settings['custom_class'] : 'ofero-banking';

        echo do_shortcode('[ofero_banking index="' . $index . '" show="' . esc_attr($show_fields) . '" class="' . esc_attr($custom_class) . '"]');
    }

    protected function content_template() {
        ?>
        <#
        var showFields = settings.show_fields.join(',');
        var customClass = settings.custom_class || 'ofero-banking';
        #>
        <div class="{{ customClass }}">
            <p><?php _e('Preview: Banking information will be displayed here.', 'ofero-shortcodes'); ?></p>
        </div>
        <?php
    }
}
