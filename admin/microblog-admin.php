<?php
/* 后台入口 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/class-mb-admin-page.php';
require_once __DIR__ . '/setting-admin-micropost.php';
require_once __DIR__ . '/setting-admin-general.php';

if (mbfun_get_general_option('mb_liveblog_regist', false)) {
    require_once __DIR__ . '/setting-admin-liveblog.php';
}

/* 当前后台页面是否属于本插件 */
function microblog_admin_needs_assets($hook)
{
    $expected = 'tools_page_' . preg_replace('/\.php$/', '', MICROBLOG_PLUGIN_BASENAME);

    if ($expected === $hook) {
        return true;
    }

    if (! in_array($hook, array('post.php', 'post-new.php'), true) || ! function_exists('get_current_screen')) {
        return false;
    }

    $screen = get_current_screen();

    if (! $screen || 'post' !== $screen->base) {
        return false;
    }

    return 'microlive' === $screen->post_type;
}

/* 加载后台资源 */
function microblog_admin_enqueue_scripts($hook)
{
    if (! microblog_admin_needs_assets($hook)) {
        return;
    }

    wp_enqueue_style('microblog-admin-css', mbfun_asset_url('css/admin-style.css'), array(), mbfun_get_plugin_version());

    if (in_array($hook, array('post.php', 'post-new.php'), true)) {
        return;
    }

    wp_enqueue_script('microblog-admin-script', mbfun_asset_url('js/admin-scripts.js'), array(), mbfun_get_plugin_version(), true);
}
add_action('admin_enqueue_scripts', 'microblog_admin_enqueue_scripts');

/* 设置页内容 */
function microblog_admin_settings()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    MB_Admin_Page::render();
}
