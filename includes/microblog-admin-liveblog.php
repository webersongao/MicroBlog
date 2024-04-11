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
 * general Panel
*/

// Function to display microlive settings section
function microblog_microlive_settings_section() {
    ?>
    <div class="microlive_settings_header">
        <?php microlive_settings_section_header(); ?>
    </div>
    <?php settings_errors('microblog_liveblog_data'); ?>
    <form method="post" action="options.php">
        <?php
        settings_fields('microblog_liveblog_plugin_settings');
        do_settings_sections('microblog-liveblog-settings');
        submit_button('保存 设置');
        ?>
    </form>
    <?php
}

function microlive_settings_section_header() {
    echo '<h3>' . esc_html__('MicroLive Settings', 'microblog') . '</h3>';
}



add_action('admin_init', 'microblog_liveblog_setting_admin');
function microblog_liveblog_setting_admin() {

    register_setting(
        'microblog_liveblog_plugin_settings',
        'microblog_liveblog_data',
        'microblog_liveblog_data_sanitize'
    );

        // liveblog settings section
        add_settings_section(
            'general_settings_section_666',
            '</br>微直播',   
            'general_settings_section_666_callback',
            'microblog-liveblog-settings'
        );
    
        add_settings_field(
            'microblog_7777_listnumber',
            '微直数',
            'microblog_7777_listnumber_input',
            'microblog-liveblog-settings',
            'general_settings_section_666'
        );

}


function microblog_liveblog_data_sanitize($input) {



    return $input;
}

// Display shortcode settings section content
function general_settings_section_666_callback() {
    echo '<p>请确认已打开【标题阿嘎】功能 </p>';
}

function microblog_7777_listnumber_input() {
    $options = mbfun_get_live_settings();
    $value = isset($options['mb_co4st_num']) ? intval($options['mb_co4st_num']) : 5;
    $value = max(3, min($value, 20));

    ?>
    <label>
        <input type='number' name='microblog_liveblog_data[mb_co4st_num]' value='<?php echo esc_attr($value); ?>' min='3' max='20' />
        &nbsp;<?php esc_html_e('每页直播显示数量 ( 区间:[3, 20] )', 'microblog'); ?>
    </label>
    <?php
}



?>