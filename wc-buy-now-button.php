<?php
/**
 * Plugin Name:       WC Buy Now Button
 * Plugin URI:        https://github.com/yourname/wc-buy-now-button
 * Description:       Adds a customizable "Buy Now" button to WooCommerce products that adds to cart and redirects to checkout instantly. Supports shortcodes and Elementor widget.
 * Version:           1.0.1
 * Requires at least: 5.0
 * Requires PHP:      5.6
 * Author:            Niloy Deb
 * Author URI:        https://niloydeb.com
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wc-buy-now-button
 * Domain Path:       /languages
 * WC requires at least: 4.0
 * WC tested up to:   9.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants.
define( 'WCBN_VERSION',    '1.0.1' );
define( 'WCBN_PLUGIN_FILE', __FILE__ );
define( 'WCBN_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'WCBN_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'WCBN_PLUGIN_BASE', plugin_basename( __FILE__ ) );

/**
 * Load all plugin classes.
 * Called both during normal init AND from the activation hook.
 */
function wcbn_load_classes() {
    require_once WCBN_PLUGIN_DIR . 'includes/class-wcbn-settings.php';
    require_once WCBN_PLUGIN_DIR . 'includes/class-wcbn-renderer.php';
    require_once WCBN_PLUGIN_DIR . 'includes/class-wcbn-ajax.php';
    require_once WCBN_PLUGIN_DIR . 'includes/class-wcbn-shortcode.php';
    require_once WCBN_PLUGIN_DIR . 'includes/class-wcbn-hooks.php';
    require_once WCBN_PLUGIN_DIR . 'includes/class-wcbn-assets.php';
}

/**
 * Display admin notice when WooCommerce is missing.
 */
function wcbn_missing_woocommerce_notice() {
    echo '<div class="notice notice-error"><p>';
    echo wp_kses_post(
        sprintf(
            /* translators: %s: WooCommerce plugin link */
            __( '<strong>WC Buy Now Button</strong> requires %s to be installed and active.', 'wc-buy-now-button' ),
            '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>'
        )
    );
    echo '</p></div>';
}

/**
 * Main plugin bootstrap — fires at plugins_loaded (priority 10).
 * WooCommerce loads at plugins_loaded priority 0, so it is always ready here.
 */
function wcbn_init() {
    // Load translations first.
    load_plugin_textdomain(
        'wc-buy-now-button',
        false,
        dirname( WCBN_PLUGIN_BASE ) . '/languages'
    );

    // Bail if WooCommerce is not active.
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', 'wcbn_missing_woocommerce_notice' );
        return;
    }

    // Load classes and boot each component.
    wcbn_load_classes();

    WCBN_Settings::instance();
    WCBN_Ajax::instance();
    WCBN_Shortcode::instance();
    WCBN_Hooks::instance();
    WCBN_Assets::instance();

    // Register Elementor widget only when Elementor is active.
    add_action( 'elementor/widgets/register', 'wcbn_register_elementor_widget' );
}
add_action( 'plugins_loaded', 'wcbn_init', 10 );

/**
 * Register the Elementor widget.
 *
 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widget manager.
 */
function wcbn_register_elementor_widget( $widgets_manager ) {
    require_once WCBN_PLUGIN_DIR . 'elementor/class-wcbn-elementor-widget.php';
    $widgets_manager->register( new WCBN_Elementor_Widget() );
}

/**
 * Declare HPOS (High-Performance Order Storage) compatibility.
 * Uses a named function instead of a closure for PHP 5.6 safety
 * (closures work in 5.3+ but named functions are cleaner for hooks).
 */
function wcbn_declare_hpos_compatibility() {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            WCBN_PLUGIN_FILE,
            true
        );
    }
}
add_action( 'before_woocommerce_init', 'wcbn_declare_hpos_compatibility' );

/**
 * Plugin activation hook.
 *
 * The activation hook fires BEFORE plugins_loaded, so we must load
 * our classes manually here — they are not yet loaded by wcbn_init().
 */
function wcbn_activate() {
    // Manually load the settings class so we can call ::defaults().
    if ( ! class_exists( 'WCBN_Settings' ) ) {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wcbn-settings.php';
    }

    // Write defaults only on the very first activation.
    if ( false === get_option( 'wcbn_settings' ) ) {
        update_option( 'wcbn_settings', WCBN_Settings::defaults() );
    }
}
register_activation_hook( __FILE__, 'wcbn_activate' );

/**
 * Plugin deactivation hook.
 */
function wcbn_deactivate() {
    // Nothing to clean up currently.
}
register_deactivation_hook( __FILE__, 'wcbn_deactivate' );
