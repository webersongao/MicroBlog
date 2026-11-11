<?php

if (!defined('ABSPATH')) {
    exit;
}

/* 渲染微博列表 */
function microblog_render_list($atts = array())
{
    $atts = shortcode_atts(array(
        'null_text'   => '(none)',
        'use_excerpt' => '',
        'q'           => '',
    ), $atts, 'microblog');

    $settings = mbfun_get_micropost_list_settings();
    $extra    = array();

    if ('' !== $atts['q']) {
        $extra['s'] = sanitize_text_field($atts['q']);
    }

    $query = mbfun_get_micropost_list_query($settings, $extra);

    if (!$query->have_posts()) {
        wp_reset_postdata();

        return "<div class='microblog-shortcode'" . mbfun_color_mode_attr() . '><p>' . esc_html($atts['null_text']) . '</p></div>';
    }

    $out  = "<div class='microblog-shortcode'" . mbfun_color_mode_attr() . '>';
    $out .= '<hr>';
    $out .= "<div class='mb-shortcode-content'>";
    $out .= "<div class='mb-shortcode-post'>";

    while ($query->have_posts()) {
        $query->the_post();

        $post_id    = get_the_ID();
        $post_title = $settings['title_on'] ? get_the_title() : '';

        $out .= "<div class='mb-shortcode-post-main micropost-main-" . (int) $post_id . "'>";

        $date_text = mbfun_micropost_format_time((int) get_post_time('U', true, $post_id));

        if ('' !== $post_title || '' !== $date_text) {
            $out .= "<div class='mb-shortcode-post-head'>";

            if ('' !== $post_title) {
                $out .= "<span class='mb-shortcode-post-head-title'><a target='_blank' href='" . esc_url(get_permalink()) . "'>" . esc_html($post_title) . '</a></span>';
            }

            if ('' !== $date_text) {
                $out .= "<span class='mb-shortcode-post-date'>" . esc_html($date_text) . '</span>';
            }

            $out .= '</div>';
        }

        $out .= "<div class='mb-shortcode-post-content'>";

        if (!empty($atts['use_excerpt'])) {
            add_filter('excerpt_more', 'mbfun_micropost_excerpt_more');
            $out .= '<p>' . esc_html(get_the_excerpt()) . '</p>';
            remove_filter('excerpt_more', 'mbfun_micropost_excerpt_more');
        } else {
            $out .= mbfun_get_micropost_content($post_id);
        }

        $out .= mbfun_get_images_html(get_the_content(), $settings['lightbox'], get_permalink($post_id));
        $out .= '</div>';

        $out .= "<div class='mb-shortcode-post-bottom'>";
        $out .= "<div class='mb-shortcode-post-user'>" . ($settings['show_user'] ? mbfun_get_author_html($post_id) : '') . '</div>';
        $out .= mbfun_get_action_link_html($post_id);
        $out .= '</div>';

        $out .= '</div>';
        $out .= '<hr>';
    }

    $out .= '</div>';

    if ($query->max_num_pages > 1) {
        $out .= "<div class='mb-shortcode-pagelink'>" . paginate_links(array('total' => $query->max_num_pages)) . '</div>';
    }

    $out .= "<div class='mb-shortcode-loadmore'>";
    $out .= "<a target='_blank' href='" . esc_url(home_url(mbfun_get_micropost_slug())) . "'>";
    $out .= "<img src='" . esc_url(mbfun_icon_url('post-more-icon.png')) . "' style='width: 16px; height: 16px;'>&nbsp;"
        . esc_html__('View all...', MICROBLOG_DOMAIN) . "</a></div>";
    $out .= '</div></div>';

    wp_reset_postdata();
    mbfun_enqueue_scripts_and_styles();

    return $out;
}
function microblog_shortcode($atts)
{
    return microblog_render_list((array) $atts);
}
add_shortcode('microblog', 'microblog_shortcode');
