<?php
/**
 * 「微博」小工具。
 *
 * @package MicroBlog
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 注册小工具。
 *
 * @return void
 */
function mbfun_load_microblog_widget()
{
    register_widget('Microblog_SideWidget');
}
add_action('widgets_init', 'mbfun_load_microblog_widget');

/**
 * 侧边栏微博列表。
 */
class Microblog_SideWidget extends WP_Widget
{
    /**
     * 默认实例。
     *
     * @return array
     */
    private function defaults()
    {
        return array(
            'title'       => '',
            'numberposts' => '5',
            'use_excerpt' => '',
            'rss'         => '',
        );
    }

    public function __construct()
    {
        parent::__construct(
            'microblog_widget',
            '微博',
            array('description' => '显示最新微博列表。')
        );
    }

    /**
     * 后台表单。
     *
     * @param array $instance 实例。
     * @return void
     */
    public function form($instance)
    {
        $instance = wp_parse_args((array) $instance, $this->defaults());
        $fields   = array(
            'title'       => array('label' => '标题:', 'type' => 'text'),
            'numberposts' => array('label' => '展示数量:', 'type' => 'text'),
            'use_excerpt' => array('label' => '是否显示摘要 ?', 'type' => 'checkbox'),
            'rss'         => array('label' => '是否展示RSS链接?', 'type' => 'checkbox'),
        );

        foreach ($fields as $key => $field) {
            $id    = $this->get_field_id($key);
            $name  = $this->get_field_name($key);
            $value = $instance[$key];

            echo '<p>';

            if ('checkbox' === $field['type']) {
                printf(
                    '<input type="checkbox" id="%1$s" name="%2$s" %3$s /> <label for="%1$s">%4$s</label>',
                    esc_attr($id),
                    esc_attr($name),
                    checked((bool) $value, true, false),
                    esc_html($field['label'])
                );
            } else {
                printf(
                    '<label for="%1$s">%2$s</label> <input type="text" id="%1$s" name="%3$s" value="%4$s" size="20">',
                    esc_attr($id),
                    esc_html($field['label']),
                    esc_attr($name),
                    esc_attr($value)
                );
            }

            echo '</p>';
        }
    }

    /**
     * 保存实例。
     *
     * @param array $new_instance 新值。
     * @param array $old_instance 旧值。
     * @return array
     */
    public function update($new_instance, $old_instance)
    {
        return array(
            'title'       => sanitize_text_field($new_instance['title'] ?? ''),
            'numberposts' => (string) max(1, min(50, (int) ($new_instance['numberposts'] ?? 5))),
            'use_excerpt' => empty($new_instance['use_excerpt']) ? '' : 1,
            'rss'         => empty($new_instance['rss']) ? '' : 1,
        );
    }

    /**
     * 前台输出。
     *
     * @param array $args     侧边栏参数。
     * @param array $instance 实例。
     * @return void
     */
    public function widget($args, $instance)
    {
        $instance = wp_parse_args((array) $instance, $this->defaults());

        $query = new WP_Query(array(
            'post_type'      => 'micropost',
            'orderby'        => 'date',
            'order'          => 'DESC',
            'posts_per_page' => max(1, (int) $instance['numberposts']),
            'post_status'    => mbfun_get_micropost_types(),
            'paged'          => max(1, (int) get_query_var('paged')),
        ));

        if (!$query->have_posts()) {
            return;
        }

        echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        echo $args['before_title'] . esc_html($instance['title']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        if (!empty($instance['rss'])) {
            $rss = get_site_url() . '/' . mbfun_get_micropost_slug() . '/feed';

            echo "<span class='microblog-widget-rss'><a target='_blank' href='" . esc_url($rss) . "' class='rss'>"
                . "<img src='" . esc_url(site_url() . '/wp-includes/images/rss.png') . "' style='width: 18px; height: 18px;'></a></span>";
        }

        echo $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        echo "<ul class='microblog-widget'>";

        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            echo '<li>';

            $title = get_the_title();

            if ('' !== $title) {
                echo "<div class='microblog-widget-head'><span class='microblog-widget-head-title'>"
                    . "<a target='_blank' href='" . esc_url(get_permalink()) . "'>" . esc_html($title) . '</a></span></div>';
            }

            echo "<div class='microblog-widget-content'>";

            if (!empty($instance['use_excerpt'])) {
                add_filter('excerpt_more', 'mbfun_micropost_excerpt_more');
                echo '<p>' . esc_html(get_the_excerpt()) . '</p>';
                remove_filter('excerpt_more', 'mbfun_micropost_excerpt_more');
            } else {
                echo mbfun_get_micropost_content($post_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }

            echo '</div>';

            $date = mbfun_micropost_format_time((int) get_post_time('U', true, $post_id));

            echo "<div class='microblog-widget-bottom'>";

            if ('' !== $date) {
                echo "<span class='microblog-widget-bottom-date'>" . esc_html($date) . '</span>';
            }

            echo mbfun_get_action_link_html($post_id, 'microblog-widget-bottom-comment');

            echo '</div><div style="clear:both;"></div>';
            echo '</li><hr>';
        }

        echo '</ul>';
        echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        wp_reset_postdata();
        mbfun_enqueue_scripts_and_styles();
    }
}
