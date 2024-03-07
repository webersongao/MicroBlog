<?php
// microblog-widget.php

require_once(plugin_dir_path(__FILE__) . 'micropost-functions.php');
/*
Description: 在仪表板中添加一个小部件，用于快速发布微博类型的文章。
*/

// 注册插件小部件
function register_quick_micropost_widget() {
    wp_add_dashboard_widget(
        'quick_micropost_widget', // Widget ID
        '快速发微博', // Widget Title
        'display_quick_micropost_widget' // Callback function
    );
}
add_action('wp_dashboard_setup', 'register_quick_micropost_widget');

// 小部件内容
function display_quick_micropost_widget() {
    // 添加HTML表单
    ?>
    <form id="quick-micropost-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <label for="micropost-title">标题：</label>
        <input type="text" id="micropost-title" name="micropost_title" placeholder=" 标题">
        <label for="micropost-content">内容：</label>
        <textarea id="micropost-content" name="micropost_content" rows="5" cols="15" placeholder=" 请输入微博内容"></textarea>
        <input type="hidden" name="action" value="quick_micropost">
        <input type="submit" value="发布微博" class="button button-primary button-large">
        <?php wp_nonce_field('quick-micropost-action', 'quick-micropost-nonce'); ?>
        <span id="micropost-message" class="micropost-message">ssss</span>
    </form>
    <?php
}

// 处理表单提交
function handle_quick_micropost_submission() {
    if (isset($_POST['micropost_content']) && isset($_POST['quick-micropost-nonce'])) {
        if (wp_verify_nonce($_POST['quick-micropost-nonce'], 'quick-micropost-action')) {
            $post_title = isset($_POST['micropost_title']) ? sanitize_text_field($_POST['micropost_title']) : '微博 ' . date('Y-m-d H:i');
            $post_id = wp_insert_post(array(
                'post_title' => $post_title, // 使用用户输入的标题，或者默认标题
                'post_content' => sanitize_textarea_field($_POST['micropost_content']),
                'post_type' => 'micropost', // 自定义文章类型
                'post_status' => 'publish' // 可自定义发布状态
            ));
            if (!is_wp_error($post_id)) {
                // 发布成功
                echo '<p>微博已成功发布！</p>';
            } else {
                // 发布失败
                echo '<p>发布微博时出错，请稍后重试。</p>';
            }
        } else {
            // 非法请求
            echo '<p>非法请求！</p>';
        }
    }
}
add_action('admin_post_quick_micropost', 'handle_quick_micropost_submission');
add_action('admin_post_nopriv_quick_micropost', 'handle_quick_micropost_submission');

?>
