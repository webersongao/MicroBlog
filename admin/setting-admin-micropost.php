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

// Function to display general settings section
function microblog_micropost_settings_section() {
    ?>
    <div class="general_settings_header">
        <?php microblog_micropost_settings_section_header(); ?>
    </div>
    <?php settings_errors('theme_microblog_data'); ?>
    <form method="post" action="options.php">
        <?php if ( isset( $_GET['settings-updated'] ) ) { ?>
            <div class="updated"><p><?php _e( 'Plugin settings have been updated.', MICROBLOG_DOMAIN ); ?></p></div>
        <?php } ?>
        <?php
        settings_fields('microblog_micropost_setting_field');
        do_settings_sections('microblog_micropost_section_name');
        submit_button('保存 设置');
        ?>
    </form>
    <?php
}

function microblog_micropost_settings_section_header() {
    echo '</br>';
    // echo '<h3>' . esc_html__('General Tab', MICROBLOG_DOMAIN ) . '</h3>';
}

// Register plugin settings
add_action('admin_init', 'microblog_micropost_setting_admin');
function microblog_micropost_setting_admin() {
    register_setting(
        'microblog_micropost_setting_field',
        'theme_microblog_data',
        'theme_microblog_data_sanitize'
    );
    
    // General settings section
    add_settings_section(
        'microblog_micropost_section_base',
        '样式设置',
        'microblog_micropost_section_base_callback',
        'microblog_micropost_section_name'
    );

    add_settings_field(
        'microblog_post_title_show',
        '标题',
        'microblog_post_title_show_input',
        'microblog_micropost_section_name',
        'microblog_micropost_section_base'
    );
    add_settings_field(
        'microblog_post_micro_foward',
        '转发',
        'microblog_post_micro_foward_input',
        'microblog_micropost_section_name',
        'microblog_micropost_section_base'
    );
    add_settings_field(
        'microblog_post_rss_feed',
        'RSS订阅',
        'microblog_post_rss_feed_input',
        'microblog_micropost_section_name',
        'microblog_micropost_section_base'
    );
    add_settings_field(
        'microblog_post_editor_func',
        '编辑器',
        'microblog_post_editor_func_callback',
        'microblog_micropost_section_name',
        'microblog_micropost_section_base'
    );
    add_settings_field(
        'microblog_post_title_listdate',
        '时间格式',
        'microblog_post_title_listdate_input',
        'microblog_micropost_section_name',
        'microblog_micropost_section_base'
    );
    add_settings_field(
        'microblog_post_slug_name',
        'URL slug',
        'microblog_post_slug_name_callback',
        'microblog_micropost_section_name',
        'microblog_micropost_section_base'
    );
    
    // Shortcode settings section
    add_settings_section(
        'microblog_micropost_section_shortcode',
        '</br>短代码设置',   
        'microblog_micropost_section_shortcode_callback',
        'microblog_micropost_section_name'
    );

    add_settings_field(
        'microblog_post_title_listnumber',
        '微博数',
        'microblog_post_title_listNumber_input',
        'microblog_micropost_section_name',
        'microblog_micropost_section_shortcode'
    );
    add_settings_field(
        'microblog_post_title_position',
        '标题位置',
        'microblog_post_title_position_input',
        'microblog_micropost_section_name',
        'microblog_micropost_section_shortcode'
    );
    add_settings_field(
        'microblog_post_image_lightbox',
        '灯箱LightBox',
        'microblog_post_image_lightbox_input',
        'microblog_micropost_section_name',
        'microblog_micropost_section_shortcode'
    );
}

function theme_microblog_data_sanitize($input) {
    if (isset($input['mb_slug_name']) && $input['mb_slug_name'] !== '') {

        $slug_name = strtolower(sanitize_title($input['mb_slug_name']));

        if (preg_match('/^[a-z0-9]{1,20}$/', $slug_name)) {
            $input['mb_slug_name'] = $slug_name;
            mbfun_update_global_microblog_slug_name($slug_name);
        } else {
            // 如果 slug 名不符合要求，添加错误消息
            add_settings_error(
                'theme_microblog_data', // 设置页面的唯一标识符
                'invalid-slug', // 错误代码，用于后续检索和处理错误
                'slug不合法，仅支持小写字母和数字，长度在1到10之间。', // 错误消息
                'error' // 消息类型（error, warning, success, info）
            );
            return mbfun_get_micropost_settings();
        }
    }

    microblog_regist_micropost_with_option($input,false); // 更新 注册微博文章类型函数 的 supports 参数
    flush_rewrite_rules(); // 刷新重写规则
    return $input;
}


// Display general settings section content
function microblog_micropost_section_base_callback() {
    echo '<p>请确认已插入 [microblog] 到页面 或 已添加 [微博] 小工具</p>';
}

// Display shortcode settings section content
function microblog_micropost_section_shortcode_callback() {
    echo '<p>请确认已打开【标题显示】功能 </p>';
}

// Display settings fields input
function microblog_post_title_show_input() {
    $options = mbfun_get_micropost_settings();
    $value = isset($options['mb_title_show']) ? $options['mb_title_show'] : false;
    ?>
    <label>
        <input type='checkbox' name='theme_microblog_data[mb_title_show]' value='1' <?php checked($value, true); ?> />
        显示标题
    </label>
    <?php
}

