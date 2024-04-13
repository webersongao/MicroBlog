<?php
/*
 * Plugin Name: 微博 MicroBlog
 * Plugin URI: https://www.webersongao.com/tag/microblog
 * Description: Use your WordPress site as a microblog; display the micrposts in a widget or using a shortcode.
 * Version: 1.7.0
 * Author: WebersonGao
 * Author URI: https://www.webersongao.com
 * Based on simple-microblogging plugin developed by Samuel Coskey, Victoria Gitman(http://boolesrings.org),Thanks to obaby(https://h4ck.org.cn/) Thanks to ChatGPT.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define('MICROBLOG_PLUGIN_FILE', __FILE__);
define( 'MICROBLOG_DOMAIN', 'microblogs-lang' );
define('MICROBLOG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MICROBLOG_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MICROBLOG_BASEFOLDER', plugin_basename(dirname(__FILE__)));


$microblog_slug_name = 'microposts';
$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'));
$microblog_plugin_version = ($plugin_data && isset($plugin_data['Version'])) ? $plugin_data['Version'] : '1.7.0';

global $microblog_slug_name , $microblog_plugin_version;

// 核心功能函数
require_once(plugin_dir_path(__FILE__) . 'includes/micropost-functions.php');

// 加载其他功能模块文件
require_once(plugin_dir_path(__FILE__) . 'liveblog/liveblog-index.php');
require_once(plugin_dir_path(__FILE__) . 'includes/microblog-index.php');

require_once(plugin_dir_path(__FILE__) . 'includes/microblog-widget.php');
require_once(plugin_dir_path(__FILE__) . 'includes/microblog-shortcode.php');
// 微博转发功能，此功能需要主题支持，默认不开启
// require_once(plugin_dir_path(__FILE__) . 'includes/microblog-forward.php');
require_once(plugin_dir_path(__FILE__) . 'includes/microblog-quick-pub.php');



// 注册微博设置页面
add_action('admin_menu', 'microblog_setting_page');
function microblog_setting_page() {
    if (!function_exists('microblog_admin_settings')) {
        require_once 'admin/microblog-admin.php';
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

// 注册激活插件时，设置默认数据
register_activation_hook( __FILE__, 'microblog_plugin_data_activation' );
function microblog_plugin_data_activation() {
    $options = mbfun_get_micropost_settings();
    if (empty($options)) {
        $defaults = array(
            'mb_title_show' => true,
            'mb_date_format' => 'date_human',
            'mb_title_position' => array('titlebottom'),
        );
        add_option('microblog_micropost_data', $defaults);
    }
}

// 注册卸载插件时运行的函数
register_uninstall_hook( __FILE__, 'microblog_plugin_uninstall' );
function microblog_plugin_uninstall() {
    // 删除配置项

}

?>
