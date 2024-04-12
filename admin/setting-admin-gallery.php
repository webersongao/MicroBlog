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

// Function to display Gallery settings section
function microblog_gallery_settings_section() {
    ?>
    <div class="gallery_settings_header">
        <?php microblog_gallery_settings_section_header(); ?>
    </div>
    <?php settings_errors('microblog_gallery_data'); ?>
    <form method="post" action="options.php">
        <?php
        settings_fields('microblog_gallery_plugin_settings');
        do_settings_sections('microblog-gallery-settings');
        submit_button('保存 设置');
        ?>
    </form>
    <?php
}

function microblog_gallery_settings_section_header() {
    echo '</br>';
    // echo '<h3>' . esc_html__('Gallery Tab', 'microblog') . '</h3>';
}

add_action('admin_init', 'microblog_gallery_setting_admin');
function microblog_gallery_setting_admin() {

    register_setting(
        'microblog_gallery_plugin_settings',
        'microblog_gallery_data',
        'microblog_gallery_data_sanitize'
    );

    // Gallery settings section
    add_settings_section(
        'microblog_gallery_section_base',
        '微相册设置',   
        'microblog_gallery_section_base_callback',
        'microblog-gallery-settings'
    );
    
    add_settings_field(
        'microblog_77111_listnumber',
        '微98数',
        'microblog_77111_listnumber_input',
        'microblog-gallery-settings',
        'microblog_gallery_section_base'
    );

}


function microblog_gallery_data_sanitize($input) {



    return $input;
}

// Display shortcode settings section content
function microblog_gallery_section_base_callback() {
    echo '<p>请确认已打开【微相册】功能 </p>';
}

function microblog_77111_listnumber_input() {
    $options = mbfun_get_gallery_settings();
    $value = isset($options['mb_co4st_num']) ? intval($options['mb_co4st_num']) : 5;
    $value = max(3, min($value, 20));

    ?>
    <label>
        <input type='number' name='microblog_gallery_data[mb_co4st_num]' value='<?php echo esc_attr($value); ?>' min='3' max='20' />
        &nbsp;<?php esc_html('每页直播显示数量 ( 区间:[3, 20] )', 'microblog'); ?>
    </label>
    <?php
}



?>