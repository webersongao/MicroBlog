<?php
/**
 * 渲染片段：短代码、小工具、模板共用的 HTML 片段。
 *
 * 旧实现里「评论图标链接」「头像」「话题」「九宫格图片」在
 * shortcode / widget 中各写一遍，改一处漏一处。这里收口。
 *
 * @package MicroBlog
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 插件资源 URL。
 *
 * @param string $path 相对 assets/ 的路径。
 * @return string
 */
function mbfun_asset_url($path = '')
{
    return MICROBLOG_PLUGIN_URL . 'assets/' . ltrim($path, '/');
}

/**
 * 图标 URL。
 *
 * @param string $name 文件名。
 * @return string
 */
function mbfun_icon_url($name)
{
    return mbfun_asset_url('images/' . $name);
}

/**
 * 从正文中提取图片片段。
 *
 * @param string $content  正文。
 * @param bool   $lightbox 是否按灯箱结构（<a><img></a>）匹配。
 * @return array 匹配到的 HTML 片段数组。
 */
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

/**
 * 图片区容器类名（按数量决定单图 / 双图 / 九宫格）。
 *
 * @param int $count 图片数量。
 * @return string
 */
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

/**
 * 生成图片区 HTML。
 *
 * @param string $content  正文。
 * @param bool   $lightbox 是否灯箱结构。
 * @return string 无图片时返回空串。
 */
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

    return $html . '</div>';
}

/**
 * 作者头像 + 昵称。
 *
 * @param int $post_id 文章 ID。
 * @return string
 */
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

/**
 * 话题（#tag#）链接串。
 *
 * @param int $post_id 文章 ID。
 * @return string
 */
function mbfun_get_topics_html($post_id = 0)
{
    $post_id = $post_id ? (int) $post_id : get_the_ID();
    $terms   = wp_get_post_terms($post_id, 'micropost_topic');

    if (is_wp_error($terms) || !$terms) {
        return '';
    }

    $html = '';

    foreach ($terms as $term) {
        $link = get_term_link($term);

        if (is_wp_error($link)) {
            continue;
        }

        $html .= '<a href="' . esc_url($link) . '" target="_blank">#' . esc_html($term->name) . '# </a>';
    }

    return $html;
}

/**
 * 底部「评论数 / 更多」链接。
 *
 * @param int    $post_id 文章 ID。
 * @param string $class   外层 span 类名。
 * @return string
 */
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

/**
 * 微博正文（去掉区块注释、图注与图片）。
 *
 * @param int|WP_Post|null $post 文章。
 * @return string
 */
function mbfun_get_micropost_content($post = null)
{
    $post = get_post($post);

    if (!$post) {
        return '';
    }

    $content = $post->post_content;

    // 古腾堡区块注释 / figure / figcaption。
    $content = preg_replace('/<!--\s*\/?wp:[^\>]+-->|<figure[^>]*>.*?<\/figure>|<figcaption[^>]*>.*?<\/figcaption>/s', '', $content);
    // 经典编辑器图注与图片链接。
    $content = preg_replace('/\[caption[^\]]*\]|\[\/caption\]|<a\s[^>]*><img[^>]+><\/a>/', '', $content);
    // 残余的独立 <img>：配图由 mbfun_get_images_html() 统一输出，避免重复。
    $content = preg_replace('/<img[^>]*>/i', '', $content);
    // 清掉因移除图片产生的空段落。
    $content = preg_replace('/<p>\s*(&nbsp;)?\s*<\/p>/i', '', $content);

    return trim(str_replace('&nbsp;', '', $content));
}
