<?php
/**
 * 微博文章类型、话题分类法、Feed 与固定链接。
 *
 * @package MicroBlog
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 注册微博文章类型。
 *
 * @return void
 */
function mbfun_create_micropost_type()
{
    $supports = array('title', 'editor', 'comments');

    foreach ((array) mbfun_setting('mb_editor_func', array()) as $feature) {
        if ('mb_author' === $feature) {
            $supports[] = 'author';
        } elseif ('mb_thumbnail' === $feature) {
            $supports[] = 'thumbnail';
        } elseif ('mb_excerpt' === $feature) {
            $supports[] = 'excerpt';
        }
    }

    mbfun_register_micropost_type(array_unique($supports), in_array('mb_posttag', (array) mbfun_setting('mb_editor_func', array()), true));
}
add_action('init', 'mbfun_create_micropost_type', 5);

/**
 * 注册文章类型；同时按设置决定是否挂载话题分类法。
 *
 * @param array $supports 编辑器支持项。
 * @param bool  $microtag 是否启用话题。
 * @return void
 */
function mbfun_register_micropost_type($supports = array(), $microtag = false)
{
    if (!$supports) {
        $supports = array('title', 'editor', 'comments');
    }

    register_post_type('micropost', array(
        'label'       => __('微博', MICROBLOG_DOMAIN),
        'labels'      => mbfun_get_micropost_labels(),
        'has_archive' => true,
        'public'      => true,
        'menu_icon'   => 'dashicons-format-status',
        'menu_position' => 6,
        'rest_base'   => 'microposts',
        'rewrite'     => array('slug' => mbfun_get_micropost_slug()),
        'supports'    => $supports,
        'show_in_rest' => use_block_editor_for_post_type('post'),
        'description' => __('通过 MicroBlog 可以快速发布一条微博，再用短代码或小工具展示。', MICROBLOG_DOMAIN),
    ));

    if ($microtag) {
        mbfun_register_micropost_taxonomy();
    }
}

/**
 * 微博文章类型的标签。
 *
 * @return array
 */
function mbfun_get_micropost_labels()
{
    return array(
        'name'                     => __('微博', MICROBLOG_DOMAIN),
        'menu_name'                => __('微博', MICROBLOG_DOMAIN),
        'singular_name'            => __('微博', MICROBLOG_DOMAIN),
        'add_new'                  => __('发微博', MICROBLOG_DOMAIN),
        'add_new_item'             => __('写微博', MICROBLOG_DOMAIN),
        'new_item'                 => __('新微博', MICROBLOG_DOMAIN),
        'all_items'                => __('微博', MICROBLOG_DOMAIN),
        'view_items'               => __('微博列表', MICROBLOG_DOMAIN),
        'view_item'                => __('查看微博', MICROBLOG_DOMAIN),
        'search_items'             => __('搜索微博', MICROBLOG_DOMAIN),
        'edit_item'                => __('编辑该微博', MICROBLOG_DOMAIN),
        'not_found'                => __('未找到相关微博', MICROBLOG_DOMAIN),
        'not_found_in_trash'       => __('回收站中没有微博', MICROBLOG_DOMAIN),
        'item_updated'             => __('微博已更新', MICROBLOG_DOMAIN),
        'item_published'           => __('微博已发布', MICROBLOG_DOMAIN),
        'item_published_privately' => __('该微博已私密发布', MICROBLOG_DOMAIN),
        'item_reverted_to_draft'   => __('该微博已恢复为草稿', MICROBLOG_DOMAIN),
        'item_scheduled'           => __('该微博已加入发布计划', MICROBLOG_DOMAIN),
        'featured_image'           => __('微博头图', MICROBLOG_DOMAIN),
        'set_featured_image'       => __('设置微博头图', MICROBLOG_DOMAIN),
        'use_featured_image'       => __('使用微博头图', MICROBLOG_DOMAIN),
        'remove_featured_image'    => __('移除微博头图', MICROBLOG_DOMAIN),
        'filter_items_list'        => __('筛选微博列表', MICROBLOG_DOMAIN),
        'filter_by_date'           => __('按微博日期筛选', MICROBLOG_DOMAIN),
        'items_list'               => __('微博列表', MICROBLOG_DOMAIN),
        'items_list_navigation'    => __('微博列表导航', MICROBLOG_DOMAIN),
    );
}

/**
 * 微博话题分类法。
 *
 * @return void
 */
