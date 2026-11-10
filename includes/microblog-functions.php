<?php
/* 公共函数层 */

if (!defined('ABSPATH')) {
    exit;
}

/* 配置 */

/* 微博配置 */
function mbfun_get_micropost_settings()
{
    return mbfun_get_settings('micropost');
}

/* 微博单项配置 */
function mbfun_get_micropost_option($key = '', $default = false)
{
    return mbfun_setting($key, $default, 'micropost');
}

/* 微博列表共用的显示设置（短代码 / 小工具 / 页面模板） */
function mbfun_get_micropost_list_settings()
{
    $options  = mbfun_get_micropost_settings();
    $per_page = isset($options['mb_codepost_num']) ? (int) $options['mb_codepost_num'] : 5;
    $per_page = min(20, max(3, $per_page > 0 ? $per_page : 5));
    $bottom   = (isset($options['mb_content_bottom']) && is_array($options['mb_content_bottom']))
        ? $options['mb_content_bottom']
        : array();

    return array(
        'per_page'  => $per_page,
        'title_on'  => !empty($options['mb_title_show']),
        'lightbox'  => !empty($options['mb_image_lightbox']),
        'show_user' => in_array('conbottomuser', $bottom, true),
    );
}

/* 微博列表查询 */
function mbfun_get_micropost_list_query($settings, $extra_args = array())
{
    $args = array(
        'post_type'      => 'micropost',
        'orderby'        => 'date',
        'order'          => 'DESC',
        'posts_per_page' => (int) $settings['per_page'],
        'post_status'    => mbfun_get_micropost_types(),
        'paged'          => max(1, (int) get_query_var('paged')),
    );

    return new WP_Query(array_merge($args, (array) $extra_args));
}

/* 连载配置 */
function mbfun_get_liveblog_settings()
{
    return mbfun_get_settings('liveblog');
}

/* 连载单项配置 */
function mbfun_get_liveblog_option($key = '', $default = false)
{
    return mbfun_setting($key, $default, 'liveblog');
}

/* 基础配置 */
function mbfun_get_general_settings()
{
    return mbfun_get_settings('general');
}

/* 基础单项配置 */
function mbfun_get_general_option($key = '', $default = false)
{
    return mbfun_setting($key, $default, 'general');
}

function mbfun_get_page_content_order()
{
    $order = mbfun_get_general_option('mb_page_content_order', 'content_first');

    return in_array($order, array('content_first', 'list_first'), true)
        ? $order
        : 'content_first';
}

function mbfun_page_background_enabled()
{
    $enabled = (bool) mbfun_get_general_option('mb_page_background', 1);

    return (bool) apply_filters('microblog_page_background_enabled', $enabled);
}

/* 前台颜色模式 */
function mbfun_get_color_mode()
{
    $mode = mbfun_get_general_option('mb_color_mode', 'auto');

    if (!in_array($mode, array('light', 'dark', 'auto'), true)) {
        $mode = 'auto';
    }

    return apply_filters('mbfun_color_mode', $mode);
}

/* 容器上的颜色模式属性 */
function mbfun_color_mode_attr()
{
    return ' data-mlb-color-mode="' . esc_attr(mbfun_get_color_mode()) . '"';
}

/* 连载单项选项 */
function mbfun_get_live_option($key = '', $default = false)
{
    $value = mbfun_setting($key, $default, 'liveblog');

    $value = apply_filters('mbfun_get_live_option', $value, $key, $default);

    return apply_filters('mbfun_get_live_option_' . $key, $value, $key, $default);
}

/* 文章类型与 slug */

/* 查询时可用的微博状态 */
function mbfun_get_micropost_types()
{
    $status = array('publish');

    if (current_user_can('read_private_posts')) {
        $status[] = 'private';
    }

    return $status;
}

/* 微博 URL slug */
function mbfun_get_micropost_slug($refresh = false)
{
    static $slug = null;

    if (null !== $slug && !$refresh) {
        return $slug;
    }

    $raw  = (string) mbfun_setting('mb_slug_name', '');
    $slug = preg_replace('/[^a-z0-9]/', '', strtolower((string) sanitize_title($raw)));

    if ('' === $slug) {
        $slug = 'microposts';
    }

    return $slug;
}

