<?php
/*
PluName: 微博 MicroBlog
PluLink: https://www.webersongao.com/tag/microblog
Desc: 将您的WordPress网站用作微博；在小部件中显示微博或使用短代码显示微博。
Author: WebersonGao
AuthorLink: https://www.webersongao.com
Based on simple-microblogging plugin developed by Samuel Coskey, Victoria Gitman(http://boolesrings.org),Thanks to obaby(https://h4ck.org.cn/) Thanks to ChatGPT.
*/

if ( ! defined('ABSPATH' ) ) {
    exit;
}  
/*
 * 连载设置：字段注册与回调。页面骨架见 class-mb-admin-page.php。
 */

/**
 * Register settings
 *
 * @return void
 */
add_action('admin_init', 'microblog_liveblog_setting_admin' );
function microblog_liveblog_setting_admin() {
	register_setting('microblog_liveblog_setting_field', 'microblog_liveblog_data', 'microblog_liveblog_data_sanitize' );

	foreach ( mbfun_get_registered_settings() as $section => $settings ) {

		add_settings_section(
			'microblog_liveblog_section_' . $section,
			'连载设置',
			'microblog_liveblog_section_desc',
			'microblog_liveblog_section_' . $section
		);

		foreach ( $settings as $option ) {
			$args = wp_parse_args(
				$option,
				array(
					'section'     => $section,
					'id'          => null,
					'desc'        => '',
					'name'        => '',
					'size'        => null,
					'options'     => '',
					'chosen'      => null,
					'placeholder' => null,
				)
			);

			add_settings_field(
				'microblog_liveblog_data[' . $args['id'] . ']',
				$args['name'],
				function_exists('microblog_live_' . $args['type'] . '_callback' ) ? 'microblog_live_' . $args['type'] . '_callback' : 'microblog_live_missing_callback',
				'microblog_liveblog_section_' . $section,
				'microblog_liveblog_section_' . $section,
				$args
			);
		}
	}
}

/**
 * 分区说明。
 *
 * @return void
 */
function microblog_liveblog_section_desc() {
	echo '<p>为文章开启滚动更新的「连载」视图，适合实时记录与发布会直播。</p>';
}

function microblog_liveblog_data_sanitize($input) {


    return $input;
}

/**
 * Get registered settings
 *
 * @return array
 */
