<?php
// microblog-widget.php

require_once(plugin_dir_path(__FILE__) . 'micropost-functions.php');

add_action('init', 'create_micropost_type');
function create_micropost_type() {
    $microtag = false;
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
        if (in_array('mb_posttag', $editor_func)) {
            $microtag = true;
        }
        // 检查是否存在重复的支持项
        $supports = array_unique($supports);
    }

    register_micropost_type($supports, $microtag);
}

// 合并mocroblog的feed输出 ，并格式化标题
add_action('pre_get_posts', 'customize_main_query');
function customize_main_query($query) {
    if (is_admin() || !$query->is_main_query() || !($query->is_feed)) {
        return;
    }
    if ($query->get('post_type') === 'micropost') {
        $feed_num = get_option('posts_per_rss'); // 获取“Feed 中显示最近”的数量
        $page_num = $feed_num ? intval($feed_num) : 10;
        $query->set('posts_per_rss', $page_num); 
    } else {
        $options = get_option('microblog_setting_data');
        $feed_miropost = isset($options['mb_rss_feed']) ? $options['mb_rss_feed'] : false;
        if ($feed_miropost){
            if (!is_post_type_archive('micropost')){
                $query->set('post_type', array('post', 'micropost'));
            }
            add_filter('the_title', 'formart_microblog_feed_title', 10, 2);
        }
    }
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