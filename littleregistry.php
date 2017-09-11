<?php
/*
 * Plugin Name: LittleRegistry
 * Version: 1.0
 * Plugin URI: http://www.jorworks.us
 * Description: Wedding Registry
 * Author: Jordan Little
 * Author URI: http://www.jorworks.us
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: littleregistry
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Jordan Little
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

register_activation_hook( __FILE__, 'registry_DB_table_create' );
register_activation_hook( __FILE__, 'create_registry_page' );

// Load plugin class files
require_once( 'includes/class-littleregistry.php' );
require_once( 'includes/class-littleregistry-settings.php' );
require_once( 'includes/class-littleregistry-myaccount.php' );

// Load plugin libraries
require_once( 'includes/lib/class-littleregistry-admin-api.php' );
require_once( 'includes/lib/class-littleregistry-post-type.php' );
require_once( 'includes/lib/class-littleregistry-taxonomy.php' );

// Load plugin assets
require_once( 'assets/php/littleregistry-ajax.php' );
require_once( 'assets/php/littleregistry-functions.php' );
require_once( 'assets/php/littleregistry-shortcodes.php' );

/**
 * Returns the main instance of LittleRegistry to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object LittleRegistry
 */
function LittleRegistry () {
	$instance = LittleRegistry::instance( __FILE__, '1.0.0' );

	if ( is_null( $instance->settings ) ) {
		$instance->settings = LittleRegistry_Settings::instance( $instance );
	}

	return $instance;
}

LittleRegistry();