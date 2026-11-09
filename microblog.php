<?php
/*
 * Plugin Name: MicroBlog
 * Plugin URI: https://www.webersongao.com/tag/microblog
 * Description: Use WordPress as a microblog: publish short posts and show them with a shortcode or a widget. (微博 / 说说)
 * Version: 1.9.2
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
define('MICROBLOG_VERSION', '1.9.2');

/* 加载顺序 */
$microblog_includes = array(
    'includes/microblog-settings.php',
    'includes/microblog-render.php',
    'includes/microblog-functions.php',
    'includes/microblog-index.php',
    'includes/microblog-widget.php',
    'includes/microblog-shortcode.php',
    'includes/microblog-page-template.php',
    'includes/microblog-quick-pub.php',
);

foreach ($microblog_includes as $microblog_file) {
    require_once MICROBLOG_PLUGIN_PATH . $microblog_file;
}

unset($microblog_includes, $microblog_file);

if (mbfun_get_general_option('mb_liveblog_regist', false)) {
    require_once MICROBLOG_PLUGIN_PATH . 'liveblog/liveblog-index.php';
}

if (is_admin()) {
    require_once MICROBLOG_PLUGIN_PATH . 'admin/microblog-admin.php';
}

add_action('admin_menu', 'microblog_setting_page');

/* 加载翻译文件 */
function microblog_load_textdomain()
{
    load_plugin_textdomain(MICROBLOG_DOMAIN, false, dirname(MICROBLOG_PLUGIN_BASENAME) . '/languages/');
}
add_action('init', 'microblog_load_textdomain', 1);

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

function microblog_setting_action_links($links, $file)
{
    if (MICROBLOG_PLUGIN_BASENAME === $file) {
        $links[] = '<a href="' . esc_url(admin_url('tools.php?page=' . MICROBLOG_PLUGIN_BASENAME)) . '">'
            . esc_html__('Settings', MICROBLOG_DOMAIN) . '</a>';
    }

    return $links;
}
add_filter('plugin_action_links', 'microblog_setting_action_links', 10, 2);

/* 激活时写入默认配置 */
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
