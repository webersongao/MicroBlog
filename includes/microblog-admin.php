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

require_once(plugin_dir_path(__FILE__) . 'micropost-functions.php');

// Enqueue admin scripts and styles
function microblog_admin_enqueue_scripts() {
    if (is_admin()) {
        global $plugin_version;
        wp_enqueue_style('microblog-admin-css', plugin_dir_url(__FILE__) . '../css/admin-style.css', array(), $plugin_version);
        wp_enqueue_script('microblog-script', plugin_dir_url(__FILE__) . '../js/admin-scripts.js', array(), $plugin_version, true);
    }
}
add_action('admin_enqueue_scripts', 'microblog_admin_enqueue_scripts');

// Create admin settings page
function microblog_admin_settings() {
    if (!current_user_can('manage_options')) { return; }
    ?>
    <div class="wrap">
        <h2>控制面板 | 微博MicroBlog</h2>
        <div class="general_settings_header">
           <?php general_settings_section_header(); ?>
        </div>
        <?php settings_errors('microblog_setting_data'); ?>
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

    add_settings_field(
        'microblog_post_title_show',
        '标题',
        'microblog_post_title_show_input',
        'microblog-settings',
        'general_settings_section_base'
    );
    add_settings_field(
        'microblog_post_rss_feed',
        'RSS订阅',
        'microblog_post_rss_feed_input',
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
    add_settings_field(
        'microblog_post_title_listdate',
        '时间格式',
        'microblog_post_title_listdate_input',
        'microblog-settings',
        'general_settings_section_base'
    );
    add_settings_field(
        'microblog_post_slug_name',
        'URL slug',
        'microblog_post_slug_name_callback',
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
        '灯箱LightBox',
        'microblog_post_image_lightbox_input',
        'microblog-settings',
        'general_settings_section_shortcode'
    );
}

function microblog_setting_data_sanitize($input) {
    if (isset($input['mb_slug_name']) && $input['mb_slug_name'] !== '') {
        $slug_name = strtolower(sanitize_title($input['mb_slug_name']));
        if (preg_match('/^[a-z0-9]{1,20}$/', $slug_name)) {
            $input['mb_slug_name'] = $slug_name;
            update_global_microblog_option($slug_name);
        } else {
            // 如果 slug 名不符合要求，添加错误消息
            add_settings_error(
                'microblog_setting_data', // 设置页面的唯一标识符
                'invalid-slug', // 错误代码，用于后续检索和处理错误
                'slug不合法，仅支持小写字母和数字，长度在1到10之间。', // 错误消息
                'error' // 消息类型（error, warning, success, info）
            );
            return get_option('microblog_setting_data');
        }
    }
    update_micropost_type_supports($input); // 更新 register_post_type 的 supports 参数
    flush_rewrite_rules(); // 刷新重写规则
    return $input;
}

// 更新自定义文章类型的支持项
function update_micropost_type_supports($options) {
    if (empty($options)) { return; }
    $microtag = false;
    $supports = array('title', 'editor','comments'); // 默认支持的参数
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
        if (in_array('mb_posttag', $editor_func)) {
            $microtag = true;
        }
    }
    register_micropost_type($supports, $microtag); // 更新自定义文章类型的支持项
}



// Display general settings section content
function general_settings_section_header() {
    global $plugin_version;
    $out = '';
    $out .= '<div class="microblog-admin-header" style="margin-bottom: 15px;">';
    $out .= '<div class="microblog-admin-leftbar">';
    $out .= '<span class="microblog-admin-logo">';
    $out .= '<img src="' . esc_url(plugin_dir_url(dirname(__FILE__))) . 'images/microblog-logo.png">';
    $out .= '</span>';
    // translators: %s is replaced with the plugin version
    $out .= '<span class="microblog-admin-bar-span">' . esc_html__('MicroBlog - 基于WP的 微博 / 说说 No1', 'microblog') . '</span>';
    if (strlen(strval($plugin_version)) > 1) {
        // translators: %s 是版本号的占位符
        $free_version_text = sprintf(esc_html__('Free V%s', 'microblog'), $plugin_version);
        $out .= '<a href="https://github.com/webersongao/microblog" target="_blank" class="microblog-admin-bar-free">' . $free_version_text . '</a>';
    }
    $out .= '</div>';
    $out .= '</div>';
    echo wp_kses_post($out);
}


