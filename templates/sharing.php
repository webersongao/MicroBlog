<?php
if ( ! mlb_display_liveblog_layout('ml_layout_tshare')) {
    return;
}

$entry_url   = mbfun_get_live_entry_url();
$entry_title = mbfun_get_live_entry_title();

/*
 * 分享卡片弹窗与二维码都由主题提供。插件只通过公开的「动作 + 过滤器」与主题协作，
 * 不硬编码任何主题函数名或文件路径：
 *   - do_action('mb_liveblog_share_card')：请主题在本页输出分享卡片弹窗
 *     （主题 waves-script / share-card.js 用 document 级事件委托，AJAX 动态插入的
 *      连载条目也能触发）。
 *   - apply_filters('mb_liveblog_qrcode_url', '', $url)：向主题索取该 URL 的二维码图片地址。
 * 没有主题响应过滤器时二维码为空，微信分享按钮自然不渲染——宁可放弃功能，也不越界引用主题实现。
 */
do_action('mb_liveblog_share_card');
$qrcode_url = (string) apply_filters('mb_liveblog_qrcode_url', '', $entry_url);

$entry_text  = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags((string) get_post_field('post_content', get_the_ID()))));

/* 卡片正文由主题的 5 行 line-clamp 兜底，这里只提供足够素材：
   中文没有空格，wp_trim_words() 会把整段当成一个词，长度不可控，
   旧写法实际只截出约 40 字，正文区大半空着。截断符交给 CSS 的省略号。 */
$excerpt     = function_exists('mb_strimwidth')
    ? mb_strimwidth($entry_text, 0, 220, '', 'UTF-8')
    : mb_substr($entry_text, 0, 110, 'UTF-8');
?>
<span class="mlb-liveblog-action-share">
    <?php if ('' !== $qrcode_url) : ?>
    <a class="weixin-share mlb-weixin-share" href="<?php echo esc_url($qrcode_url); ?>"
       title="<?php echo esc_attr__('Share on WeChat', MICROBLOG_DOMAIN); ?>"
       data-blog="<?php echo esc_attr(get_bloginfo('name')); ?>"
       data-title="<?php echo esc_attr($entry_title); ?>"
       data-excerpt="<?php echo esc_attr($excerpt); ?>"
       rel="nofollow"><i class="iconfont icon-weixin" aria-hidden="true"></i></a>
    <?php endif; ?>

    <a href="<?php echo esc_url('https://x.com/intent/post?text=' . rawurlencode($entry_title . ' ' . $entry_url)); ?>"
       target="_blank" title="<?php echo esc_attr__('Share via X/Twitter', MICROBLOG_DOMAIN); ?>" rel="nofollow">
        <?php echo mbfun_get_live_social_logo('x'); ?>
    </a>

    <a href="<?php echo esc_url('https://service.weibo.com/share/share.php?url=' . rawurlencode($entry_url) . '&title=' . rawurlencode($entry_title)); ?>"
       target="_blank" title="<?php echo esc_attr__('Share via Weibo', MICROBLOG_DOMAIN); ?>" rel="nofollow">
        <?php echo mbfun_get_live_social_logo('weibo'); ?>
    </a>
</span>
