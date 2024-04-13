<?php
// microblog-widget.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once(plugin_dir_path(__FILE__) . 'microblog-functions.php');

add_action('init', 'mbfun_create_micropost_type');

function mbfun_create_micropost_type() {
    $microtag = false;
    $options = mbfun_get_micropost_settings();
    $supports = array('title', 'editor', 'comments'); // 默认支持的参数
    // 如果$options存在并且不为空，则更新supports参数
    if (!empty($options) && isset($options['mb_editor_func'])) {
        $editor_func = $options['mb_editor_func'];
        if (in_array('mb_author', $editor_func)) {
            $supports[] = 'author';
        }
        if (in_array('mb_thumbnail', $editor_func)) {
            $supports[] = 'thumbnail';
        }
        if (in_array('mb_excerpt', $editor_func)) {
            $supports[] = 'excerpt';
        }
        if (in_array('mb_posttag', $editor_func)) {
            $microtag = true;
        }
        // 检查是否存在重复的支持项
        $supports = array_unique($supports);
    }

    mbfun_register_micropost_type($supports, $microtag);
}

function mbfun_register_micropost_type($supports, $microtag = false) {

    $slug_name = mbfun_get_lasted_micropost_slug_name();
    if (empty($supports)) { $supports = array('title', 'editor','comments');}
    $labels = array(
        'name' => __('微博' , MICROBLOG_DOMAIN),
        'menu_name' => __('微博' , MICROBLOG_DOMAIN),
        'singular_name' => __('微博' , MICROBLOG_DOMAIN),
        'add_new' => __('发微博' , MICROBLOG_DOMAIN),
        'add_new_item' => __('写微博' , MICROBLOG_DOMAIN),
        'all_items' => __('微博' , MICROBLOG_DOMAIN),
        'view_items' => __('微博列表' , MICROBLOG_DOMAIN),
        'view_item' => __('查看微博' , MICROBLOG_DOMAIN),
        'search_items' => __('搜索微博' , MICROBLOG_DOMAIN),
        'edit_item' => __('编辑该微博' , MICROBLOG_DOMAIN),
        'not_found' => __('未找到相关微博' , MICROBLOG_DOMAIN),
        'item_updated' => __('微博已更新' , MICROBLOG_DOMAIN),
        'item_published' => __('微博已发布' , MICROBLOG_DOMAIN),
        'not_found_in_trash' => __('回收站中没有微博' , MICROBLOG_DOMAIN),
        'item_scheduled' => __('该微博已加入发布计划' , MICROBLOG_DOMAIN),
        'featured_image' => __('微博头图' , MICROBLOG_DOMAIN),
        'filter_items_list' => __('筛选微博列表' , MICROBLOG_DOMAIN),
        'filter_by_date' => __('按微博日期筛选' , MICROBLOG_DOMAIN),
        'set_featured_image' => __('设置微博头图' , MICROBLOG_DOMAIN),
        'use_featured_image' => __('使用微博头图' , MICROBLOG_DOMAIN),
        'remove_featured_image' => __('移除微博头图' , MICROBLOG_DOMAIN),
        'item_reverted_to_draft' => __('该微博已恢复为草稿' , MICROBLOG_DOMAIN),
        'items_list' => __('微博列表' , MICROBLOG_DOMAIN),
        'items_list_navigation' => __('微博列表导航' , MICROBLOG_DOMAIN),
        'item_published_privately' => __('该微博已私密发布' , MICROBLOG_DOMAIN),
        
        
        // 以下为实验数据，边用边改
        'new_item' => __('微博-new_item' , MICROBLOG_DOMAIN),
        'parent_item_colon' => __('微博-parent_item_colon' , MICROBLOG_DOMAIN),
        'archives' => __('微博-archives' , MICROBLOG_DOMAIN),
        'attributes' => __('微博-attributes' , MICROBLOG_DOMAIN),
        'insert_into_item' => __('微博-insert_into_item' , MICROBLOG_DOMAIN),
        'uploaded_to_this_item' => __('微博-uploaded_to_this_item' , MICROBLOG_DOMAIN),
        'item_trashed' => __('微博-item_trashed'),

        'item_link' => __('微博-item_link' , MICROBLOG_DOMAIN),
        'item_link_description' => __('微博-item_link_description' , MICROBLOG_DOMAIN),
    );

    $args = array(
        'label' => __('微博' , MICROBLOG_DOMAIN),
        'labels' => $labels,
        'has_archive' => true,
        'menu_position' => 6,
        'public' => true,
        'menu_icon' => 'dashicons-format-status',
        'rest_base' => 'microposts', // REST API 路由的根 URL Type
        'rewrite' => array('slug' => $slug_name), // 使用动态获取的 slug
        'supports' => $supports, // 更新为动态获取的支持项
        'show_in_rest' => use_block_editor_for_post_type('post'), // 跟站点编辑器保持一致
        'description' => __('通过 MicroBlog 你可以轻松创建一条微博，通过短代码或插件增加到某页面或边栏' , MICROBLOG_DOMAIN),
        // 'taxonomies' => array ('micropost_topic' ),  // 与 mbfun_register_micropost_taxonomy 功能重复，故注释
    );
    register_post_type('micropost', $args);
    
    if ($microtag) {
        mbfun_register_micropost_taxonomy();
    }
    
    flush_rewrite_rules();
}