function mbfun_get_registered_settings() {

	$mlb_alltypes = mbfun_get_live_all_post_types();
	$mlb_sections = array(
		'general' => array(
			array(
				'id'            => 'ml_post_types',
				'name'          => __('支持', MICROBLOG_DOMAIN ),
				'desc'          => __('勾选需要开启【连载】的内容类型。', MICROBLOG_DOMAIN ),
				'type'          => 'multiple_checkbox',
				'options'       => $mlb_alltypes,
				'default_value' => array('post' ),
			),
			array(
				'id'            => 'ml_theme_style',
				'name'          => __('主题', MICROBLOG_DOMAIN ),
				'desc'          => __('连载区块的配色主题。', MICROBLOG_DOMAIN ),
				'type'          => 'select',
				'options'       => array(
					'light'     => __('浅色', MICROBLOG_DOMAIN ),
					'dark'      => __('深色', MICROBLOG_DOMAIN ),
				),
				'default_value' => 'light',
			),
			array(
				'id'   => 'ml_display_autopolling',
				'name' => __('自动刷新', MICROBLOG_DOMAIN ),
				'desc' => __('自动检查并加载新的连载条目。', MICROBLOG_DOMAIN ),
				'type' => 'checkbox',
			),
			// array(
			// 	'id'   => 'ml_title_prefix',
			// 	'name' => __('标题前缀', MICROBLOG_DOMAIN ),
			// 	'desc' => __('为空则默认：liveblog ,长度(1,20) .', MICROBLOG_DOMAIN ),
			// 	'type' => 'text_input',
			// ),
			array(
				'id'            => 'ml_post_layout',
				'name'          => __('样式布局', MICROBLOG_DOMAIN ),
				'desc'          => __('单条【连载】中需要展示的信息。', MICROBLOG_DOMAIN ),
				'type'          => 'multiple_checkbox',
				'options'       => array(
					'ml_layout_title' => __('标题', MICROBLOG_DOMAIN ),
					'ml_layout_author' => __('作者', MICROBLOG_DOMAIN ),
					'ml_layout_tshare' => __('分享按钮', MICROBLOG_DOMAIN ),
				),
				'default_value' => array('post' ),
			),
			array(
				'id'   => 'ml_display_revort_sort',
				'name' => __('展示顺序', MICROBLOG_DOMAIN ),
				'desc' => __('默认从新到旧；开启后改为从旧到新。', MICROBLOG_DOMAIN ),
				'type' => 'checkbox',
			),
			array(
				'id'            => 'ml_update_interval',
				'name'          => __('刷新间隔', MICROBLOG_DOMAIN ),
				'desc'          => __('自动刷新时检查新条目的间隔秒数，区间 10-360。', MICROBLOG_DOMAIN ),
				'type'          => 'number',
				'min'           => 10,
				'max'           => 360,
				'default_value' => 30,
			),
			array(
				'id'            => 'ml_show_entries',
				'name'          => __('每页连载数', MICROBLOG_DOMAIN ),
				'desc'          => __('点击「加载更多」前显示的条目数量，区间 3-50。', MICROBLOG_DOMAIN ),
				'type'          => 'number',
				'min'           => 3,
				'max'           => 50,
				'default_value' => 10,
			),
			array(
				'id'   => 'ml_prefix_title',
				'name' => __('显示前缀', MICROBLOG_DOMAIN ),
				'desc' => __('自动在连载标题前加上「Liveblog」。', MICROBLOG_DOMAIN ),
				'type' => 'checkbox',
			),
			array(
				'id'      => 'ml_entry_date_format',
				'name'    => __('时间格式', MICROBLOG_DOMAIN ),
				'desc'    => __('连载条目中日期的显示格式。', MICROBLOG_DOMAIN ),
				'type'    => 'select',
				'options' => array(
					''         => __('相对时间（如 3 分钟前）', MICROBLOG_DOMAIN ),
					'date'     => sprintf( __('日期：%s', MICROBLOG_DOMAIN ), get_option('date_format' ) ),
					'time'     => sprintf( __('时间：%s', MICROBLOG_DOMAIN ), get_option('time_format' ) ),
					'datetime' => sprintf( __('日期与时间：%s', MICROBLOG_DOMAIN ), mbfun_get_live_datetime_format() ),
				),
			),
			array(
				'id'      => 'ml_cache_enabled',
				'name'    => __('启用缓存', MICROBLOG_DOMAIN ),
				'desc'    => __('为连载内容启用缓存，可选对象缓存或 Transient。', MICROBLOG_DOMAIN ),
				'type'    => 'select',
				'options' => array(
					''          => __('不启用', MICROBLOG_DOMAIN ),
					'object'    => __('Object', MICROBLOG_DOMAIN ),
					'transient' => __('Transient', MICROBLOG_DOMAIN ),
				),
			),
			array(
				'id'   => 'ml_append_timestamp',
				'name' => __('feed时间戳', MICROBLOG_DOMAIN ),
				'desc' => __('在连载 Feed 地址后附加时间戳，避免读到旧缓存。', MICROBLOG_DOMAIN ),
				'type' => 'checkbox',
			),
		),
	);

	return apply_filters('mlb_registered_settings', $mlb_sections );
}

/**
 * Missing callback
 *
 * @param  array $args
 * @return void
 */
function microblog_live_missing_callback( $args ) {
	printf(
		__('The callback function used for the %s setting is missing.', MICROBLOG_DOMAIN ),
		'<strong>' . $args['id'] . '</strong>'
	);
}

/**
 * Checkbox callback
 *
 * @param  array $args
 * @return void
 */
