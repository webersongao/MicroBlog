<?php

if ( ! defined('ABSPATH' ) ) {
	exit;
}

add_action('init', 'mbfun_create_liveblog_type');
function mbfun_create_liveblog_type() {

	mbfun_update_microblog_display_module();
	
}


function mbfun_register_liveblog_args() {

	$labels = array(
		'name'               => _x('Liveblog', 'post type general name', MICROBLOG_DOMAIN ),
		'menu_name'          => __('Liveblog', MICROBLOG_DOMAIN),
		'singular_name'      => _x('Liveblog', 'post type singular name', MICROBLOG_DOMAIN ),
		'add_new'            => __('New Liveblog', MICROBLOG_DOMAIN ),
		'add_new_item'       => __('Write Liveblog', MICROBLOG_DOMAIN ),
		'edit_item'          => __('Edit Liveblog', MICROBLOG_DOMAIN ),
		'new_item'           => __('New Liveblog', MICROBLOG_DOMAIN ),
		'view_items'         => __('Liveblog List', MICROBLOG_DOMAIN),
		'all_items'          => __('Liveblog', MICROBLOG_DOMAIN ),
		'view_item'          => __('View Link', MICROBLOG_DOMAIN ),
		'search_items'       => __('Search Liveblogs', MICROBLOG_DOMAIN ),
		'not_found'          => __('No liveblogs found.', MICROBLOG_DOMAIN ),
		'not_found_in_trash' => __('No liveblogs found in Trash.', MICROBLOG_DOMAIN ),
		'item_updated'       => __('Liveblog updated.', MICROBLOG_DOMAIN),
		'item_published'     => __('Liveblog published.', MICROBLOG_DOMAIN),
		'item_scheduled'     => __('Liveblog scheduled.', MICROBLOG_DOMAIN),
		'featured_image'     => __('Liveblog featured image', MICROBLOG_DOMAIN),
		'filter_items_list'  => __('Filter liveblog list', MICROBLOG_DOMAIN),
		'filter_by_date'     => __('Filter by liveblog date', MICROBLOG_DOMAIN),
		'set_featured_image' => __('Set featured image', MICROBLOG_DOMAIN),
		'use_featured_image'       => __('Use featured image', MICROBLOG_DOMAIN),
		'remove_featured_image'    => __('Remove featured image', MICROBLOG_DOMAIN),
		'item_reverted_to_draft'   => __('Liveblog reverted to draft.', MICROBLOG_DOMAIN),
		'items_list'               => __('Liveblog List', MICROBLOG_DOMAIN),
		'items_list_navigation'    => __('Liveblog list navigation', MICROBLOG_DOMAIN),
		'item_published_privately' => __('Liveblog published privately.', MICROBLOG_DOMAIN),
		'parent_item_colon'        => '',
	);
	$reg_args   = array(
		'labels'          => apply_filters('mlb_post_type_labels', $labels ),
		'public'          => false,
		'query_var'       => false,
		'rewrite'         => false,
		'show_ui'         => true,
		'menu_position'   => 7,
		'capability_type' => 'post',
		'map_meta_cap'    => true,
		'can_export'      => true,
		'menu_icon'       => 'dashicons-format-aside',
		'supports'        => array('title', 'author', 'editor' ),
		'show_in_rest' 	  => use_block_editor_for_post_type('post'),    
	);

	return $reg_args;

}