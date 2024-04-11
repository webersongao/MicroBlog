<?php
// micropost-functions.php

global $microblog_slug_name;

function register_micropost_type($supports, $tageEnable = false) {

    $slug_name = get_microblog_slug_name();
    if (empty($supports)) { $supports = array('title', 'editor','comments');}
    $labels = array(
        'name' => __('微博'),
        'menu_name' => __('微博'),
        'singular_name' => __('微博'),
        'add_new' => __('发微博'),
        'add_new_item' => __('写微博'),
        'all_items' => __('微博'),
        'view_items' => __('微博列表'),
        'view_item' => __('查看微博'),
        'search_items' => __('搜索微博'),
        'edit_item' => __('编辑该微博'),
        'not_found' => __('未找到相关微博'),
        'item_updated' => __('微博已更新'),
        'item_published' => __('微博已发布'),
        'not_found_in_trash' => __('回收站中没有微博'),
        'item_scheduled' => __('该微博已加入发布计划'),
        'featured_image' => __('微博头图'),
        'filter_items_list' => __('筛选微博列表'),
        'filter_by_date' => __('按微博日期筛选'),
        'set_featured_image' => __('设置微博头图'),
        'use_featured_image' => __('使用微博头图'),
        'remove_featured_image' => __('移除微博头图'),
        'item_reverted_to_draft' => __('该微博已恢复为草稿'),
        'items_list' => __('微博列表'),
        'items_list_navigation' => __('微博列表导航'),
        'item_published_privately' => __('该微博已私密发布'),
        
        
        // 以下为实验数据，边用边改
        'new_item' => __('微博-new_item'),
        'parent_item_colon' => __('微博-parent_item_colon'),
        'archives' => __('微博-archives'),
        'attributes' => __('微博-attributes'),
        'insert_into_item' => __('微博-insert_into_item'),
        'uploaded_to_this_item' => __('微博-uploaded_to_this_item'),
        'item_trashed' => __('微博-item_trashed'),

        'item_link' => __('微博-item_link'),
        'item_link_description' => __('微博-item_link_description'),
    );

    $args = array(
        'label' => '微博',
        'labels' => $labels,
        'has_archive' => true,
        'menu_position' => 5,
        'public' => true,
        'menu_icon' => 'dashicons-format-status',
        'rest_base' => 'microposts', // REST API 路由的根 URL Type
        'rewrite' => array('slug' => $slug_name), // 使用动态获取的 slug
        'supports' => $supports, // 更新为动态获取的支持项
        'show_in_rest' => use_block_editor_for_post_type('post'), // 跟站点编辑器保持一致
        'description' => __('通过 MicroBlog 你可以轻松创建一条微博，通过短代码或插件增加到某页面或边栏'),
        // 'taxonomies' => array ('micropost_topic' ),
    );
    register_post_type('micropost', $args);
    
    register_micropost_taxonomy($tageEnable);
    
    flush_rewrite_rules();
}


function register_micropost_taxonomy($enable) {
    
    if (!$enable) {
        return;
    }
    // 创建一个新的分类法，用于 'micropost' 类型的文章
    $labels = array(
            'name' => __('微博话题'),
            'singular_name' => __('微博话题'),
            'add_new_item' => __('添加新话题'),
            'search_items' => __('搜索话题'),
            'edit_item' => __('编辑话题'),
            'view_item' => __('查看话题'),
            'most_used' => __('常用话题'),
            'not_found' => __('未找到该话题'),
            'choose_from_most_used' => __('选择已有话题'),
            'separate_items_with_commas' => __('多个话题请用英文逗号（,）分开'),
            
            // 以下为实验数据，边用边改
            'all_items' => __('微话-all_items'),
            'popular_items' => __('微话-popular_items'),
            'parent_item' => __('微话-parent_item'),
            'update_item' => __('微话-update_item'),
            'new_item_name' => __('微话-new_item_name'),
            'no_terms' => __('微话-no_terms'),
            'filter_by_item' => __('微话-filter_by_item'),
            'back_to_items' => __('微话-back_to_items'),
            'item_link' => __('微话-item_link'),
            'item_link_description' => __('微话-item_link_description'),
        );
        
    $taxonomy_args = array(
        'label' => __('微博话题'),
        'labels' => $labels,
        'public' => true,
        'show_in_rest' => true,
        'hierarchical' => false,
        'show_tagcloud' => false,
        'show_admin_column' => true,
        'rest_base' => 'micropost_topics', // REST API 路由的根 URL Type
        'rewrite' => array( 'slug' => 'micropost_topics' ), // 自定义分类法的重写规则
        'description' => __('通过 话题标签 创建微博话题，针对一个主题，连续讨论'),
    );
    
    register_taxonomy('micropost_topic', 'micropost', $taxonomy_args);
    register_taxonomy_for_object_type('micropost_topic', 'micropost');
}

