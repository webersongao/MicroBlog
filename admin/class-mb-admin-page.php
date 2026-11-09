<?php
/* 后台设置页骨架 */

if (!defined('ABSPATH')) {
    exit;
}

/* 设置页渲染器 */
class MB_Admin_Page
{
    /* 标签页定义 */
    public static function tabs()
    {
        $tabs = array(
            'micropost' => array(
                'label'        => __('Microblog', MICROBLOG_DOMAIN),
                'option_group' => 'microblog_micropost_setting_field',
                'option_name'  => 'microblog_micropost_data',
                'sections'     => array('microblog_micropost_section_name'),
            ),
            'general'   => array(
                'label'        => __('General', MICROBLOG_DOMAIN),
                'option_group' => 'microblog_genaral_plugin_settings',
                'option_name'  => 'microblog_general_data',
                'sections'     => array('microblog_general_section_name'),
            ),
        );

        if (mbfun_get_general_option('mb_liveblog_regist', false)) {
            $tabs['liveblog'] = array(
                'label'        => __('Liveblog', MICROBLOG_DOMAIN),
                'option_group' => 'microblog_liveblog_setting_field',
                'option_name'  => 'microblog_liveblog_data',
                'sections'     => array('microblog_liveblog_section_general'),
            );
        }

        return apply_filters('microblog_admin_tabs', $tabs);
    }

    /* 当前标签页 */
    public static function current_tab($tabs)
    {
        $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'micropost';

        return isset($tabs[$tab]) ? $tab : 'micropost';
    }

    /* 页面 URL */
    public static function url($tab)
    {
        return add_query_arg(
            array(
                'page' => MICROBLOG_BASEFOLDER . '/microblog.php',
                'tab'  => $tab,
            ),
            admin_url('tools.php')
        );
    }

    /* 输出整页 */
    public static function render()
    {
        $tabs = self::tabs();
        $tab  = self::current_tab($tabs);

        echo '<div class="wrap microblog-settings-wrap">';
        self::render_header();

        echo '<h2 class="nav-tab-wrapper">';

        foreach ($tabs as $key => $config) {
            printf(
                '<a href="%1$s" class="nav-tab %2$s">%3$s</a>',
                esc_url(self::url($key)),
                $key === $tab ? 'nav-tab-active' : '',
                esc_html($config['label'])
            );
        }

        echo '</h2>';

        self::render_form($tabs[$tab]);
        echo '</div>';
    }

    /* 页头 */
    private static function render_header()
    {
        $version = mbfun_get_plugin_version();

        echo '<h1 class="wp-heading-inline">' . esc_html__('MicroBlog', MICROBLOG_DOMAIN) . '</h1>';

        if (strlen((string) $version) > 1) {
            echo '<span class="microblog-version">V' . esc_html($version) . '</span>';
        }

        echo '<p class="microblog-desc">'
            . esc_html__('Short-form posts for WordPress, shown with a shortcode or a widget.', MICROBLOG_DOMAIN)
            . '</p>';
    }

    /* 输出某个标签页的表单 */
    private static function render_form($tab)
    {
        settings_errors($tab['option_name']);

        echo '<form method="post" action="' . esc_url(admin_url('options.php')) . '">';
        settings_fields($tab['option_group']);

        foreach ((array) $tab['sections'] as $section) {
            do_settings_sections($section);
        }

        submit_button(esc_html__('Save Changes', MICROBLOG_DOMAIN));
        echo '</form>';
    }

}
