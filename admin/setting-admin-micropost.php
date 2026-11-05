<?php
if (! defined('ABSPATH')) {
	exit;
}
/*
PluName: 微博 MicroBlog
PluLink: https://www.webersongao.com/tag/microblog
Desc: 将您的WordPress网站用作微博；在小部件中显示微博或使用短代码显示微博。
Author: WebersonGao
AuthorLink: https://www.webersongao.com
Based on simple-microblogging plugin developed by Samuel Coskey, Victoria Gitman(http://boolesrings.org),Thanks to obaby(https://h4ck.org.cn/) Thanks to ChatGPT.
*/

/*
 * Admin Panel
*/

/*
 * 微博设置：字段注册与回调。页面骨架见 class-mb-admin-page.php。
 */

// Register plugin settings
add_action('admin_init', 'microblog_micropost_setting_admin');
function microblog_micropost_setting_admin() {
    register_setting(
        'microblog_micropost_setting_field',
        'theme_microblog_data',
        'theme_microblog_data_sanitize'
    );
    
    // General settings section
    add_settings_section(
        'microblog_micropost_section_base',
        __('Style Settings', MICROBLOG_DOMAIN),
        'microblog_micropost_section_base_callback',
        'microblog_micropost_section_name'
    );

    add_settings_field(
        'microblog_post_title_show',
        __('Title', MICROBLOG_DOMAIN),
        'microblog_post_title_show_input',
        'microblog_micropost_section_name',
        'microblog_micropost_section_base'
    );
    add_settings_field(
        'microblog_post_micro_foward',
        __('Repost', MICROBLOG_DOMAIN),
        'microblog_post_micro_foward_input',
        'microblog_micropost_section_name',
        'microblog_micropost_section_base'
    );
    add_settings_field(
        'microblog_post_rss_feed',
        __('RSS Feed', MICROBLOG_DOMAIN),
        'microblog_post_rss_feed_input',
        'microblog_micropost_section_name',
        'microblog_micropost_section_base'
    );
    add_settings_field(
        'microblog_post_editor_func',
        __('Editor', MICROBLOG_DOMAIN),
        'microblog_post_editor_func_callback',
        'microblog_micropost_section_name',
        'microblog_micropost_section_base'
    );
    add_settings_field(
        'microblog_post_title_listdate',
        __('Time Format', MICROBLOG_DOMAIN),
        'microblog_post_title_listdate_input',
        'microblog_micropost_section_name',
        'microblog_micropost_section_base'
    );
    add_settings_field(
        'microblog_post_slug_name',
        __('URL slug', MICROBLOG_DOMAIN),
        'microblog_post_slug_name_callback',
        'microblog_micropost_section_name',
        'microblog_micropost_section_base'
    );

    // Shortcode settings section
    add_settings_section(
        'microblog_micropost_section_shortcode',
        __('Shortcode & Widget', MICROBLOG_DOMAIN),
        'microblog_micropost_section_shortcode_callback',
        'microblog_micropost_section_name'
    );

    add_settings_field(
        'microblog_post_title_listnumber',
        __('Number of Microblogs', MICROBLOG_DOMAIN),
        'microblog_post_title_listNumber_input',
        'microblog_micropost_section_name',
        'microblog_micropost_section_shortcode'
    );
    add_settings_field(
        'microblog_post_content_bottom',
        __('Bottom Bar', MICROBLOG_DOMAIN),
        'microblog_post_content_bottom_input',
        'microblog_micropost_section_name',
        'microblog_micropost_section_shortcode'
    );
    add_settings_field(
        'microblog_post_image_lightbox',
        __('Lightbox', MICROBLOG_DOMAIN),
        'microblog_post_image_lightbox_input',
        'microblog_micropost_section_name',
        'microblog_micropost_section_shortcode'
    );
}

function theme_microblog_data_sanitize($input) {
    if (!is_array($input)) {
        return mbfun_get_micropost_settings();
    }

    if (isset($input['mb_slug_name']) && '' !== $input['mb_slug_name']) {
        $slug_name = strtolower(sanitize_title($input['mb_slug_name']));

        if (preg_match('/^[a-z0-9]{1,20}$/', $slug_name)) {
            $input['mb_slug_name'] = $slug_name;
        } else {
            add_settings_error(
                'theme_microblog_data',
                'invalid-slug',
                __('Invalid slug. Use lowercase letters and numbers only, 1-20 characters.', MICROBLOG_DOMAIN),
                'error'
            );

            unset($input['mb_slug_name']);

            // 非法值不落库，保留原设置。
            $old = mbfun_get_micropost_settings();

            if (isset($old['mb_slug_name'])) {
                $input['mb_slug_name'] = $old['mb_slug_name'];
            }
        }
    }

    return $input;
}


