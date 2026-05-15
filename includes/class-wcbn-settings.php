<?php
/**
 * Settings — admin page with lightweight AJAX save.
 *
 * Uses a custom AJAX endpoint instead of WordPress options.php to avoid
 * triggering a full admin page load (which causes 503 on busy servers with
 * many active plugins). The save request is tiny and isolated.
 *
 * @package WC_Buy_Now_Button
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCBN_Settings {

    private static $instance = null;
    private $settings        = array();

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->settings = $this->get_all();

        add_action( 'admin_menu',                              array( $this, 'add_menu_page' ) );
        add_action( 'admin_enqueue_scripts',                   array( $this, 'enqueue_admin_assets' ) );
        add_filter( 'plugin_action_links_' . WCBN_PLUGIN_BASE, array( $this, 'action_links' ) );

        // Lightweight AJAX save — bypasses options.php entirely.
        add_action( 'wp_ajax_wcbn_save_settings', array( $this, 'ajax_save' ) );
    }

    // -----------------------------------------------------------------------
    // Public helpers
    // -----------------------------------------------------------------------

    public function get_all() {
        return wp_parse_args(
            (array) get_option( 'wcbn_settings', array() ),
            self::defaults()
        );
    }

    public function get( $key, $default = null ) {
        return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : $default;
    }

    public static function defaults() {
        return array(
            'button_text'          => 'Buy Now',
            'button_text_color'    => '#ffffff',
            'button_bg_color'      => '#2c6fad',
            'button_hover_color'   => '#1a4f82',
            'button_border_color'  => '#2c6fad',
            'button_border_width'  => '0',
            'button_border_radius' => '4',
            'button_padding_v'     => '12',
            'button_padding_h'     => '24',
            'button_font_size'     => '16',
            'button_font_weight'   => '600',
            'show_on_archive'      => 'yes',
            'show_on_single'       => 'yes',
            'redirect_url'         => 'checkout',
            'quantity'             => '1',
        );
    }

    // -----------------------------------------------------------------------
    // AJAX save — lightweight, no options.php, no full admin reload
    // -----------------------------------------------------------------------

    public function ajax_save() {
        // Security checks.
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'wc-buy-now-button' ) ), 403 );
        }

        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'wcbn_settings_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'wc-buy-now-button' ) ), 403 );
        }

        $raw = isset( $_POST['settings'] ) ? (array) wp_unslash( $_POST['settings'] ) : array();

        update_option( 'wcbn_settings', $this->sanitize( $raw ) );

        // Refresh cached settings.
        $this->settings = $this->get_all();

        wp_send_json_success( array( 'message' => __( 'Settings saved.', 'wc-buy-now-button' ) ) );
    }

    // -----------------------------------------------------------------------
    // Sanitize
    // -----------------------------------------------------------------------

    private function sanitize( $input ) {
        $clean    = array();
        $defaults = self::defaults();

        foreach ( $defaults as $key => $default ) {
            $val = isset( $input[ $key ] ) ? $input[ $key ] : $default;

            switch ( $key ) {
                case 'button_text_color':
                case 'button_bg_color':
                case 'button_hover_color':
                case 'button_border_color':
                    $clean[ $key ] = sanitize_hex_color( $val ) ?: $default;
                    break;

                case 'button_border_width':
                case 'button_border_radius':
                case 'button_padding_v':
                case 'button_padding_h':
                case 'button_font_size':
                case 'quantity':
                    $clean[ $key ] = (string) absint( $val );
                    break;

                case 'button_font_weight':
                    $clean[ $key ] = in_array( $val, array( '400','600','700','800' ), true ) ? $val : '600';
                    break;

                case 'redirect_url':
                    $clean[ $key ] = in_array( $val, array( 'checkout', 'cart' ), true ) ? $val : 'checkout';
                    break;

                case 'show_on_archive':
                case 'show_on_single':
                    $clean[ $key ] = ( 'yes' === $val ) ? 'yes' : 'no';
                    break;

                default:
                    $clean[ $key ] = sanitize_text_field( $val );
                    break;
            }
        }

        return $clean;
    }

    // -----------------------------------------------------------------------
    // Admin menu
    // -----------------------------------------------------------------------

    public function add_menu_page() {
        add_submenu_page(
            'woocommerce',
            __( 'Buy Now Button', 'wc-buy-now-button' ),
            __( 'Buy Now Button', 'wc-buy-now-button' ),
            'manage_woocommerce',
            'wcbn-settings',
            array( $this, 'render_page' )
        );
    }

    public function enqueue_admin_assets( $hook ) {
        // Enqueue copy-ID script on product list page too.
        if ( 'edit.php' === $hook && isset( $_GET['post_type'] ) && 'product' === $_GET['post_type'] ) {
            wp_enqueue_script( 'wcbn-admin-copy', WCBN_PLUGIN_URL . 'public/js/wcbn-admin.js', array( 'jquery' ), WCBN_VERSION, true );
        }

        if ( 'woocommerce_page_wcbn-settings' !== $hook ) {
            return;
        }
        wp_enqueue_script( 'jquery' );
    }

    // -----------------------------------------------------------------------
    // Settings page HTML — pure form, saved via AJAX
    // -----------------------------------------------------------------------

    public function render_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $s   = $this->settings;
        $nonce = wp_create_nonce( 'wcbn_settings_nonce' );
        ?>
        <div class="wrap" id="wcbn-settings-wrap">
            <h1><?php esc_html_e( 'WC Buy Now Button — Settings', 'wc-buy-now-button' ); ?></h1>

            <div id="wcbn-notice" style="display:none;margin:10px 0;padding:10px 14px;border-radius:4px;font-size:14px;"></div>

            <form id="wcbn-settings-form">
                <input type="hidden" id="wcbn-nonce" value="<?php echo esc_attr( $nonce ); ?>">

                <?php $this->section( __( 'General', 'wc-buy-now-button' ) ); ?>
                <table class="form-table" role="presentation">
                    <?php
                    $this->row_text(   'button_text',     __( 'Button Text', 'wc-buy-now-button' ),           $s );
                    $this->row_select( 'redirect_url',    __( 'After Add to Cart', 'wc-buy-now-button' ),     $s,
                        array( 'checkout' => __( 'Redirect to Checkout', 'wc-buy-now-button' ),
                               'cart'     => __( 'Redirect to Cart', 'wc-buy-now-button' ) ) );
                    $this->row_number( 'quantity',        __( 'Default Quantity', 'wc-buy-now-button' ),      $s );
                    $this->row_select( 'show_on_single',  __( 'Show on Single Product', 'wc-buy-now-button' ),$s,
                        array( 'yes' => __( 'Yes', 'wc-buy-now-button' ), 'no' => __( 'No', 'wc-buy-now-button' ) ) );
                    $this->row_select( 'show_on_archive', __( 'Show on Shop / Archive', 'wc-buy-now-button' ),$s,
                        array( 'yes' => __( 'Yes', 'wc-buy-now-button' ), 'no' => __( 'No', 'wc-buy-now-button' ) ) );
                    ?>
                </table>

                <?php $this->section( __( 'Button Style', 'wc-buy-now-button' ) ); ?>
                <table class="form-table" role="presentation">
                    <?php
                    $this->row_color(  'button_text_color',    __( 'Text Color', 'wc-buy-now-button' ),            $s );
                    $this->row_color(  'button_bg_color',      __( 'Background Color', 'wc-buy-now-button' ),      $s );
                    $this->row_color(  'button_hover_color',   __( 'Hover Background Color', 'wc-buy-now-button' ),$s );
                    $this->row_color(  'button_border_color',  __( 'Border Color', 'wc-buy-now-button' ),          $s );
                    $this->row_number( 'button_border_width',  __( 'Border Width (px)', 'wc-buy-now-button' ),     $s );
                    $this->row_number( 'button_border_radius', __( 'Border Radius (px)', 'wc-buy-now-button' ),    $s );
                    $this->row_number( 'button_padding_v',     __( 'Padding Top/Bottom (px)', 'wc-buy-now-button' ),$s );
                    $this->row_number( 'button_padding_h',     __( 'Padding Left/Right (px)', 'wc-buy-now-button' ),$s );
                    $this->row_number( 'button_font_size',     __( 'Font Size (px)', 'wc-buy-now-button' ),        $s );
                    $this->row_select( 'button_font_weight',   __( 'Font Weight', 'wc-buy-now-button' ),           $s,
                        array( '400' => 'Normal (400)', '600' => 'Semi-Bold (600)',
                               '700' => 'Bold (700)',   '800' => 'Extra-Bold (800)' ) );
                    ?>
                </table>

                <p>
                    <button type="button" id="wcbn-save-btn" class="button button-primary" style="font-size:14px;padding:6px 20px;">
                        <?php esc_html_e( 'Save Settings', 'wc-buy-now-button' ); ?>
                    </button>
                    <span id="wcbn-saving" style="display:none;margin-left:10px;color:#666;">
                        <?php esc_html_e( 'Saving...', 'wc-buy-now-button' ); ?>
                    </span>
                </p>
            </form>

            <hr>
            <h2><?php esc_html_e( 'Shortcode', 'wc-buy-now-button' ); ?></h2>
            <p><?php esc_html_e( 'Place this shortcode on any product page — no ID needed:', 'wc-buy-now-button' ); ?></p>
            <code>[buy_now_button]</code>
            <p style="margin-top:10px;"><?php esc_html_e( 'Optional overrides:', 'wc-buy-now-button' ); ?></p>
            <code>[buy_now_button text="Order Now" redirect="cart" quantity="2"]</code>
        </div>

        <script>
        (function($){
            // Inline AJAX save — no page reload, no options.php, no 503.
            $('#wcbn-save-btn').on('click', function(){
                var $btn     = $(this);
                var settings = {};

                // Collect all named inputs inside the form.
                $('#wcbn-settings-form').find('[name]').each(function(){
                    var el = $(this);
                    if ( el.attr('type') === 'checkbox' ) {
                        settings[ el.attr('name') ] = el.is(':checked') ? 'yes' : 'no';
                    } else {
                        settings[ el.attr('name') ] = el.val();
                    }
                });

                $btn.prop('disabled', true);
                $('#wcbn-saving').show();
                $('#wcbn-notice').hide();

                $.post(
                    ajaxurl,
                    {
                        action:   'wcbn_save_settings',
                        nonce:    $('#wcbn-nonce').val(),
                        settings: settings
                    },
                    function(response){
                        $btn.prop('disabled', false);
                        $('#wcbn-saving').hide();

                        var $notice = $('#wcbn-notice');
                        if ( response.success ) {
                            $notice.css({ background:'#d4edda', border:'1px solid #c3e6cb', color:'#155724' })
                                   .text( response.data.message ).show();
                        } else {
                            var msg = (response.data && response.data.message)
                                ? response.data.message : '<?php echo esc_js( __( 'Save failed. Please try again.', 'wc-buy-now-button' ) ); ?>';
                            $notice.css({ background:'#f8d7da', border:'1px solid #f5c6cb', color:'#721c24' })
                                   .text( msg ).show();
                        }

                        // Auto-hide notice after 4 seconds.
                        setTimeout(function(){ $notice.fadeOut(); }, 4000);
                    }
                ).fail(function( xhr ){
                    $btn.prop('disabled', false);
                    $('#wcbn-saving').hide();
                    var msg = '<?php echo esc_js( __( 'Server error. Please try again.', 'wc-buy-now-button' ) ); ?>';
                    $('#wcbn-notice').css({ background:'#f8d7da', border:'1px solid #f5c6cb', color:'#721c24' })
                                    .text( msg + ' (' + xhr.status + ')' ).show();
                });
            });

            // Live hex preview next to colour inputs.
            $('[type="color"]').on('input change', function(){
                $(this).next('code').text( $(this).val() );
            });
        })(jQuery);
        </script>
        <?php
    }

    // -----------------------------------------------------------------------
    // Field row helpers
    // -----------------------------------------------------------------------

    private function section( $title ) {
        echo '<h2 style="margin-top:24px;">' . esc_html( $title ) . '</h2>';
    }

    private function row_text( $key, $label, $s ) {
        echo '<tr><th scope="row"><label for="wcbn_' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th>';
        echo '<td><input type="text" id="wcbn_' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $s[ $key ] ) . '" class="regular-text"></td></tr>';
    }

    private function row_number( $key, $label, $s ) {
        echo '<tr><th scope="row"><label for="wcbn_' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th>';
        echo '<td><input type="number" id="wcbn_' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $s[ $key ] ) . '" min="0" max="999" class="small-text"></td></tr>';
    }

    private function row_color( $key, $label, $s ) {
        echo '<tr><th scope="row"><label for="wcbn_' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th>';
        echo '<td><input type="color" id="wcbn_' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $s[ $key ] ) . '"> <code>' . esc_html( $s[ $key ] ) . '</code></td></tr>';
    }

    private function row_select( $key, $label, $s, $options ) {
        echo '<tr><th scope="row"><label for="wcbn_' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td>';
        echo '<select id="wcbn_' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
        foreach ( $options as $val => $text ) {
            echo '<option value="' . esc_attr( $val ) . '"' . selected( $s[ $key ], $val, false ) . '>' . esc_html( $text ) . '</option>';
        }
        echo '</select></td></tr>';
    }

    // -----------------------------------------------------------------------
    // Plugin action links
    // -----------------------------------------------------------------------

    public function action_links( $links ) {
        $url  = admin_url( 'admin.php?page=wcbn-settings' );
        array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'wc-buy-now-button' ) . '</a>' );
        return $links;
    }
}

// -----------------------------------------------------------------------
// Product ID column in WooCommerce → Products list
// -----------------------------------------------------------------------
function wcbn_add_product_id_column( $columns ) {
    $new = array();
    foreach ( $columns as $key => $label ) {
        $new[ $key ] = $label;
        if ( 'name' === $key ) {
            $new['wcbn_product_id'] = __( 'Product ID', 'wc-buy-now-button' );
        }
    }
    return $new;
}
add_filter( 'manage_product_posts_columns', 'wcbn_add_product_id_column' );

function wcbn_render_product_id_column( $column, $post_id ) {
    if ( 'wcbn_product_id' !== $column ) {
        return;
    }
    // No inline onclick — JS handles copy via data attribute (WP.org compliant).
    echo '<code class="wcbn-copy-id" '
        . 'style="background:#f0f0f0;padding:2px 6px;border-radius:3px;font-size:13px;cursor:pointer;" '
        . 'title="' . esc_attr__( 'Click to copy ID', 'wc-buy-now-button' ) . '" '
        . 'data-id="' . esc_attr( $post_id ) . '">'
        . esc_html( $post_id )
        . '</code>';
}
add_action( 'manage_product_posts_custom_column', 'wcbn_render_product_id_column', 10, 2 );
