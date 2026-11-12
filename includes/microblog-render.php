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

/* 从正文中提取图片片段：按正文顺序收集全部图片，不遗漏裸图 */
function mbfun_get_post_images($content, $lightbox = false)
{
    if ('' === (string) $content) {
        return array();
    }

    /* 同一正则按出现顺序匹配「<a><img></a>」与「裸 <img>」：带链接的图片
       优先整体命中，internal <img> 不会被重复计入。旧实现在灯箱开启时只认
       带 <a> 的结构，导致 linkDestination=none / 核心「点击放大」的裸图
       在列表、短代码与小工具里被整批丢弃。 */
    $pattern = '/<a\s[^>]*?href=["\']([^"\']+)["\'][^>]*>\s*<img\b[^>]*>\s*<\/a>|<img\b[^>]*>/i';

    if (!preg_match_all($pattern, $content, $matches)) {
        return array();
    }

    $images = array();

    foreach ($matches[0] as $fragment) {
        $linked = (bool) preg_match('/^<a\b/i', $fragment);

        preg_match('/<img\b[^>]*>/i', $fragment, $img_match);
        $img_tag = isset($img_match[0]) ? $img_match[0] : '';

        if ('' === $img_tag) {
            continue;
        }

        /* 灯箱关闭时沿用旧行为：只输出 <img> 本身，不带链接。 */
        if (!$lightbox) {
            $images[] = $img_tag;
            continue;
        }

        /* 已有链接的图片原样保留，media / attachment / custom 链接都不改动。 */
        if ($linked) {
            $images[] = $fragment;
            continue;
        }

        /* 裸图补一层指向媒体文件的链接，让 slb_activate() 能统一激活。
           src 用官方 WP_HTML_Tag_Processor 读取，避免正则解析属性；
           缩放/水印 query 不属于媒体文件地址，按 URL 规则从第一个 ? 或 # 处截断。 */
        if (!class_exists('WP_HTML_Tag_Processor')) {
            /* WP 6.0/6.1 没有标签处理器时保留裸图并停用灯箱，避免页面报错。 */
            $images[] = $img_tag;
            continue;
        }

        $tag = new WP_HTML_Tag_Processor($img_tag);
        $src = $tag->next_tag('img') ? (string) $tag->get_attribute('src') : '';
        $src = html_entity_decode($src, ENT_QUOTES, 'UTF-8');

        $href = $src ? preg_replace('/[?#].*$/', '', $src) : '';
        $path = $href ? (string) parse_url($href, PHP_URL_PATH) : '';

        /* 用 WP 官方图片扩展名列表（含 heic/heif/avif），避免遗漏iPhone 图片。 */
        static $ext_pattern = null;
        if (null === $ext_pattern) {
            $types = wp_get_ext_types();
            $exts  = !empty($types['image']) ? (array) $types['image'] : array('jpg', 'jpeg', 'png', 'gif', 'webp');
            $ext_pattern = '/\.(' . implode('|', array_map(fn($e) => preg_quote($e, '/'), $exts)) . ')$/i';
        }

        if ($path && preg_match($ext_pattern, $path)) {
            $images[] = '<a href="' . esc_url($href) . '">' . $img_tag . '</a>';
        } else {
            /* data: URI / 无法判定的地址不补链接，至少保证图片正常显示。 */
            $images[] = $img_tag;
        }
    }

    return $images;
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

/* 生成图片区 HTML
   $permalink 由调用方传入（短代码 / 页面模板 / 小工具都在自己的 WP_Query 循环里），
   避免依赖全局 $post；只有图片超过 3 张时才需要。 */
function mbfun_get_images_html($content, $lightbox = false, $permalink = '')
{
    $images = mbfun_get_post_images($content, $lightbox);

    if (!$images) {
        return '';
    }

    /* 列表里最多展示一行 3 张。拿不到详情页地址时退回全量展示，宁可多排一行也不丢图。 */
    $max        = 3;
    $total      = count($images);
    $detail_url = '';

    if ($total > $max) {
        if ('' === $permalink) {
            $permalink = get_permalink();
        }
        $detail_url = is_string($permalink) ? $permalink : '';
    }

    $more  = ($total > $max && $detail_url) ? $total - $max : 0;
    $shown = $more ? array_slice($images, 0, $max) : $images;

    $html      = "<div class='" . esc_attr(mbfun_get_images_class(count($shown))) . "'>";
    $more_html = '';

    foreach ($shown as $i => $image) {
        if ($more && $i === $max - 1) {
            /* 第 3 张保持原样：不压暗、不遮挡，媒体文件 / 附件页 / 自定义链接都保留，
               继续参与灯箱预览。「还有 N 张」只由右下角的 +N 胶囊表达，它是图片链接的
               兄弟节点而非嵌套，点图与点胶囊互不干扰。胶囊自带深色底，不依赖蒙层托底。
               胶囊等 slb_activate() 处理完再注入，避免 Simple Lightbox 把详情页链接
               （.html / 无扩展名）扫进媒体组。 */
            $more_html = "<a class='mb-image-more-link' href='" . esc_url($detail_url) . "'"
                . " aria-label='" . esc_attr__('View all images in this microblog post', MICROBLOG_DOMAIN) . "'>"
                . "<span class='mb-image-more-count'>+" . (int) $more . '</span></a>';

            $html .= "<div class='mb-shortcode-post-content-image-item mb-image-item-more'>"
                . $image . '<!--MB_MORE_SLOT-->' . '</div>';
            continue;
        }

        $html .= "<div class='mb-shortcode-post-content-image-item'>" . $image . '</div>';
    }

    $html .= '</div>';

    /* 页面模板与小工具直接 echo 列表，不经过 the_content；Simple Lightbox 只在
       the_content 上扫描并缓存媒体项，缓存为空时 wp_footer 不输出主脚本。
       用官方 slb_activate() 主动激活自定义内容，覆盖三条渲染路径。 */
    if ($lightbox && !is_admin() && !is_feed() && function_exists('slb_activate')) {
        $html = slb_activate($html);
    }

    if ($more_html) {
        $html = str_replace('<!--MB_MORE_SLOT-->', $more_html, $html);
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
