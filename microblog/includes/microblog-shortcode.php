<?php
// microblog-shortcode.php

require_once(plugin_dir_path(__FILE__) . 'micropost-functions.php');


add_shortcode('microblog', 'microblog_shortcode');

function microblog_shortcode($atts) {

    $options = get_option('microblog_setting_data');

    extract(shortcode_atts(array(
        'null_text' => '(none)',
        'use_excerpt' => '',
        'q' => '',
    ), $atts));

    $show_date = true;// (isset($options) && isset($options['mb_date_show']) && $options['mb_date_show']) ? true : false;
    $numvalue = (isset($options) && isset($options['mb_codepost_num'])) ? intval($options['mb_codepost_num']) : 5;
    
    $args = array(
        'post_type' => 'micropost',
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page' => $numvalue,
        'post_status' => 'publish',
        'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
    );

    if (isset($atts['q'])) {
        $q = sanitize_text_field($atts['q']);
        $args['s'] = $q;
    }

    $title_show = (isset($options) && isset($options['mb_title_show'])) ? $options['mb_title_show'] : false;
    $position_option = isset($options) && isset($options['mb_title_position']) && is_array($options['mb_title_position']);

    $query_results = new WP_Query($args);
    $out = "<div class='microblog-shortcode'>";
    if ($query_results->have_posts()) {
            global $post;
        $out .= "<div class='microblog-shortcode-content'>";
        $out .= "<ul class='microblog-shortcode-post'>";
        while ($query_results->have_posts()) {
            $query_results->the_post();
            $author_avatar = get_avatar(get_the_author_meta('ID'), 25);
            $author_name = get_the_author_meta('display_name', get_the_author_meta('ID'));
            $out .= "<li>";
            $out .= "<div class='microblog-shortcode-post-head'>";
            $out .= "<span class='microblog-shortcode-post-avatar'>" . $author_avatar . "</span>";
            $out .= "<span class='microblog-shortcode-post-username'>" . $author_name . "</span>";
            if ($show_date) {
                $out .= "<span class='microblog-shortcode-post-date'>" . micropost_format_time(strtotime($post->post_date)) . "</span>";
            }
            $out .= "</div>";

            $post_title = (get_the_title() && $title_show) ? get_the_title() : '';
            if (strlen($post_title) && $position_option && (in_array('titletop', $options['mb_title_position']))) {
                $out .= "<div class='microblog-shortcode-post-title'><a target='_blank' href='" . get_permalink() . "'>" . $post_title . "</a></div>";
            }
            $out .= "<div class='microblog-shortcode-post-content'>";
            if ($use_excerpt) {
                add_filter('excerpt_more', 'micropost_excerpt_more');
                $out .= "<p>" . get_the_excerpt() . "</p>";
                remove_filter('excerpt_more', 'micropost_excerpt_more');
            } else {
                $out .= micropost_shortcode_content();
            }
            /* =======   图片九宫格  --- 开始   */
            $post_content = get_the_content();
            $is_light_box = (isset($options) && isset($options['mb_image_lightbox']) && $options['mb_image_lightbox']) ? true : false;
            $count = 0;
            if ($is_light_box) {
                // 兼容light-box效果的正则
                if (preg_match_all('/<a\s[^>]*?href="([^"]+)"[^>]*?>\s*<img[^>]*?>\s*<\/a>/', $post_content, $matches)) {
                    $count = count($matches[0]); // 获取匹配到的元素数量
                }
            } else {
                //（纯图片）确保在匹配 <img> 标签时尽可能地少匹配字符，这样可以避免匹配到多个 <img> 标签作为一个整体
                if (preg_match_all('/<img.+?src=[\'"]([^\'"]+)[\'"].*?>/i', $post_content, $matches)) {
                    $count = count($matches[0]); // 获取匹配到的元素数量
                }
            }
            if (!empty($count)) {
                if ($count == 1) {
                    // 单张 宽度 300 ，居中显示
                    $out .= "<div class='microblog-shortcode-post-content-image-single'>";
                } elseif ($count == 2) {
                    // 两张 宽度 300 ，居中显示
                    $out .= "<div class='microblog-shortcode-post-content-image-double'>"; 
                } else {
                    // 其他情况下的操作 九宫格
                    $out .= "<div class='microblog-shortcode-post-content-image-grid'>";
                }
            
                foreach ($matches[0] as $img_html) {
                    $out .= "<div class='microblog-shortcode-post-content-image-item'>$img_html</div>"; 
                }
                $out .= "</div>";
            }
            // =======   图片九宫格  --- 结束 ----- */   
            $out .= "</div>";
            // 底部评论按钮
            $titleShow = (strlen($post_title) && $position_option && (in_array('titlebottom', $options['mb_title_position']))) ? true : false;
            if ($titleShow || comments_open()){
                $out .= "<div class='microblog-shortcode-post-comment'>";
                if ($titleShow) {
                    $out .= "<span class='microblog-shortcode-post-comment-title'><a target='_blank' href='" . get_permalink() . "'>" . $post_title . "</a></span>";
                }
                if (comments_open()) {
                    $out .= "<span class='microblog-shortcode-post-comment-link'><a target='_blank' href='" . get_permalink() . "'><img src='" . plugin_dir_url(dirname(__FILE__)) .'images/post-comment-icon.png'. "' style='width: 16px; height: 16px;'>&nbsp;" . get_comments_number() . "</a></span>";   
                } else {
                    $out .= "<span class='microblog-shortcode-post-comment-link'><a target='_blank' href='" . get_permalink() . "'><img src='" . plugin_dir_url(dirname(__FILE__)) . 'images/post-more-icon.png' . "' style='width: 16px; height: 16px;'>&nbsp;</a></span>";
                }
                $out .= "</div>";
            }
            $out .= "</li><hr>";
        }
        $out .= "</ul>";
        $out .= "</div>";
        // 分页按钮
        $out .= "<div class='microblog-shortcode-pagelink'>";
        $out .= paginate_links(array('total' => $query_results->max_num_pages));
        $out .= "</div>";
        // 查看所有
        $out .= "<div class='microblog-shortcode-loadmore'>";
        $out .= "<a target='_blank' href='" . home_url(get_microblog_slug_name()) . "'><img src='" . plugin_dir_url(dirname(__FILE__)) . 'images/post-more-icon.png' . "' style='width: 16px; height: 16px;'>&nbsp;查看全部...</a></div>";
    } else {
        $out .= "<div class='microblog-shortcode'><p>" . wp_kses($null_text, array()) . "</p></div>";
    }

    $out .= "</div>";
    wp_reset_postdata();
    microblog_enqueue_scripts_and_styles();
    return $out;
}

function micropost_shortcode_content() {
    global $post;
    $post_content = $post->post_content;
    // 古腾堡
    $post_content = preg_replace('/<!--\s*\/?wp:[^\>]+-->|<figure[^>]*>.*?<\/figure>|<figcaption[^>]*>.*?<\/figcaption>/s', '', $post_content);
    // 经典编辑器
    $post_content = preg_replace('/\[caption[^\]]*\]|\[\/caption\]|<a\s[^>]*><img[^>]+><\/a>/', '', $post_content);
    // 移除&nbsp;并修剪内容
    $post_content = trim(str_replace('&nbsp;', '', $post_content));
    return $post_content;
}






?>