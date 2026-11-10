<?php
/* 基础设置 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_init', 'microblog_general_setting_admin');

/* 注册基础设置 */
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

    add_settings_field(
        'microblog_page_content_order',
        __('Page template content order', MICROBLOG_DOMAIN),
        'microblog_page_content_order_input',
        'microblog_general_section_name',
        'microblog_general_section_base'
    );

    add_settings_field(
        'microblog_page_background',
        __('Single-page background', MICROBLOG_DOMAIN),
        'microblog_page_background_input',
        'microblog_general_section_name',
        'microblog_general_section_base'
    );
}

/* 清洗基础设置 */
function microblog_general_data_sanitize($input)
{
    if (!is_array($input)) {
        return mbfun_get_general_settings();
    }

    $input['mb_liveblog_regist'] = empty($input['mb_liveblog_regist']) ? 0 : 1;

    if (!isset($input['mb_color_mode']) || !in_array($input['mb_color_mode'], array('light', 'dark', 'auto'), true)) {
        $input['mb_color_mode'] = 'auto';
    }

    if (!isset($input['mb_page_content_order'])
        || !in_array($input['mb_page_content_order'], array('content_first', 'list_first'), true)) {
        $input['mb_page_content_order'] = 'content_first';
    }

    /* 键缺失时按「开启」处理，与 mbfun_page_background_enabled() 的缺省值 1 一致；
       正常表单提交会带 hidden 0，仍可显式关闭 */
    if (!array_key_exists('mb_page_background', $input)) {
        $input['mb_page_background'] = 1;
    } else {
        $input['mb_page_background'] = empty($input['mb_page_background']) ? 0 : 1;
    }

    return $input;
}

add_action('update_option_microblog_general_data', function () {
    mbfun_flush_settings_cache('general');

    if (mbfun_get_general_option('mb_liveblog_regist', false) && !post_type_exists('microlive')) {
        mbfun_update_microblog_display_module();
    }

    mbfun_maybe_flush_rewrite_rules();
});

/* 分区说明 */
function microblog_general_section_base_callback()
{
    echo '<p>' . esc_html__('Optional MicroBlog modules. Enable the ones you need.', MICROBLOG_DOMAIN) . '</p>';
}

/* 连载模块开关 */
function microblog_module_display_input()
{
    $value = mbfun_get_general_option('mb_liveblog_regist', false);
    ?>
    <label>
        <input type='hidden' name='microblog_general_data[mb_liveblog_regist]' value='0' />
        <input type='checkbox' name='microblog_general_data[mb_liveblog_regist]' value='1' <?php checked((bool) $value, true); ?> />
        <?php esc_html_e('Enable (turning it off keeps your data and settings)', MICROBLOG_DOMAIN); ?>
    </label>
    <?php
}

/* 前台颜色模式 */
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

function microblog_page_content_order_input()
{
    $value   = mbfun_get_page_content_order();
    $options = array(
        'content_first' => __('Page content before the microblog list', MICROBLOG_DOMAIN),
        'list_first'    => __('Microblog list before page content', MICROBLOG_DOMAIN),
    );

    echo '<select name="microblog_general_data[mb_page_content_order]" id="microblog_page_content_order">';

    foreach ($options as $key => $label) {
        printf(
            '<option value="%1$s" %2$s>%3$s</option>',
            esc_attr($key),
            selected($value, $key, false),
            esc_html($label)
        );
    }

    echo '</select>';
    echo '<p class="description">'
        . esc_html__('Choose whether the page body appears before or after the feed on the "Microblog list (plugin)" page template. The shortcode and widget are not affected.', MICROBLOG_DOMAIN)
        . '</p>';
}

function microblog_page_background_input()
{
    $enabled = mbfun_page_background_enabled();
    ?>
    <label for="microblog_page_background">
        <input type='hidden' name='microblog_general_data[mb_page_background]' value='0' />
        <input type='checkbox' name='microblog_general_data[mb_page_background]' id='microblog_page_background' value='1' <?php checked($enabled, true); ?> />
        <?php esc_html_e('Enable (match the theme\'s single-page background)', MICROBLOG_DOMAIN); ?>
    </label>
    <p class="description">
        <?php esc_html_e('Enabled: the "Microblog list (plugin)" page matches a standard KoneyGao page, with the same card background, spacing, rounded corners, shadow and article-list typography. Disabled: a plain WordPress page with no card background, using the plugin\'s default list styling. Other themes are not affected.', MICROBLOG_DOMAIN); ?>
    </p>
    <?php
}