function microblog_live_checkbox_callback( $args ) {
	$mlb_options = mbfun_get_liveblog_settings();

	$checked = checked( isset( $mlb_options[ $args['id'] ] ) ? $mlb_options[ $args['id'] ] : '', '1', false );

	$html  = '<input type="checkbox" ' . $checked . ' id="microblog_liveblog_data[' . $args['id'] . ']" name="microblog_liveblog_data[' . $args['id'] . ']" value="1" />';
	$html .= '<label for="microblog_liveblog_data[' . $args['id'] . ']">' . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Text callback
 *
 * @param  array $args
 * @return void
 */
function microblog_live_text_callback( $args ) {
	$mlb_options = mbfun_get_liveblog_settings();

	$value = isset( $mlb_options[ $args['id'] ] ) ? $mlb_options[ $args['id'] ] : '';

	$html  = '<input type="text" id="microblog_liveblog_data[' . $args['id'] . ']" name="microblog_liveblog_data[' . $args['id'] . ']" value="' . $value . '" />';
	$html .= '<label for="microblog_liveblog_data[' . $args['id'] . ']">' . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Select callback
 *
 * @param  array $args
 * @return void
 */
function microblog_live_select_callback( $args ) {
	$mlb_options = mbfun_get_liveblog_settings();

	$value = isset( $mlb_options[ $args['id'] ] ) ? $mlb_options[ $args['id'] ] : ( $args['default_value'] ?? null );

	$html = '<select id="microblog_liveblog_data[' . $args['id'] . ']" name="microblog_liveblog_data[' . $args['id'] . ']" />';

	if ( ! empty( $args['options'] ) ) {
		foreach ( $args['options'] as $option_value => $option_name ) {
			$selected = selected( $value, $option_value, false );

			$html .= '<option value="' . $option_value . '" ' . $selected . '>' . $option_name . '</option>';
		}
	}

	$html .= '</select>';

	$html .= '<label for="microblog_liveblog_data[' . $args['id'] . ']">' . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Multiple Select callback
 *
 * @param  array $args
 * @return void
 */
function microblog_live_multiple_checkbox_callback( $args ) {
	$mlb_options = mbfun_get_liveblog_settings();
	$value = isset( $mlb_options[ $args['id'] ] ) ? $mlb_options[ $args['id'] ] : $args['default_value'];
	$html = '';
	if ( ! empty( $args['options'] ) ) {
		foreach ( $args['options'] as $option_value => $option_name ) {
			$checked = in_array( $option_value, is_array( $value ) ? $value : array() ) ? 'checked' : '';
			$translated_option_name = __( $option_name, MICROBLOG_DOMAIN );
			$html .= '<label class="microblog-admin-option-label">';
			$html .= '<input type="checkbox" id="microblog_liveblog_data[' . $args['id'] . '][' . $option_value . ']" name="microblog_liveblog_data[' . $args['id'] . '][]" value="' . $option_value . '" ' . $checked . '>';
			$html .= $translated_option_name;
			$html .= '</label>';
		}
	}
	$html .= '<label>' . '<br><br>' . $args['desc'] . '</label>';
	echo $html;
}

/**
 * Number callback
 *
 * @param  array $args
 * @return void
 */
function microblog_live_number_callback( $args ) {
	$mlb_options = mbfun_get_liveblog_settings();

	$value = isset( $mlb_options[ $args['id'] ] ) ? $mlb_options[ $args['id'] ] : $args['default_value'];
	$min   = ! empty( $args['min'] ) ? 'min="' . $args['min'] . '"' : null;
	$max   = ! empty( $args['max'] ) ? 'max="' . $args['max'] . '"' : null;

	$html  = '<input type="number" ' . $min . ' ' . $max . ' id="microblog_liveblog_data[' . $args['id'] . ']" name="microblog_liveblog_data[' . $args['id'] . ']" value="' . $value . '" />';
	$html .= '<label for="microblog_liveblog_data[' . $args['id'] . ']">' . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Number callback
 *
 * @param  array $args
 * @return void
 */
function microblog_live_text_input_callback( $args ) {

	$mlb_options = mbfun_get_liveblog_settings();
	$value = isset( $mlb_options[ $args['id'] ] ) ? $mlb_options[ $args['id'] ] : $args['default_value'];

    $html  = '<input type="text" id="microblog_liveblog_data[' . $args['id'] . ']" name="microblog_liveblog_data[' . $args['id'] . ']" value="' . $value . '" maxlength="20" style="width: 120px;" />';
	$html .= '<p class="description">' . $args['desc'] . '</p>';

	echo $html;
}



?>