/* 微博 slug（主题模板通过 function_exists 调用，保留此别名） */
function mbfun_get_micropost_slug_name()
{
    return mbfun_get_micropost_slug();
}

function mbfun_update_microblog_display_module()
{
    if (!function_exists('mbfun_register_liveblog_args')) {
        return;
    }

    if (mbfun_get_general_option('mb_liveblog_regist', false)) {
        register_post_type('microlive', mbfun_register_liveblog_args());
    } elseif (post_type_exists('microlive')) {
        unregister_post_type('microlive');
    }
}

/* 时间格式 */

/* 按设置格式化微博时间 */
function mbfun_micropost_format_time($post_time)
{
    $format = (string) mbfun_setting('mb_date_format', 'date_human');

    if ('date_hide' === $format) {
        return '';
    }

    if ('date_human' === $format) {
        return mbfun_post_fuzzy_time($post_time);
    }

    return date_i18n(get_option('date_format'), $post_time);
}

/* 模糊时间 */
function mbfun_post_fuzzy_time($post_time)
{
    $diff = current_time('timestamp') - (int) $post_time;

    if ($diff < 60) {
        return __('just now', MICROBLOG_DOMAIN);
    }

    if ($diff < 3600) {
        return sprintf(__('%d minutes ago', MICROBLOG_DOMAIN), (int) round($diff / 60));
    }

    if ($diff < 86400) {
        return sprintf(__('%d hours ago', MICROBLOG_DOMAIN), (int) round($diff / 3600));
    }

    if ($diff < YEAR_IN_SECONDS) {
        return gmdate('m/d H:i', $post_time);
    }

    return date_i18n(get_option('date_format'), $post_time);
}

/* 摘要后缀 */
function mbfun_micropost_excerpt_more()
{
    return ' ...';
}

/* 查询 */

/* 最近微博 */
function mbfun_get_recent_microposts($paged = 1, $atts = array())
{
    $args = wp_parse_args($atts, array(
        'post_type'      => 'micropost',
        'orderby'        => 'date',
        'order'          => 'DESC',
        'posts_per_page' => 10,
        'post_status'    => mbfun_get_micropost_types(),
        'paged'          => (int) $paged,
    ));

    if (!empty($atts['q'])) {
        $args['s'] = sanitize_text_field($atts['q']);
    }

    $query = new WP_Query($args);

    return $query->posts;
}

/* 最近微博摘要 */
function mbfun_get_recent_excerpt_microblogs($atts = array())
{
    $args = wp_parse_args($atts, array(
        'post_type'      => 'micropost',
        'orderby'        => 'date',
        'order'          => 'DESC',
        'posts_per_page' => 10,
        'post_status'    => mbfun_get_micropost_types(),
        'paged'          => 1,
    ));

    if (!empty($atts['q'])) {
        $args['s'] = sanitize_text_field($atts['q']);
    }

    $query  = new WP_Query($args);
    $result = array();

    foreach ($query->posts as $item) {
        $result[] = array(
            'title'        => get_the_title($item),
            'content'      => get_the_content(null, false, $item),
            'permalink'    => get_permalink($item),
            'publish_time' => get_the_date('Y年n月j日', $item),
        );
    }

    wp_reset_postdata();

    return $result;
}

/* 资源与版本 */

/* 前端样式与脚本 */
function mbfun_enqueue_scripts_and_styles()
{
    $version = mbfun_get_plugin_version();

    wp_enqueue_style('microblog-style', mbfun_asset_url('css/microblog-style.css'), array(), $version);
    wp_enqueue_script('microblog-script', mbfun_asset_url('js/microblog-script.js'), array(), $version, true);
}

/* 插件 URL */
function mbfun_get_plugin_url()
{
    return MICROBLOG_PLUGIN_URL;
}

/* 插件目录路径 */
function mbfun_get_plugin_path()
{
    return MICROBLOG_PLUGIN_PATH;
}

