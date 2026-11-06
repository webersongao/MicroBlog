<?php
/**
 * 设置访问层：全插件唯一的配置读写入口。
 *
 * 旧实现有 3 组配置（微博 / 基础 / 连载），每组各写一遍
 * `get_option` + `mbfun_get_xxx_option`，且用 !empty() 判断，
 * 导致 0、false、空数组永远取不到值。这里统一为一张表 + 一次请求内缓存。
 *
 * @package MicroBlog
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 配置组 => option 名。
 *
 * @return array
 */
function mbfun_setting_groups()
{
    return array(
        'micropost' => 'microblog_micropost_data',
        'general'   => 'microblog_general_data',
        'liveblog'  => 'microblog_liveblog_data',
    );
}

/**
 * 读取整组配置。
 *
 * @param string $group   组名。
 * @param bool   $refresh 是否强制重新读取。
 * @return array
 */
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

/**
 * 读取单个配置项。存在即返回，不受 0 / false / 空数组影响。
 *
 * @param string $key     配置键。
 * @param mixed  $default 缺省值。
 * @param string $group   组名。
 * @return mixed
 */
function mbfun_setting($key, $default = null, $group = 'micropost')
{
    $settings = mbfun_get_settings($group);

    return array_key_exists($key, $settings) ? $settings[$key] : $default;
}

/**
 * 清空缓存（保存设置、切换模块后调用）。
 *
 * @param string|null $group 组名，null 表示全部。
 * @return void
 */
function mbfun_flush_settings_cache($group = null)
{
    unset($GLOBALS['mbfun_settings_cache']);

    if (null !== $group) {
        mbfun_get_settings($group);
    }
}

// option 被更新后让缓存失效，避免同一请求内读到旧值。
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
