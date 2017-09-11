<?php

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_ajax_add_registry', 'add_registry' );

function add_registry(){
  $user_id = $_POST['user_id'];
  $registry_name = $_POST['registry_name'];
  $wedding_date = $_POST['wedding_date'];
  $returnObject = create_registry($user_id, $registry_name, $wedding_date);
  echo $returnObject;
  die(); // this is required to return a proper result
}

function create_registry($user_id, $name, $wedding_date){
	if(is_name_already_taken($name) == 1){ //Registry Name Taken
		return 1;
	} else {
		global $wpdb;
		$key = 'registry_id';

		$table_name_items = $wpdb->prefix . 'littleregistry';
		
		$wpdb->insert( 
			$table_name_items,
			array( 
				'time' => current_time( 'mysql' ), 
				'user_id' => $user_id,
				'name' => $name,
				'wedding_date' => $wedding_date,
			) 
		);

		$lastid = $wpdb->insert_id;

		add_user_meta( $user_id, $key, $lastid, true );

		create_registry_page($user_id, $lastid, $name);

		return 0;
	}
}

function create_registry_page($user_id, $lastid, $name){
	$guid = parse_site_url().'/registry/'.rawurlencode($name);
	$gmt_date = gmdate('Y-m-d H:i:s');
	$current_user_id = get_current_user_id();
	$post_content = '[registry_display registry_id="'.$lastid.'"][/registry_display]';
	$post_title = $name;
	$post_type = 'page';
	$comment_status = 'closed';
	$post_status = 'draft';
	$page = get_page_by_title( $post_title );
	$args = array(
      'post_author'       => $current_user_id,
      'post_content'      => $post_content,
      'post_title'        => $post_title,
      'post_type'         => $post_type,
      'comment_status'    => $comment_status,
      'guid'              => $guid,
      'post_date_gmt'     => $gmt_date,
      'post_modified_gmt' => $gmt_date,
      'post_status'       => $post_status
    );
    wp_insert_post($args);
}




add_action( 'wp_ajax_add_registry_item', 'add_registry_item' );

function add_registry_item() {
  check_ajax_referer( 'littleregistry-frontend-ajax-nonce', 'security' );
  $user_id = $_POST['user_id'];
  $prod_id = $_POST['prod_id'];
  $selection = $_POST['selection'];
  $variation_id = get_variant_id($prod_id, $selection);
  if($variation_id != -1){
  	echo add_to_registry($user_id, $prod_id, $variation_id);
  } else {
  	echo $variation_id;
  }
  die(); // this is required to return a proper result
}

function get_variant_id($prod_id, $selection){
	$_factory = new WC_Product_Factory();
	$data = $_factory->get_product($prod_id);
	$variations = $data->get_available_variations();
	$field = 'slug';
	
	foreach ($variations as $key => $variation) {
		$variationAttrs = $variation['attributes'];
		$variation_id = $variation['variation_id'];

		$indexMatches = 0;
		$byAttributes = explode(",", $selection);
		$attrCount = count($byAttributes);
		foreach ($variationAttrs as $key => $value) {
			$tax = substr($key, 10);
			$term = get_term_by($field, $value, $tax);
			$termName = $term->name;
			$taxonomy = substr($key, 13);					
		   	for ($i=0; $i < count($byAttributes); $i++) {
		   		$byValues = explode(":",$byAttributes[$i]);
		   		$prodTaxonomy = $byValues[0];
		   		$prodName = $byValues[1];
		   		if($prodTaxonomy == $taxonomy){
		   			if($prodName == $termName){
		   				$indexMatches++;
		   			}
		   		}
		   	}
		}
		if($indexMatches == $attrCount){
			return $variation_id;
		}
	}
	return -1;
}

function add_to_registry($user_id, $prod_id, $variation_id){
	global $wpdb;
	$key = 'registry_id';

	$table_name_items = $wpdb->prefix . 'littleregistry_items';

	$registry_id = get_user_meta($user_id, $key, true);

	$wpdb->insert( 
		$table_name_items,
		array( 
			'time' => current_time( 'mysql' ), 
			'registry_id' => $registry_id, 
			'prod_id' => $prod_id,
			'variation_id' => $variation_id,
			'quantity' => 1,
			'purchased' => 0,
		) 
	);

	return 'Product Added';
}