<?php

/*
PluName: 微博 MicroBlog
PluLink: https://www.webersongao.com/microposts
Desc: 将您的WordPress网站用作微博；在小部件中显示微博或使用短代码显示微博。
Ver: 1.0
Author: WebersonGao
AuthorLink: https://www.webersongao.com
Based on simple-microblogging plugin developed by original Samuel Coskey, Victoria Gitman(http://boolesrings.org),obaby(https://h4ck.org.cn/) Thanks to ChatGPT.
*/

/*
 * admin Panel
*/

function microblog_admin_enqueue_scripts() {
    $plugin_data = get_plugin_data( __FILE__ ); 
    $plugin_version = ( $plugin_data && isset( $plugin_data['Version'] ) )  ? $plugin_data['Version'] : '1.0';
    wp_enqueue_style( 'microblog-admin-css', plugin_dir_url( __FILE__ ) . 'css/admin-style.css', array(), $plugin_version );
}
add_action( 'admin_enqueue_scripts', 'microblog_admin_enqueue_scripts' );

// 创建控制面板页面
function microblog_admin_settings() {
    // 检查用户权限
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h2>微博-控制面板</h2>
        <form method="post" action="options.php">
            <?php
            // WordPress 提供的设置存储功能
            settings_fields('microblog_plugin_settings');
            do_settings_sections('microblog-settings');
            submit_button('保存 设置');
            ?>
        </form>
    </div>
    <?php
}

// 添加设置字段和选项
function microblog_plugin_setting_admin() {
    // 注册设置存储
    register_setting(
        'microblog_plugin_settings', // 设置组名
        'microblog_setting_data'   // 设置选项名
    );

    // 添加设置节 base
    add_settings_section(
        'general_settings_section_base',   // 节ID
        '基本设置',    // 节标题
        'general_settings_section_basecallback', // 显示节内容的回调函数
        'microblog-settings'   // 页面slug
    );

    // 添加设置字段
    add_settings_field(
        'microblog_post_title_show',       // 字段ID
        '标题显示',                // 字段标签
        'microblog_post_title_show_input', // 字段输入的回调函数
        'microblog-settings',           // 页面slug
        'general_settings_section_base'             // 节ID
    );

    add_settings_field(
        'microblog_post_title_listdate',       
        '显示日期',         
        'microblog_post_title_listdate_input', 
        'microblog-settings',           
        'general_settings_section_base'             
    );

    // --------------   短代码 ------------ //
    // 添加设置节 短代码
    add_settings_section(
        'general_settings_section_shortcode', 
        '</br>短代码设置',   
        'general_settings_section_shortcodecallback', 
        'microblog-settings'  
    );

    add_settings_field(
        'microblog_post_title_listnumber',       
        '每页微博数',         
        'microblog_post_title_listNumber_input',
        'microblog-settings',           
        'general_settings_section_shortcode' 
    );

    // 添加设置字段
    add_settings_field(
        'microblog_post_title_position',       
        '标题展示位置',        
        'microblog_post_title_position_input', 
        'microblog-settings',          
        'general_settings_section_shortcode'             
    );

}
add_action('admin_init', 'microblog_plugin_setting_admin');

// 显示设置节内容
function general_settings_section_basecallback() {
    $out = '';
    $out .= '<div class="microblog-admin-header" style="margin-bottom: 15px;">';
    $out .= '<div class="microblog-admin-leftbar">';
    $out .= '<span class="microblog-admin-logo">';
    $out .= '<img src="' . esc_url(plugin_dir_url(__FILE__)) . 'images/microblog-logo.png">';
    $out .= '</span>';
    $out .= '<span class="microblog-admin-bar-span">' . esc_html__('MicroBlog - 基于WP开发的微博/说说插件 No1', 'microblog') . '</span><span class="microblog-admin-bar-free">' . esc_html__('Free V1.0', 'microblog') . '</span>';
    $out .= '</div>';
    $out .= '</div>';
    echo $out;
}


function general_settings_section_shortcodecallback() {
    echo '<p>请打开标题显示: 基础设置 -> 标题显示 </p>';
}

// 显示设置字段输入框
function microblog_post_title_show_input() {
    $options = get_option('microblog_setting_data');
    $value = isset($options['mb_title_show']) ? $options['mb_title_show'] : false;
    ?>
    <label>
        <input type='checkbox' name='microblog_setting_data[mb_title_show]' value='1' <?php checked($value, true); ?> />
        是否显示标题
    </label>
    <?php
}

function microblog_post_title_listdate_input() {
    $options = get_option('microblog_setting_data');
    $value = isset($options['mb_date_show']) ? $options['mb_date_show'] : false;
    ?>
    <label>
        <input type='checkbox' name='microblog_setting_data[mb_date_show]' value='1' <?php checked($value, true); ?> />
        是否显示时间
    </label>
    <?php
}

function microblog_post_title_listNumber_input() {
    $options = get_option('microblog_setting_data');
    $value = isset($options['mb_codepost_num']) ? intval($options['mb_codepost_num']) : 5;
    $value = max(3, min($value, 20));

    ?>
    <label>
        <input type='number' name='microblog_setting_data[mb_codepost_num]' value='<?php echo esc_attr($value); ?>' min='3' max='20' />
        &nbsp;<?php esc_html_e('显示数量[3, 20]', 'microblog'); ?>
    </label>
    <?php
}

function microblog_post_title_position_input() {
    $options = get_option('microblog_setting_data');
    $value = isset($options['mb_title_position']) ? $options['mb_title_position'] : array();
    ?>
    <label>
        <input type='checkbox' name='microblog_setting_data[mb_title_position][]' value='<?php echo esc_attr('titletop'); ?>' <?php if (in_array('titletop', $value)) echo 'checked="checked"'; ?> />
        头像下方
    </label>
    <label>
        <input type='checkbox' name='microblog_setting_data[mb_title_position][]' value='<?php echo esc_attr('titlebottom'); ?>' <?php if (in_array('titlebottom', $value)) echo 'checked="checked"'; ?> />
        正文下方（评论按钮左侧）
    </label>
    <?php
}


?>
