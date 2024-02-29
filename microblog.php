<?php
/*
 * Plugin Name: 微博 MicroBlog
 * Plugin URI: https://www.webersongao.com/tag/microblog
 * Description: Use your WordPress site as a microblog; display the micrposts in a widget or using a shortcode.
 * Version: 1.4
 * Author: WebersonGao
 * Author URI: https://www.webersongao.com
 * Based on simple-microblogging plugin developed by original Samuel Coskey, Victoria Gitman(http://boolesrings.org),obaby(https://h4ck.org.cn/) Thanks to ChatGPT.
 */

define('MICROBLOG_BASEFOLDER', plugin_basename(dirname(__FILE__)));

$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'));
$plugin_version = ($plugin_data && isset($plugin_data['Version'])) ? $plugin_data['Version'] : '1.4';
global $microblog_slug_name, $plugin_version;

add_action('init', 'create_micropost_type');
function create_micropost_type() {
    $options = get_option('microblog_setting_data');
    $supports = array('title', 'editor','comments','comments'); // 默认支持的参数
    $slug_name = get_microblog_slug_name(); // 使用函数获取微博 Slug 名称
    // 如果$options存在并且不为空，则更新supports参数
    if (!empty($options)) {
        if (isset($options['mb_editor_func'])) {
            $editor_func = $options['mb_editor_func'];
            if (in_array('mb_author', $editor_func)) {
                $supports[] = 'author';
            }
            if (in_array('mb_thumbnail', $editor_func)) {
                $supports[] = 'thumbnail';
            }
            if (in_array('mb_excerpt', $editor_func)) {
                $supports[] = 'excerpt';
            }
            // 检查是否存在重复的支持项
            $supports = array_unique($supports);
        }
    }
    $valid_supports = array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments');
    $supports = array_intersect($supports, $valid_supports);
    if (empty($supports)) { $supports = array('title', 'editor','comments');}
    register_post_type('micropost', array(
        'labels' => array(
            'name' => __('微博'),
            'menu_name' => __('微博'),
            'singular_name' => __('微博'),
            'description' => __('通过 MicroBlog 你可以轻松创建一条微博，通过短代码或插件增加到某页面或边栏'),
        ),
        'has_archive' => true,
        'menu_icon' => 'dashicons-format-status',
        'menu_position' => 5,
        'public' => true,
        'rewrite' => array('slug' => $slug_name), // 使用动态获取的 slug
        'supports' => $supports, // 更新为动态获取的支持项
        'show_in_rest' => use_block_editor_for_post_type('post'), // 跟站点编辑器保持一致
        //  'taxonomies' => array ( 'category', 'post_tag' ),
    ));
}

// 注册激活插件时，设置默认数据
register_activation_hook( __FILE__, 'microblog_plugin_data_activation' );
function microblog_plugin_data_activation() {
    $options = get_option('microblog_setting_data');
    if (empty($options)) {
        $defaults = array(
            'mb_title_show' => true, // 默认为显示标题
            'mb_date_show' => true, // 默认为显示日期
            'mb_title_position' => array('titlebottom'), // 默认标题位置为 titlebottom
        );
        add_option('microblog_setting_data', $defaults);
    }
    // 刷新重写规则
    microblog_rewrite_flush();
}

function microblog_rewrite_flush() {
    create_micropost_type();
    flush_rewrite_rules();
}

// 注册卸载插件时运行的函数
register_uninstall_hook( __FILE__, 'microblog_plugin_uninstall' );
function microblog_plugin_uninstall() {
    // 删除选项
    delete_option('microblog_setting_data');
    delete_option('widget_microblog_widget');
    // 还可以执行其他清理操作，如删除数据库条目等
}

