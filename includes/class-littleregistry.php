<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('LittleRegistry') ) {
	class LittleRegistry {

		/**
		 * The single instance of LittleRegistry.
		 * @var 	object
		 * @access  private
		 * @since 	1.0.0
		 */
		private static $_instance = null;

		/**
		 * Key for registry meta data.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */
		public static $key = 'registry_id';

		/**
		 * Settings class object
		 * @var     object
		 * @access  public
		 * @since   1.0.0
		 */
		public $settings = null;

		/**
		 * The version number.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */
		public $_version;

		/**
		 * The token.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */
		public $_token;

		/**
		 * The main plugin file.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */
		public $file;

		/**
		 * The main plugin directory.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */
		public $dir;

		/**
		 * The plugin assets directory.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */
		public $assets_dir;

		/**
		 * The plugin assets URL.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */
		public $assets_url;

		/**
		 * Suffix for Javascripts.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */
		public $script_suffix;

		/**
		 * Suffix for Javascripts.
		 * @var     string
		 * @access  public
		 * @since   1.0.0
		 */
		public $post_type;

		/**
		 * Constructor function.
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function __construct ( $file = '', $version = '1.0.0' ) {
			$this->_version = $version;
			$this->_token = 'littleregistry';

			// Load plugin environment variables
			$this->file = $file;
			$this->dir = dirname( $this->file );
			$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
			$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

			$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			// Register new post type
			$this->post_type = $this->register_post_type('registry_post','Registries','Registry', 'A registry of gifts for a couple.');
		
			register_activation_hook( $this->file, array( $this, 'install' ) );

			// Load frontend JS & CSS
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

			// Load admin JS & CSS
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

			// Load API for generic admin functions
			if ( is_admin() ) {
				$this->admin = new LittleRegistry_Admin_API();
			}

			// Handle localisation
			$this->load_plugin_textdomain();
			add_action( 'init', array( $this, 'load_localisation' ), 0 );

			// Add Registry Link
			add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'add_registry_button_after_add_to_cart_button' ), 10, 1 );

			add_action( 'init', array( $this, 'create_registry_page' ), 10 ); 

		} // End __construct ()

		public function add_registry_button_after_add_to_cart_button(  ) {
		    if(testEnv('Jordan')){
				if ( is_user_logged_in() ) {
					$current_user = wp_get_current_user();
				    $product = get_product();
				    $user_id = intval($current_user->ID);
					$prod_id = $product->id;
					$attr = $product->get_attributes();
					$prodAttr = $this->dechiper_attr($attr, $product);
					//delete_user_meta($user_id, self::$key);
					$registry_id = get_user_meta($user_id, self::$key, true);
				    echo '<p><a href="javascript:void(0)" class="add-to-registry">Add to Registry</a></p>';
				    echo '<script>';
				    if(!empty($registry_id)){
				    	echo 'var has_registry = true;';
				    	echo 'var registry_id = '.$registry_id.';';
				    } else {
				    	echo 'var has_registry = false;';
				    }
				    echo 'var user_id = '.$user_id.';';
				    echo 'var prod_id = '.$prod_id.';';
				    echo 'var attrJSON = '.$prodAttr.';';
				    echo 'var dateRangeMin = "'.date("Y-m-d", strtotime('tomorrow')).'";';
				    echo 'var prodImgSrc = \''.get_the_post_thumbnail_url($prod_id).'\';';
				    echo 'jQuery(".add-to-registry").on("click", createRegistryWindow);';
					echo '</script>';
				} else { }
			}
		}

		private function dechiper_attr($array, $product){
			$attrArr = [];
			foreach ($array as $key => $value) {
				if($value['is_variation'] == 1){
					$prodAttrArr = $product->get_attribute($value['name']);
					$attrArr[substr($value['name'],3)] = $prodAttrArr;
					echo '<br><br>';
				}
			}
			return json_encode($attrArr);
		}

		private function array_to_select_HTML($attr, $attrId){
			$selectHTML = '<select id="'.$attrId.'">';
			for ($i=0; $i < count($attr); $i++) {
				$selectHTML .= '<option value="'.$attr[$i].'">'.$attr[$i].'</option>';
			}
			$selectHTML .= '</select>';
			return $selectHTML;
		}

		public function testEnv($name){
	        $current_user = wp_get_current_user();
	        $user_name = $current_user->first_name;
	        $roles = $current_user->roles;
	        $role = '';
	        if(count($roles) > 0){
	            $role = $current_user->roles[0];
	        }
	        $result = false;
	        if($user_name == $name && $role == 'administrator'){
	            $result = true;
	        } else {
	            $result = false;
	        }
	        return $result;
	    }

		/**
		 * Wrapper function to register a new post type
		 * @param  string $post_type   Post type name
		 * @param  string $plural      Post type item plural name
		 * @param  string $single      Post type item single name
		 * @param  string $description Description of post type
		 * @return object              Post type class object
		 */
		public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

			if ( ! $post_type || ! $plural || ! $single ) return;

			$post_type = new LittleRegistry_Post_Type( $post_type, $plural, $single, $description, $options );

			return $post_type;
		}

		/**
		 * Wrapper function to register a new taxonomy
		 * @param  string $term   Taxonomy name
		 * @param  string $plural     Taxonomy single name
		 * @param  string $single     Taxonomy plural name
		 * @param  array  $post_types Post types to which this taxonomy applies
		 * @return object             Taxonomy class object
		 */
		public function register_taxonomy ( $term = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

			if ( ! $term || ! $plural || ! $single ) return;

			$term = new LittleRegistry_Taxonomy( $term, $plural, $single, $post_types, $taxonomy_args );

			return $term;
		}

		/**
		 * Load frontend CSS.
		 * @access  public
		 * @since   1.0.0
		 * @return void
		 */
		public function enqueue_styles () {
			wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
			wp_enqueue_style( $this->_token . '-frontend' );

			wp_register_style( $this->_token . '-fontawesome', esc_url( $this->assets_url ) . 'css/font-awesome.min.css', array(), $this->_version );
			wp_enqueue_style( $this->_token . '-fontawesome' );

			wp_register_style( $this->_token . '-bootstrap', esc_url( $this->assets_url ) . 'css/bootstrap.min.css', array(), $this->_version );
			wp_enqueue_style( $this->_token . '-bootstrap' );

			wp_register_style( $this->_token . '-bootstrap-theme', esc_url( $this->assets_url ) . 'css/bootstrap-theme.min.css', array(), $this->_version );
			wp_enqueue_style( $this->_token . '-bootstrap-theme' );
		} // End enqueue_styles ()

		/**
		 * Load frontend Javascript.
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function enqueue_scripts () {
			wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
			wp_enqueue_script( $this->_token . '-frontend' );

			wp_localize_script( $this->_token . '-frontend', 'RegistryAjax', array(
				// URL to wp-admin/admin-ajax.php to process the request
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				// generate a nonce with a unique ID "myajax-post-comment-nonce"
				// so that you can check it later when an AJAX request is sent
				'security' => wp_create_nonce( 'littleregistry-frontend-ajax-nonce' )
			));
		} // End enqueue_scripts ()

		/**
		 * Load admin CSS.
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function admin_enqueue_styles ( $hook = '' ) {
			wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
			wp_enqueue_style( $this->_token . '-admin' );
		} // End admin_enqueue_styles ()

		/**
		 * Load admin Javascript.
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function admin_enqueue_scripts ( $hook = '' ) {
			wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
			wp_enqueue_script( $this->_token . '-admin' );
		} // End admin_enqueue_scripts ()

		/**
		 * Load plugin localisation
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function load_localisation () {
			load_plugin_textdomain( 'littleregistry', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
		} // End load_localisation ()

		/**
		 * Load plugin textdomain
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function load_plugin_textdomain () {
		    $domain = 'littleregistry';

		    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
		} // End load_plugin_textdomain ()

		/**
		 * Main LittleRegistry Instance
		 *
		 * Ensures only one instance of LittleRegistry is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @see LittleRegistry()
		 * @return Main LittleRegistry instance
		 */
		public static function instance ( $file = '', $version = '1.0.0' ) {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self( $file, $version );
			}
			return self::$_instance;
		} // End instance ()

		/**
		 * Cloning is forbidden.
		 *
		 * @since 1.0.0
		 */
		public function __clone () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
		} // End __clone ()

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 1.0.0
		 */
		public function __wakeup () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
		} // End __wakeup ()

		/**
		 * Installation. Runs on activation.
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function install () {
			$this->_log_version_number();
		} // End install ()

		/**
		 * Log the plugin version number.
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		private function _log_version_number () {
			update_option( $this->_token . '_version', $this->_version );
		} // End _log_version_number ()

	}
}