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

    add_settings_field(
        'microblog_color_mode',
        __('Color mode', MICROBLOG_DOMAIN),
        'microblog_color_mode_input',
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

    $input['mb_liveblog_regist'] = empty($input['mb_liveblog_regist']) ? 0 : 1;

    if (!isset($input['mb_color_mode']) || !in_array($input['mb_color_mode'], array('light', 'dark', 'auto'), true)) {
        $input['mb_color_mode'] = 'auto';
    }

    return $input;
}

// 模块开关落库后再同步文章类型，避免读到旧值。
add_action('update_option_microblog_general_data', function () {
    mbfun_flush_settings_cache('general');

    if (mbfun_get_general_option('mb_liveblog_regist', false) && !post_type_exists('microlive')) {
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
    $value = mbfun_get_general_option('mb_liveblog_regist', false);
    ?>
    <label>
        <!-- 隐藏域保证取消勾选时 $_POST 里仍有该键，否则整个数组缺失导致保存无效 -->
        <input type='hidden' name='microblog_general_data[mb_liveblog_regist]' value='0' />
        <input type='checkbox' name='microblog_general_data[mb_liveblog_regist]' value='1' <?php checked((bool) $value, true); ?> />
        <?php esc_html_e('Enable (turning it off keeps your data and settings)', MICROBLOG_DOMAIN); ?>
    </label>
    <?php
}

/**
 * 前台颜色模式。
 *
 * 影响插件所有前台输出：微博短代码、小工具、连载区块。
 * auto 在宿主主题提供了 data-color-mode 时跟随主题，否则跟随设备。
 *
 * @return void
 */
function microblog_color_mode_input()
{
    $value   = mbfun_get_color_mode();
    $options = array(
        'auto'  => __('Automatic (follow device)', MICROBLOG_DOMAIN),
        'light' => __('Light', MICROBLOG_DOMAIN),
        'dark'  => __('Dark', MICROBLOG_DOMAIN),
    );

    echo '<select name="microblog_general_data[mb_color_mode]" id="microblog_color_mode">';

    foreach ($options as $key => $label) {
        printf(
            '<option value="%1$s" %2$s>%3$s</option>',
            esc_attr($key),
            selected($value, $key, false),
            esc_html($label)
        );
    }

    echo '</select>';
    echo '<p class="description">' . esc_html__('Applies to the microblog shortcode, the widget and liveblog blocks. Automatic follows your theme when it supports color modes (including the visitor switch in the header), and the visitor device otherwise.', MICROBLOG_DOMAIN) . '</p>';
}