function mbfun_get_live_settings() {
	$settings = get_option( 'microblog_liveblog_data', array() );
	return $settings;
}

function mbfun_get_gallery_settings() {
	$settings = get_option( 'microblog_gallery_data', array() );
	return $settings;
}

function mbfun_get_general_settings() {
	$settings = get_option( 'microblog_general_data', array() );
	return $settings;
}

// 更新全局变量的示例
function update_global_microblog_option($new_value) {
    if (!is_string($new_value)) { return; }
    global $microblog_slug_name;
    $microblog_slug_name = $new_value;
}


function micropost_format_time($post_time) {
    
    $options = mbfun_get_general_settings();
    $date_format = isset($options['mb_date_format']) ? $options['mb_date_format'] : '';
    if ($date_format == 'date_hide'){
        // return date('m-d H:i', $post_time);
        return '';
    } elseif ($date_format == 'date_date') {
        return date_i18n(get_option('date_format'), $post_time);
    } elseif ($date_format == 'date_human') {
        // 格式化模糊时间
        return post_fuzzy_time($post_time);
    }
    return date_i18n(get_option('date_format'), $post_time);
}

function post_fuzzy_time($post_time) {
    $time_diff = current_time('timestamp') - $post_time;
    if ($time_diff < 60) {
        return '刚刚';
    } elseif ($time_diff < 3600) {
        $minutes = round($time_diff / 60);
        return $minutes . '分钟前';
    } elseif ($time_diff < 86400) {
        $hours = round($time_diff / 3600);
        return $hours . '小时前';
    } elseif ($time_diff < 31536000) { // 365 * 24 * 3600
        return gmdate('m/d H:i', $post_time);
    } else {
        return date_i18n(get_option('date_format'), $post_time);
    }
}

function micropost_excerpt_more($more) {
    return ' ...';
}

function get_microblog_slug_name() {
    global $microblog_slug_name;
    if (!empty($microblog_slug_name)) {
        return $microblog_slug_name;
    }
    $options = mbfun_get_general_settings();
    $slug_name = isset($options['mb_slug_name']) ? $options['mb_slug_name'] : 'microposts'; 
    $slug_name = preg_replace('/[^a-zA-Z0-9]/', '', $slug_name); // 过滤非法字符
    if (empty($slug_name)) { $slug_name = 'microposts'; }

    // 设置全局变量
    update_global_microblog_option($slug_name);

    return $slug_name;
}

// 引入js
function microblog_enqueue_scripts_and_styles() {
    global $plugin_version;
    wp_enqueue_style('microblog-style', plugins_url('css/microblog-style.css', dirname(__FILE__)), array(), $plugin_version, 'all', true);
    wp_enqueue_script('microblog-script', plugins_url('js/microblog-script.js', dirname(__FILE__)), array(), $plugin_version, true);
}

// 全站Feed中格式化输出 微博 标题
function formart_microblog_feed_title($title, $post_id) {
    $post_type = get_post_type($post_id);
    if ($post_type === 'micropost') {
        return '【微博】：' . $title;
    }
    return $title;
}





?>