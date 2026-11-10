<?php
/* 渲染片段 */

if (!defined('ABSPATH')) {
    exit;
}

/* 插件资源 URL */
function mbfun_asset_url($path = '')
{
    return MICROBLOG_PLUGIN_URL . 'assets/' . ltrim($path, '/');
}

/* 图标 URL */
function mbfun_icon_url($name)
{
    return mbfun_asset_url('images/' . $name);
}

/* 从正文中提取图片片段 */
function mbfun_get_post_images($content, $lightbox = false)
{
    if ('' === (string) $content) {
        return array();
    }

    $pattern = $lightbox
        ? '/<a\s[^>]*?href="([^"]+)"[^>]*?>\s*<img[^>]*?>\s*<\/a>/'
        : '/<img.+?src=[\'"]([^\'"]+)[\'"].*?>/i';

    if (!preg_match_all($pattern, $content, $matches)) {
        return array();
    }

    return $matches[0];
}

/* 图片区容器类名 */
function mbfun_get_images_class($count)
{
    if (1 === (int) $count) {
        return 'mb-shortcode-post-content-image-single';
    }

    if (2 === (int) $count) {
        return 'mb-shortcode-post-content-image-double';
    }

    return 'mb-shortcode-post-content-image-grid';
}

/* 生成图片区 HTML */
function mbfun_get_images_html($content, $lightbox = false)
{
    $images = mbfun_get_post_images($content, $lightbox);

    if (!$images) {
        return '';
    }

    $html = "<div class='" . esc_attr(mbfun_get_images_class(count($images))) . "'>";

    foreach ($images as $image) {
        $html .= "<div class='mb-shortcode-post-content-image-item'>" . $image . "</div>";
    }

    $html .= '</div>';

    /* 页面模板与小工具直接 echo 列表，不经过 the_content；Simple Lightbox 只在
       the_content 上扫描并缓存媒体项，缓存为空时 wp_footer 不输出主脚本。
       用官方 slb_activate() 主动激活自定义内容，覆盖三条渲染路径。 */
    if ($lightbox && !is_admin() && !is_feed() && function_exists('slb_activate')) {
        $html = slb_activate($html);
    }

    return $html;
}

/* 作者头像 + 昵称 */
function mbfun_get_author_html($post_id = 0)
{
    $post_id = $post_id ? (int) $post_id : get_the_ID();
    $author  = (int) get_post_field('post_author', $post_id);

    if (!$author) {
        return '';
    }

    return "<span class='mb-shortcode-post-user-avatar'>" . get_avatar($author, 35) . "</span>"
        . "<span class='mb-shortcode-post-user-name'>" . esc_html(get_the_author_meta('display_name', $author)) . "</span>";
}

function mbfun_get_action_link_html($post_id = 0, $class = 'mb-shortcode-post-bottom-link')
{
    $post_id = $post_id ? (int) $post_id : get_the_ID();
    $link    = get_permalink($post_id);

    if (!$link) {
        return '';
    }

    if (comments_open($post_id)) {
        $icon  = mbfun_icon_url('post-comment-icon.png');
        $label = (string) get_comments_number($post_id);
    } else {
        $icon  = mbfun_icon_url('post-more-icon.png');
        $label = '';
    }

    return "<span class='" . esc_attr($class) . "'>"
        . "<a target='_blank' href='" . esc_url($link) . "'>"
        . "<img src='" . esc_url($icon) . "' style='width: 16px; height: 16px;'>&nbsp;" . esc_html($label)
        . "</a></span>";
}

/* 微博正文 */
function mbfun_get_micropost_content($post = null)
{
    $post = get_post($post);

    if (!$post) {
        return '';
    }

    $content = $post->post_content;

    $content = preg_replace('/<!--\s*\/?wp:[^\>]+-->|<figure[^>]*>.*?<\/figure>|<figcaption[^>]*>.*?<\/figcaption>/s', '', $content);
    $content = preg_replace('/\[caption[^\]]*\]|\[\/caption\]|<a\s[^>]*><img[^>]+><\/a>/', '', $content);
    $content = preg_replace('/<img[^>]*>/i', '', $content);
    $content = preg_replace('/<p>\s*(&nbsp;)?\s*<\/p>/i', '', $content);

    return trim(str_replace('&nbsp;', '', $content));
}