function mbfun_register_micropost_taxonomy()
{
    register_taxonomy('micropost_topic', 'micropost', array(
        'label'        => __('微博话题', MICROBLOG_DOMAIN),
        'labels'       => array(
            'name'                       => __('微博话题', MICROBLOG_DOMAIN),
            'singular_name'              => __('微博话题', MICROBLOG_DOMAIN),
            'all_items'                  => __('全部话题', MICROBLOG_DOMAIN),
            'add_new_item'               => __('添加新话题', MICROBLOG_DOMAIN),
            'search_items'               => __('搜索话题', MICROBLOG_DOMAIN),
            'popular_items'              => __('常用话题', MICROBLOG_DOMAIN),
            'edit_item'                  => __('编辑话题', MICROBLOG_DOMAIN),
            'view_item'                  => __('查看话题', MICROBLOG_DOMAIN),
            'update_item'                => __('更新话题', MICROBLOG_DOMAIN),
            'not_found'                  => __('未找到该话题', MICROBLOG_DOMAIN),
            'no_terms'                   => __('暂无话题', MICROBLOG_DOMAIN),
            'choose_from_most_used'      => __('选择已有话题', MICROBLOG_DOMAIN),
            'separate_items_with_commas' => __('多个话题请用英文逗号（,）分开', MICROBLOG_DOMAIN),
            'back_to_items'              => __('返回话题列表', MICROBLOG_DOMAIN),
        ),
        'public'       => true,
        'show_in_rest' => true,
        'hierarchical' => false,
        'show_tagcloud' => false,
        'show_admin_column' => true,
        'rest_base'    => 'micropost_topics',
        'rewrite'      => array('slug' => 'micropost_topics'),
        'description'  => __('用话题标签把同一主题的微博串起来。', MICROBLOG_DOMAIN),
    ));
}

/**
 * Feed 中带上微博。
 *
 * @param WP_Query $query 查询对象。
 * @return void
 */
function mbfun_customize_main_query($query)
{
    if (is_admin() || !$query->is_main_query() || !$query->is_feed) {
        return;
    }

    if ('micropost' === $query->get('post_type')) {
        $per_feed = (int) get_option('posts_per_rss');
        $query->set('posts_per_rss', $per_feed > 0 ? $per_feed : 10);

        return;
    }

    if (!mbfun_setting('mb_rss_feed', false)) {
        return;
    }

    if (!is_post_type_archive('micropost')) {
        $query->set('post_type', array('post', 'micropost'));
    }

    add_filter('the_title', 'microblog_formart_micropost_feed_title', 10, 2);
}
add_action('pre_get_posts', 'mbfun_customize_main_query');

/**
 * Feed 中给微博标题加前缀。
 *
 * @param string $title   标题。
 * @param int    $post_id 文章 ID。
 * @return string
 */
function microblog_formart_micropost_feed_title($title, $post_id)
{
    if ('micropost' === get_post_type($post_id)) {
        return __('【微博】：', MICROBLOG_DOMAIN) . $title;
    }

    return $title;
}

/**
 * 微博固定链接：/microposts/{ID}.html。
 *
 * @return void
 */
function mbfun_custom_microblog_rewrite_rule()
{
    add_rewrite_rule(
        '^' . preg_quote(mbfun_get_micropost_slug(), '/') . '/([0-9]+)\.html/?$',
        'index.php?post_type=micropost&p=$matches[1]',
        'top'
    );
}
add_action('init', 'mbfun_custom_microblog_rewrite_rule');

/**
 * 微博永久链接。
 *
 * @param string  $permalink 原链接。
 * @param WP_Post $post      文章。
 * @return string
 */
function mbfun_custom_microblog_permalink($permalink, $post)
{
    if ('micropost' !== get_post_type($post)) {
        return $permalink;
    }

    $url = home_url(mbfun_get_micropost_slug() . '/' . $post->ID . '.html');

    // 跟随站点固定链接的结尾斜杠设置，避免每次访问都吃一次 301。
    return user_trailingslashit($url, 'single');
}
add_filter('post_type_link', 'mbfun_custom_microblog_permalink', 10, 2);

/**
 * 分页时去掉 name 参数，避免 404。
 *
 * @param array $query_string 查询变量。
 * @return array
 */
function mbfun_remove_page_from_query_string($query_string)
{
    if (isset($query_string['name'], $query_string['paged'])) {
        unset($query_string['name']);

        $parts = explode('/', (string) $query_string['paged']);
        $query_string['paged'] = $parts[0];
    }

    return $query_string;
}
add_filter('request', 'mbfun_remove_page_from_query_string');

/**
 * 分类分页时把自定义文章类型带进查询。
 *
 * 会改变分类归档的内容集合，允许主题/插件用过滤器关掉。
 *
 * @param array $qs 查询变量。
 * @return array
 */
function mbfun_fix_category_pagination($qs)
{
    if (empty($qs['category_name']) || empty($qs['paged'])) {
        return $qs;
    }

    if (!apply_filters('microblog_include_cpt_in_category_pagination', true)) {
        return $qs;
    }

    $types   = get_post_types(array('public' => true, '_builtin' => false));
    $types[] = 'post';
    $qs['post_type'] = $types;

    return $qs;
}
add_filter('request', 'mbfun_fix_category_pagination');

/**
 * 规则变化后刷新重写规则（只写一次库）。
 *
 * @return void
 */
function mbfun_maybe_flush_rewrite_rules_on_init()
{
    mbfun_maybe_flush_rewrite_rules();
}
add_action('init', 'mbfun_maybe_flush_rewrite_rules_on_init', 99);
