<?php
/**
 * 后台设置页骨架。
 *
 * 三个标签页原本各写一遍「页头 + 标签导航 + settings_errors + form」，
 * 这里统一：标签页只描述数据（option / section），页面输出沿用
 * WordPress 后台原生结构（h1 + nav-tab-wrapper + form-table + submit_button），
 * 保证与系统其它设置页风格一致。
 *
 * @package MicroBlog
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 设置页渲染器。
 */
class MB_Admin_Page
{
    /**
     * 标签页定义。
     *
     * @return array
     */
    public static function tabs()
    {
        $tabs = array(
            'micropost' => array(
                'label'        => '微博',
                'option_group' => 'microblog_micropost_setting_field',
                'option_name'  => 'theme_microblog_data',
                'sections'     => array('microblog_micropost_section_name'),
            ),
            'general'   => array(
                'label'        => '基础设置',
                'option_group' => 'microblog_genaral_plugin_settings',
                'option_name'  => 'microblog_general_data',
                'sections'     => array('microblog_general_section_name'),
            ),
        );

        if (mbfun_get_general_option('msk_liveblog_regist', false)) {
            $tabs['liveblog'] = array(
                'label'        => '连载',
                'option_group' => 'microblog_liveblog_setting_field',
                'option_name'  => 'microblog_liveblog_data',
                'sections'     => array('microblog_liveblog_section_general'),
            );
        }

        return apply_filters('microblog_admin_tabs', $tabs);
    }

    /**
     * 当前标签页（非法值回落到微博）。
     *
     * @param array $tabs 标签页定义。
     * @return string
     */
    public static function current_tab($tabs)
    {
        $tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'micropost';

        return isset($tabs[$tab]) ? $tab : 'micropost';
    }

    /**
     * 页面 URL。
     *
     * @param string $tab 标签页。
     * @return string
     */
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

    /**
     * 输出整页。
     *
     * @return void
     */
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

    /**
     * 页头：原生 h1 + 版本标记 + 一句说明。
     *
     * @return void
     */
    private static function render_header()
    {
        $version = mbfun_get_plugin_version();

        echo '<h1 class="wp-heading-inline">微博 MicroBlog</h1>';

        if (strlen((string) $version) > 1) {
            echo '<span class="microblog-version">V' . esc_html($version) . '</span>';
        }

        echo '<p class="microblog-desc">基于 WordPress 的微博 / 说说：发布短内容，用短代码或小工具展示。</p>';
    }

    /**
     * 输出某个标签页的表单。
     *
     * @param array $tab 标签页定义。
     * @return void
     */
    private static function render_form($tab)
    {
        // 只显示当前标签页自己的提示，避免串页。
        settings_errors($tab['option_name']);

        echo '<form method="post" action="' . esc_url(admin_url('options.php')) . '">';
        settings_fields($tab['option_group']);

        foreach ((array) $tab['sections'] as $section) {
            do_settings_sections($section);
        }

        submit_button('保存设置');
        echo '</form>';
    }
}