/*
 * MicroBlog widget code
*/
add_action('widgets_init', 'load_microblog_widget');
function load_microblog_widget() {
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
        $rss = $instance['rss'];

        // retrieve posts information from database
        $query = array(
            'post_type' => 'micropost',
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page' => $numberposts,
            'post_status' => 'publish',
            'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
        );
        $options = get_option('microblog_setting_data');
        $show_date = (isset($options) && isset($options['mb_date_show']) && $options['mb_date_show']) ? true : false;
        $query_results = new WP_Query($query);

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
                add_filter('excerpt_more', 'micropost_excerpt_more');
                $out .= "<p>" . get_the_excerpt() . "</p>";
                remove_filter('excerpt_more', 'micropost_excerpt_more');
            } else {
                $out .= trim($post->post_content);
            }
            $out .= "</div>";
            if (comments_open() || $show_date) {
                $out .= "<div class='microblog-widget-bottom'>";
                if ($show_date) {
                    $out .= "<span class='microblog-widget-bottom-date'>" . get_the_date(get_option('date_format')) . "</span>";
                }
                if (comments_open()) {
                 $out .= "<span class='microblog-widget-bottom-comment'>" . "<a target='_blank' href='" . get_permalink() . "'>" . "<img src='" . plugins_url('/images/post-comment-icon.png', __FILE__) . "' style='width: 16px; height: 16px;'>&nbsp;" . get_comments_number() . "</a>" . "</span>";
                }
                $out .= "</div>"; 
                $out .= "<div style='clear:both;'></div>";
            }
            $out .= "</li><hr>";
        }
        $out .= "</ul>";
        // Print the widget for the sidebar
        echo $before_widget;
        echo $before_title;
        echo $title;
        if ($rss) {
            $rssout = "";
            $rssout .= "<span class='microblog-widget-rss'>";
            $rssout .= '<a target="_blank" href="' . get_site_url() . '/' . get_microblog_slug_name() . '/feed" class="rss"><img src="' . site_url() . '/wp-includes/images/rss.png" style="width: 18px; height: 18px;"></a>';
            $rssout .= "</span>";
            echo $rssout;
        }
        echo $after_title;
        echo $out;
        echo $after_widget;

        // Clean up
        wp_reset_postdata();
        microblog_enqueue_scripts_and_styles();
    }
}

