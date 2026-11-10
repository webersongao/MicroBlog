<?php

if (!defined('ABSPATH')) {
    exit;
}

/* 注册微博文章类型 */
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

    mbfun_register_micropost_type(array_unique($supports));
}
add_action('init', 'mbfun_create_micropost_type', 5);

/* 注册文章类型 */
function mbfun_register_micropost_type($supports = array())
{
    if (!$supports) {
        $supports = array('title', 'editor', 'comments');
    }

    register_post_type('micropost', array(
        'label'       => __('Microblog', MICROBLOG_DOMAIN),
        'labels'      => mbfun_get_micropost_labels(),
        'has_archive' => true,
        'public'      => true,
        'menu_icon'   => 'dashicons-format-status',
        'menu_position' => 6,
        'rest_base'   => 'microposts',
        'rewrite'     => array('slug' => mbfun_get_micropost_slug()),
        'supports'    => $supports,
        'show_in_rest' => use_block_editor_for_post_type('post'),
        'description' => __('Publish a short microblog with MicroBlog, then show it with a shortcode or a widget.', MICROBLOG_DOMAIN),
    ));
}

/* 微博文章类型的标签 */
function mbfun_get_micropost_labels()
{
    return array(
        'name'                     => __('Microblog', MICROBLOG_DOMAIN),
        'menu_name'                => __('Microblog', MICROBLOG_DOMAIN),
        'singular_name'            => __('Microblog', MICROBLOG_DOMAIN),
        'add_new'                  => __('New Microblog', MICROBLOG_DOMAIN),
        'add_new_item'             => __('Write Microblog', MICROBLOG_DOMAIN),
        'new_item'                 => __('New Microblog', MICROBLOG_DOMAIN),
        'all_items'                => __('Microblog', MICROBLOG_DOMAIN),
        'view_items'               => __('Microblog List', MICROBLOG_DOMAIN),
        'view_item'                => __('View Microblog', MICROBLOG_DOMAIN),
        'search_items'             => __('Search Microblogs', MICROBLOG_DOMAIN),
        'edit_item'                => __('Edit Microblog', MICROBLOG_DOMAIN),
        'not_found'                => __('No microblogs found.', MICROBLOG_DOMAIN),
        'not_found_in_trash'       => __('No microblogs found in Trash.', MICROBLOG_DOMAIN),
        'item_updated'             => __('Microblog updated.', MICROBLOG_DOMAIN),
        'item_published'           => __('Microblog published.', MICROBLOG_DOMAIN),
        'item_published_privately' => __('Microblog published privately.', MICROBLOG_DOMAIN),
        'item_reverted_to_draft'   => __('Microblog reverted to draft.', MICROBLOG_DOMAIN),
        'item_scheduled'           => __('Microblog scheduled.', MICROBLOG_DOMAIN),
        'featured_image'           => __('Microblog featured image', MICROBLOG_DOMAIN),
        'set_featured_image'       => __('Set featured image', MICROBLOG_DOMAIN),
        'use_featured_image'       => __('Use featured image', MICROBLOG_DOMAIN),
        'remove_featured_image'    => __('Remove featured image', MICROBLOG_DOMAIN),
        'filter_items_list'        => __('Filter microblog list', MICROBLOG_DOMAIN),
        'filter_by_date'           => __('Filter by microblog date', MICROBLOG_DOMAIN),
        'items_list'               => __('Microblog List', MICROBLOG_DOMAIN),
        'items_list_navigation'    => __('Microblog list navigation', MICROBLOG_DOMAIN),
    );
}

/* Feed 中带上微博 */
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

/* Feed 中给微博标题加前缀 */
function microblog_formart_micropost_feed_title($title, $post_id)
{
    if ('micropost' === get_post_type($post_id)) {
        return __('[Microblog] ', MICROBLOG_DOMAIN) . $title;
    }

    return $title;
}

/* 微博固定链接 */
function mbfun_custom_microblog_rewrite_rule()
{
    add_rewrite_rule(
        '^' . preg_quote(mbfun_get_micropost_slug(), '/') . '/([0-9]+)\.html/?$',
        'index.php?post_type=micropost&p=$matches[1]',
        'top'
    );
}
add_action('init', 'mbfun_custom_microblog_rewrite_rule');

/* 微博永久链接 */
function mbfun_custom_microblog_permalink($permalink, $post)
{
    if ('micropost' !== get_post_type($post)) {
        return $permalink;
    }

    $url = home_url(mbfun_get_micropost_slug() . '/' . $post->ID . '.html');

    return user_trailingslashit($url, 'single');
}
add_filter('post_type_link', 'mbfun_custom_microblog_permalink', 10, 2);

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

function mbfun_fix_category_pagination($qs)
{
    if (is_admin() || empty($qs['category_name']) || empty($qs['paged'])) {
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

