<?php


function get_registry($user_id){
	global $wpdb;

	$table_name = $wpdb->prefix . 'littleregistry';
	
	$charset_collate = $wpdb->get_charset_collate();

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$results = $wpdb->get_results( "SELECT * FROM $table_name WHERE user_id=$user_id", OBJECT );
	
	return $results[0];
}

function get_registry_rows($registry_id){
	global $wpdb;

	$table_name_items = $wpdb->prefix . 'littleregistry_items';
	
	$charset_collate = $wpdb->get_charset_collate();

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$results = $wpdb->get_results( "SELECT * FROM $table_name_items WHERE registry_id=$registry_id", OBJECT );
	
	return $results;
}

function stringify_variance($variation_attrs){
	$variation_string = '';
	foreach ($variation_attrs as $key => $value) {
		$variation_string .= $value.', ';
	}
	return rtrim($variation_string, ', ');
}

function is_name_already_taken($name){
	global $wpdb;

	$table_name = $wpdb->prefix . 'littleregistry';
	$table_name_items = $wpdb->prefix . 'littleregistry_items';
	
	$charset_collate = $wpdb->get_charset_collate();

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	$results = $wpdb->get_results( "SELECT * FROM $table_name", OBJECT );
	
	$nameTaken = 0;

	foreach ($results as $key => $value) {
		if($value->name == $name){
			$nameTaken = 1;
		}
	}

	return $nameTaken;
}

function parse_site_url(){
  $subject = get_site_url();
  $needle = array('https://', 'http://');
  $replacement = '';
  return str_replace($needle, $replacement, $subject);
}