<?php
/**
 * 卸载清理：只删插件自己的配置与重写规则签名。
 *
 * 微博 / 连载的内容属于用户数据，不在这里删除。
 *
 * @package MicroBlog
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$microblog_options = array(
    'microblog_micropost_data',
    'microblog_general_data',
    'microblog_liveblog_data',
    'microblog_rewrite_signature',
    'widget_microblog_widget',
);

foreach ($microblog_options as $microblog_option) {
    delete_option($microblog_option);
}
