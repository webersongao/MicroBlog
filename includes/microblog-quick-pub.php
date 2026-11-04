<?php
/**
 * 仪表盘「快速发微博」小部件。
 *
 * @package MicroBlog
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 注册仪表盘小部件。
 *
 * @return void
 */
function mbfun_register_quick_micropost_widget()
{
    if (!current_user_can('edit_posts')) {
        return;
    }

    wp_add_dashboard_widget('quick_micropost_widget', '快速发微博', 'mbfun_display_quick_micropost_widget');
}
add_action('wp_dashboard_setup', 'mbfun_register_quick_micropost_widget');

/**
 * 小部件内容。
 *
 * @return void
 */
function mbfun_display_quick_micropost_widget()
{
    $notice = isset($_GET['micropost_pub_alert']) ? sanitize_text_field(wp_unslash($_GET['micropost_pub_alert'])) : '';

    if ('' !== $notice) {
        echo '<div class="notice notice-info inline"><p>' . esc_html($notice) . '</p></div>';
    }
    ?>
    <form id="quick-micropost-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="quick_micropost">
        <?php wp_nonce_field('quick-micropost-action', 'quick-micropost-nonce'); ?>
        <p><label for="quick-micropost-title">标题：</label>
        <input type="text" id="quick-micropost-title" name="micropost_title" class="widefat" placeholder="可留空"></p>
        <p><label for="micropost-content">内容：</label>
        <textarea id="quick-micropost-content" name="micropost_content" rows="5" class="widefat" placeholder="请输入微博内容"></textarea></p>
        <p><input type="submit" value="发布微博" class="button button-primary button-large"></p>
    </form>
    <?php
}

/**
 * 处理提交。
 *
 * @return void
 */
function mbfun_handle_quick_micropost_submission()
{
    if (!current_user_can('edit_posts')) {
        wp_die(esc_html__('您没有权限执行此操作。', MICROBLOG_DOMAIN), 403);
    }

    check_admin_referer('quick-micropost-action', 'quick-micropost-nonce');

    $content = isset($_POST['micropost_content']) ? wp_kses_post(wp_unslash($_POST['micropost_content'])) : '';

    if (mb_strlen(trim(wp_strip_all_tags($content))) < 10) {
        mbfun_redirect_with_message('❌ 微博不可少于10个字！');
    }

    $title = isset($_POST['micropost_title']) ? sanitize_text_field(wp_unslash($_POST['micropost_title'])) : '';

    if ('' === $title) {
        $title = '微博 ' . gmdate('Y-m-d H:i');
    }

    // 区块编辑器下把纯文本段落包成区块，避免编辑器显示为「经典」内容。
    if (use_block_editor_for_post_type('micropost')) {
        $blocks = '';

        // 必须带 /u：\R 在非 UTF-8 模式下会把汉字里的 0x85 当成换行，切碎字符。
        foreach (preg_split('/\R/u', $content) as $paragraph) {
            $paragraph = trim($paragraph);

            if ('' === $paragraph) {
                continue;
            }

            $blocks .= "<!-- wp:paragraph -->\n<p>" . $paragraph . "</p>\n<!-- /wp:paragraph -->\n\n";
        }

        $content = rtrim($blocks);
    }

    $post_id = wp_insert_post(array(
        'post_title'   => $title,
        'post_content' => $content,
        'post_type'    => 'micropost',
        'post_status'  => 'publish',
    ), true);

    if (is_wp_error($post_id)) {
        mbfun_redirect_with_message('❌ 微博发布出错：' . $post_id->get_error_message());
    }

    mbfun_redirect_with_message('✅ 微博发布成功~');
}
add_action('admin_post_quick_micropost', 'mbfun_handle_quick_micropost_submission');

/**
 * 带提示信息跳回上一页。
 *
 * @param string $message 提示。
 * @return void
 */
function mbfun_redirect_with_message($message)
{
    $back = wp_get_referer();

    if (!$back) {
        $back = admin_url('index.php');
    }

    wp_safe_redirect(add_query_arg('micropost_pub_alert', rawurlencode($message), $back));
    exit;
}
