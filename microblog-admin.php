<?php

/*
PluName: 微博 MicroBlog
PluLink: https://www.webersongao.com/tag/microblog
Desc: 将您的WordPress网站用作微博；在小部件中显示微博或使用短代码显示微博。
Ver: 1.2
Author: WebersonGao
AuthorLink: https://www.webersongao.com
Based on simple-microblogging plugin developed by original Samuel Coskey, Victoria Gitman(http://boolesrings.org),obaby(https://h4ck.org.cn/) Thanks to ChatGPT.
*/

/*
 * Admin Panel
*/

// Enqueue admin scripts and styles
function microblog_admin_enqueue_scripts() {
    $plugin_data = get_plugin_data( __FILE__ ); 
    $plugin_version = ( $plugin_data && isset( $plugin_data['Version'] ) )  ? $plugin_data['Version'] : '1.2';
    wp_enqueue_style( 'microblog-admin-css', plugin_dir_url( __FILE__ ) . 'css/admin-style.css', array(), $plugin_version );
    wp_enqueue_script('microblog-script', plugins_url('js/microblog-script.js', __FILE__), array(), $plugin_version, true);
}
add_action( 'admin_enqueue_scripts', 'microblog_admin_enqueue_scripts' );

// Create admin settings page
function microblog_admin_settings() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h2>控制面板 | 微博MicroBlog</h2>
        <div class="general_settings_header">
           <?php general_settings_section_header(); ?>
        </div>
        <form method="post" action="options.php">
            <?php
            settings_fields('microblog_plugin_settings');
            do_settings_sections('microblog-settings');
            submit_button('保存 设置');
            ?>
        </form>
    </div>

    <?php
}

// Add admin menu
add_action('admin_menu', 'microblog_add_admin_menu');
function microblog_add_admin_menu() {
    add_menu_page('微博 MicroBlog', '微博设置', 'manage_options', 'microblog-settings', 'microblog_admin_settings');
}

// Register plugin settings
add_action('admin_init', 'microblog_plugin_setting_admin');
function microblog_plugin_setting_admin() {
    register_setting(
        'microblog_plugin_settings',
        'microblog_setting_data',
        'microblog_setting_data_sanitize'
    );
    
    // General settings section
    add_settings_section(
        'general_settings_section_base',
        '基本设置',
        'general_settings_section_base_callback',
        'microblog-settings'
    );
    // Add settings fields
    add_settings_field(
        'microblog_post_title_show',
        '标题',
        'microblog_post_title_show_input',
        'microblog-settings',
        'general_settings_section_base'
    );
    add_settings_field(
        'microblog_post_title_listdate',
        '日期',
        'microblog_post_title_listdate_input',
        'microblog-settings',
        'general_settings_section_base'
    );
    add_settings_field(
        'microblog_post_editor_func',
        '编辑器',
        'microblog_post_editor_func_callback',
        'microblog-settings',
        'general_settings_section_base'
    );

    // Shortcode settings section
    add_settings_section(
        'general_settings_section_shortcode',
        '</br>短代码设置',   
        'general_settings_section_shortcode_callback',
        'microblog-settings'
    );

    add_settings_field(
        'microblog_post_title_listnumber',
        '微博数',
        'microblog_post_title_listNumber_input',
        'microblog-settings',
        'general_settings_section_shortcode'
    );
    add_settings_field(
        'microblog_post_title_position',
        '标题位置',
        'microblog_post_title_position_input',
        'microblog-settings',
        'general_settings_section_shortcode'
    );
    add_settings_field(
        'microblog_post_image_lightbox',
        '影箱LightBox',
        'microblog_post_image_lightbox_input',
        'microblog-settings',
        'general_settings_section_shortcode'
    );
}

function microblog_setting_data_sanitize($input) {
    // 更新 register_post_type 的 supports 参数
    update_micropost_type_supports($input);
    // 返回已处理过的选项
    return $input;
}

        
function update_micropost_type_supports($options) {
    if (empty($options)) { return; }
    $supports = array('title', 'editor'); // 默认支持的参数
    // 根据用户选项更新 supports 参数
    if (isset($options['mb_editor_func'])) {
        $editor_func = $options['mb_editor_func'];
        if (in_array('mb_author', $editor_func)) {
            $supports[] = 'author';
        }
        if (in_array('mb_thumbnail', $editor_func)) {
            $supports[] = 'thumbnail';
        }
        if (in_array('mb_excerpt', $editor_func)) {
            $supports[] = 'excerpt';
        }
    }
    // 更新 register_post_type 的 supports 参数
    $args = get_post_type_object('micropost');
    $args->supports = $supports;
    register_post_type('micropost', $args);
}