function mbfun_get_plugin_version()
{
    if (function_exists('wp_get_environment_type') && 'development' === wp_get_environment_type()) {
        return (string) time();
    }

    return MICROBLOG_VERSION;
}

/* 占位标题 */

/* 占位标题里的日期时间 */
function mbfun_micropost_placeholder_datetime($local_date = '')
{
    $format = trim((string) get_option('date_format') . ' ' . (string) get_option('time_format'));

    if ('' === $format) {
        $format = 'Y-m-d H:i';
    }

    if (is_string($local_date)
        && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $local_date)
        && '0000-00-00' !== substr($local_date, 0, 10)
    ) {
        $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $local_date, wp_timezone());

        if ($dt instanceof DateTimeImmutable) {
            return (string) wp_date($format, $dt->getTimestamp());
        }
    }

    return (string) wp_date($format);
}

function mbfun_micropost_placeholder_title($data, $postarr)
{
    if ('micropost' !== $data['post_type'] || '' !== trim((string) $data['post_title'])) {
        return $data;
    }

    $date = mbfun_micropost_placeholder_datetime(isset($data['post_date']) ? $data['post_date'] : '');

    $data['post_title'] = sprintf(__('Microblog: %s', MICROBLOG_DOMAIN), $date);

    return $data;
}
add_filter('wp_insert_post_data', 'mbfun_micropost_placeholder_title', 10, 2);

/* 重写规则 */

/* 只在签名变化时刷新规则；不再每请求读取 rewrite_rules 做完整校验。
   触发点：插件激活/升级、后台保存设置。404 自愈单独在 template_redirect 处理。 */
function mbfun_maybe_flush_rewrite_rules()
{
    $supports  = (array) mbfun_setting('mb_editor_func', array());
    $structure = (string) get_option('permalink_structure');
    $signature = md5(implode('|', array(
        mbfun_get_micropost_slug(),
        implode(',', $supports),
        MICROBLOG_VERSION,
        $structure,
        mbfun_get_general_option('mb_liveblog_regist', false) ? 'liveblog-on' : 'liveblog-off',
    )));

    if (get_option('microblog_rewrite_signature') === $signature) {
        return false;
    }

    flush_rewrite_rules(false);
    update_option('microblog_rewrite_signature', $signature);

    return true;
}

/* 升级后自动刷新（激活钩子在自动更新时不一定执行） */
function mbfun_check_plugin_version_flush()
{
    $stored = get_option('microblog_plugin_version');
    if ($stored === MICROBLOG_VERSION) {
        return;
    }

    mbfun_create_micropost_type();
    mbfun_maybe_flush_rewrite_rules();
    update_option('microblog_plugin_version', MICROBLOG_VERSION);
}
add_action('admin_init', 'mbfun_check_plugin_version_flush');

/* 微博 slug/编辑器支持变化后刷新 */
add_action('update_option_microblog_micropost_data', 'mbfun_maybe_flush_rewrite_rules');

/* 404 自愈：当访问微博 URL 且是 404 时，5 分钟内最多重建一次规则。
   这覆盖规则被外部插件清空、但签名仍匹配的场景，无需每请求读规则。 */
function mbfun_rewrite_rules_404_heal()
{
    if (!is_404()) {
        return;
    }

    $slug = mbfun_get_micropost_slug();
    if (!$slug) {
        return;
    }

    $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '';
    $home_path    = parse_url(home_url(), PHP_URL_PATH) ?: '';
    if ($home_path && 0 === strpos($request_path, $home_path)) {
        $request_path = substr($request_path, strlen($home_path));
    }

    if (!preg_match('#^/' . preg_quote($slug, '#') . '(/|$)#', $request_path)) {
        return;
    }

    if (get_transient('microblog_rewrite_rules_repair')) {
        return;
    }

    set_transient('microblog_rewrite_rules_repair', 1, 5 * MINUTE_IN_SECONDS);
    flush_rewrite_rules(false);
}
add_action('template_redirect', 'mbfun_rewrite_rules_404_heal', 1);
