<?php
// microblog-widget.php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once(plugin_dir_path(__FILE__) . 'microblog-functions.php');

add_action('widgets_init', 'mbfun_load_microblog_widget');
function mbfun_load_microblog_widget() {
    register_widget('Microblog_SideWidget');
}

class Microblog_SideWidget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'microblog_widget',
            '微博',
            array('description' => '允许您显示微博条目列表，并将它们从文章中排除。')
        );
    }

    public function form($instance) {
        $defaults = array(
            'numberposts' => '5',
            'use_excerpt' => '',
            'title' => '',
            'rss' => '',
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">标题:</label>
            <input type="text" name="<?php echo esc_attr($this->get_field_name('title')) ?>" id="<?php echo esc_attr($this->get_field_id('title')) ?> " value="<?php echo esc_attr($instance['title']) ?>" size="20">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('numberposts')); ?>">展示数量:</label>
            <input type="text" name="<?php echo esc_attr($this->get_field_name('numberposts')); ?>" id="<?php echo esc_attr($this->get_field_id('numberposts')); ?>" value="<?php echo esc_attr($instance['numberposts']); ?>">
        </p>
        <p>
            <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('use_excerpt')); ?>" name="<?php echo esc_attr($this->get_field_name('use_excerpt')); ?>" <?php if ($instance['use_excerpt']) echo 'checked="checked"' ?> />
            <label for="<?php echo esc_attr($this->get_field_id('use_excerpt')); ?>">是否显示摘要 ?</label>
        </p>
        <p>
            <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('rss')); ?>" name="<?php echo esc_attr($this->get_field_name('rss')); ?>" <?php if ($instance['rss']) echo 'checked="checked"' ?> />
            <label for="<?php echo esc_attr($this->get_field_id('rss')); ?>">是否展示RSS链接?</label>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        $instance['numberposts'] = $new_instance['numberposts'];
        $instance['use_excerpt'] = $new_instance['use_excerpt'];
        $instance['rss'] = $new_instance['rss'];

        return $instance;
    }

    public function widget($args, $instance) {
        extract($args);
        $title = sanitize_text_field($instance['title']);
        $numberposts = $instance['numberposts'];
        $use_excerpt = $instance['use_excerpt'];
        $show_rss = $instance['rss'];

        // retrieve posts information from database
        $query = array(
            'post_type' => 'micropost',
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page' => $numberposts,
            'post_status' => 'publish',
            'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
        );
        $options = mbfun_get_micropost_settings();
        $show_date = true;//(isset($options) && isset($options['mb_date_show']) && $options['mb_date_show']) ? true : false;
        
        $query_results = new WP_Query($query);
        global $post;
        // build the widget contents!
        $out = ''; // 定义输出变量
        $out .= "<ul class='microblog-widget'>";
        while ($query_results->have_posts()) {
            $query_results->the_post();
            $out .= "<li>";
            $post_title = the_title('', '', false);
            if ($post_title) {
                $out .= "<div class='microblog-widget-head'>";
                $out .= "<span class='microblog-widget-head-title'><a target='_blank' href='" . get_permalink() . "'>" . $post_title . "</a></span>";
                
                $out .= "</div>";
            }
            $out .= "<div class='microblog-widget-content'>";
            if ($use_excerpt) {
                add_filter('excerpt_more', 'mbfun_micropost_excerpt_more');
                $out .= "<p>" . get_the_excerpt() . "</p>";
                remove_filter('excerpt_more', 'mbfun_micropost_excerpt_more');
            } else {
                $out .= trim($post->post_content);
            }
            $out .= "</div>";
            if (comments_open() || $show_date) {
                $out .= "<div class='microblog-widget-bottom'>";
                if ($show_date) {
                    $out .= "<span class='microblog-widget-bottom-date'>" . mbfun_micropost_format_time(strtotime($post->post_date)) . "</span>";
                }
                if (comments_open()) {
                    $out .= "<span class='microblog-widget-bottom-comment'><a target='_blank' href='" . get_permalink() . "'><img src='" . plugin_dir_url(dirname(__FILE__)) .'assets/images/post-comment-icon.png'. "' style='width: 16px; height: 16px;'>&nbsp;" . get_comments_number() . "</a>" . "</span>";
                } else {
                    $out .= "<span class='microblog-widget-bottom-comment'><a target='_blank' href='" . get_permalink() . "'><img src='" . plugin_dir_url(dirname(__FILE__)) . 'assets/images/post-more-icon.png' . "' style='width: 16px; height: 16px;'>&nbsp;</a></span>";
                }
                $out .= "</div>"; 
                $out .= "<div style='clear:both;'></div>";
            }
            $out .= "</li><hr>";
        }
        $out .= "</ul>";
        // Print the widget for the sidebar
        echo wp_kses_post($before_widget);
        echo wp_kses_post($before_title);
        echo esc_html($title);
        if ($show_rss) {
            $rssout = "";
            $rssout .= "<span class='microblog-widget-rss'>";
            $rssout .= '<a target="_blank" href="' . esc_url(get_site_url() . '/' . mbfun_get_micropost_slug_name() . '/feed') . '" class="rss"><img src="' . esc_url(site_url() . '/wp-includes/images/rss.png') . '" style="width: 18px; height: 18px;"></a>';
            $rssout .= "</span>";
            echo wp_kses_post($rssout);
        }
        echo wp_kses_post($after_title);
        echo wp_kses_post($out);
        echo wp_kses_post($after_widget);        

        // Clean up
        wp_reset_postdata();
        mbfun_enqueue_scripts_and_styles();
    }
}

?>