function microblog_post_title_listdate_input() {
    $options = mbfun_get_micropost_settings();
    $value = isset($options['mb_date_format']) ? $options['mb_date_format'] : '';
    ?>
    <label class="microblog-admin-option-label">
        <input type='radio' name='theme_microblog_data[mb_date_format]' value='<?php echo esc_attr('date_hide'); ?>' <?php checked($value, 'date_hide'); ?> />
        不显示时间
        <!--echo date_i18n('m-d H:i', current_time('timestamp'));-->
    </label>
    <label class="microblog-admin-option-label">
        <input type='radio' name='theme_microblog_data[mb_date_format]' value='<?php echo esc_attr('date_date'); ?>' <?php checked($value, 'date_date'); ?> />
        <?php echo esc_html( date_i18n( get_option('date_format') ) ); ?>
    </label>
    <label class="microblog-admin-option-label">
        <input type='radio' name='theme_microblog_data[mb_date_format]' value='<?php echo esc_attr('date_human'); ?>' <?php checked($value, 'date_human'); ?> />
        1分钟前/3天前...
    </label>
    <?php
}

function microblog_post_micro_foward_input() {
    $options = mbfun_get_micropost_settings();
    $value = isset($options['mb_foward_enable']) ? $options['mb_foward_enable'] : false;
    ?>
    <label>
        <input type='checkbox' name='theme_microblog_data[mb_foward_enable]' value='1' <?php checked($value, true); ?> />
        启用微博转发
    </label>
    <?php
}

function microblog_post_rss_feed_input() {
    $options = mbfun_get_micropost_settings();
    $value = isset($options['mb_rss_feed']) ? $options['mb_rss_feed'] : false;
    ?>
    <label>
        <input type='checkbox' name='theme_microblog_data[mb_rss_feed]' value='1' <?php checked($value, true); ?> />
        加入全站Feed流（ <a target="_blank" href="/feed" class="rss">RSS</a> ）
    </label>
    <?php
}


// Display settings fields input for editor functionality
function microblog_post_editor_func_callback() {
    $options = mbfun_get_micropost_settings();
    $editor_func = isset($options['mb_editor_func']) ? $options['mb_editor_func'] : array();
    ?>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='theme_microblog_data[mb_editor_func][]' value='mb_author' <?php if (in_array('mb_author', $editor_func)) echo 'checked="checked"'; ?> />
        作者
    </label>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='theme_microblog_data[mb_editor_func][]' value='mb_excerpt' <?php if (in_array('mb_excerpt', $editor_func)) echo 'checked="checked"'; ?> />
        摘要
    </label>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='theme_microblog_data[mb_editor_func][]' value='mb_thumbnail' <?php if (in_array('mb_thumbnail', $editor_func)) echo 'checked="checked"'; ?> />
        特色图片
    </label>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='theme_microblog_data[mb_editor_func][]' value='mb_posttag' <?php if (in_array('mb_posttag', $editor_func)) echo 'checked="checked"'; ?> />
        微博话题
    </label>
    <?php
}

function microblog_post_slug_name_callback() {
    $options = mbfun_get_micropost_settings();
    $value = isset($options['mb_slug_name']) ? sanitize_title($options['mb_slug_name']) : ''; // 获取已保存的设置值
    ?>
    <input type='text' name='theme_microblog_data[mb_slug_name]' value='<?php echo esc_attr($value); ?>' maxlength='20' style='width: 120px;' />
    <p class="description">仅支持字母和数字，长度(1,20) 为空则默认microposts（如microposts/feed/ 或 microposts/123.html）</p>
    <?php
}

function microblog_post_title_listNumber_input() {
    $options = mbfun_get_micropost_settings();
    $value = isset($options['mb_codepost_num']) ? intval($options['mb_codepost_num']) : 5;
    $value = max(3, min($value, 20));

    ?>
    <label>
        <input type='number' name='theme_microblog_data[mb_codepost_num]' value='<?php echo esc_attr($value); ?>' min='3' max='20' />
        &nbsp;<?php esc_html('每页显示数量 ( 区间:[3, 20] )', MICROBLOG_DOMAIN ); ?>
    </label>
    <?php
}

function microblog_post_image_lightbox_input() {
    $options = mbfun_get_micropost_settings();
    $value = isset($options['mb_image_lightbox']) ? $options['mb_image_lightbox'] : false;
    ?>
    <label>
    <input type='checkbox' name='theme_microblog_data[mb_image_lightbox]' value='1' <?php checked($value, true); ?> />
    是否开启？（请确认已安装 
    <a href="https://wordpress.org/plugins/simple-lightbox" target="_blank">Simple Lightbox插件</a> 
    或 当前主题支持 <a href="https://fooplugins.com/what-is-a-lightbox-in-wordpress" target="_blank">Lightbox效果</a> ）
    </label>
    <?php
}

function microblog_post_title_position_input() {
    $options = mbfun_get_micropost_settings();
    $value = isset($options['mb_title_position']) ? $options['mb_title_position'] : array();
    ?>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='theme_microblog_data[mb_title_position][]' value='<?php echo esc_attr('titletop'); ?>' <?php if (in_array('titletop', $value)) echo 'checked="checked"'; ?> />
        头像下方
    </label>
    <label>
        <input type='checkbox' name='theme_microblog_data[mb_title_position][]' value='<?php echo esc_attr('titlebottom'); ?>' <?php if (in_array('titlebottom', $value)) echo 'checked="checked"'; ?> />
        正文下方（评论按钮左侧）
    </label>
    <?php
}

?>