<?php
/**
 * 后台入口：注册菜单、加载设置页。
 *
 * @package MicroBlog
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/class-mb-admin-page.php';
require_once __DIR__ . '/setting-admin-micropost.php';
require_once __DIR__ . '/setting-admin-general.php';

// 连载设置依赖连载模块的函数，模块启用时才加载。
if (mbfun_get_general_option('msk_liveblog_regist', false)) {
    require_once __DIR__ . '/setting-admin-liveblog.php';
}

/**
 * 当前后台页面是否属于本插件：设置页，或出现连载 metabox 的文章编辑页。
 *
 * @param string $hook 当前页面 hook。
 * @return bool
 */
function microblog_admin_needs_assets($hook)
{
    // 注意：管理页 hook 不带 .php 后缀（tools_page_MicroBlog/microblog）。
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

    // 连载模块未启用时 meta box 不存在，但条目类型仍需这套样式。
    $post_types = array('microlive');

    if (function_exists('mbfun_get_live_supported_post_types')) {
        $post_types = array_merge($post_types, (array) mbfun_get_live_supported_post_types());
    }

    return in_array($screen->post_type, array_values(array_unique($post_types)), true);
}

/**
 * 加载后台资源：样式覆盖设置页与编辑页（metabox 排版依赖它），脚本只在设置页需要。
 *
 * @param string $hook 当前页面 hook。
 * @return void
 */
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

/**
 * 设置页内容（菜单在 microblog.php 注册）。
 *
 * @return void
 */
function microblog_admin_settings()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    MB_Admin_Page::render();
}
