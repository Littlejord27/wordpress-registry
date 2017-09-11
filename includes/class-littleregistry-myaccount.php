<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('Registry_My_Account_Endpoint') ) {
	class Registry_My_Account_Endpoint {

		/**
		 * Custom endpoint name.
		 *
		 * @var string
		 */
		public static $endpoint = 'registry';

		/**
		 * Title of endpoint to display.
		 *
		 * @var string
		 */
		public static $title = 'Registry';

		/**
		 * Key for registry meta data.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */
		public static $key = 'registry_id';

		/**
		 * Plugin actions.
		 */
		public function __construct() {
			// Actions used to insert a new endpoint in the WordPress.
			add_action( 'init', array( $this, 'add_endpoints' ) );
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

			// Change the My Accout page title.
			add_filter( 'the_title', array( $this, 'endpoint_title' ) );

			// Insering your new tab/page into the My Account page.
			add_filter( 'woocommerce_account_menu_items', array( $this, 'new_menu_items' ) );
			add_action( 'woocommerce_account_' . self::$endpoint .  '_endpoint', array( $this, 'endpoint_content' ) );
		}

		/**
		 * Register new endpoint to use inside My Account page.
		 *
		 * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
		 */
		public function add_endpoints() {
			add_rewrite_rule( '^registry/?', 'index.php?map=map', 'top' );
			add_rewrite_endpoint( self::$endpoint, EP_ROOT | EP_PAGES );
		}

		/**
		 * Add new query var.
		 *
		 * @param array $vars
		 * @return array
		 */
		public function add_query_vars( $vars ) {
			$vars[] = self::$endpoint;

			return $vars;
		}

		/**
		 * Set endpoint title.
		 *
		 * @param string $title
		 * @return string
		 */
		public function endpoint_title( $title ) {
			global $wp_query;

			$is_endpoint = isset( $wp_query->query_vars[ self::$endpoint ] );

			if ( $is_endpoint && ! is_admin() && is_main_query() && in_the_loop() && is_account_page() ) {
				// New page title.
				$title = __( self::$title, 'woocommerce' );

				remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
			}

			return $title;
		}

		/**
		 * Insert the new endpoint into the My Account menu.
		 *
		 * @param array $items
		 * @return array
		 */
		public function new_menu_items( $items ) {
			// Remove the logout menu item.
			$logout = $items['customer-logout'];
			unset( $items['customer-logout'] );

			// Insert your custom endpoint.
			$items[ self::$endpoint ] = __( self::$title, 'woocommerce' );

			// Insert back the logout item.
			$items['customer-logout'] = $logout;

			return $items;
		}

		/**
		 * Endpoint HTML content.
		 */
		public function endpoint_content() {
			$current_user = wp_get_current_user();
		    $user_id = intval($current_user->ID);
		    $registry_id = get_user_meta($user_id, self::$key, true);

		    $registry = get_registry($user_id);
		    $registry_rows = get_registry_rows($registry_id);

			?>

				<h2><?php echo $registry->name;?></h2>
				<p>
					<?php 
						$unformatted_date = $registry->wedding_date;
						$formatted_date = date("l, F d, Y", strtotime($unformatted_date));
						echo $formatted_date;
					?>
				</p>


				<table style="width:100%">
				  <tr>
				  	<th>Product</th>
				    <th>Name</th>
				    <th>Price</th> 
				    <th>Style</th>
				    <th>Quantity</th>
				    <th></th>
				  </tr>

				  <?php 
				  	foreach ($registry_rows as $key => $value) {
				  		$variation_id = $value->variation_id;
				    	$variation_product = wc_get_product($variation_id);
				    	$variation_attributes = $variation_product->get_variation_attributes();
				    	$variation_string = stringify_variance($variation_attributes);
						echo '<tr>';
						echo '<td class="registry-img"><img src="'.get_the_post_thumbnail_url($variation_id).'" width="100" height="100"></td>';
						echo '<td class="registry-name">'.$variation_product->get_title().'</td>';
						echo '<td class="registry-price">'.$variation_product->get_price_html().'</td>';
						echo '<td class="registry-attr">'.$variation_string.'</td>';
						echo '<td class="registry-quantity">'.$value->quantity.'</td>';
						echo '<td class="registry-remove"><i class="fa fa-trash-o fa-2x" aria-hidden="true"></i></td>';
						echo '</tr>';
				  ?>
				  <?php
				   }
				   ?>
				</table>




		<?php

		}



		/**
		 * Plugin install action.
		 * Flush rewrite rules to make our custom endpoint available.
		 */
		public static function install() {
			flush_rewrite_rules();
		}
	}
}

new Registry_My_Account_Endpoint();

// Flush rewrite rules on plugin activation.
register_activation_hook( __FILE__, array( 'Registry_My_Account_Endpoint', 'install' ) );