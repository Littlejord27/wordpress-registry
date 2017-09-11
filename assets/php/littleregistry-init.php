<?php

function registry_DB_table_create() {
  global $wpdb;

  $table_name = $wpdb->prefix . 'littleregistry';
  $table_name_items = $wpdb->prefix . 'littleregistry_items';
  
  $charset_collate = $wpdb->get_charset_collate();
  
  $sqlRegistries = "CREATE TABLE $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    user_id varchar(20) NOT NULL,
    name varchar(255) NOT NULL,
    wedding_date date DEFAULT '0000-00-00' NOT NULL,
    PRIMARY KEY  (id)
  ) $charset_collate;";

  $sqlItems = "CREATE TABLE $table_name_items (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
    registry_id varchar(20) NOT NULL,
    prod_id varchar(20) NOT NULL,
    variation_id varchar(20) NOT NULL,
    quantity smallint(4) DEFAULT 1 NOT NULL,
    purchased boolean DEFAULT false NOT NULL,
    purchaser varchar(255),
    PRIMARY KEY  (id)
  ) $charset_collate;";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sqlRegistries );
  dbDelta( $sqlItems );
}

function create_registry_page(){
  $guid = parse_site_url().'/registry';
  $gmt_date = gmdate('Y-m-d H:i:s');
  $current_user_id = get_current_user_id();
  $post_content = '[registry_landingpage][/registry_landingpage]';
  $post_title = 'Registry';
  $post_type = 'page';
  $comment_status = 'closed';
  $post_status = 'draft';
  $page = get_page_by_title( $post_title );

  if(empty($page)){
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
  } else { 
    //page already made

    // TODO: Inform User that this page already exists. 
    // "You may need to add in the shortcode [registry_landingpage] yourself."
  }
}