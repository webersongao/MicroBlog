<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function elb_setup_post_types() {
	/** Liveblog Entries Post Type */
	$labels = array(
		'name'               => _x( 'Liveblog Entries', 'post type general name', ELB_TEXT_DOMAIN ),
		'singular_name'      => _x( 'Entry', 'post type singular name', ELB_TEXT_DOMAIN ),
		'add_new'            => __( 'Add New', ELB_TEXT_DOMAIN ),
		'add_new_item'       => __( 'Add New Entry', ELB_TEXT_DOMAIN ),
		'edit_item'          => __( 'Edit Entry', ELB_TEXT_DOMAIN ),
		'new_item'           => __( 'New Entry', ELB_TEXT_DOMAIN ),
		'all_items'          => __( 'All Liveblog Entries', ELB_TEXT_DOMAIN ),
		'view_item'          => __( 'View Entry', ELB_TEXT_DOMAIN ),
		'search_items'       => __( 'Search Liveblog Entries', ELB_TEXT_DOMAIN ),
		'not_found'          => __( 'No Liveblog Entries found', ELB_TEXT_DOMAIN ),
		'not_found_in_trash' => __( 'No Liveblog Entries found in Trash', ELB_TEXT_DOMAIN ),
		'parent_item_colon'  => '',
		'menu_name'          => __( 'Easy Liveblogs', ELB_TEXT_DOMAIN ),
	);
	$args   = array(
		'labels'          => apply_filters( 'elb_post_type_labels', $labels ),
		'public'          => false,
		'query_var'       => false,
		'rewrite'         => false,
		'show_ui'         => true,
		'capability_type' => 'post',
		'map_meta_cap'    => true,
		'supports'        => array( 'title', 'author', 'editor' ),
		'can_export'      => true,
		'menu_icon'       => 'dashicons-image-rotate',
	);
	register_post_type( 'elb_entry', $args );
}
add_action( 'init', 'elb_setup_post_types', 1 );
