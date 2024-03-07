<?php
/*
 * Plugin Name: 微博 MicroBlog
 * Plugin URI: https://www.webersongao.com/tag/microblog
 * Description: Use your WordPress site as a microblog; display the micrposts in a widget or using a shortcode.
 * Version: 1.6
 * Author: WebersonGao
 * Author URI: https://www.webersongao.com
 * Based on simple-microblogging plugin developed by original Samuel Coskey, Victoria Gitman(http://boolesrings.org),obaby(https://h4ck.org.cn/) Thanks to ChatGPT.
 */

define('MICROBLOG_BASEFOLDER', plugin_basename(dirname(__FILE__)));
define('MICROBLOG_PLUGIN_FILE', __FILE__);
define('MICROBLOG_PLUGIN_URL', plugin_dir_url(__FILE__));

$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'));
$plugin_version = ($plugin_data && isset($plugin_data['Version'])) ? $plugin_data['Version'] : '1.6';
global $plugin_version;

// 加载其他功能模块文件
require_once(plugin_dir_path(__FILE__) . 'includes/microblog-index.php');
require_once(plugin_dir_path(__FILE__) . 'includes/microblog-widget.php');
require_once(plugin_dir_path(__FILE__) . 'includes/microblog-shortcode.php');
require_once(plugin_dir_path(__FILE__) . 'includes/microblog-quick-pub.php');
require_once(plugin_dir_path(__FILE__) . 'includes/micropost-functions.php');

// 注册微博设置页面
add_action('admin_menu', 'microblog_setting_page');
function microblog_setting_page() {
    if (!function_exists('microblog_admin_settings')) {
        require_once 'includes/microblog-admin.php';
    }
    add_management_page('微博 MicroBlog - 控制面板', '微博设置', 'manage_options', __FILE__, 'microblog_admin_settings');
}

//添加插件设置链接
add_filter('plugin_action_links', 'microblog_setting_action_links', 10, 2);
function microblog_setting_action_links($links, $file) {
    if ($file == plugin_basename(__FILE__)) {
        $links[] = '<a href="tools.php?page=' . MICROBLOG_BASEFOLDER . '/microblog.php">设置</a>';
    }
    return $links;
}

?>