// 创建一个新的分类法，用于 'micropost' 类型的文章
function mbfun_register_micropost_taxonomy() {
    
    $labels = array(
            'name' => __('微博话题' , MICROBLOG_DOMAIN),
            'singular_name' => __('微博话题' , MICROBLOG_DOMAIN),
            'add_new_item' => __('添加新话题' , MICROBLOG_DOMAIN),
            'search_items' => __('搜索话题' , MICROBLOG_DOMAIN),
            'edit_item' => __('编辑话题' , MICROBLOG_DOMAIN),
            'view_item' => __('查看话题' , MICROBLOG_DOMAIN),
            'most_used' => __('常用话题' , MICROBLOG_DOMAIN),
            'not_found' => __('未找到该话题' , MICROBLOG_DOMAIN),
            'choose_from_most_used' => __('选择已有话题'),
            'separate_items_with_commas' => __('多个话题请用英文逗号（,）分开'),
            
            // 以下为实验数据，边用边改
            'all_items' => __('微话-all_items' , MICROBLOG_DOMAIN),
            'popular_items' => __('微话-popular_items' , MICROBLOG_DOMAIN),
            'parent_item' => __('微话-parent_item' , MICROBLOG_DOMAIN),
            'update_item' => __('微话-update_item' , MICROBLOG_DOMAIN),
            'new_item_name' => __('微话-new_item_name' , MICROBLOG_DOMAIN),
            'no_terms' => __('微话-no_terms' , MICROBLOG_DOMAIN),
            'filter_by_item' => __('微话-filter_by_item' , MICROBLOG_DOMAIN),
            'back_to_items' => __('微话-back_to_items' , MICROBLOG_DOMAIN),
            'item_link' => __('微话-item_link' , MICROBLOG_DOMAIN),
            'item_link_description' => __('微话-item_link_description' , MICROBLOG_DOMAIN),
        );
        
    $taxonomy_args = array(
        'label' => __('微博话题' , MICROBLOG_DOMAIN),
        'labels' => $labels,
        'public' => true,
        'show_in_rest' => true,
        'hierarchical' => false,
        'show_tagcloud' => false,
        'show_admin_column' => true,
        'rest_base' => 'micropost_topics', // REST API 路由的根 URL Type
        'rewrite' => array( 'slug' => 'micropost_topics' ), // 自定义分类法的重写规则
        'description' => __('通过 话题标签 创建微博话题，针对一个主题，连续讨论' , MICROBLOG_DOMAIN),
    );
    
    register_taxonomy('micropost_topic', 'micropost', $taxonomy_args);
    register_taxonomy_for_object_type('micropost_topic', 'micropost');
}

// 合并mocroblog的feed输出 ，并格式化标题
add_action('pre_get_posts', 'mbfun_customize_main_query');
function mbfun_customize_main_query($query) {
    if (is_admin() || !$query->is_main_query() || !($query->is_feed)) {
        return;
    }
    if ($query->get('post_type') === 'micropost') {
        $feed_num = get_option('posts_per_rss'); // 获取“Feed 中显示最近”的数量
        $page_num = $feed_num ? intval($feed_num) : 10;
        $query->set('posts_per_rss', $page_num); 
    } else {
        $options = mbfun_get_micropost_settings();
        $feed_miropost = isset($options['mb_rss_feed']) ? $options['mb_rss_feed'] : false;
        if ($feed_miropost){
            if (!is_post_type_archive('micropost')){
                $query->set('post_type', array('post', 'micropost'));
            }
            add_filter('the_title', 'microblog_formart_micropost_feed_title', 10, 2);
        }
    }
}

// 全站Feed中格式化输出 微博 标题
function microblog_formart_micropost_feed_title($title, $post_id) {
    $post_type = get_post_type($post_id);
    if ($post_type === 'micropost') {
        return  __('【微博】：' , MICROBLOG_DOMAIN) . $title;
    }
    return $title;
}

// Add rewrite rule for microblog permalink structure
add_action('init', 'mbfun_custom_microblog_rewrite_rule');
function mbfun_custom_microblog_rewrite_rule() {
    $slug_name = mbfun_get_lasted_micropost_slug_name(); // 获取微博的 slug
    add_rewrite_rule('^' . $slug_name . '/([0-9]+)\.html/?$', 'index.php?post_type=micropost&p=$matches[1]', 'top');
}

// Modify microblog permalink structure
add_filter('post_type_link', 'mbfun_custom_microblog_permalink', 10, 2);
function mbfun_custom_microblog_permalink($permalink, $post) {
    if ('micropost' === get_post_type($post)) {
        $slug_name = mbfun_get_lasted_micropost_slug_name();
        return home_url($slug_name . '/' . $post->ID . '.html');
    }
    return $permalink;
}

// 处理分页
add_filter('request', 'mbfun_remove_page_from_query_string');
function mbfun_remove_page_from_query_string($query_string) {
    if (isset($query_string['name']) && isset($query_string['paged'])) {
        unset($query_string['name']);
        @list($delim, $page_index) = explode('/', $query_string['paged']);
        $query_string['paged'] = $page_index;
    }
    return $query_string;
}

// adapted from Custom Post Type Category Pagination
add_filter('request', 'mbfun_fix_category_pagination');
function mbfun_fix_category_pagination($qs) {
    if (isset($qs['category_name']) && isset($qs['paged'])) {
        $qs['post_type'] = get_post_types(array('public' => true, '_builtin' => false));
        array_push($qs['post_type'], 'post');
    }
    return $qs;
}




?>