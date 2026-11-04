<?php
/*
 * Plugin Name: 微博 MicroBlog
 * Plugin URI: https://www.webersongao.com/tag/microblog
 * Description: 把 WordPress 当微博用：发布短内容，用短代码或小工具展示。
 * Version: 1.8.0
 * Author: WebersonGao
 * Author URI: https://www.webersongao.com
 * Requires at least: 6.0
 * Requires PHP: 7.4
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
define('MICROBLOG_DOMAIN', 'microblogs-lang');
define('MICROBLOG_VERSION', '1.8.0');

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
    'includes/microblog-forward.php',
    'includes/microblog-quick-pub.php',
);

foreach ($microblog_includes as $microblog_file) {
    require_once MICROBLOG_PLUGIN_PATH . $microblog_file;
}

unset($microblog_includes, $microblog_file);

// 连载是可选模块：未启用时不加载它的 12 个文件与前端脚本。
if (mbfun_get_general_option('msk_liveblog_regist', false)) {
    require_once MICROBLOG_PLUGIN_PATH . 'liveblog/liveblog-index.php';
}

// 后台设置页（仅在后台加载）。
if (is_admin()) {
    require_once MICROBLOG_PLUGIN_PATH . 'admin/microblog-admin.php';
}

add_action('admin_menu', 'microblog_setting_page');

/**
 * 注册「工具 → 微博设置」。
 *
 * @return void
 */
function microblog_setting_page()
{
    add_management_page(
        '微博 MicroBlog - 控制面板',
        '微博设置',
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
        $links[] = '<a href="' . esc_url(admin_url('tools.php?page=' . MICROBLOG_PLUGIN_BASENAME)) . '">设置</a>';
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
    if (false === get_option('theme_microblog_data')) {
        add_option('theme_microblog_data', array(
            'mb_title_show'     => true,
            'mb_date_format'    => 'date_human',
            'mb_content_bottom' => array('conbottomuser'),
        ));
    }

    mbfun_flush_settings_cache();
    mbfun_maybe_flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'microblog_plugin_data_activation');