// Display general settings section content
function microblog_micropost_section_base_callback() {
    echo '<p>' . esc_html__('Make sure you have added the [microblog] shortcode to a page or the Microblog widget to a sidebar.', MICROBLOG_DOMAIN) . '</p>';
}

// Display shortcode settings section content
function microblog_micropost_section_shortcode_callback() {
    echo '<p>' . esc_html__('Make sure "Show title" is enabled.', MICROBLOG_DOMAIN) . '</p>';
}

// Display settings fields input
function microblog_post_title_show_input() {
    $options = mbfun_get_micropost_settings();
    $value = isset($options['mb_title_show']) ? $options['mb_title_show'] : false;
    ?>
    <label>
        <input type='checkbox' name='theme_microblog_data[mb_title_show]' value='1' <?php checked($value, true); ?> />
        <?php esc_html_e('Show title', MICROBLOG_DOMAIN); ?>
    </label>
    <?php
}

function microblog_post_title_listdate_input() {
    $options = mbfun_get_micropost_settings();
    $value = isset($options['mb_date_format']) ? $options['mb_date_format'] : '';
    ?>
    <label class="microblog-admin-option-label">
        <input type='radio' name='theme_microblog_data[mb_date_format]' value='<?php echo esc_attr('date_hide'); ?>' <?php checked($value, 'date_hide'); ?> />
        <?php esc_html_e('Hide time', MICROBLOG_DOMAIN); ?>
        <!--echo date_i18n('m-d H:i', current_time('timestamp'));-->
    </label>
    <label class="microblog-admin-option-label">
        <input type='radio' name='theme_microblog_data[mb_date_format]' value='<?php echo esc_attr('date_date'); ?>' <?php checked($value, 'date_date'); ?> />
        <?php echo esc_html( date_i18n( get_option('date_format') ) ); ?>
    </label>
    <label class="microblog-admin-option-label">
        <input type='radio' name='theme_microblog_data[mb_date_format]' value='<?php echo esc_attr('date_human'); ?>' <?php checked($value, 'date_human'); ?> />
        <?php esc_html_e('1 minute ago / 3 days ago...', MICROBLOG_DOMAIN); ?>
    </label>
    <?php
}

function microblog_post_micro_foward_input() {
    $options = mbfun_get_micropost_settings();
    $value = isset($options['mb_foward_enable']) ? $options['mb_foward_enable'] : false;
    ?>
    <label>
        <input type='checkbox' name='theme_microblog_data[mb_foward_enable]' value='1' <?php checked($value, true); ?> />
        <?php esc_html_e('Enable reposting', MICROBLOG_DOMAIN); ?>
    </label>
    <?php
}

function microblog_post_rss_feed_input() {
    $options = mbfun_get_micropost_settings();
    $value = isset($options['mb_rss_feed']) ? $options['mb_rss_feed'] : false;
    ?>
    <label>
        <input type='checkbox' name='theme_microblog_data[mb_rss_feed]' value='1' <?php checked($value, true); ?> />
        <?php
        printf(
            /* translators: %s: RSS link. */
            esc_html__('Include in the main site feed ( %s )', MICROBLOG_DOMAIN),
            '<a target="_blank" href="' . esc_url(home_url('/feed')) . '" class="rss">RSS</a>'
        );
        ?>
    </label>
    <?php
}


// Display settings fields input for editor functionality
function microblog_post_editor_func_callback() {
    $options = mbfun_get_micropost_settings();
    $editor_func = isset($options['mb_editor_func']) ? $options['mb_editor_func'] : array();
    ?>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='theme_microblog_data[mb_editor_func][]' value='mb_author' <?php if (in_array('mb_author', $editor_func)) echo 'checked="checked"'; ?> />
        <?php esc_html_e('Author', MICROBLOG_DOMAIN); ?>
    </label>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='theme_microblog_data[mb_editor_func][]' value='mb_excerpt' <?php if (in_array('mb_excerpt', $editor_func)) echo 'checked="checked"'; ?> />
        <?php esc_html_e('Excerpt', MICROBLOG_DOMAIN); ?>
    </label>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='theme_microblog_data[mb_editor_func][]' value='mb_thumbnail' <?php if (in_array('mb_thumbnail', $editor_func)) echo 'checked="checked"'; ?> />
        <?php esc_html_e('Featured image', MICROBLOG_DOMAIN); ?>
    </label>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='theme_microblog_data[mb_editor_func][]' value='mb_posttag' <?php if (in_array('mb_posttag', $editor_func)) echo 'checked="checked"'; ?> />
        <?php esc_html_e('Microblog topics', MICROBLOG_DOMAIN); ?>
    </label>
    <?php
}

