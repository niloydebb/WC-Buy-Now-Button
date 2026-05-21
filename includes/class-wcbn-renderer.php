<?php
/**
 * Renderer — generates the Buy Now button HTML.
 *
 * Used by: shortcode, auto-hooks (single/archive pages).
 * NOT used by Elementor widget (widget renders its own markup so
 * Elementor CSS selectors work without inline style interference).
 *
 * @package WC_Buy_Now_Button
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCBN_Renderer {

    /**
     * Build button HTML with inline styles (for shortcode / hook usage).
     * Elementor widget renders its own HTML to avoid inline-style conflicts.
     *
     * @param  WC_Product $product
     * @param  array      $args
     * @return string
     */
    public static function button( $product, $args = array() ) {
        $settings = WCBN_Settings::instance();

        $defaults = array(
            'text'          => $settings->get( 'button_text', __( 'Buy Now', 'wc-buy-now-button' ) ),
            'quantity'      => $settings->get( 'quantity', 1 ),
            'redirect'      => $settings->get( 'redirect_url', 'checkout' ),
            'class'         => '',
            'bg_color'      => '',
            'text_color'    => '',
            'hover_color'   => '',
            'border_radius' => '',
            'font_size'     => '',
            'variation_id'  => 0,
        );

        $args = wp_parse_args( $args, $defaults );

        // Resolve colours.
        $bg_color     = ( '' !== $args['bg_color'] )    ? sanitize_hex_color( $args['bg_color'] )    : $settings->get( 'button_bg_color',    '#2c6fad' );
        $text_color   = ( '' !== $args['text_color'] )  ? sanitize_hex_color( $args['text_color'] )  : $settings->get( 'button_text_color',  '#ffffff' );
        $hover_color  = ( '' !== $args['hover_color'] ) ? sanitize_hex_color( $args['hover_color'] ) : $settings->get( 'button_hover_color', '#1a4f82' );
        $border_color = $settings->get( 'button_border_color', '#2c6fad' );
        $border_width = absint( $settings->get( 'button_border_width', 0 ) );

        $border_radius = ( '' !== $args['border_radius'] ) ? absint( $args['border_radius'] ) : absint( $settings->get( 'button_border_radius', 4 ) );
        $padding_v     = absint( $settings->get( 'button_padding_v', 12 ) );
        $padding_h     = absint( $settings->get( 'button_padding_h', 24 ) );
        $font_size     = ( '' !== $args['font_size'] )  ? absint( $args['font_size'] )  : absint( $settings->get( 'button_font_size', 16 ) );
        $font_weight   = $settings->get( 'button_font_weight', '600' );

        // Fallback for empty colours.
        if ( ! $bg_color )    { $bg_color    = '#2c6fad'; }
        if ( ! $text_color )  { $text_color  = '#ffffff'; }
        if ( ! $hover_color ) { $hover_color = '#1a4f82'; }

        // Build inline style.
        $style = implode( ';', array(
            'background-color:' . $bg_color,
            'color:' . $text_color,
            'border-radius:' . $border_radius . 'px',
            'padding:' . $padding_v . 'px ' . $padding_h . 'px',
            'font-size:' . $font_size . 'px',
            'font-weight:' . $font_weight,
            'border:' . $border_width . 'px solid ' . $border_color,
        ) );

        // CSS classes — sanitize each token individually.
        $extra = array_filter( array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
        $classes = trim( 'wcbn-buy-now-btn button ' . implode( ' ', $extra ) );

        // Sanitized data attrs.
        $product_id   = (int) $product->get_id();
        $variation_id = absint( $args['variation_id'] );
        $quantity     = max( 1, absint( $args['quantity'] ) );
        $redirect     = in_array( $args['redirect'], array( 'checkout', 'cart' ), true ) ? $args['redirect'] : 'checkout';
        $nonce        = wp_create_nonce( 'wcbn_nonce' );
        $btn_text     = ( '' !== trim( $args['text'] ) ) ? $args['text'] : $settings->get( 'button_text', __( 'Buy Now', 'wc-buy-now-button' ) );

        /* translators: %s: product name */
        $aria = sprintf( __( 'Buy %s now', 'wc-buy-now-button' ), $product->get_name() );

        ob_start();
        ?>
<div class="wcbn-wrap">
    <button
        type="button"
        class="<?php echo esc_attr( $classes ); ?>"
        style="<?php echo esc_attr( $style ); ?>"
        data-product-id="<?php echo esc_attr( $product_id ); ?>"
        data-variation-id="<?php echo esc_attr( $variation_id ); ?>"
        data-quantity="<?php echo esc_attr( $quantity ); ?>"
        data-redirect="<?php echo esc_attr( $redirect ); ?>"
        data-nonce="<?php echo esc_attr( $nonce ); ?>"
        data-hover-color="<?php echo esc_attr( $hover_color ); ?>"
        data-original-text="<?php echo esc_attr( $btn_text ); ?>"
        aria-label="<?php echo esc_attr( $aria ); ?>"
    >
        <span class="wcbn-btn-text"><?php echo esc_html( $btn_text ); ?></span>
        <span class="wcbn-spinner" aria-hidden="true" style="display:none;"></span>
    </button>
</div>
        <?php
        return ob_get_clean();
    }
}
