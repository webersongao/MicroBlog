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
        self::render_upgrade_box();
        self::render_upgrade_notice();
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

        echo '<h1 class="wp-heading-inline">' . esc_html__('MicroBlog', MICROBLOG_DOMAIN) . '</h1>';

        if (strlen((string) $version) > 1) {
            echo '<span class="microblog-version">V' . esc_html($version) . '</span>';
        }

        echo '<p class="microblog-desc">'
            . esc_html__('Short-form posts for WordPress, shown with a shortcode or a widget.', MICROBLOG_DOMAIN)
            . '</p>';
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

        submit_button(esc_html__('Save Changes', MICROBLOG_DOMAIN));
        echo '</form>';
    }

    /**
     * 输出「数据库升级」区块：说明 + 独立按钮。
     *
     * 与设置表单分开（表单提交走 options.php，这里走 admin-post.php）。
     *
     * @return void
     */
    private static function render_upgrade_box()
    {
        $url = wp_nonce_url(admin_url('admin-post.php?action=microblog_db_upgrade'), 'microblog_db_upgrade');

        echo '<div class="microblog-db-upgrade" style="margin-top:24px;padding:16px;background:#fff;border:1px solid #c3c4c7;border-left-width:4px;">';
        echo '<h2 class="title" style="margin-top:0;">' . esc_html__('Database upgrade', MICROBLOG_DOMAIN) . '</h2>';
        echo '<p class="description" style="max-width:640px;">'
            . esc_html__('Manually migrate legacy option names and setting keys from older versions to the current naming scheme. Safe to run anytime.', MICROBLOG_DOMAIN)
            . '</p>';
        echo '<p><a class="button button-secondary" href="' . esc_url($url) . '">'
            . esc_html__('Run upgrade', MICROBLOG_DOMAIN)
            . '</a></p>';
        echo '</div>';
    }

    /**
     * 输出「数据库升级」结果通知（带回来的是只读标记，仅按 0/1 取值）。
     *
     * @return void
     */
    private static function render_upgrade_notice()
    {
        if (!isset($_GET['mb_upgrade'])) {
            return;
        }

        $moved = !empty($_GET['mb_o']);
        $keys  = !empty($_GET['mb_k']);

        echo '<div class="notice notice-success is-dismissible"><p><strong>'
            . esc_html__('Database upgrade', MICROBLOG_DOMAIN)
            . '</strong></p><ul style="margin-left:18px;list-style:disc;">';

        if ($moved) {
            echo '<li>' . esc_html__('Migrated the legacy option "theme_microblog_data" to "microblog_micropost_data".', MICROBLOG_DOMAIN) . '</li>';
        }

        if ($keys) {
            echo '<li>' . esc_html__('Renamed the legacy setting key "msk_liveblog_regist" to "mb_liveblog_regist".', MICROBLOG_DOMAIN) . '</li>';
        }

        if (!$moved && !$keys) {
            echo '<li>' . esc_html__('Database is up to date. Nothing to migrate.', MICROBLOG_DOMAIN) . '</li>';
        }

        echo '</ul></div>';
    }

    /**
     * 处理「数据库升级」按钮（admin-post）。
     *
     * 手动触发旧配置迁移并把结果带回设置页展示，便于旧库主动升级。
     *
     * @return void
     */
    public static function handle_db_upgrade()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to do this.', MICROBLOG_DOMAIN), 403);
        }

        check_admin_referer('microblog_db_upgrade');

        $result = microblog_migrate_legacy_option();

        wp_safe_redirect(add_query_arg(
            array(
                'page'       => MICROBLOG_BASEFOLDER . '/microblog.php',
                'tab'        => 'micropost',
                'mb_upgrade' => 1,
                'mb_o'       => (int) $result['option_moved'],
                'mb_k'       => (int) $result['subkey_renamed'],
            ),
            admin_url('tools.php')
        ));
        exit;
    }
}
add_action('admin_post_microblog_db_upgrade', array('MB_Admin_Page', 'handle_db_upgrade'));
