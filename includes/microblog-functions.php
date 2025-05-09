<?php
// 核心公共函数库.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function mbfun_get_micropost_settings() {
	$settings = get_option( 'theme_microblog_data', array() );
	return $settings;
}
function mbfun_get_micropost_option( $key = '', $default = false ) {
    $options = mbfun_get_micropost_settings();
	$value = (isset($options[ $key ]) && !empty( $options[ $key ] )) ? $options[ $key ] : $default;
	return $value;
}

function mbfun_get_liveblog_settings() {
	$settings = get_option( 'microblog_liveblog_data', array() );
	return $settings;
}
function mbfun_get_liveblog_option( $key = '', $default = false ) {
    $options = mbfun_get_liveblog_settings();
    $value = (isset($options[ $key ]) && !empty( $options[ $key ] )) ? $options[ $key ] : $default;
	return $value;
}

function mbfun_get_general_settings() {
	$settings = get_option( 'microblog_general_data', array() );
	return $settings;
}
function mbfun_get_general_option( $key = '', $default = false ) {
    $options = mbfun_get_general_settings();
    $value = (isset($options[ $key ]) && !empty( $options[ $key ] )) ? $options[ $key ] : $default;
	return $value;
}


function mbfun_get_micropost_slug_name() {
	$options = mbfun_get_micropost_settings();
    $slug_name = ! empty($options['mb_slug_name']) ? $options['mb_slug_name'] : 'microposts'; 
	return $slug_name;
}

// 更新全局变量的示例
function mbfun_update_global_microblog_slug_name($new_value) {
    if (!is_string($new_value)) { return; }
    global $microblog_slug_name;
    $microblog_slug_name = $new_value;
}

function mbfun_update_microblog_display_module() {

    $live_regist = mbfun_get_general_option('msk_liveblog_regist', false);
    if ($live_regist) {
        register_post_type( 'microlive', mbfun_register_liveblog_args() );
    } else {
	    unregister_post_type( 'microlive' );
    }
}

function mbfun_micropost_format_time($post_time) {
    
    $options = mbfun_get_micropost_settings();
    $date_format = isset($options['mb_date_format']) ? $options['mb_date_format'] : '';
    if ($date_format == 'date_hide'){
        // return date('m-d H:i', $post_time);
        return '';
    } elseif ($date_format == 'date_date') {
        return date_i18n(get_option('date_format'), $post_time);
    } elseif ($date_format == 'date_human') {
        // 格式化模糊时间
        return mbfun_post_fuzzy_time($post_time);
    }
    return date_i18n(get_option('date_format'), $post_time);
}

function mbfun_post_fuzzy_time($post_time) {
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

function mbfun_micropost_excerpt_more($more) {
    return ' ...';
}

// 获取最新的全局变量值，刷新规则和文章注册slug
function mbfun_get_lasted_micropost_slug_name() {
    global $microblog_slug_name;
    if (!empty($microblog_slug_name)) {
        return $microblog_slug_name;
    }
    $slug_name = mbfun_get_micropost_slug_name(); 
    $slug_name = preg_replace('/[^a-zA-Z0-9]/', '', $slug_name); // 过滤非法字符
    if (empty($slug_name)) { $slug_name = 'microposts'; }

    // 设置全局变量
    mbfun_update_global_microblog_slug_name($slug_name);

    return $slug_name;
}

// 引入js
function mbfun_enqueue_scripts_and_styles() {
    wp_enqueue_style('microblog-style', plugins_url('assets/css/microblog-style.css', dirname(__FILE__)), array(), mbfun_get_plugin_version(), 'all', true);
    wp_enqueue_script('microblog-script', plugins_url('assets/js/microblog-script.js', dirname(__FILE__)), array(), mbfun_get_plugin_version(), true);
}


function mbfun_get_recent_microposts($paged = 1, $atts = array()) {
    // 设置默认参数
    $defaults = array(
        'post_type'      => 'micropost',
        'orderby'        => 'date',
        'order'          => 'DESC',
        'posts_per_page' => 10,
        'post_status'    => 'publish',
        'paged'          => $paged,
    );
    $args = wp_parse_args($atts, $defaults);
    if (isset($atts['q'])) {
        $q = sanitize_text_field($atts['q']);
        $args['s'] = $q;
    }
    $mbpost_query = new WP_Query($args);
    
    // 直接返回 WP_Query 结果的数组
    return $mbpost_query->posts;
}


# 首页查询微博数量 （最新10条，KonyGao主题使用）
# =======================================================

function mbfun_get_recent_excerpt_microblogs($atts = array()) {
    // 设置默认参数
    $defaults = array(
        'post_type' => 'micropost',
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page' => 10,
        'post_status' => 'publish',
        'paged' => 1,
    );
    $args = wp_parse_args($atts, $defaults);
    if (isset($atts['q'])) {
        $q = sanitize_text_field($atts['q']);
        $args['s'] = $q;
    }
    $mbpost_query = new WP_Query($args);
    $mbposts = array();
    while ($mbpost_query->have_posts()) {
        $mbpost_query->the_post();
        $microblog = array(
            'title' => get_the_title(),
            'content' => get_the_content(),
            'permalink' => get_permalink(),
            'publish_time' => get_the_date('Y年n月j日'),
            // 可以根据需要添加其他字段
        );
        $mbposts[] = $microblog;
    }
    wp_reset_postdata();
    return $mbposts;
}


function mbfun_get_plugin_url() {
    return MICROBLOG_PLUGIN_URL;
}

/**
 * Get plugin path
 */
function mbfun_get_plugin_path() {
    return MICROBLOG_PLUGIN_PATH;
}

/**
 * Get plugin version.
 *
 * @return string
 */
function mbfun_get_plugin_version() {
    if ( function_exists( 'wp_get_environment_type' ) && wp_get_environment_type() === 'development' ) {
        return time();
    }
    global $microblog_plugin_version;
    return $microblog_plugin_version;
}









/**
 * Get global options
 *
 * @return array
 */
function mbfun_get_live_options() {
	global $mlb_options;

	return ! empty( $mlb_options ) ? $mlb_options : array();
}

/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @return mixed
 */
function mbfun_get_live_option( $key = '', $default = false ) {
	$live_options = mbfun_get_live_options();
	$value = ! empty( $live_options[ $key ] ) ? $live_options[ $key ] : $default;
	$value = apply_filters( 'mbfun_get_live_option', $value, $key, $default );
	return apply_filters( 'mbfun_get_live_option_' . $key, $value, $key, $default );
}







?>