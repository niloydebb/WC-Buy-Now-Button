# WC Buy Now Button

**Version:** 1.0.1  
**Requires WordPress:** 5.0+  
**Requires WooCommerce:** 4.0+  
**Requires PHP:** 5.6+  
**License:** GPL-2.0+

---

## Description

WC Buy Now Button adds a fully customisable **"Buy Now"** button to your WooCommerce store. When clicked, the button adds the product to the cart and immediately redirects the customer to either the checkout or cart page — reducing friction and increasing conversions.

### Features

- ✅ Works on **single product pages** and **archive/shop pages**
- ✅ **Shortcode** `[buy_now_button id="X"]` — use it anywhere
- ✅ **Elementor Widget** — full drag-and-drop support with live preview
- ✅ Fully customisable styles (text, colours, padding, radius, hover, font)
- ✅ **Variable product** support (reads selected variation on single pages)
- ✅ Does **not** interfere with the default Add to Cart button
- ✅ Nonce-verified AJAX — secure by design
- ✅ Translation-ready (`.pot` file included)
- ✅ HPOS (High-Performance Order Storage) compatible
- ✅ PHP 5.6 → 8.3 compatible

---

## Installation

### Method A — Manual upload (ZIP)

1. Download or clone this repository.
2. Compress the `wc-buy-now-button` folder into a `.zip` file.
3. Go to **WordPress Admin → Plugins → Add New → Upload Plugin**.
4. Choose the `.zip` file and click **Install Now**.
5. Click **Activate**.

### Method B — FTP / cPanel

1. Upload the `wc-buy-now-button` folder to `/wp-content/plugins/`.
2. Go to **WordPress Admin → Plugins** and click **Activate** next to *WC Buy Now Button*.

---

## Configuration

Navigate to **WooCommerce → Buy Now Button** in your WordPress admin.

### General Settings

| Setting | Description |
|---|---|
| Button Text | Label displayed on the button (default: *Buy Now*) |
| After Add to Cart | Redirect to **Checkout** or **Cart** |
| Default Quantity | Items added per click (default: 1) |
| Show on Shop/Archive | Auto-display on product listing pages |
| Show on Single Product | Auto-display below Add to Cart on product pages |

### Style Settings

| Setting | Description |
|---|---|
| Text Color | Button label colour |
| Background Color | Normal state background |
| Hover Background Color | Background when hovered |
| Border Color / Width | Outline styling |
| Border Radius (px) | Rounded corners |
| Padding V / H (px) | Inner spacing |
| Font Size (px) | Label font size |
| Font Weight | 400 / 600 / 700 / 800 |

---

## Shortcode

### Basic usage

```
[buy_now_button id="42"]
```

Renders a Buy Now button for product ID **42**.

### All parameters

```
[buy_now_button
    id="42"
    text="Order Now"
    quantity="2"
    redirect="checkout"
    class="my-custom-class"
    bg_color="#e44"
    text_color="#fff"
    hover_color="#c22"
    border_radius="8"
    font_size="18"
]
```

| Parameter | Default | Description |
|---|---|---|
| `id` | *(required)* | WooCommerce product ID |
| `text` | Plugin setting | Button label |
| `quantity` | Plugin setting | Quantity to add |
| `redirect` | Plugin setting | `checkout` or `cart` |
| `class` | *(empty)* | Additional CSS classes |
| `bg_color` | Plugin setting | Background hex colour |
| `text_color` | Plugin setting | Text hex colour |
| `hover_color` | Plugin setting | Hover background hex colour |
| `border_radius` | Plugin setting | Border radius in px |
| `font_size` | Plugin setting | Font size in px |

---

## Elementor Widget

1. Open any page in **Elementor**.
2. Search for **"Buy Now Button"** in the widget panel.
3. Drag it onto the canvas.
4. In the **Content** tab:
   - Enter the **Product ID** (leave blank on product template pages).
   - Set the **Button Text**, **Quantity**, and redirect destination.
5. In the **Style** tab:
   - Customise typography, colours, padding, border, and shadow.
   - Use the **Normal / Hover** tabs to set state-specific colours.
6. In the **Advanced** tab:
   - Add custom CSS, motion effects, etc. (standard Elementor features).

### Using in WooCommerce Product Templates

When using Elementor's **Single Product** template or **WooCommerce Builder**, leave the Product ID field **empty** — the widget will automatically use the current product being displayed.

---

## Hooks & Filters

### Actions

```php
// Fires before the product is added to the cart.
do_action( 'wcbn_before_add_to_cart', $product_id, $quantity, $variation_id );

// Fires after successful add-to-cart.
do_action( 'wcbn_after_add_to_cart', $product_id, $quantity, $variation_id, $cart_item_key );
```

### Filters

```php
// Change the redirect URL after add-to-cart.
add_filter( 'wcbn_redirect_url', function( $url, $product_id, $redirect_to ) {
    // Return custom URL.
    return $url;
}, 10, 3 );

// Force-load front-end assets on a custom page.
add_filter( 'wcbn_load_assets', '__return_true' );
```

---

## Template / Theme Integration

If your theme uses a custom template and you want to add the button programmatically:

```php
// In any theme template where $product is available.
if ( function_exists( 'WCBN_Renderer' ) ) {
    // phpcs:ignore
    echo WCBN_Renderer::button( $product, array(
        'text'     => 'Buy Now',
        'redirect' => 'checkout',
        'quantity' => 1,
    ) );
}
```

---

## Variable Products

On single product pages, the plugin automatically reads the variation selected by the shopper (via WooCommerce's standard hidden `input[name="variation_id"]` field). No extra configuration needed.

On archive pages, variable products are **skipped** by default to avoid ambiguity. Use the shortcode or Elementor widget and set a specific `variation_id` to show a button for a particular variation.

---

## Security

- All AJAX requests are protected by **WordPress nonces** (`wp_create_nonce` / `wp_verify_nonce`).
- Every user-facing value is **sanitized** on input and **escaped** on output.
- Admin settings use the **Settings API** with a dedicated sanitize callback.
- Capability check (`manage_woocommerce`) guards the settings page.

---

## Frequently Asked Questions

**Does it clear the cart before redirecting?**  
No. By default, the product is added to whatever items are already in the cart. This is intentional so it behaves like the standard Add to Cart. You can hook into `wcbn_before_add_to_cart` to empty the cart first if needed.

**Does it work with WPML / Polylang?**  
Yes. The plugin is translation-ready and uses `__()` / `esc_html_e()` throughout.

**Does it work with subscription products?**  
Yes, as long as the product is purchasable (`is_purchasable()` returns `true`).

**Will it conflict with my theme?**  
The button uses the WooCommerce `.button` class so it inherits your theme's button styles as a base, with plugin-defined styles applied on top. No theme files are overridden.

---

## Changelog

### 1.0.0 (Initial Release)
- Buy Now button on single and archive product pages.
- Shortcode `[buy_now_button]` with full parameter support.
- Elementor widget with live preview and full style controls.
- Plugin settings page under WooCommerce menu.
- Variable product support.
- Nonce-secured AJAX handler.
- HPOS compatibility.
- Translation-ready.

---

## License

WC Buy Now Button is released under the [GNU General Public License v2.0](https://www.gnu.org/licenses/gpl-2.0.html).
