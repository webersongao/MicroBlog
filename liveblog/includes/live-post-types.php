<?php

// Exit if accessed directly
if ( ! defined('ABSPATH' ) ) {
	exit;
}

add_action('init', 'mbfun_create_liveblog_type');
function mbfun_create_liveblog_type() {

	mbfun_update_microblog_display_module();
	
}


function mbfun_register_liveblog_args() {

	$labels = array(
		'name'               => _x('连载', 'post type general name', MICROBLOG_DOMAIN ),
		'menu_name'          => __('连载', MICROBLOG_DOMAIN),
		'singular_name'      => _x('微连载', 'post type singular name', MICROBLOG_DOMAIN ),
		'add_new'            => __('新连载', MICROBLOG_DOMAIN ),
		'add_new_item'       => __('写连载', MICROBLOG_DOMAIN ),
		'edit_item'          => __('编辑连载', MICROBLOG_DOMAIN ),
		'new_item'           => __('新连载', MICROBLOG_DOMAIN ),
		'view_items'         => __('连载列表', MICROBLOG_DOMAIN),
		'all_items'          => __('连载', MICROBLOG_DOMAIN ),
		'view_item'          => __('查看链接', MICROBLOG_DOMAIN ),
		'search_items'       => __('搜索连载', MICROBLOG_DOMAIN ),
		'not_found'          => __('未找到相关连载', MICROBLOG_DOMAIN ),
		'not_found_in_trash' => __('回收站中没有连载', MICROBLOG_DOMAIN ),
		'item_updated'       => __('连载已更新', MICROBLOG_DOMAIN),
		'item_published'     => __('连载已发布', MICROBLOG_DOMAIN),
		'item_scheduled'     => __('该连载已加入发布计划', MICROBLOG_DOMAIN),
		'featured_image'     => __('连载头图', MICROBLOG_DOMAIN),
		'filter_items_list'  => __('筛选连载列表', MICROBLOG_DOMAIN),
		'filter_by_date'     => __('按连载日期筛选', MICROBLOG_DOMAIN),
		'set_featured_image' => __('设置连载头图', MICROBLOG_DOMAIN),
		'use_featured_image'       => __('使用连载头图', MICROBLOG_DOMAIN),
		'remove_featured_image'    => __('移除连载头图', MICROBLOG_DOMAIN),
		'item_reverted_to_draft'   => __('该连载已恢复为草稿', MICROBLOG_DOMAIN),
		'items_list'               => __('连载列表', MICROBLOG_DOMAIN),
		'items_list_navigation'    => __('连载列表导航', MICROBLOG_DOMAIN),
		'item_published_privately' => __('该连载已私密发布', MICROBLOG_DOMAIN),
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