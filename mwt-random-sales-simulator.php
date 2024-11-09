<?php
/**
 * Plugin Name: WooCommerce Random Sales Simulator
 * Description: Generates 10-15 random WooCommerce orders monthly with order dates distributed within the past month.
 * Version: 1.0.0
 * Author: mywptrek
 *
 * Requires: woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Plugin main class
 */
class WC_Random_Sales_Simulator {

	/**
	 * Constructor to initialize the plugin.
	 *
	 * Registers activation and deactivation hooks to manage scheduled events.
	 * Adds a custom cron schedule for monthly sales simulation.
	 * Sets up an action to trigger the generation of random sales.
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		add_filter( 'cron_schedules', array( $this, 'add_monthly_cron_schedule' ) );
		add_action( 'wc_monthly_sales_simulate_event', array( $this, 'generate_monthly_random_sales' ) );
	}

	/**
	 * Activates the plugin by scheduling a monthly event for sales simulation.
	 *
	 * This function checks if the 'wc_monthly_sales_simulate_event' is already scheduled.
	 * If not, it schedules the event to occur monthly.
	 */
	public function activate() {
		if ( ! wp_next_scheduled( 'wc_monthly_sales_simulate_event' ) ) {
			wp_schedule_event( time(), 'monthly', 'wc_monthly_sales_simulate_event' );
		}
	}

	/**
	 * Deactivates the plugin by removing the scheduled event for sales simulation.
	 *
	 * This function clears the scheduled event for 'wc_monthly_sales_simulate_event'.
	 */
	public function deactivate() {
		wp_clear_scheduled_hook( 'wc_monthly_sales_simulate_event' );
	}

	/**
	 * Adds a custom cron schedule for monthly events.
	 *
	 * This function is used as a filter on the 'cron_schedules' hook.
	 * It checks if the 'monthly' schedule is already registered.
	 * If not, it adds a new entry to the list of available schedules.
	 * The 'monthly' schedule has an interval of 30 days and a display name of 'Once a Month'.
	 *
	 * @param array $schedules The list of available cron schedules.
	 * @return array The updated list of available cron schedules.
	 */
	public function add_monthly_cron_schedule( $schedules ) {
		if ( ! isset( $schedules['monthly'] ) ) {
			$schedules['monthly'] = array(
				'interval' => 30 * DAY_IN_SECONDS,
				'display'  => __( 'Once a Month' ),
			);
		}
		return $schedules;
	}

	/**
	 * Generates a random number of WooCommerce sales orders for the month.
	 *
	 * This method simulates between 10 and 15 completed sales orders with
	 * random order dates distributed over the past 30 days. It iterates
	 * over the randomly determined number of sales and creates each order
	 * by invoking the create_random_order_with_date() method.
	 */
	public function generate_monthly_random_sales() {
		$sales_to_generate = wp_rand( 10, 15 ); // Randomly choose between 10 and 15 orders.

		for ( $i = 0; $i < $sales_to_generate; $i++ ) {
			$this->create_random_order_with_date();
		}
	}

	/**
	 * Creates a single random order with a random product, customer, and date in the past 30 days.
	 *
	 * This method first retrieves a list of published product IDs. If the list is empty, it returns immediately.
	 * It then randomly selects a product ID from the list and creates a new order, adding the product to the order.
	 * It sets the customer ID to a randomly selected customer, calculates the order totals, and sets the status to 'completed'.
	 * Finally, it sets the date created to a random timestamp in the past 30 days and saves the order.
	 */
	private function create_random_order_with_date() {
		$product_ids = $this->get_product_ids();
		if ( empty( $product_ids ) ) {
			return;
		}

		$product_id = $product_ids[ array_rand( $product_ids ) ];

		$order = wc_create_order();
		$order->add_product( wc_get_product( $product_id ), 1 );
		$order->set_customer_id( $this->get_random_customer_id() );
		$order->calculate_totals();
		$order->update_status( 'completed', 'Random sale generated for testing' );

		$random_timestamp = strtotime( '-' . wp_rand( 1, 30 ) . ' days' );
		$order->set_date_created( gmdate( 'Y-m-d H:i:s', $random_timestamp ) );
		$order->save();
	}

	/**
	 * Retrieves a list of published product IDs.
	 *
	 * @return array List of IDs of published products.
	 */
	private function get_product_ids() {
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'posts_per_page' => -1,
		);
		return get_posts( $args );
	}

	/**
	 * Retrieves a random customer ID.
	 *
	 * This method uses a WP_User_Query to search for customers, ordering by 'rand' to
	 * retrieve a random user. If no customers are found, it returns 0, representing a
	 * guest checkout.
	 *
	 * @return int The ID of a random customer, or 0 if no customers are found.
	 */
	private function get_random_customer_id() {
		$user_query = new WP_User_Query(
			array(
				'role'    => 'customer',
				'number'  => 1,
				'orderby' => 'rand'
			)
		);

		$users = $user_query->get_results();

		if ( ! empty( $users ) ) {
			return $users[0]->ID;
		}

		return 0; // Guest checkout if no customers found.
	}
}

new WC_Random_Sales_Simulator();