// Display general settings section content
function general_settings_section_base_callback() {
    echo '<p>请确认已插入 [microblog] 到页面 或 已添加 [微博] 小工具</p>';
}

// Display shortcode settings section content
function general_settings_section_shortcode_callback() {
    echo '<p>请确认已打开【标题显示】功能 </p>';
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
    $value = isset($options['mb_date_format']) ? $options['mb_date_format'] : '';
    ?>
    <label class="microblog-admin-option-label">
        <input type='radio' name='microblog_setting_data[mb_date_format]' value='<?php echo esc_attr('date_format_notime'); ?>' <?php checked($value, 'date_format_notime'); ?> />
        不显示时间
        <!--echo date_i18n('m-d H:i', current_time('timestamp'));-->
    </label>
    <label class="microblog-admin-option-label">
        <input type='radio' name='microblog_setting_data[mb_date_format]' value='<?php echo esc_attr('date_format_date'); ?>' <?php checked($value, 'date_format_date'); ?> />
        <?php echo esc_html( date_i18n( get_option('date_format') ) ); ?>
    </label>
    <label class="microblog-admin-option-label">
        <input type='radio' name='microblog_setting_data[mb_date_format]' value='<?php echo esc_attr('date_format_vague'); ?>' <?php checked($value, 'date_format_vague'); ?> />
        1分钟前/3天前...
    </label>
    <?php
}

function microblog_post_rss_feed_input() {
    $options = get_option('microblog_setting_data');
    $value = isset($options['mb_rss_feed']) ? $options['mb_rss_feed'] : false;
    ?>
    <label>
        <input type='checkbox' name='microblog_setting_data[mb_rss_feed]' value='1' <?php checked($value, true); ?> />
        是否加入全站Feed
    </label>
    <?php
}


// Display settings fields input for editor functionality
function microblog_post_editor_func_callback() {
    $options = get_option('microblog_setting_data');
    $editor_func = isset($options['mb_editor_func']) ? $options['mb_editor_func'] : array();
    ?>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='microblog_setting_data[mb_editor_func][]' value='mb_author' <?php if (in_array('mb_author', $editor_func)) echo 'checked="checked"'; ?> />
        作者
    </label>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='microblog_setting_data[mb_editor_func][]' value='mb_thumbnail' <?php if (in_array('mb_thumbnail', $editor_func)) echo 'checked="checked"'; ?> />
        特色图片
    </label>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='microblog_setting_data[mb_editor_func][]' value='mb_posttag' <?php if (in_array('mb_posttag', $editor_func)) echo 'checked="checked"'; ?> />
        话题标签
    </label>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='microblog_setting_data[mb_editor_func][]' value='mb_excerpt' <?php if (in_array('mb_excerpt', $editor_func)) echo 'checked="checked"'; ?> />
        微博摘要
    </label>
    <?php
}

function microblog_post_slug_name_callback() {
    $options = get_option('microblog_setting_data');
    $value = isset($options['mb_slug_name']) ? sanitize_title($options['mb_slug_name']) : ''; // 获取已保存的设置值
    ?>
    <input type='text' name='microblog_setting_data[mb_slug_name]' value='<?php echo esc_attr($value); ?>' maxlength='20' style='width: 120px;' />
    <p class="description">仅支持字母和数字，长度(1,20) 为空则默认microposts（如microposts/feed/ 或 microposts/123.html）</p>
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
    或 当前主题支持 <a href="https://fooplugins.com/what-is-a-lightbox-in-wordpress" target="_blank">Lightbox效果</a> ）
    </label>
    <?php
}

function microblog_post_title_position_input() {
    $options = get_option('microblog_setting_data');
    $value = isset($options['mb_title_position']) ? $options['mb_title_position'] : array();
    ?>
    <label class="microblog-admin-option-label">
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