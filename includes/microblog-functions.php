<?php
/**
 * 公共函数层：文章类型、时间格式、查询、资源注册。
 *
 * 本文件只放「多个模块都要用」的东西；配置读写见 microblog-settings.php，
 * HTML 片段见 microblog-render.php。旧的 mbfun_get_*_option 系列保留为
 * 薄封装，避免第三方或主题调用时中断。
 *
 * @package MicroBlog
 */

if (!defined('ABSPATH')) {
    exit;
}

/** ------------------------------------------------------------------
 * 配置（兼容层）
 * ----------------------------------------------------------------- */

/**
 * 微博配置。
 *
 * @return array
 */
function mbfun_get_micropost_settings()
{
    return mbfun_get_settings('micropost');
}

/**
 * 微博单项配置。
 *
 * @param string $key     键名。
 * @param mixed  $default 缺省值。
 * @return mixed
 */
function mbfun_get_micropost_option($key = '', $default = false)
{
    return mbfun_setting($key, $default, 'micropost');
}

/**
 * 连载配置。
 *
 * @return array
 */
function mbfun_get_liveblog_settings()
{
    return mbfun_get_settings('liveblog');
}

/**
 * 连载单项配置。
 *
 * @param string $key     键名。
 * @param mixed  $default 缺省值。
 * @return mixed
 */
function mbfun_get_liveblog_option($key = '', $default = false)
{
    return mbfun_setting($key, $default, 'liveblog');
}

/**
 * 基础配置。
 *
 * @return array
 */
function mbfun_get_general_settings()
{
    return mbfun_get_settings('general');
}

/**
 * 基础单项配置。
 *
 * @param string $key     键名。
 * @param mixed  $default 缺省值。
 * @return mixed
 */
function mbfun_get_general_option($key = '', $default = false)
{
    return mbfun_setting($key, $default, 'general');
}

/**
 * 前台颜色模式：light / dark / auto。
 *
 * 插件所有前台输出（短代码、小工具、连载区块）共用这一个设置，
 * 避免同一页面上出现两套配色。
 *
 * @return string
 */
function mbfun_get_color_mode()
{
    $mode = mbfun_get_general_option('mb_color_mode', 'auto');

    if (!in_array($mode, array('light', 'dark', 'auto'), true)) {
        $mode = 'auto';
    }

    return apply_filters('mbfun_color_mode', $mode);
}

/**
 * 容器上的颜色模式属性。
 *
 * 只输出属性片段，调用处负责放进标签里。
 *
 * @return string
 */
function mbfun_color_mode_attr()
{
    return ' data-mlb-color-mode="' . esc_attr(mbfun_get_color_mode()) . '"';
}

/**
 * 连载选项（带过滤器，保持向后兼容）。
 *
 * @return array
 */
function mbfun_get_live_options()
{
    return mbfun_get_settings('liveblog');
}

/**
 * 连载单项选项（带过滤器）。
 *
 * @param string $key     键名。
 * @param mixed  $default 缺省值。
 * @return mixed
 */
function mbfun_get_live_option($key = '', $default = false)
{
    $value = mbfun_setting($key, $default, 'liveblog');

    $value = apply_filters('mbfun_get_live_option', $value, $key, $default);

    return apply_filters('mbfun_get_live_option_' . $key, $value, $key, $default);
}

/** ------------------------------------------------------------------
 * 文章类型与 slug
 * ----------------------------------------------------------------- */

/**
 * 查询时可用的微博状态。
 *
 * @return array
 */
function mbfun_get_micropost_types()
{
    $status = array('publish');

    if (current_user_can('read_private_posts')) {
        $status[] = 'private';
    }

    return $status;
}

/**
 * 微博 URL slug（同一请求内只解析一次）。
 *
 * @param bool $refresh 强制重算。
 * @return string
 */
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

/**
 * 微博 slug（旧名，保留兼容）。
 *
 * @return string
 */
function mbfun_get_micropost_slug_name()
{
    return mbfun_get_micropost_slug();
}

/**
 * 微博 slug（旧名，保留兼容）。
 *
 * @return string
 */
function mbfun_get_lasted_micropost_slug_name()
{
    return mbfun_get_micropost_slug();
}

/**
 * 旧接口：曾用于写全局变量，现在只是让 slug 缓存失效。
 *
 * @deprecated 直接调用 mbfun_get_micropost_slug(true)。
 * @param string $new_value 新 slug。
 * @return void
 */
function mbfun_update_global_microblog_slug_name($new_value)
{
    mbfun_get_micropost_slug(true);
}

/**
 * 按「基础设置」里的开关注册/注销连载文章类型。
 *
 * @return void
 */
