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
		add_action( 'init', array( $this, 'handle_cron_status' ) );

		add_filter( 'cron_schedules', array( $this, 'add_monthly_cron_schedule' ) );
		add_action( 'wc_monthly_sales_simulate_event', array( $this, 'generate_monthly_random_sales' ) );

		add_action( 'admin_menu', array( $this, 'simulator_woocommerce_add_admin_page' ) );
		add_action( 'admin_init', array( $this, 'simulator_woocommerce_register_settings' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'simulator_enqueue_admin_scripts' ) );

		add_action( 'wp_ajax_install_demo_customers', array( $this, 'simulator_install_demo_customers' ) );
		add_action( 'wp_ajax_install_sample_products', array( $this, 'simulator_install_sample_products' ) );
	}
	/**
	 * Checks the status of the cron job and schedules/unschedules it as needed.
	 *
	 * If the cron job is enabled (option simulator_enable_cron is set to 1)
	 * and there is no scheduled event (wp_next_scheduled returns false), then
	 * it schedules the event for the current time and sets the cron hook to
	 * the monthly schedule.
	 *
	 * If the cron job is disabled (option simulator_enable_cron is set to 0),
	 * it unschedules the event by calling wp_clear_scheduled_hook.
	 */
	public function handle_cron_status() {
		$is_enabled = get_option( 'simulator_enable_cron' );

		if ( $is_enabled && ! wp_next_scheduled( 'wc_monthly_sales_simulate_event' ) ) {
			wp_schedule_event( time(), 'monthly', 'wc_monthly_sales_simulate_event' );
		} elseif ( ! $is_enabled ) {
			wp_clear_scheduled_hook( 'wc_monthly_sales_simulate_event' );
		}
	}
	/**
	 * Handles the AJAX request to install demo customers.
	 *
	 * This function checks the AJAX request for a valid nonce, processes the
	 * logic to install demo customers, and returns a JSON success response
	 * once the installation is successful.
	 *
	 * @since 1.0.0
	 */
	public function simulator_install_demo_customers() {
		check_ajax_referer( 'simulator_nonce' );

		$names = array(
			array( 'first_name' => 'John', 'last_name' => 'Doe' ),
			array( 'first_name' => 'Jane', 'last_name' => 'Smith' ),
			array( 'first_name' => 'Michael', 'last_name' => 'Johnson' ),
			array( 'first_name' => 'Emily', 'last_name' => 'Davis' ),
			array( 'first_name' => 'Chris', 'last_name' => 'Brown' ),
			array( 'first_name' => 'Anna', 'last_name' => 'Taylor' ),
			array( 'first_name' => 'David', 'last_name' => 'Wilson' ),
			array( 'first_name' => 'Laura', 'last_name' => 'Martinez' ),
			array( 'first_name' => 'Kevin', 'last_name' => 'Anderson' ),
			array( 'first_name' => 'Rachel', 'last_name' => 'Lee' ),
		);

		foreach ( $names as $index => $name ) {
			$user_data = array(
				'user_login' => strtolower( $name['first_name'] . '_' . $name['last_name'] ),
				'user_pass'  => wp_generate_password( 8 ),
				'user_email' => strtolower( $name['first_name'] . '_' . $name['last_name'] ) . '@example.com',
				'first_name' => $name['first_name'],
				'last_name'  => $name['last_name'],
				'role'       => 'customer',
			);

			// Insert user and check for errors.
			$user_id = wp_insert_user( $user_data );

			if ( is_wp_error( $user_id ) ) {
				wp_send_json_error( 'Error creating customer: ' . $user_id->get_error_message() );
				return;
			}
		}
		wp_send_json_success( 'Demo customers installed successfully!' );
	}

	/**
	 * Handles the AJAX request to install sample products.
	 *
	 * This function checks the AJAX request for a valid nonce, reads the
	 * sample.xls file to retrieve product information, and installs the
	 * products. It returns a JSON success response once the installation
	 * is successful.
	 *
	 * @since 1.0.0
	 */
	public function simulator_install_sample_products() {
		check_ajax_referer( 'simulator_nonce' );

		// Logic to read sample.xls and install products
		wp_send_json_success( 'Sample products installed successfully!' );
	}
	/**
	 * Enqueues a JavaScript script for handling AJAX calls to the
	 * WooCommerce Simulator plugin.
	 *
	 * The script is enqueued with the name 'simulator-ajax' and is
	 * dependent on jQuery. The script is localized with an object
	 * named 'simulator_ajax_obj' that contains the AJAX url and a
	 * nonce value.
	 */
	public function simulator_enqueue_admin_scripts() {
		wp_enqueue_script( 'simulator-ajax', plugins_url( '/js/simulator-ajax.js', __FILE__ ), array( 'jquery' ), null, true );
		wp_localize_script(
			'simulator-ajax',
			'simulator_ajax_obj',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'simulator_nonce' ),
			)
		);
	}
	/**
	 * Adds a WooCommerce Simulator settings page to the WordPress admin menu.
	 *
	 * This method creates a new admin menu page titled 'WooCommerce Simulator Settings'
	 * under the 'Settings' menu in the WordPress dashboard. The page is accessible to users
	 * with 'manage_options' capability. The page is represented by the 'dashicons-admin-settings'
	 * icon and is positioned at menu order 56. The 'simulator_woocommerce_settings_page' function
	 * is called to render the content of the page.
	 */
	public function simulator_woocommerce_add_admin_page() {
		add_menu_page(
			'WooCommerce Simulator Settings',
			'WC Simulator Settings',
			'manage_options',
			'wc-simulator-settings',
			array( $this, 'simulator_woocommerce_settings_page' ),
			'dashicons-admin-settings',
			56
		);
	}
	/**
	 * Displays the WooCommerce Simulator settings page in the WordPress admin.
	 *
	 * This method outputs a form for configuring the simulator settings,
	 * including security fields and setting sections. The form allows
	 * users to submit changes to the WooCommerce Simulator plugin settings.
	 */
	public function simulator_woocommerce_settings_page() {
		?>
		<div class="wrap">
			<h1>WooCommerce Simulator Settings</h1>
			<form method="post" action="options.php">
				<?php
				// Security fields for the settings page.
				settings_fields( 'simulator_woocommerce_settings_group' );
				// Output setting sections and their fields.
				do_settings_sections( 'wc-simulator-settings' );
				// Submit button.
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
	/**
	 * Registers the settings for the WooCommerce Simulator plugin.
	 *
	 * This method registers settings, settings sections, and settings fields
	 * for the WooCommerce Simulator plugin. It registers the cron job toggle
	 * setting, demo installation options, and sample products options.
	 *
	 * @since 1.0.0
	 */
	public function simulator_woocommerce_register_settings() {
		// Register the cron job toggle setting.
		register_setting( 'simulator_woocommerce_settings_group', 'simulator_enable_cron' );
		add_settings_section( 'simulator_main_settings', 'Main Settings', null, 'wc-simulator-settings' );

		add_settings_field(
			'simulator_enable_cron',
			'Enable Cron Job',
			array( $this, 'simulator_enable_cron_callback' ),
			'wc-simulator-settings',
			'simulator_main_settings'
		);

		// Register the demo installation options.
		add_settings_field(
			'install_demo_customers',
			'Install Demo Customers',
			array( $this, 'simulator_install_demo_customers_callback' ),
			'wc-simulator-settings',
			'simulator_main_settings'
		);

		add_settings_field(
			'install_sample_products',
			'Install Sample Products',
			array( $this, 'simulator_install_sample_products_callback' ),
			'wc-simulator-settings',
			'simulator_main_settings'
		);
	}
	/**
	 * Displays a checkbox for toggling the cron job for generating random sales.
	 *
	 * This method retrieves the current value of the simulator_enable_cron setting
	 * and outputs a checkbox with the name "simulator_enable_cron" and value "1".
	 * The checkbox is checked if the setting is enabled (value is 1).
	 *
	 * @since 1.0.0
	 */
	public function simulator_enable_cron_callback() {
		$option = get_option( 'simulator_enable_cron' );
		?>
		<input type="checkbox" name="simulator_enable_cron" value="1" <?php checked( 1, $option, true ); ?> />
		Enable Cron Job
		<?php
	}

	/**
	 * Displays a button to install demo customers.
	 *
	 * Outputs a button that, when clicked, triggers a JavaScript function
	 * to initiate an AJAX call for installing demo customers. An alert
	 * notifies the user that the demo customer installation has been triggered.
	 *
	 * @since 1.0.0
	 */
	public function simulator_install_demo_customers_callback() {
		?>
		<button type="button" onclick="installDemoCustomers()">Install Demo Customers</button>
		<script>
			function installDemoCustomers() {
				// AJAX call to handle demo customer installation
				alert('Demo customers installation triggered!');
			}
		</script>
		<?php
	}

	/**
	 * Displays a button to install sample products.
	 *
	 * Outputs a button that, when clicked, triggers a JavaScript function
	 * to initiate an AJAX call for installing sample products. An alert
	 * notifies the user that the sample product installation has been triggered.
	 *
	 * @since 1.0.0
	 */
	public function simulator_install_sample_products_callback() {
		?>
		<button type="button" onclick="installSampleProducts()">Install Sample Products</button>
		<script>
			function installSampleProducts() {
				// AJAX call to handle sample product installation
				alert('Sample products installation triggered!');
			}
		</script>
		<?php
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
				'orderby' => 'rand',
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
