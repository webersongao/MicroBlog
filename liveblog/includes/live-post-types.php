<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mlb_setup_post_types() {
	$labels = array(
		'name'               => _x( '连载', 'post type general name', MICROBLOG_DOMAIN ),
		'singular_name'      => _x( 'Live Entry', 'post type singular name', MICROBLOG_DOMAIN ),
		'add_new'            => __( '写连载', MICROBLOG_DOMAIN ),
		'add_new_item'       => __( 'Add New Entry', MICROBLOG_DOMAIN ),
		'edit_item'          => __( 'Edit Entry', MICROBLOG_DOMAIN ),
		'new_item'           => __( 'New Entry', MICROBLOG_DOMAIN ),
		'all_items'          => __( '连载', MICROBLOG_DOMAIN ),
		'view_item'          => __( 'View Entry', MICROBLOG_DOMAIN ),
		'search_items'       => __( 'Search Liveblog Entries', MICROBLOG_DOMAIN ),
		'not_found'          => __( 'No Liveblog Entries found', MICROBLOG_DOMAIN ),
		'not_found_in_trash' => __( 'No Liveblog Entries found in Trash', MICROBLOG_DOMAIN ),
		'parent_item_colon'  => '',
		'menu_name'          => __( '连载', MICROBLOG_DOMAIN ),
	);
	$args   = array(
		'labels'          => apply_filters( 'mlb_post_type_labels', $labels ),
		'public'          => false,
		'query_var'       => false,
		'rewrite'         => false,
		'show_ui'         => true,
		'menu_position'   => 7,
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
