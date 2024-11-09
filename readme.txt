=== WooCommerce Random Sales Simulator ===
Contributors: mywptrek
Tags: WooCommerce, sales, orders, cron
Requires at least: 5.0
Tested up to: 6.3
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generates 10-15 random WooCommerce orders monthly with order dates distributed within the past month, simulating real sales activity.

== Description ==

This plugin creates random WooCommerce sales orders on a monthly basis for testing purposes. Each run generates 10-15 completed orders with dates spread randomly across the past 30 days. Great for testing sales reports, inventory management, and performance metrics.

= Features =
* Generates 10-15 random WooCommerce orders each month.
* Order dates are randomly spread over the past 30 days.
* Orders are completed to simulate real sales.
* Selects a random WooCommerce product and assigns it to each order.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/woocommerce-random-sales-simulator` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. The plugin will schedule an event to create random WooCommerce sales monthly.

== Frequently Asked Questions ==

= Will this plugin work if I don’t have WooCommerce installed? =
No, this plugin requires WooCommerce to be installed and active.

= Can I manually trigger the sales generation? =
Yes, you can manually trigger the sales generation by calling `do_action('wc_monthly_sales_simulate_event');` in your theme or WP CLI.

= What happens if I deactivate the plugin? =
Deactivating the plugin will remove the scheduled cron event, so no new orders will be generated.

== Changelog ==

= 1.0 =
* Initial release.

== Upgrade Notice ==
* Ensure WooCommerce is active for this plugin to function properly.
