<?php
/*
PluName: 微博 MicroBlog
PluLink: https://www.webersongao.com/tag/microblog
Desc: 将您的WordPress网站用作微博；在小部件中显示微博或使用短代码显示微博。
Author: WebersonGao
AuthorLink: https://www.webersongao.com
Based on simple-microblogging plugin developed by Samuel Coskey, Victoria Gitman(http://boolesrings.org),Thanks to obaby(https://h4ck.org.cn/) Thanks to ChatGPT.
*/

/*
 * general Panel
*/

// Function to display Gallery settings section
function microblog_general_settings_section() {
?>
    <div class="general_settings_header">
        <?php microblog_general_settings_section_header(); ?>
    </div>
    <?php settings_errors('microblog_general_data'); ?>
    <form method="post" action="options.php">
        <?php if ( isset( $_GET['settings-updated'] ) ) { ?>
            <div class="updated"><p><?php _e( 'Plugin settings have been updated.', MICROBLOG_DOMAIN ); ?></p></div>
        <?php } ?>
        <?php
        settings_fields('microblog_genaral_plugin_settings');
        do_settings_sections('microblog_general_section_name');
        submit_button('保存 设置');
        ?>
    </form>
    <!-- <button type="button" id="clean_microblog_data" class="button">清理 MicroBlog 数据</button>
    <script>
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        jQuery(document).ready(function($) {
            $('#clean_microblog_data').click(function() {
                var data = {
                    'action': 'clean_microblog_data'
                };
                $.post(ajaxurl, data, function(response) {
                    alert('MicroBlog 数据已清理');
                });
            });
        });
    </script> -->
<?php
}

// add_action('wp_ajax_clean_microblog_data', 'clean_microblog_data_callback');
// function clean_microblog_data_callback() {
//     if (delete_option('microblog_general_data')) {
//         error_log('MicroBlog 数据已成功清理');
//         add_settings_error('microblog_general_data', 'microblog_data_cleaned', __('MicroBlog 数据已成功清理'), 'success');
//     } else {
//         error_log('MicroBlog 数据清理失败，请重试');
//         add_settings_error('microblog_general_data', 'microblog_data_not_cleaned', __('MicroBlog 数据清理失败，请重试'), 'error');
//     }
//     // Save any settings errors
//     error_log('MicroBlog 数据清理失败，=====================');
//     settings_errors('microblog_general_data');
//     die(); // It's important to end AJAX functions with die()
// }

function microblog_general_settings_section_header() {
    echo '</br>';
    // echo '<h3>' . esc_html__('Gallery Tab', MICROBLOG_DOMAIN ) . '</h3>';
}

add_action('admin_init', 'microblog_general_setting_admin');
function microblog_general_setting_admin() {

    register_setting(
        'microblog_genaral_plugin_settings',
        'microblog_general_data',
        'microblog_general_data_sanitize'
    );

    // Gallery settings section
    add_settings_section(
        'microblog_general_section_base',
        '基础设置',   
        'microblog_general_section_base_callback',
        'microblog_general_section_name'
    );

    add_settings_field(
        'microblog_module_display',
        '微连载',
        'microblog_module_display_input',
        'microblog_general_section_name',
        'microblog_general_section_base'
    );

    add_settings_field(
        'microblog_configtest_display',
        '测试数据',
        'microblog_configtest_display_input',
        'microblog_general_section_name',
        'microblog_general_section_base'
    );
}


function microblog_general_data_sanitize($input) {
    
    // 刷新模块
    $module_live = mbfun_get_general_option('mb_module_liveblog', false);
    if (isset($input['mb_module_liveblog']) && ($input['mb_module_liveblog'] !== $module_live)) {
        mbfun_update_microblog_display_module();
    }

    return $input;
}


// Display shortcode settings section content
function microblog_general_section_base_callback() {
    echo '<p>以下为 MicroBlog 插件的基础配置，请按需调整 </p>';
}


// Display settings fields input
function microblog_module_display_input() {
    $options = mbfun_get_general_settings();
    $value = isset($options['mb_module_liveblog']) ? $options['mb_module_liveblog'] : false;
    ?>
    <label>
        <input type='checkbox' name='microblog_general_data[mb_module_liveblog]' value='1' <?php checked($value, true); ?> />
         启用 ( 关闭后,不会删除数据,也不会导致配置项丢失 )
    </label>
    <?php
}

// Display settings fields input
function microblog_configtest_display_input() {
    $options = mbfun_get_general_settings();
    $editor_func = isset($options['mb_test_func']) ? $options['mb_test_func'] : array();
    ?>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='microblog_general_data[mb_test_func][]' value='mb_author' <?php if (in_array('mb_author', $editor_func)) echo 'checked="checked"'; ?> />
        傻子
    </label>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='microblog_general_data[mb_test_func][]' value='mb_thumbnail' <?php if (in_array('mb_thumbnail', $editor_func)) echo 'checked="checked"'; ?> />
        呆子
    </label>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='microblog_general_data[mb_test_func][]' value='mb_posttag' <?php if (in_array('mb_posttag', $editor_func)) echo 'checked="checked"'; ?> />
        聋子
    </label>
    <label class="microblog-admin-option-label">
        <input type='checkbox' name='microblog_general_data[mb_test_func][]' value='mb_excerpt' <?php if (in_array('mb_excerpt', $editor_func)) echo 'checked="checked"'; ?> />
        瞎子
    </label>
    <?php
}



?>