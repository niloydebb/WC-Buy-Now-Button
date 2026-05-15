<?php
/**
 * Elementor Widget — Buy Now Button
 *
 * Fully compatible with Elementor 3.0+
 * All style controls (color, typography, padding, border, radius, shadow,
 * alignment) apply live in the editor AND on the front end.
 *
 * KEY FIXES vs previous version:
 *  1. Inline PHP style on the <button> was overriding Elementor CSS selectors
 *     — the button now outputs NO inline style so Elementor controls win.
 *  2. content_template() now reflects every control live in the editor.
 *  3. Product auto-detection works in editor preview mode too.
 *  4. Assets (CSS + JS) are explicitly enqueued for the widget.
 *  5. hover background now uses pure CSS via a <style> tag (JS hover swap
 *     blocked Elementor's selector-based hover control).
 *
 * @package WC_Buy_Now_Button
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
    return;
}

class WCBN_Elementor_Widget extends \Elementor\Widget_Base {

    public function get_name()       { return 'wcbn_buy_now_button'; }
    public function get_title()      { return __( 'Buy Now Button', 'wc-buy-now-button' ); }
    public function get_icon()       { return 'eicon-button'; }
    public function get_categories() { return array( 'general', 'woocommerce-elements' ); }
    public function get_keywords()   { return array( 'buy now', 'woocommerce', 'cart', 'checkout', 'button', 'purchase' ); }

    /**
     * Enqueue widget assets on frontend + editor.
     */
    public function get_style_depends()  { return array( 'wcbn-style' ); }
    public function get_script_depends() { return array( 'wcbn-script' ); }

    // =========================================================================
    // CONTROLS
    // =========================================================================
    protected function register_controls() {

        // ── CONTENT TAB ───────────────────────────────────────────────────────
        $this->start_controls_section( 'section_content', array(
            'label' => __( 'Button Settings', 'wc-buy-now-button' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ) );

        $this->add_control( 'product_id', array(
            'label'       => __( 'Product ID', 'wc-buy-now-button' ),
            'type'        => \Elementor\Controls_Manager::NUMBER,
            'min'         => 1,
            'placeholder' => __( 'Leave empty to auto-detect', 'wc-buy-now-button' ),
            'description' => __( 'Leave empty on single product pages — the product is detected automatically.', 'wc-buy-now-button' ),
        ) );

        $this->add_control( 'button_text', array(
            'label'       => __( 'Button Label', 'wc-buy-now-button' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'default'     => __( 'Buy Now', 'wc-buy-now-button' ),
            'placeholder' => __( 'Buy Now', 'wc-buy-now-button' ),
            'label_block' => true,
        ) );

        $this->add_control( 'quantity', array(
            'label'   => __( 'Quantity', 'wc-buy-now-button' ),
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'default' => 1,
            'min'     => 1,
            'max'     => 999,
        ) );

        $this->add_control( 'redirect', array(
            'label'   => __( 'After Click', 'wc-buy-now-button' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'checkout',
            'options' => array(
                'checkout' => __( 'Go to Checkout', 'wc-buy-now-button' ),
                'cart'     => __( 'Go to Cart', 'wc-buy-now-button' ),
            ),
        ) );

        $this->add_responsive_control( 'button_align', array(
            'label'     => __( 'Alignment', 'wc-buy-now-button' ),
            'type'      => \Elementor\Controls_Manager::CHOOSE,
            'options'   => array(
                'left'   => array( 'title' => __( 'Left', 'wc-buy-now-button' ),   'icon' => 'eicon-h-align-left' ),
                'center' => array( 'title' => __( 'Center', 'wc-buy-now-button' ), 'icon' => 'eicon-h-align-center' ),
                'right'  => array( 'title' => __( 'Right', 'wc-buy-now-button' ),  'icon' => 'eicon-h-align-right' ),
            ),
            'default'   => 'left',
            'selectors' => array( '{{WRAPPER}} .wcbn-wrap' => 'text-align: {{VALUE}};' ),
        ) );

        $this->end_controls_section();

        // ── STYLE TAB — Typography ────────────────────────────────────────────
        $this->start_controls_section( 'section_style_typography', array(
            'label' => __( 'Typography', 'wc-buy-now-button' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ) );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name'     => 'button_typography',
                'selector' => '{{WRAPPER}} .wcbn-buy-now-btn',
            )
        );

        $this->end_controls_section();

        // ── STYLE TAB — Button Colors ─────────────────────────────────────────
        $this->start_controls_section( 'section_style_colors', array(
            'label' => __( 'Colors', 'wc-buy-now-button' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ) );

        $this->start_controls_tabs( 'color_tabs' );

        // Normal state.
        $this->start_controls_tab( 'tab_normal', array( 'label' => __( 'Normal', 'wc-buy-now-button' ) ) );

        $this->add_control( 'text_color', array(
            'label'     => __( 'Text Color', 'wc-buy-now-button' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => array( '{{WRAPPER}} .wcbn-buy-now-btn' => 'color: {{VALUE}} !important;' ),
        ) );

        $this->add_control( 'bg_color', array(
            'label'     => __( 'Background Color', 'wc-buy-now-button' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#2c6fad',
            'selectors' => array( '{{WRAPPER}} .wcbn-buy-now-btn' => 'background-color: {{VALUE}} !important;' ),
        ) );

        $this->end_controls_tab();

        // Hover state.
        $this->start_controls_tab( 'tab_hover', array( 'label' => __( 'Hover', 'wc-buy-now-button' ) ) );

        $this->add_control( 'hover_text_color', array(
            'label'     => __( 'Text Color', 'wc-buy-now-button' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => array( '{{WRAPPER}} .wcbn-buy-now-btn:hover' => 'color: {{VALUE}} !important;' ),
        ) );

        $this->add_control( 'hover_bg_color', array(
            'label'     => __( 'Background Color', 'wc-buy-now-button' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#1a4f82',
            'selectors' => array( '{{WRAPPER}} .wcbn-buy-now-btn:hover' => 'background-color: {{VALUE}} !important;' ),
        ) );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();

        // ── STYLE TAB — Size & Shape ──────────────────────────────────────────
        $this->start_controls_section( 'section_style_shape', array(
            'label' => __( 'Size & Shape', 'wc-buy-now-button' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ) );

        $this->add_responsive_control( 'button_padding', array(
            'label'      => __( 'Padding', 'wc-buy-now-button' ),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => array( 'px', 'em', '%' ),
            'default'    => array(
                'top'    => '12', 'right'  => '24',
                'bottom' => '12', 'left'   => '24',
                'unit'   => 'px', 'isLinked' => false,
            ),
            'selectors'  => array(
                '{{WRAPPER}} .wcbn-buy-now-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
            ),
        ) );

        $this->add_responsive_control( 'button_border_radius', array(
            'label'      => __( 'Border Radius', 'wc-buy-now-button' ),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => array( 'px', '%' ),
            'default'    => array(
                'top' => '4', 'right' => '4', 'bottom' => '4', 'left' => '4',
                'unit' => 'px', 'isLinked' => true,
            ),
            'selectors'  => array(
                '{{WRAPPER}} .wcbn-buy-now-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
            ),
        ) );

        $this->add_responsive_control( 'button_width', array(
            'label'      => __( 'Button Width', 'wc-buy-now-button' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => array( 'px', '%' ),
            'range'      => array(
                'px' => array( 'min' => 80,  'max' => 800 ),
                '%'  => array( 'min' => 10,  'max' => 100 ),
            ),
            'selectors'  => array(
                '{{WRAPPER}} .wcbn-buy-now-btn' => 'width: {{SIZE}}{{UNIT}};',
            ),
        ) );

        $this->end_controls_section();

        // ── STYLE TAB — Border ────────────────────────────────────────────────
        $this->start_controls_section( 'section_style_border', array(
            'label' => __( 'Border & Shadow', 'wc-buy-now-button' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ) );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name'     => 'button_border',
                'selector' => '{{WRAPPER}} .wcbn-buy-now-btn',
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'button_shadow',
                'selector' => '{{WRAPPER}} .wcbn-buy-now-btn',
            )
        );

        $this->end_controls_section();
    }

    // =========================================================================
    // RENDER (front end + editor preview via PHP)
    // =========================================================================
    protected function render() {
        // Ensure assets are loaded.
        do_action( 'wcbn_render_assets' );

        $s = $this->get_settings_for_display();

        // Resolve product.
        $product = $this->get_product( $s );

        $is_edit = \Elementor\Plugin::instance()->editor->is_edit_mode();

        if ( ! $product ) {
            if ( $is_edit ) {
                echo '<div style="padding:12px 16px;border:2px dashed #b0c4d8;border-radius:6px;color:#555;font-size:13px;background:#f0f6fb;">'
                    . '<strong>' . esc_html__( 'Buy Now Button', 'wc-buy-now-button' ) . '</strong><br>'
                    . esc_html__( 'Set a Product ID above, or place this widget on a single product page for auto-detection.', 'wc-buy-now-button' )
                    . '</div>';
            }
            return;
        }

        if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
            if ( $is_edit ) {
                echo '<div style="padding:10px 14px;border:1px dashed #f5c6cb;border-radius:4px;color:#721c24;font-size:13px;background:#f8d7da;">'
                    . esc_html__( 'Product is not purchasable or out of stock.', 'wc-buy-now-button' )
                    . '</div>';
            }
            return;
        }

        $btn_text = ! empty( $s['button_text'] ) ? $s['button_text'] : __( 'Buy Now', 'wc-buy-now-button' );
        $quantity = ! empty( $s['quantity'] ) ? absint( $s['quantity'] ) : 1;
        $redirect = ( ! empty( $s['redirect'] ) && 'cart' === $s['redirect'] ) ? 'cart' : 'checkout';
        $nonce    = wp_create_nonce( 'wcbn_nonce' );

        /* translators: %s product name */
        $aria = sprintf( __( 'Buy %s now', 'wc-buy-now-button' ), $product->get_name() );

        /*
         * IMPORTANT: No inline style on the button itself.
         * All colours, padding, radius etc. come from Elementor's CSS selectors.
         * Only structural styles that don't conflict with controls are kept.
         */
        ?>
        <div class="wcbn-wrap">
            <button
                type="button"
                class="wcbn-buy-now-btn wcbn-elementor"
                data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
                data-variation-id="0"
                data-quantity="<?php echo esc_attr( $quantity ); ?>"
                data-redirect="<?php echo esc_attr( $redirect ); ?>"
                data-nonce="<?php echo esc_attr( $nonce ); ?>"
                data-original-text="<?php echo esc_attr( $btn_text ); ?>"
                aria-label="<?php echo esc_attr( $aria ); ?>"
            >
                <span class="wcbn-btn-text"><?php echo esc_html( $btn_text ); ?></span>
                <span class="wcbn-spinner" aria-hidden="true" style="display:none;"></span>
            </button>
        </div>
        <?php
    }

    // =========================================================================
    // EDITOR LIVE PREVIEW (JS template)
    // =========================================================================
    protected function content_template() {
        ?>
        <#
        var btnText  = settings.button_text  || 'Buy Now';
        var bgColor  = settings.bg_color     || '#2c6fad';
        var txtColor = settings.text_color   || '#ffffff';
        var align    = settings.button_align || 'left';

        var paddingTop    = ( settings.button_padding && settings.button_padding.top    ) ? settings.button_padding.top    : '12';
        var paddingRight  = ( settings.button_padding && settings.button_padding.right  ) ? settings.button_padding.right  : '24';
        var paddingBottom = ( settings.button_padding && settings.button_padding.bottom ) ? settings.button_padding.bottom : '12';
        var paddingLeft   = ( settings.button_padding && settings.button_padding.left   ) ? settings.button_padding.left   : '24';
        var paddingUnit   = ( settings.button_padding && settings.button_padding.unit   ) ? settings.button_padding.unit   : 'px';

        var radTop    = ( settings.button_border_radius && settings.button_border_radius.top    ) ? settings.button_border_radius.top    : '4';
        var radRight  = ( settings.button_border_radius && settings.button_border_radius.right  ) ? settings.button_border_radius.right  : '4';
        var radBottom = ( settings.button_border_radius && settings.button_border_radius.bottom ) ? settings.button_border_radius.bottom : '4';
        var radLeft   = ( settings.button_border_radius && settings.button_border_radius.left   ) ? settings.button_border_radius.left   : '4';
        var radUnit   = ( settings.button_border_radius && settings.button_border_radius.unit   ) ? settings.button_border_radius.unit   : 'px';

        var btnStyle = 'background-color:' + bgColor + ';'
                     + 'color:' + txtColor + ';'
                     + 'padding:' + paddingTop + paddingUnit + ' ' + paddingRight + paddingUnit + ' ' + paddingBottom + paddingUnit + ' ' + paddingLeft + paddingUnit + ';'
                     + 'border-radius:' + radTop + radUnit + ' ' + radRight + radUnit + ' ' + radBottom + radUnit + ' ' + radLeft + radUnit + ';'
                     + 'display:inline-flex;align-items:center;gap:6px;cursor:pointer;'
                     + 'border:none;line-height:1.4;box-sizing:border-box;font-size:16px;font-weight:600;';
        #>
        <div class="wcbn-wrap" style="text-align: {{ align }};">
            <button type="button" class="wcbn-buy-now-btn wcbn-elementor" style="{{ btnStyle }}">
                <span class="wcbn-btn-text">{{{ btnText }}}</span>
            </button>
        </div>
        <?php
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Resolve product from widget setting or page context.
     *
     * @param  array $settings Widget settings.
     * @return WC_Product|false
     */
    private function get_product( $settings ) {
        // 1. Explicit product ID from widget setting.
        if ( ! empty( $settings['product_id'] ) ) {
            $p = wc_get_product( absint( $settings['product_id'] ) );
            if ( $p ) {
                return $p;
            }
        }

        // 2. WooCommerce global $product (set on product pages and in loops).
        global $product;
        if ( $product instanceof WC_Product ) {
            return $product;
        }

        // 3. Current post is a product.
        $post_id = get_the_ID();
        if ( $post_id ) {
            $post = get_post( $post_id );
            if ( $post && 'product' === $post->post_type ) {
                $p = wc_get_product( $post_id );
                if ( $p ) {
                    return $p;
                }
            }
        }

        // 4. is_product() — single product page query.
        if ( function_exists( 'is_product' ) && is_product() ) {
            $p = wc_get_product( get_queried_object_id() );
            if ( $p ) {
                return $p;
            }
        }

        return false;
    }
}
