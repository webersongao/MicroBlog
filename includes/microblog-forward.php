<?php

// 添加操作链接
function add_forward_link($actions, $post) {
    if ($post->post_type === 'micropost') {
        $post_id = $post->ID;
        $actions = array_slice($actions, 0, 2, true) +
                   array('forward' => '<a href="' . admin_url('post-new.php?post_type=micropost&forward_id=' . $post_id) . '">转发此微博</a>') +
                   array_slice($actions, 2, null, true);
    }
    return $actions;
}
add_filter('post_row_actions', 'add_forward_link', 10, 2);

function get_recent_microposts() {
    return get_posts(array(
        'post_type' => 'micropost',
        'posts_per_page' => 20,
        'orderby' => 'date',
        'order' => 'DESC',
    ));
}

// 获取转发微博的 HTML
function get_forward_select_html($forward_id, $recent_posts) {
    ob_start();
    ?>
    <select id="forward_id" name="forward_id">
        <option value="">选择被转发微博</option>
        <?php foreach ($recent_posts as $recent_post) : ?>
            <option value="<?php echo esc_attr($recent_post->ID); ?>" <?php selected($forward_id, $recent_post->ID); ?>>
                <?php echo esc_html($recent_post->post_title); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
    return ob_get_clean();
}

// 添加 meta box
function add_forward_meta_box() {
    add_meta_box('forward_meta_box', '转发微博', 'display_forward_meta_box', 'micropost', 'side', 'high');
}
add_action('add_meta_boxes', 'add_forward_meta_box');

// 显示 meta box
function display_forward_meta_box($post) {
    $post_type = $post->post_type;
    if ($post_type !== 'micropost') {
        return;
    }
    
    // 检查当前页面是否为编辑页面并且文章类型为 micropost
    if (isset($_GET['action']) && $_GET['action'] === 'edit') {
        $recent_posts = get_recent_microposts();
        $forward_id = get_post_meta($post->ID, 'forward_id', true);
        $forward_post = (absint($forward_id) !== 0) ? get_post($forward_id) : null;
        if ($forward_post) {
            $forward_title = '';
            $forward_post = get_post($forward_id);
            if ($forward_post) {
                $forward_post_title = $forward_post->post_title;
                $forward_post_content = $forward_post->post_content;
                if (empty($forward_post_title)) {
                    $forward_title = substr(wp_strip_all_tags($forward_post_content), 0, 100);
                } else {
                    $forward_title = $forward_post_title;
                }
            }
            ?>
            <label for="forward_id">已转发微博：</label>
            <input type="text" id="forward_title" name="forward_title" value="<?php echo esc_attr($forward_title); ?>" readonly><br>
            <label for="forward_id">修改转发为其他：</label><br><br>
            <?php echo esc_html(get_forward_select_html($forward_id, $recent_posts)); ?>
            <?php
        } else {
            echo esc_html(get_forward_select_html($forward_id, $recent_posts));
        }
    } else {
        if (isset($_GET['post_type']) && $_GET['post_type'] === 'micropost') {
            // 获取 forward_id 参数
            $forward_id = absint($_GET['forward_id']) ?: 0;
            $forward_post = ($forward_id !== 0) ? get_post($forward_id) : null;
            if ($forward_post) {
                $forward_title = '';
                $forward_post_title = $forward_post->post_title;
                $forward_post_content = $forward_post->post_content;
                if (empty($forward_post_title)) {
                    $forward_title = substr(wp_strip_all_tags($forward_post_content), 0, 100);
                } else {
                    $forward_title = $forward_post_title;
                }
                ?>
                <label for="forward_id">转发微博：</label>
                <br>
                <input type="text" id="forward_title" name="forward_title" value="<?php echo esc_attr($forward_title); ?>" readonly>
                <?php
            } else {
                $recent_posts = get_recent_microposts();
                echo esc_html(get_forward_select_html($forward_id, $recent_posts));
            }
        } 
    }
}

function save_forward_id($post_id) {
    $forward_id = absint($_POST['forward_id']) ?: (absint($_GET['forward_id']) ?: 0);
    if ($forward_id !== 0) {
        update_post_meta($post_id, 'forward_id', $forward_id);
    }
}
add_action('save_post', 'save_forward_id');

?>