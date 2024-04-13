<?php

/*
Description: 在仪表板中添加一个小部件，用于快速发布微博类型的文章。
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once(plugin_dir_path(__FILE__) . 'micropost-functions.php');

// 注册插件小部件
function mbfun_register_quick_micropost_widget() {
    wp_add_dashboard_widget(
        'quick_micropost_widget', // Widget ID
        '快速发微博', // Widget Title
        'mbfun_display_quick_micropost_widget' // Callback function
    );
}
add_action('wp_dashboard_setup', 'mbfun_register_quick_micropost_widget');

// 小部件内容
function mbfun_display_quick_micropost_widget() {
    ?>
    <form id="quick-micropost-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <label for="quick-micropost-title">标题：</label>
        <input type="text" id="quick-micropost-title" name="micropost_title" placeholder=" 标题">
        <label for="micropost-content">内容：</label>
        <textarea id="quick-micropost-content" name="micropost_content" rows="5" cols="15" placeholder=" 请输入微博内容"></textarea>
        <input type="hidden" name="action" value="quick_micropost">
        <input type="submit" value="发布微博" class="button button-primary button-large button-quick-gao">
        <?php wp_nonce_field('quick-micropost-action', 'quick-micropost-nonce'); ?>
        <span id="quick-micropost-message" class="quick-micropost-message"><a href="<?php echo esc_url(admin_url('edit.php?post_type=micropost')); ?>"></a></span>
    </form>
    <?php
}

// 处理表单提交
function mbfun_handle_quick_micropost_submission() {

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( '您没有权限执行此操作。' );
    }
    
    if (isset($_POST['micropost_content']) && isset($_POST['quick-micropost-nonce'])) {
        if (wp_verify_nonce($_POST['quick-micropost-nonce'], 'quick-micropost-action')) {
            $post_title = isset($_POST['micropost_title']) ? sanitize_text_field($_POST['micropost_title']) : '微博 ' . gmdate('Y-m-d H:i');
            $post_content = isset($_POST['micropost_content']) ? wp_kses_post($_POST['micropost_content']) : '';
            if (mb_strlen($post_content) < 10) {
                $message = '微博内容不可少于10个字，请重新输入。';
            } else {
                $paragraph_blocks = '';
                $block_editor = use_block_editor_for_post_type('post');
                if ($block_editor) {
                    $paragraphs = explode("\n", $post_content);
                    foreach ($paragraphs as $paragraph) {
                        $paragraph_blocks .= '<!-- wp:paragraph -->' . "\n" . "<p>" . trim($paragraph) . "</p>" . "\n". '<!-- /wp:paragraph -->' . "\n\n";
                    }
                    $paragraph_blocks = rtrim($paragraph_blocks);
                } else {
                    $paragraph_blocks = $post_content;
                }
                $post_id = wp_insert_post(array(
                    'post_title' => $post_title,
                    'post_content' => $paragraph_blocks,
                    'post_type' => 'micropost',
                    'post_status' => 'publish'
                ));
                if (!is_wp_error($post_id)) {
                    $message = '微博发布成功~';
                } else {
                    $message = '发布微博时出错~';
                }
            }
        } else {
            $message = '非法请求！';
        }
        // 将消息存储为查询参数以便在页面刷新后显示
        wp_safe_redirect(add_query_arg('micropost_message', urlencode($message), wp_get_referer()));
        exit();
    }
}

// 注册 admin-post.php 处理程序
add_action('admin_post_quick_micropost', 'mbfun_handle_quick_micropost_submission');
add_action('admin_post_nopriv_quick_micropost', 'mbfun_handle_quick_micropost_submission');

?>
