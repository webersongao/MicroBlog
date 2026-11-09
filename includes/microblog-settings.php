<?php
/* 设置访问层 */

if (!defined('ABSPATH')) {
    exit;
}

function mbfun_setting_groups()
{
    return array(
        'micropost' => 'microblog_micropost_data',
        'general'   => 'microblog_general_data',
        'liveblog'  => 'microblog_liveblog_data',
    );
}

/* 读取整组配置 */
function mbfun_get_settings($group = 'micropost', $refresh = false)
{
    $groups = mbfun_setting_groups();

    if (!isset($groups[$group])) {
        return array();
    }

    if ($refresh) {
        unset($GLOBALS['mbfun_settings_cache']);
    }

    if (!isset($GLOBALS['mbfun_settings_cache'])) {
        $GLOBALS['mbfun_settings_cache'] = array();
    }

    if (!array_key_exists($group, $GLOBALS['mbfun_settings_cache'])) {
        $value = get_option($groups[$group], array());

        $GLOBALS['mbfun_settings_cache'][$group] = is_array($value) ? $value : array();
    }

    return $GLOBALS['mbfun_settings_cache'][$group];
}

function mbfun_setting($key, $default = null, $group = 'micropost')
{
    $settings = mbfun_get_settings($group);

    return array_key_exists($key, $settings) ? $settings[$key] : $default;
}

/* 清空缓存 */
function mbfun_flush_settings_cache($group = null)
{
    unset($GLOBALS['mbfun_settings_cache']);

    if (null !== $group) {
        mbfun_get_settings($group);
    }
}

add_action('update_option', function ($option) {
    if (in_array($option, mbfun_setting_groups(), true)) {
        mbfun_flush_settings_cache();
    }
});

add_action('add_option', function ($option) {
    if (in_array($option, mbfun_setting_groups(), true)) {
        mbfun_flush_settings_cache();
    }
});