function mbfun_update_microblog_display_module()
{
    if (!function_exists('mbfun_register_liveblog_args')) {
        return;
    }

    if (mbfun_get_general_option('msk_liveblog_regist', false)) {
        register_post_type('microlive', mbfun_register_liveblog_args());
    } elseif (post_type_exists('microlive')) {
        unregister_post_type('microlive');
    }
}

/** ------------------------------------------------------------------
 * 时间格式
 * ----------------------------------------------------------------- */

/**
 * 按设置格式化微博时间。
 *
 * @param int $post_time 时间戳。
 * @return string
 */
function mbfun_micropost_format_time($post_time)
{
    $format = (string) mbfun_setting('mb_date_format', '');

    if ('date_hide' === $format) {
        return '';
    }

    if ('date_human' === $format) {
        return mbfun_post_fuzzy_time($post_time);
    }

    return date_i18n(get_option('date_format'), $post_time);
}

/**
 * 模糊时间。
 *
 * @param int $post_time 时间戳。
 * @return string
 */
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

/**
 * 摘要后缀。
 *
 * @return string
 */
function mbfun_micropost_excerpt_more()
{
    return ' ...';
}

/** ------------------------------------------------------------------
 * 查询
 * ----------------------------------------------------------------- */

/**
 * 最近微博（WP_Post 数组）。
 *
 * @param int   $paged 页码。
 * @param array $atts  查询参数。
 * @return array
 */
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

/**
 * 最近微博摘要（主题首页调用，返回结构不可改动）。
 *
 * @param array $atts 查询参数。
 * @return array
 */
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

/** ------------------------------------------------------------------
 * 资源与版本
 * ----------------------------------------------------------------- */

/**
 * 前端样式与脚本（重复调用只入队一次）。
 *
 * @return void
 */
function mbfun_enqueue_scripts_and_styles()
{
    $version = mbfun_get_plugin_version();

    wp_enqueue_style('microblog-style', mbfun_asset_url('css/microblog-style.css'), array(), $version);
    wp_enqueue_script('microblog-script', mbfun_asset_url('js/microblog-script.js'), array(), $version, true);
}

/**
 * 插件 URL（带结尾斜杠）。
 *
 * @return string
 */
function mbfun_get_plugin_url()
{
    return MICROBLOG_PLUGIN_URL;
}

/**
 * 插件目录路径（带结尾斜杠）。
 *
 * @return string
 */
function mbfun_get_plugin_path()
{
    return MICROBLOG_PLUGIN_PATH;
}

/**
 * 资源版本号；开发环境用文件修改时间，避免改完看不到效果。
 *
 * @return string
 */
function mbfun_get_plugin_version()
{
    if (function_exists('wp_get_environment_type') && 'development' === wp_get_environment_type()) {
        return (string) time();
    }

    return MICROBLOG_VERSION;
}

/** ------------------------------------------------------------------
 * 占位标题
 * ----------------------------------------------------------------- */

/**
 * 微博发布时若无标题，自动补「Microblog: 2026-10-12」占位标题（随站点语言翻译）。
 *
 * @param array $data    过滤后的文章数据。
 * @param array $postarr 原始数据。
 * @return array
 */
function mbfun_micropost_placeholder_title($data, $postarr)
{
    if ('micropost' !== $data['post_type'] || '' !== trim((string) $data['post_title'])) {
        return $data;
    }

    // 以文章自身发布日期为准（兼容草稿补发/定时发布），本地时区。
    $date = $data['post_date'] ? mysql2date('Y-m-d', $data['post_date'], false) : current_time('Y-m-d');

    $data['post_title'] = sprintf(__('Microblog: %s', MICROBLOG_DOMAIN), $date);

    return $data;
}
add_filter('wp_insert_post_data', 'mbfun_micropost_placeholder_title', 10, 2);

/** ------------------------------------------------------------------
 * 重写规则
 * ----------------------------------------------------------------- */

/**
 * 重写规则签名变化时才刷新，避免每次请求写库。
 *
 * @return bool 是否执行了刷新。
 */
function mbfun_maybe_flush_rewrite_rules()
{
    $supports  = (array) mbfun_setting('mb_editor_func', array());
    $signature = md5(implode('|', array(
        mbfun_get_micropost_slug(),
        implode(',', $supports),
        MICROBLOG_VERSION,
        (string) get_option('permalink_structure'),
        // 连载启停会增删 microlive 文章类型的重写规则，必须参与签名。
        mbfun_get_general_option('msk_liveblog_regist', false) ? 'liveblog-on' : 'liveblog-off',
    )));

    if (get_option('microblog_rewrite_signature') === $signature) {
        return false;
    }

    flush_rewrite_rules(false);
    update_option('microblog_rewrite_signature', $signature);

    return true;
}
