=== WC Buy Now Button ===
Contributors: niloydeb
Tags: woocommerce, buy now, button, checkout, cart
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 1.0.1
Requires PHP: 5.6
WC requires at least: 4.0
WC tested up to: 9.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a customizable "Buy Now" button to WooCommerce products. One click adds to cart and redirects to checkout instantly.

== Description ==

**WC Buy Now Button** adds a fully customizable "Buy Now" button to your WooCommerce store. When clicked, the button adds the product to the cart and immediately redirects the customer to checkout — reducing friction and increasing conversions.

No configuration needed. Just activate the plugin and the button appears automatically on your product pages.

= Key Features =

* **Zero setup** — works automatically on all product pages
* **Shortcode** — place `[buy_now_button]` anywhere, no product ID needed
* **Elementor Widget** — full drag-and-drop with live preview
* **Fully customizable** — text, colors, padding, border, radius, font, hover effects
* **Separate settings page** under WooCommerce menu
* **Variable product support** — reads selected variation automatically
* **Does not interfere** with the default Add to Cart button
* **AJAX powered** — fast, no page reload on save
* **HPOS compatible** — works with WooCommerce High-Performance Order Storage
* **Translation ready** — fully internationalized

= Shortcode Usage =

Simply place this shortcode on any product page:

`[buy_now_button]`

The plugin automatically detects the product. No ID required.

Optional parameters:

`[buy_now_button text="Order Now" redirect="cart" quantity="2"]`

Available parameters:

* `text` — Button label (default: "Buy Now")
* `quantity` — Quantity to add (default: 1)
* `redirect` — `checkout` or `cart` (default: checkout)
* `class` — Extra CSS classes
* `bg_color` — Background color override
* `text_color` — Text color override

= Elementor Widget =

1. Open any page in Elementor
2. Search for **"Buy Now Button"** in the widget panel
3. Drag it onto your page
4. Customize colors, typography, padding, and more from the Style tab

= Developer Hooks =

**Actions:**

`do_action( 'wcbn_before_add_to_cart', $product_id, $quantity, $variation_id );`
`do_action( 'wcbn_after_add_to_cart', $product_id, $quantity, $variation_id, $cart_item_key );`

**Filters:**

`add_filter( 'wcbn_redirect_url', function( $url, $product_id, $redirect_to ) { return $url; }, 10, 3 );`
`add_filter( 'wcbn_load_assets', '__return_true' ); // Force load assets on custom pages`

== Installation ==

1. Upload the `wc-buy-now-button` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Ensure WooCommerce is installed and active
4. The Buy Now button will appear automatically on product pages
5. Go to **WooCommerce → Buy Now Button** to customize styles

== Frequently Asked Questions ==

= Do I need to add a product ID to the shortcode? =

No. Just use `[buy_now_button]` on any product page and the plugin detects the product automatically — the same way WooCommerce's own Add to Cart button works.

= Does it work with variable products? =

Yes. On single product pages, it reads the variation the customer selected. On archive pages, only simple products show the button (since no variation UI is available).

= Will it conflict with my theme? =

No. The button uses WooCommerce's standard `.button` class as a base and does not override any theme files or WooCommerce templates.

= Does it clear the cart before redirecting? =

No — the product is added to whatever is already in the cart. If you want to clear the cart first, use the `wcbn_before_add_to_cart` action hook.

= Is it compatible with WPML and Polylang? =

Yes. The plugin is fully translation-ready with a `.pot` file included.

= Why is the settings save using AJAX instead of the standard WordPress Settings API? =

On servers running many plugins simultaneously, the standard `options.php` save can trigger a 503 error due to server memory limits. Our lightweight AJAX save bypasses this entirely — the save request is isolated and uses a fraction of the resources.

== Screenshots ==

1. Buy Now button on a single product page
2. Buy Now button on the shop/archive page
3. Plugin settings page under WooCommerce menu
4. Elementor widget with full style controls
5. Elementor widget Style tab — colors, typography, padding

== Changelog ==

= 1.0.1 =
* Fixed: Button text showing raw UTF-8 escape sequences during loading state
* Fixed: Settings save causing 503 error on busy servers — replaced with lightweight AJAX save
* Fixed: Elementor widget controls (color, padding, radius) not applying due to inline style conflicts
* Fixed: Elementor hover color not working
* Fixed: Assets not loading when shortcode is inside Elementor widgets or reusable blocks
* Fixed: inline onclick removed from admin product list (WordPress.org compliance)
* Improved: Button now auto-detects product on all page types without needing a product ID
* Added: Author updated to Niloy Deb

= 1.0.0 =
* Initial release
* Buy Now button on single product and archive pages
* Shortcode `[buy_now_button]` support
* Elementor widget with live preview
* Plugin settings page under WooCommerce menu
* Variable product support
* Nonce-secured AJAX handler
* HPOS compatibility
* Translation ready

== Upgrade Notice ==

= 1.0.1 =
Important fixes for Elementor widget styling, settings save on busy servers, and WordPress.org compliance improvements. Upgrade recommended for all users.

