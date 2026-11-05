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

    wp_add_dashboard_widget('quick_micropost_widget', __('Quick Microblog', MICROBLOG_DOMAIN), 'mbfun_display_quick_micropost_widget');
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
        <p><label for="quick-micropost-title"><?php esc_html_e('Title:', MICROBLOG_DOMAIN); ?></label>
        <input type="text" id="quick-micropost-title" name="micropost_title" class="widefat" placeholder="<?php esc_attr_e('Optional', MICROBLOG_DOMAIN); ?>"></p>
        <p><label for="micropost-content"><?php esc_html_e('Content:', MICROBLOG_DOMAIN); ?></label>
        <textarea id="quick-micropost-content" name="micropost_content" rows="5" class="widefat" placeholder="<?php esc_attr_e('Enter your microblog content', MICROBLOG_DOMAIN); ?>"></textarea></p>
        <p><input type="submit" value="<?php esc_attr_e('Publish Microblog', MICROBLOG_DOMAIN); ?>" class="button button-primary button-large"></p>
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
        wp_die(esc_html__('You are not allowed to perform this action.', MICROBLOG_DOMAIN), 403);
    }

    check_admin_referer('quick-micropost-action', 'quick-micropost-nonce');

    $content = isset($_POST['micropost_content']) ? wp_kses_post(wp_unslash($_POST['micropost_content'])) : '';

    $min_length = (int) apply_filters('microblog_quickpub_min_length', 10);

    if (mb_strlen(trim(wp_strip_all_tags($content))) < $min_length) {
        mbfun_redirect_with_message(sprintf(
            __('❌ The microblog must be at least %d characters long!', MICROBLOG_DOMAIN),
            $min_length
        ));
    }

    $title = isset($_POST['micropost_title']) ? sanitize_text_field(wp_unslash($_POST['micropost_title'])) : '';

    if ('' === $title) {
        $title = sprintf(__('Microblog %s', MICROBLOG_DOMAIN), gmdate('Y-m-d H:i'));
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
        mbfun_redirect_with_message(__('❌ Failed to publish the microblog: ', MICROBLOG_DOMAIN) . $post_id->get_error_message());
    }

    mbfun_redirect_with_message(__('✅ Microblog published.', MICROBLOG_DOMAIN));
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
