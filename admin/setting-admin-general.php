<?php
/*
 * 基础设置：字段注册与回调。页面骨架见 class-mb-admin-page.php。
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_init', 'microblog_general_setting_admin');

/**
 * 注册基础设置。
 *
 * @return void
 */
function microblog_general_setting_admin()
{
    register_setting(
        'microblog_genaral_plugin_settings',
        'microblog_general_data',
        'microblog_general_data_sanitize'
    );

    add_settings_section(
        'microblog_general_section_base',
        __('General', MICROBLOG_DOMAIN),
        'microblog_general_section_base_callback',
        'microblog_general_section_name'
    );

    add_settings_field(
        'microblog_module_display',
        __('Liveblog', MICROBLOG_DOMAIN),
        'microblog_module_display_input',
        'microblog_general_section_name',
        'microblog_general_section_base'
    );
}

/**
 * 清洗基础设置。
 *
 * @param mixed $input 提交值。
 * @return array
 */
function microblog_general_data_sanitize($input)
{
    if (!is_array($input)) {
        return mbfun_get_general_settings();
    }

    $input['msk_liveblog_regist'] = empty($input['msk_liveblog_regist']) ? 0 : 1;

    return $input;
}

// 模块开关落库后再同步文章类型，避免读到旧值。
add_action('update_option_microblog_general_data', function () {
    mbfun_flush_settings_cache('general');

    if (mbfun_get_general_option('msk_liveblog_regist', false) && !post_type_exists('microlive')) {
        mbfun_update_microblog_display_module();
    }

    mbfun_maybe_flush_rewrite_rules();
});

/**
 * 分区说明。
 *
 * @return void
 */
function microblog_general_section_base_callback()
{
    echo '<p>' . esc_html__('Optional MicroBlog modules. Enable the ones you need.', MICROBLOG_DOMAIN) . '</p>';
}

/**
 * 连载模块开关。
 *
 * @return void
 */
function microblog_module_display_input()
{
    $value = mbfun_get_general_option('msk_liveblog_regist', false);
    ?>
    <label>
        <input type='checkbox' name='microblog_general_data[msk_liveblog_regist]' value='1' <?php checked((bool) $value, true); ?> />
        <?php esc_html_e('Enable (turning it off keeps your data and settings)', MICROBLOG_DOMAIN); ?>
    </label>
    <?php
}