// Display general settings section content
function general_settings_section_header() {
    $out = '';
    $out .= '<div class="microblog-admin-header" style="margin-bottom: 15px;">';
    $out .= '<div class="microblog-admin-leftbar">';
    $out .= '<span class="microblog-admin-logo">';
    $out .= '<img src="' . esc_url(plugin_dir_url(__FILE__)) . 'images/microblog-logo.png">';
    $out .= '</span>';
    $out .= '<span class="microblog-admin-bar-span">' . esc_html__('MicroBlog - 基于WP的 微博 / 说说 No1', 'microblog') . '</span><span class="microblog-admin-bar-free">' . esc_html__('Free V1.2', 'microblog') . '</span>';
    $out .= '</div>';
    $out .= '</div>';
    echo $out;
}

// Display general settings section content
function general_settings_section_base_callback() {
    echo '<p>请确认已插入 [microblog] 到页面 或 已添加[微博]小工具</p>';
}

// Display shortcode settings section content
function general_settings_section_shortcode_callback() {
    echo '<p>请打开标题显示: 基础设置 -> 标题显示 </p>';
}

// Display settings fields input
function microblog_post_title_show_input() {
    $options = get_option('microblog_setting_data');
    $value = isset($options['mb_title_show']) ? $options['mb_title_show'] : false;
    ?>
    <label>
        <input type='checkbox' name='microblog_setting_data[mb_title_show]' value='1' <?php checked($value, true); ?> />
        是否显示标题
    </label>
    <?php
}

function microblog_post_title_listdate_input() {
    $options = get_option('microblog_setting_data');
    $value = isset($options['mb_date_show']) ? $options['mb_date_show'] : false;
    ?>
    <label>
        <input type='checkbox' name='microblog_setting_data[mb_date_show]' value='1' <?php checked($value, true); ?> />
        是否显示时间
    </label>
    <?php
}


// Display settings fields input for editor functionality
function microblog_post_editor_func_callback() {
    $options = get_option('microblog_setting_data');
    $editor_func = isset($options['mb_editor_func']) ? $options['mb_editor_func'] : array();
    ?>
    <label class="microblog-admin-checkbox-label">
        <input type='checkbox' name='microblog_setting_data[mb_editor_func][]' value='mb_author' <?php if (in_array('mb_author', $editor_func)) echo 'checked="checked"'; ?> />
        作者
    </label>
    <label class="microblog-admin-checkbox-label">
        <input type='checkbox' name='microblog_setting_data[mb_editor_func][]' value='mb_thumbnail' <?php if (in_array('mb_thumbnail', $editor_func)) echo 'checked="checked"'; ?> />
        特色图片
    </label>
    <label class="microblog-admin-checkbox-label">
        <input type='checkbox' name='microblog_setting_data[mb_editor_func][]' value='mb_excerpt' <?php if (in_array('mb_excerpt', $editor_func)) echo 'checked="checked"'; ?> />
        微博摘要
    </label>
    <?php
}


function microblog_post_title_listNumber_input() {
    $options = get_option('microblog_setting_data');
    $value = isset($options['mb_codepost_num']) ? intval($options['mb_codepost_num']) : 5;
    $value = max(3, min($value, 20));

    ?>
    <label>
        <input type='number' name='microblog_setting_data[mb_codepost_num]' value='<?php echo esc_attr($value); ?>' min='3' max='20' />
        &nbsp;<?php esc_html_e('每页显示数量 ( 区间:[3, 20] )', 'microblog'); ?>
    </label>
    <?php
}

function microblog_post_image_lightbox_input() {
    $options = get_option('microblog_setting_data');
    $value = isset($options['mb_image_lightbox']) ? $options['mb_image_lightbox'] : false;
    ?>
    <label>
    <input type='checkbox' name='microblog_setting_data[mb_image_lightbox]' value='1' <?php checked($value, true); ?> />
    是否开启？（请确认已安装 
    <a href="https://wordpress.org/plugins/simple-lightbox" target="_blank">Simple Lightbox插件</a> 
    或者 当前主题支持 <a href="https://fooplugins.com/what-is-a-lightbox-in-wordpress" target="_blank">Lightbox效果</a> ）
    </label>
    <?php
}

function microblog_post_title_position_input() {
    $options = get_option('microblog_setting_data');
    $value = isset($options['mb_title_position']) ? $options['mb_title_position'] : array();
    ?>
    <label class="microblog-admin-checkbox-label">
        <input type='checkbox' name='microblog_setting_data[mb_title_position][]' value='<?php echo esc_attr('titletop'); ?>' <?php if (in_array('titletop', $value)) echo 'checked="checked"'; ?> />
        头像下方
    </label>
    <label>
        <input type='checkbox' name='microblog_setting_data[mb_title_position][]' value='<?php echo esc_attr('titlebottom'); ?>' <?php if (in_array('titlebottom', $value)) echo 'checked="checked"'; ?> />
        正文下方（评论按钮左侧）
    </label>
    <?php
}

?>