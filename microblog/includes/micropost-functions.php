<?php
// micropost-functions.php

global $microblog_slug_name;

function register_micropost_type($supports) {
    $slug_name = get_microblog_slug_name();
    if (empty($supports)) { $supports = array('title', 'editor','comments');}
    $labels = array(
        'name' => __('微博'),
        'menu_name' => __('微博'),
        'singular_name' => __('微博'),
        'description' => __('通过 MicroBlog 你可以轻松创建一条微博，通过短代码或插件增加到某页面或边栏'),
    );
    $args = array(
        'labels' => $labels,
        'has_archive' => true,
        'menu_icon' => 'dashicons-format-status',
        'menu_position' => 5,
        'public' => true,
        'rewrite' => array('slug' => $slug_name), // 使用动态获取的 slug
        'supports' => $supports, // 更新为动态获取的支持项
        'show_in_rest' => use_block_editor_for_post_type('post'), // 跟站点编辑器保持一致
    //  'taxonomies' => array ( 'category', 'post_tag' ),
    );
    register_post_type('micropost', $args);
}

// 更新全局变量的示例
function update_global_microblog_option($new_value) {
    if (!is_string($new_value)) { return; }
    global $microblog_slug_name;
    $microblog_slug_name = $new_value;
}


function micropost_format_time($post_time) {
    $options = get_option('microblog_setting_data');
    $date_format = isset($options['mb_date_format']) ? $options['mb_date_format'] : '';
    if ($date_format == 'date_format_notime'){
        // return date('m-d H:i', $post_time);
        return '';
    } elseif ($date_format == 'date_format_date') {
        return date_i18n(get_option('date_format'), $post_time);
    } elseif ($date_format == 'date_format_vague') {
        // 格式化模糊时间
        return post_fuzzy_time($post_time);
    }
    return date_i18n(get_option('date_format'), $post_time);
}

function post_fuzzy_time($post_time) {
    $time_diff = time() - $post_time;
    if ($time_diff < 60) {
        return '刚刚';
    } elseif ($time_diff < 3600) {
        $minutes = round($time_diff / 60);
        return $minutes . '分钟前';
    } elseif ($time_diff < 86400) {
        $hours = round($time_diff / 3600);
        return $hours . '小时前';
    } elseif ($time_diff < 2592000) { // 30 * 24 * 3600
        $days = round($time_diff / 86400);
        return $days . '天前';
    } elseif ($time_diff < 31536000) { // 365 * 24 * 3600
        return date('m/d H:i', $post_time);
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
    $options = get_option('microblog_setting_data');
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
    wp_enqueue_style('microblog-style', plugins_url('css/microblog-style.css', dirname(__FILE__)), array(), $plugin_version);
    // wp_enqueue_script('microblog-script', plugins_url('js/microblog-script.js', dirname(__FILE__)), array(), $plugin_version, true);
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