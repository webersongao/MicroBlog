<?php
// microblog-widget.php

require_once(plugin_dir_path(__FILE__) . 'micropost-functions.php');


add_action('init', 'create_micropost_type');
function create_micropost_type() {
    $options = get_option('microblog_setting_data');
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
        // 检查是否存在重复的支持项
        $supports = array_unique($supports);
    }

    register_micropost_type($supports);
}

// 微博Rss的每页数量限制
add_action('pre_get_posts', 'limit_rss_posts_per_page');
function limit_rss_posts_per_page($query) {
    // $options = get_option('microblog_setting_data');
    // $page_num = isset($options['mb_postrss_num']) ? intval($options['mb_postrss_num']) : 10;
    $page_num = 10;
    if ($query->is_feed && $query->is_main_query() && $query->get('post_type') === 'micropost') {
        $query->set('posts_per_rss', $page_num); // 设置每页 15 个文章
    }
}

// 注册激活插件时，设置默认数据
register_activation_hook( __FILE__, 'microblog_plugin_data_activation' );
function microblog_plugin_data_activation() {
    $options = get_option('microblog_setting_data');
    if (empty($options)) {
        $defaults = array(
            'mb_title_show' => true, // 默认为显示标题
            'mb_date_show' => true, // 默认为显示日期
            'mb_title_position' => array('titlebottom'), // 默认标题位置为 titlebottom
        );
        add_option('microblog_setting_data', $defaults);
    }
    // 刷新重写规则
    microblog_rewrite_flush();
}

function microblog_rewrite_flush() {
    create_micropost_type();
    flush_rewrite_rules();
}

// 注册卸载插件时运行的函数
register_uninstall_hook( __FILE__, 'microblog_plugin_uninstall' );
function microblog_plugin_uninstall() {
    // 删除选项
    delete_option('microblog_setting_data');
    delete_option('widget_microblog_widget');
    // 还可以执行其他清理操作，如删除数据库条目等
}

// Add rewrite rule for microblog permalink structure
add_action('init', 'custom_microblog_rewrite_rule');
function custom_microblog_rewrite_rule() {
    $slug_name = get_microblog_slug_name(); // 获取微博的 slug
    add_rewrite_rule('^' . $slug_name . '/([0-9]+)\.html/?$', 'index.php?post_type=micropost&p=$matches[1]', 'top');
}

// Modify microblog permalink structure
add_filter('post_type_link', 'custom_microblog_permalink', 10, 2);
function custom_microblog_permalink($permalink, $post) {
    if ('micropost' === get_post_type($post)) {
        $slug_name = get_microblog_slug_name();
        return home_url($slug_name . '/' . $post->ID . '.html');
    }
    return $permalink;
}

// 处理分页
add_filter('request', 'remove_page_from_query_string');
function remove_page_from_query_string($query_string) {
    if (isset($query_string['name']) && isset($query_string['paged'])) {
        unset($query_string['name']);
        @list($delim, $page_index) = explode('/', $query_string['paged']);
        $query_string['paged'] = $page_index;
    }
    return $query_string;
}

// adapted from Custom Post Type Category Pagination
add_filter('request', 'fix_category_pagination');
function fix_category_pagination($qs) {
    if (isset($qs['category_name']) && isset($qs['paged'])) {
        $qs['post_type'] = get_post_types(array('public' => true, '_builtin' => false));
        array_push($qs['post_type'], 'post');
    }
    return $qs;
}




?>