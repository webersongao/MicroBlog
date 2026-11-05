<?php
/**
 * 微博转发：列表行操作、编辑页 meta box、保存。
 *
 * @package MicroBlog
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 是否启用转发。
 *
 * @return bool
 */
function mbfun_forward_enabled()
{
    return (bool) mbfun_get_micropost_option('mb_foward_enable', false);
}

/**
 * 列表中加「转发此微博」。
 *
 * @param array   $actions 操作链接。
 * @param WP_Post $post    文章。
 * @return array
 */
function mbfun_add_forward_link($actions, $post)
{
    if (!mbfun_forward_enabled() || 'micropost' !== $post->post_type) {
        return $actions;
    }

    $url = add_query_arg(array(
        'post_type'  => 'micropost',
        'forward_id' => $post->ID,
    ), admin_url('post-new.php'));

    $link = '<a href="' . esc_url($url) . '">' . esc_html__('Repost this microblog', MICROBLOG_DOMAIN) . '</a>';

    // 插在「编辑」之后。
    $result = array();

    foreach ($actions as $key => $value) {
        $result[$key] = $value;

        if ('edit' === $key) {
            $result['forward'] = $link;
        }
    }

    if (!isset($result['forward'])) {
        $result['forward'] = $link;
    }

    return $result;
}
add_filter('post_row_actions', 'mbfun_add_forward_link', 10, 2);

/**
 * 注册 meta box。
 *
 * @return void
 */
function mbfun_add_forward_meta_box()
{
    if (!mbfun_forward_enabled()) {
        return;
    }

    add_meta_box('micro_forward_meta_box', __('Repost', MICROBLOG_DOMAIN), 'mbfun_display_forward_meta_box', 'micropost', 'side', 'high');
}
add_action('add_meta_boxes', 'mbfun_add_forward_meta_box');

/**
 * 取被转发微博的展示标题。
 *
 * @param WP_Post $post 被转发微博。
 * @return string
 */
function mbfun_get_forward_title($post)
{
    if (!$post instanceof WP_Post) {
        return '';
    }

    if ('' !== trim((string) $post->post_title)) {
        return $post->post_title;
    }

    return mb_substr(wp_strip_all_tags($post->post_content), 0, 100);
}

/**
 * meta box 内容。
 *
 * @param WP_Post $post 当前微博。
 * @return void
 */
function mbfun_display_forward_meta_box($post)
{
    $forward_id = (int) get_post_meta($post->ID, '_micro_blog_forward_id', true);

    // 新建时由列表页传入。
    if (!$forward_id && isset($_GET['forward_id'])) {
        $forward_id = absint($_GET['forward_id']);
    }

    $target = $forward_id ? get_post($forward_id) : null;

    wp_nonce_field('mb_forward_save', 'mb_forward_nonce');

    if ($target && 'micropost' === $target->post_type) {
        echo '<p><strong>' . esc_html__('Repost of:', MICROBLOG_DOMAIN) . '</strong></p>';
        echo '<input type="text" class="widefat" readonly value="' . esc_attr(mbfun_get_forward_title($target)) . '">';
        echo '<input type="hidden" name="forward_id" value="' . (int) $forward_id . '">';
        echo '<p class="description">' . esc_html__('The repost link is saved with this post. To repost a different microblog, use "Repost this microblog" from the list screen again.', MICROBLOG_DOMAIN) . '</p>';

        return;
    }

    echo mbfun_get_forward_select_html(0, mbfun_get_recent_microposts(1, array('posts_per_page' => 50)));
}

/**
 * 选择被转发微博的下拉框。
 *
 * @param int   $current 当前值。
 * @param array $posts   候选微博。
 * @return string
 */
function mbfun_get_forward_select_html($current, $posts)
{
    $html = '<select id="forward_id" name="forward_id"><option value="">'
        . esc_html__('Select a microblog to repost', MICROBLOG_DOMAIN) . '</option>';

    foreach ((array) $posts as $item) {
        $label = '' !== trim((string) $item->post_title)
            ? $item->post_title
            : mb_substr(wp_strip_all_tags($item->post_content), 0, 40);

        $html .= '<option value="' . (int) $item->ID . '" ' . selected($current, $item->ID, false) . '>'
            . esc_html($label) . '</option>';
    }

    return $html . '</select>';
}

/**
 * 保存转发关系。
 *
 * @param int $post_id 文章 ID。
 * @return void
 */
function mbfun_save_forward_id($post_id)
{
    if (!mbfun_forward_enabled() || 'micropost' !== get_post_type($post_id)) {
        return;
    }

    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }

    if (!isset($_POST['mb_forward_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mb_forward_nonce'])), 'mb_forward_save')) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $forward_id = isset($_POST['forward_id']) ? absint($_POST['forward_id']) : 0;

    if (!$forward_id || $forward_id === (int) $post_id) {
        delete_post_meta($post_id, '_micro_blog_forward_id');

        return;
    }

    update_post_meta($post_id, '_micro_blog_forward_id', $forward_id);
}
add_action('save_post', 'mbfun_save_forward_id');