function microblog_post_slug_name_callback() {
    $options = mbfun_get_micropost_settings();
    $value = isset($options['mb_slug_name']) ? sanitize_title($options['mb_slug_name']) : ''; // 获取已保存的设置值
    ?>
    <input type='text' name='theme_microblog_data[mb_slug_name]' value='<?php echo esc_attr($value); ?>' maxlength='20' style='width: 120px;' />
    <p class="description"><?php esc_html_e('Letters and numbers only, 1-20 characters. Leave empty to use "microposts".', MICROBLOG_DOMAIN); ?></p>
    <?php
}

function microblog_post_title_listNumber_input() {
    $options = mbfun_get_micropost_settings();
    $value = isset($options['mb_codepost_num']) ? intval($options['mb_codepost_num']) : 5;
    $value = max(3, min($value, 20));

    ?>
    <label>
        <input type='number' name='theme_microblog_data[mb_codepost_num]' value='<?php echo esc_attr($value); ?>' min='3' max='20' />
        &nbsp;<?php echo esc_html__('Number per page (range: 3-20)', MICROBLOG_DOMAIN ); ?>
    </label>
    <?php
}

function microblog_post_image_lightbox_input() {
    $options = mbfun_get_micropost_settings();
    $value = isset($options['mb_image_lightbox']) ? $options['mb_image_lightbox'] : false;
    ?>
    <label>
    <input type='checkbox' name='theme_microblog_data[mb_image_lightbox]' value='1' <?php checked($value, true); ?> />
    <?php
    printf(
        /* translators: 1: link to the Simple Lightbox plugin, 2: link to a Lightbox explanation. */
        esc_html__('Enable? (make sure the %1$s plugin is installed, or your theme supports %2$s)', MICROBLOG_DOMAIN),
        '<a href="https://wordpress.org/plugins/simple-lightbox" target="_blank">' . esc_html__('Simple Lightbox', MICROBLOG_DOMAIN) . '</a>',
        '<a href="https://fooplugins.com/what-is-a-lightbox-in-wordpress" target="_blank">' . esc_html__('Lightbox effects', MICROBLOG_DOMAIN) . '</a>'
    );
    ?>
    </label>
    <?php
    // Simple Lightbox 未安装且当前用户可安装插件时，提供一键安装/启用入口。
    if (!defined('SLB_VERSION') && !class_exists('SLB_Lightbox') && current_user_can('install_plugins')) {
        $slb_installed = file_exists(WP_PLUGIN_DIR . '/simple-lightbox/simple-lightbox.php');

        if ($slb_installed && current_user_can('activate_plugins')) {
            $slb_url = wp_nonce_url(admin_url('plugins.php?action=activate&plugin=simple-lightbox%2Fsimple-lightbox.php'), 'activate-plugin_simple-lightbox/simple-lightbox.php');
            $slb_label = __('Activate Simple Lightbox', MICROBLOG_DOMAIN);
        } elseif (!$slb_installed) {
            $slb_url = wp_nonce_url(admin_url('update.php?action=install-plugin&plugin=simple-lightbox'), 'install-plugin_simple-lightbox');
            $slb_label = __('Install Simple Lightbox', MICROBLOG_DOMAIN);
        } else {
            $slb_url = '';
            $slb_label = '';
        }

        if ($slb_url) {
            printf(
                ' <a class="button button-small" href="%s" style="margin-left:8px;">%s</a>',
                esc_url($slb_url),
                esc_html($slb_label)
            );
        }
    }
}

function microblog_post_content_bottom_input() {
    $options = mbfun_get_micropost_settings();
    $value = isset($options['mb_content_bottom']) ? $options['mb_content_bottom'] : array();
    ?>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='theme_microblog_data[mb_content_bottom][]' value='<?php echo esc_attr('conbottomuser'); ?>' <?php if (in_array('conbottomuser', $value)) echo 'checked="checked"'; ?> />
        <?php esc_html_e('Show avatar', MICROBLOG_DOMAIN); ?>
    </label>
    <label>
        <input type='checkbox' name='theme_microblog_data[mb_content_bottom][]' value='<?php echo esc_attr('conbottomtag'); ?>' <?php if (in_array('conbottomtag', $value)) echo 'checked="checked"'; ?> />
        <?php esc_html_e('Show topics', MICROBLOG_DOMAIN); ?>
    </label>
    <?php
}

?>