/*
 * MicroBlog shortcode code
*/
add_shortcode('microblog', 'microblog_shortcode');
function microblog_shortcode($atts) {

    $options = get_option('microblog_setting_data');

    extract(shortcode_atts(array(
        'null_text' => '(none)',
        'date_format' => get_option('date_format'),
        'use_excerpt' => '',
        'q' => '',
    ), $atts));

    global $post;
    $show_date = (isset($options) && isset($options['mb_date_show']) && $options['mb_date_show']) ? true : false;
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
        $out .= "<div class='microblog-shortcode-content'>";
        $out .= "<ul class='microblog-shortcode-post'>";
        while ($query_results->have_posts()) {
            $query_results->the_post();
            // global $post; // 添加全局变量 $post
            $author_avatar = get_avatar(get_the_author_meta('ID'), 25);
            $author_name = get_the_author_meta('display_name', get_the_author_meta('ID'));
            $out .= "<li>";
            $out .= "<div class='microblog-shortcode-post-head'>";
            $out .= "<span class='microblog-shortcode-post-avatar'>" . $author_avatar . "</span>";
            $out .= "<span class='microblog-shortcode-post-username'>" . $author_name . "</span>";
            if ($show_date) {
                $out .= "<span class='microblog-shortcode-post-date'>" . get_the_date($date_format) . "</span>";
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
                    $out .= "<span class='microblog-shortcode-post-comment-link'><a target='_blank' href='" . get_permalink() . "'><img src='" . plugins_url('/images/post-comment-icon.png', __FILE__) . "' style='width: 16px; height: 16px;'>&nbsp;" . get_comments_number() . "</a></span>";   
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
        $out .= "<a target='_blank' href='" . home_url(get_microblog_slug_name()) . "'><img src='" . plugins_url('/images/post-more-icon.png', __FILE__) . "' style='width: 16px; height: 16px;'>&nbsp;查看全部...</a></div>";
    } else {
        $out .= "<div class='microblog-shortcode'><p>" . wp_kses($null_text, array()) . "</p></div>";
    }

    $out .= "</div>";
    wp_reset_postdata();
    microblog_enqueue_scripts_and_styles();
    return $out;
}

function micropost_excerpt_more($more) {
    return ' ...';
}

// short post_content
function micropost_shortcode_content() {
    global $post;
    $post_content = $post->post_content;
    //精确地匹配包含单个 <img> 元素的 <a> 标签，即 <a> 标签内只有一个 <img> 元素，没有其他内容
    $post_content = preg_replace('/<a\s[^>]*><img[^>]+><\/a>/', '', $post_content);
    // 移除 caption 标签
    $post_content = preg_replace('/\[caption[^\]]*\]|\[\/caption\]/', '', $post_content);
    $post_content = trim(str_replace('&nbsp;', '', $post_content));
    return $post_content;
}


// -----------   shortcode 结束 --------------


function get_microblog_slug_name() {
    global $microblog_slug_name;
    if (!empty($microblog_slug_name)) {
        return $microblog_slug_name;
    }
    $options = get_option('microblog_setting_data');
    $slug_name = isset($options['mb_slug_name']) ? $options['mb_slug_name'] : 'microposts'; 
    $slug_name = preg_replace('/[^a-zA-Z0-9]/', '', $slug_name); // 过滤非法字符
    if (empty($slug_name)) { $slug_name = 'microposts'; }

    // 设置全局变量
    $microblog_slug_name = $slug_name;

    return $slug_name;
}

// 更新全局变量的示例
function update_global_microblog_option($new_value) {
    if (!is_string($new_value)) { return; }
    global $microblog_slug_name;
    $microblog_slug_name = $new_value;
}

// 引入js
function microblog_enqueue_scripts_and_styles() {
    global $plugin_version;
    wp_enqueue_style('microblog-style', plugins_url('css/microblog-style.css', __FILE__), array(), $plugin_version);
    // wp_enqueue_script('microblog-script', plugins_url('js/microblog-script.js', __FILE__), array(), $plugin_version, true);
}

// Add rewrite rule for microblog permalink structure
add_action('init', 'custom_microblog_rewrite_rule');
function custom_microblog_rewrite_rule() {
    $slug_name = get_microblog_slug_name(); // 获取微博的 slug
    add_rewrite_rule('^' . $slug_name . '/([0-9]+)\.html/?$', 'index.php?post_type=micropost&p=$matches[1]', 'top');
}

// Modify microblog permalink structure
add_filter('post_type_link', 'custom_microblog_permalink', 10, 2);
function custom_microblog_permalink($permalink, $post) {
    if ('micropost' === get_post_type($post)) {
        $slug_name = get_microblog_slug_name();
        return home_url($slug_name . '/' . $post->ID . '.html');
    }
    return $permalink;
}

add_action('init', 'custom_microblog_flush_rewrite_rules');
function custom_microblog_flush_rewrite_rules() {
    flush_rewrite_rules();
}

// 处理分页
add_filter('request', 'remove_page_from_query_string');
function remove_page_from_query_string($query_string) {
    if (isset($query_string['name']) && isset($query_string['paged'])) {
        unset($query_string['name']);
        @list($delim, $page_index) = explode('/', $query_string['paged']);
        $query_string['paged'] = $page_index;
    }
    return $query_string;
}

// adapted from Custom Post Type Category Pagination
function fix_category_pagination($qs) {
    if (isset($qs['category_name']) && isset($qs['paged'])) {
        $qs['post_type'] = get_post_types(array('public' => true, '_builtin' => false));
        array_push($qs['post_type'], 'post');
    }
    return $qs;
}
add_filter('request', 'fix_category_pagination');

// 注册微博设置页面
add_action('admin_menu', 'microblog_setting_page');
function microblog_setting_page() {
    if (!function_exists('microblog_admin_settings')) {
        require_once 'microblog-admin.php';
    }
    add_management_page('微博 MicroBlog - 控制面板', '微博设置', 'manage_options', __FILE__, 'microblog_admin_settings');
}

// 添加插件设置链接
add_filter('plugin_action_links', 'microblog_setting_action_links', 10, 2);
function microblog_setting_action_links($links, $file) {
    if ($file == plugin_basename(__FILE__)) {
        $links[] = '<a href="tools.php?page=' . MICROBLOG_BASEFOLDER . '/microblog.php">设置</a>';
    }
    return $links;
}


?>
