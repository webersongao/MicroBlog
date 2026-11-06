<?php
/*
 * Plugin Name: MicroBlog
 * Plugin URI: https://www.webersongao.com/tag/microblog
 * Description: Use WordPress as a microblog: publish short posts and show them with a shortcode or a widget. (微博 / 说说)
 * Version: 1.9.1
 * Author: WebersonGao
 * Author URI: https://www.webersongao.com
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: microblog
 * Domain Path: /languages
 * Tags: microblog, weibo, short content, liveblog
 * Based on simple-microblogging plugin developed by Samuel Coskey, Victoria Gitman(http://boolesrings.org),Thanks to obaby(https://h4ck.org.cn/) Thanks to ChatGPT.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('MICROBLOG_PLUGIN_FILE', __FILE__);
define('MICROBLOG_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MICROBLOG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MICROBLOG_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('MICROBLOG_BASEFOLDER', plugin_basename(dirname(__FILE__)));
define('MICROBLOG_DOMAIN', 'microblog');
define('MICROBLOG_VERSION', '1.9.1');

/*
 * 加载顺序：设置层 → 渲染片段 → 公共函数 → 各功能模块。
 * 每个模块只注册自己的钩子，不再互相 require。
 */
$microblog_includes = array(
    'includes/microblog-settings.php',
    'includes/microblog-render.php',
    'includes/microblog-functions.php',
    'includes/microblog-index.php',
    'includes/microblog-widget.php',
    'includes/microblog-shortcode.php',
    'includes/microblog-quick-pub.php',
);

foreach ($microblog_includes as $microblog_file) {
    require_once MICROBLOG_PLUGIN_PATH . $microblog_file;
}

unset($microblog_includes, $microblog_file);

// 连载是可选模块：未启用时不加载它的 12 个文件与前端脚本。
if (mbfun_get_general_option('mb_liveblog_regist', false)) {
    require_once MICROBLOG_PLUGIN_PATH . 'liveblog/liveblog-index.php';
}

// 后台设置页（仅在后台加载）。
if (is_admin()) {
    require_once MICROBLOG_PLUGIN_PATH . 'admin/microblog-admin.php';
}

add_action('admin_menu', 'microblog_setting_page');

/**
 * 加载翻译文件（languages/microblog-{locale}.mo）。
 *
 * @return void
 */
function microblog_load_textdomain()
{
    load_plugin_textdomain(MICROBLOG_DOMAIN, false, dirname(MICROBLOG_PLUGIN_BASENAME) . '/languages/');
}
add_action('init', 'microblog_load_textdomain', 1);

/**
 * 注册「工具 → 微博设置」。
 *
 * @return void
 */
function microblog_setting_page()
{
    add_management_page(
        __('MicroBlog - Control Panel', MICROBLOG_DOMAIN),
        __('MicroBlog Settings', MICROBLOG_DOMAIN),
        'manage_options',
        MICROBLOG_PLUGIN_BASENAME,
        'microblog_admin_settings'
    );
}

/**
 * 插件列表页加「设置」。
 *
 * @param array  $links 操作链接。
 * @param string $file  插件文件。
 * @return array
 */
function microblog_setting_action_links($links, $file)
{
    if (MICROBLOG_PLUGIN_BASENAME === $file) {
        $links[] = '<a href="' . esc_url(admin_url('tools.php?page=' . MICROBLOG_PLUGIN_BASENAME)) . '">'
            . esc_html__('Settings', MICROBLOG_DOMAIN) . '</a>';
    }

    return $links;
}
add_filter('plugin_action_links', 'microblog_setting_action_links', 10, 2);

/**
 * 激活时写入默认配置。
 *
 * @return void
 */
function microblog_plugin_data_activation()
{
    if (false === get_option('microblog_micropost_data')) {
        add_option('microblog_micropost_data', array(
            'mb_title_show'     => true,
            'mb_date_format'    => 'date_human',
            'mb_content_bottom' => array('conbottomuser'),
        ));
    }

    mbfun_flush_settings_cache();
    mbfun_maybe_flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'microblog_plugin_data_activation');

/**
 * 旧配置键一次性迁移。
 *
 * 2026-11-06 统一配置键前缀：theme_microblog_data -> microblog_micropost_data，
 * general 组子键 msk_liveblog_regist -> mb_liveblog_regist。
 * 老版本升级上来时把设置搬过去，搬完即删旧键；不新增常驻标记键。
 * 预计两个版本后（届时旧键已绝迹）可删除本函数。
 *
 * 后台「数据库升级」按钮也会调用本函数，返回迁移结果。
 *
 * @return array ['option_moved' => bool, 'subkey_renamed' => bool]
 */
function microblog_migrate_legacy_option()
{
    $result = array('option_moved' => false, 'subkey_renamed' => false);

    if (!is_admin()) {
        return $result;
    }

    if (false !== get_option('theme_microblog_data')) {
        if (false === get_option('microblog_micropost_data')) {
            add_option('microblog_micropost_data', get_option('theme_microblog_data'));
        }
        delete_option('theme_microblog_data');
        $result['option_moved'] = true;
    }

    $general = get_option('microblog_general_data');

    if (is_array($general) && array_key_exists('msk_liveblog_regist', $general)) {
        $general['mb_liveblog_regist'] = $general['msk_liveblog_regist'];
        unset($general['msk_liveblog_regist']);
        update_option('microblog_general_data', $general);
        $result['subkey_renamed'] = true;
    }

    return $result;
}

/**
 * 被动钩子包装：升级按钮请求（admin-post.php 也会触发 admin_init）时让位，
 * 由按钮处理器统一执行并汇报结果，避免「明明迁了却报无事可做」。
 *
 * @return array
 */
function microblog_migrate_legacy_option_hooked()
{
    if (isset($_GET['action']) && 'microblog_db_upgrade' === $_GET['action']) {
        return array('option_moved' => false, 'subkey_renamed' => false);
    }

    return microblog_migrate_legacy_option();
}
add_action('admin_init', 'microblog_migrate_legacy_option_hooked');
