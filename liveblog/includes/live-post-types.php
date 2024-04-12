<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mlb_setup_post_types() {
	$labels = array(
		'name'               => _x( '连载', 'post type general name', MLB_TEXT_DOMAIN ),
		'singular_name'      => _x( 'Live Entry', 'post type singular name', MLB_TEXT_DOMAIN ),
		'add_new'            => __( 'Add New', MLB_TEXT_DOMAIN ),
		'add_new_item'       => __( 'Add New Entry', MLB_TEXT_DOMAIN ),
		'edit_item'          => __( 'Edit Entry', MLB_TEXT_DOMAIN ),
		'new_item'           => __( 'New Entry', MLB_TEXT_DOMAIN ),
		'all_items'          => __( '连载列表', MLB_TEXT_DOMAIN ),
		'view_item'          => __( 'View Entry', MLB_TEXT_DOMAIN ),
		'search_items'       => __( 'Search Liveblog Entries', MLB_TEXT_DOMAIN ),
		'not_found'          => __( 'No Liveblog Entries found', MLB_TEXT_DOMAIN ),
		'not_found_in_trash' => __( 'No Liveblog Entries found in Trash', MLB_TEXT_DOMAIN ),
		'parent_item_colon'  => '',
		'menu_name'          => __( '连载', MLB_TEXT_DOMAIN ),
	);
	$args   = array(
		'labels'          => apply_filters( 'mlb_post_type_labels', $labels ),
		'public'          => false,
		'query_var'       => false,
		'rewrite'         => false,
		'show_ui'         => true,
		'capability_type' => 'post',
		'map_meta_cap'    => true,
		'can_export'      => true,
		'menu_icon'       => 'dashicons-playlist-video',
		'supports'        => array( 'title', 'author', 'editor' ),
		'show_in_rest' 	  => use_block_editor_for_post_type('post'), 
	);
	register_post_type( 'microlive', $args );
}
add_action( 'init', 'mlb_setup_post_types', 1 );
