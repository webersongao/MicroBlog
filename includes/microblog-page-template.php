<?php
/* 插件自带页面模板 */

if (!defined('ABSPATH')) {
    exit;
}

define('MICROBLOG_PAGE_TEMPLATE_SLUG', 'microblog-list.php');

function microblog_register_page_template($templates)
{
    $templates[MICROBLOG_PAGE_TEMPLATE_SLUG] = __('Microblog list (plugin)', MICROBLOG_DOMAIN);
    return $templates;
}
add_filter('theme_page_templates', 'microblog_register_page_template');

function microblog_is_page_template_request()
{
    if (!is_page()) {
        return false;
    }

    return MICROBLOG_PAGE_TEMPLATE_SLUG === get_page_template_slug(get_queried_object_id());
}

/* 前台把模板文件指向插件目录 */
function microblog_page_template_include($template)
{
    if (microblog_is_page_template_request()) {
        $file = MICROBLOG_PLUGIN_PATH . 'templates/page-microblog.php';
        if (is_readable($file)) {
            return $file;
        }
    }

    return $template;
}
add_filter('template_include', 'microblog_page_template_include', 99);

/* 模板专用资源 */
function microblog_page_template_assets()
{
    if (!microblog_is_page_template_request()) {
        return;
    }

    wp_enqueue_style(
        'microblog-page-template',
        MICROBLOG_PLUGIN_URL . 'assets/css/page-template.css',
        array(),
        function_exists('mbfun_get_plugin_version') ? mbfun_get_plugin_version() : MICROBLOG_VERSION
    );

    if (function_exists('mbfun_enqueue_scripts_and_styles')) {
        mbfun_enqueue_scripts_and_styles();
    }
}
add_action('wp_enqueue_scripts', 'microblog_page_template_assets', 1);

/* 统一头部 */
function microblog_page_header_html($post_id)
{
    $post_id = (int) $post_id;
    $views   = (int) get_post_meta($post_id, 'views', true);
    $date    = get_the_time(get_option('date_format'), $post_id);

    $out  = '<header class="mb-page-header">';
    $out .= '<h1 class="mb-page-title">' . esc_html(get_the_title($post_id)) . '</h1>';
    $out .= '<div class="mb-page-meta"><span>' . esc_html($date) . '</span>';
    $out .= '<span aria-hidden="true"> &middot; </span><span>' . esc_html(sprintf(
        /* translators: %s: number of views */
        __('%s views', MICROBLOG_DOMAIN),
        number_format_i18n($views)
    )) . '</span></div>';
    $out .= '</header>';

    return $out;
}

/* 正文位置开关 */
function microblog_page_content_before_list()
{
    $before = 'list_first' !== mbfun_get_page_content_order();

    return (bool) apply_filters('microblog_page_content_before_list', $before);
}

/* 查找主题的外壳文件 */
function microblog_locate_theme_shell($kind)
{
    $names = 'footer' === $kind
        ? array('footer.php', 'template-parts/footer.php')
        : array('header.php', 'template-parts/header.php');

    $dirs = array_unique(array_filter(array(get_stylesheet_directory(), get_template_directory())));

    foreach ($dirs as $dir) {
        foreach ($names as $name) {
            $file = trailingslashit($dir) . $name;
            if (is_readable($file)) {
                return $file;
            }
        }
    }

    return '';
}
