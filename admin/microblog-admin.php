<?php

/*
PluName: 微博 MicroBlog
PluLink: https://www.webersongao.com/tag/microblog
Desc: 将您的WordPress网站用作微博；在小部件中显示微博或使用短代码显示微博。
Author: WebersonGao
AuthorLink: https://www.webersongao.com
Based on simple-microblogging plugin developed by Samuel Coskey, Victoria Gitman(http://boolesrings.org),Thanks to obaby(https://h4ck.org.cn/) Thanks to ChatGPT.
*/

/*
 * Admin Panel
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once(plugin_dir_path(__FILE__) . 'setting-admin-micropost.php');
require_once(plugin_dir_path(__FILE__) . 'setting-admin-general.php');
require_once(plugin_dir_path(__FILE__) . 'setting-admin-liveblog.php');

// Enqueue admin scripts and styles
function microblog_admin_enqueue_scripts() {
    if (is_admin()) {
        global $plugin_version;
        wp_enqueue_style('microblog-admin-css', plugin_dir_url(__FILE__) . '../assets/css/admin-style.css', array(), $plugin_version);
        wp_enqueue_script('microblog-script', plugin_dir_url(__FILE__) . '../assets/js/admin-scripts.js', array(), $plugin_version, true);
    }
}
add_action('admin_enqueue_scripts', 'microblog_admin_enqueue_scripts');

// Create admin settings page
function microblog_admin_settings() {
    if (!current_user_can('manage_options')) { return; }
    ?>
    <div class="wrap">
        <h2>控制面板 | 微博MicroBlog</h2>
        <div class="admin_settings_header">
            <?php microblog_admin_section_header(); ?>
        </div>
        <!-- Add tab navigation -->
        <h2 class="nav-tab-wrapper">
            <a href="?page=<?php echo MICROBLOG_BASEFOLDER; ?>/microblog.php&tab=micropost" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'micropost') ? 'nav-tab-active' : ''; ?>">微博</a>
            <a href="?page=<?php echo MICROBLOG_BASEFOLDER; ?>/microblog.php&tab=liveblog" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'liveblog') ? 'nav-tab-active' : ''; ?>">微连载</a>
            <a href="?page=<?php echo MICROBLOG_BASEFOLDER; ?>/microblog.php&tab=general" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'general') ? 'nav-tab-active' : ''; ?>">基础设置</a>    
        </h2>
        <div class="wrap">
            <?php
            // Display the appropriate settings section based on the selected tab
            if (isset($_GET['tab']) && $_GET['tab'] === 'general') {
                microblog_general_settings_section();
            } elseif (isset($_GET['tab']) && $_GET['tab'] === 'liveblog') {
                microblog_liveblog_settings_section();
            } elseif (isset($_GET['tab']) && $_GET['tab'] === 'micropost') {
                microblog_micropost_settings_section();
            } else {
                microblog_micropost_settings_section();
            }
            ?>
        </div>
    </div>
    <?php
}

// Display Common settings section content
function microblog_admin_section_header() {
    global $plugin_version;
    $out = '';
    $out .= '<div class="microblog-admin-header" style="margin-bottom: 15px;">';
    $out .= '<div class="microblog-admin-leftbar">';
    $out .= '<span class="microblog-admin-logo">';
    $out .= '<img src="' . esc_url(plugin_dir_url(dirname(__FILE__))) . 'assets/images/microblog-logo.png">';
    $out .= '</span>';
    // translators: %s is replaced with the plugin version
    $out .= '<span class="microblog-admin-bar-span">' . esc_html__('MicroBlog - 基于WP的 微博 / 说说 No1', MICROBLOG_DOMAIN ) . '</span>';
    if (strlen(strval($plugin_version)) > 1) {
        $free_version_text = sprintf(esc_html__('Free V%s', MICROBLOG_DOMAIN ), $plugin_version);
        $out .= '<a href="https://github.com/webersongao/microblog" target="_blank" class="microblog-admin-bar-free">' . $free_version_text . '</a>';
    }
    $out .= '</div>';
    $out .= '</div>';
    echo wp_kses_post($out);
}


?>