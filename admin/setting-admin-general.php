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
function microblog_general_settings_section() {
    ?>
    <div class="general_settings_header">
        <?php microblog_general_settings_section_header(); ?>
    </div>
    <?php settings_errors('microblog_general_data'); ?>
    <form method="post" action="options.php">
        <?php if ( isset( $_GET['settings-updated'] ) ) { ?>
            <div class="updated"><p><?php _e( 'Plugin settings have been updated.', MICROBLOG_DOMAIN ); ?></p></div>
        <?php } ?>
        <?php
        settings_fields('microblog_genaral_plugin_settings');
        do_settings_sections('microblog_general_section_name');
        submit_button('保存 设置');
        ?>
    </form>
    <?php
}

function microblog_general_settings_section_header() {
    echo '</br>';
    // echo '<h3>' . esc_html__('Gallery Tab', MICROBLOG_DOMAIN ) . '</h3>';
}

add_action('admin_init', 'microblog_general_setting_admin');
function microblog_general_setting_admin() {

    register_setting(
        'microblog_genaral_plugin_settings',
        'microblog_general_data',
        'microblog_general_data_sanitize'
    );

    // Gallery settings section
    add_settings_section(
        'microblog_general_section_base',
        '基础设置',   
        'microblog_general_section_base_callback',
        'microblog_general_section_name'
    );

    add_settings_field(
        'microblog_module_display',
        '模块',
        'microblog_module_display_input',
        'microblog_general_section_name',
        'microblog_general_section_base'
    );

}


function microblog_general_data_sanitize($input) {


    return $input;
}

// Display shortcode settings section content
function microblog_general_section_base_callback() {
    echo '<p>以下为 MicroBlog 插件的基础配置，请按需调整 </p>';
}

// Display settings fields input
function microblog_module_display_input() {
    $options = mbfun_get_general_settings();
    $editor_func = isset($options['mb_editor_func']) ? $options['mb_editor_func'] : array();
    ?>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='microblog_general_data[mb_editor_func][]' value='mb_author' <?php if (in_array('mb_author', $editor_func)) echo 'checked="checked"'; ?> />
        傻子
    </label>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='microblog_general_data[mb_editor_func][]' value='mb_thumbnail' <?php if (in_array('mb_thumbnail', $editor_func)) echo 'checked="checked"'; ?> />
        呆子
    </label>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='microblog_general_data[mb_editor_func][]' value='mb_posttag' <?php if (in_array('mb_posttag', $editor_func)) echo 'checked="checked"'; ?> />
        聋子
    </label>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='microblog_general_data[mb_editor_func][]' value='mb_excerpt' <?php if (in_array('mb_excerpt', $editor_func)) echo 'checked="checked"'; ?> />
        瞎子
    </label>
    <?php
}



?>