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
			__('Liveblog Settings', MICROBLOG_DOMAIN),
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
	echo '<p>' . esc_html__('Enable a rolling liveblog view for posts — great for live coverage and events.', MICROBLOG_DOMAIN) . '</p>';
}

function microblog_liveblog_data_sanitize($input) {

	if ( ! is_array( $input ) ) {
		return array();
	}

	// 轮询间隔必须落在 10–300 秒：只靠 HTML min/max 挡不住绕过表单的请求。
	if ( isset( $input['ml_auto_refresh_interval'] ) ) {
		$seconds = absint( $input['ml_auto_refresh_interval'] );
		$input['ml_auto_refresh_interval'] = min( 300, max( 10, $seconds ?: 30 ) );
	}

	// 条目时间只收四个白名单值；空串是旧版本「相对时间」的存法，一并纠正。
	if ( isset( $input['ml_entry_date_format'] ) ) {
		$allowed = array( 'human', 'date', 'time', 'datetime' );
		$format  = (string) $input['ml_entry_date_format'];

		$input['ml_entry_date_format'] = in_array( $format, $allowed, true ) ? $format : 'human';
	}

	return $input;
}

/**
 * 条目时间的可选值。
 *
 * 标签里直接放「渲染后的示例」，而不是 Y年n月j日 / ag:i 这类格式符：
 * 格式符对普通用户等于乱码，也看不出两档的区别。示例读的是站点时区与
 * 「设置 → 常规」里的格式，所见即前台所得。
 *
 * 值只有 human / date / time / datetime 四个，前台按白名单渲染，
 * 详见 mbfun_get_live_entry_display_date()。
 *
 * @return array 值 => 带示例的标签
 */
function microblog_live_entry_time_options() {
	return array(
		'human'    => __('Relative time (3 minutes ago)', MICROBLOG_DOMAIN ),
		/* translators: %s: example date rendered with the site date format */
		'date'     => sprintf( __('Date only (%s)', MICROBLOG_DOMAIN ), date_i18n( get_option('date_format') ) ),
		/* translators: %s: example time rendered with the site time format */
		'time'     => sprintf( __('Time only (%s)', MICROBLOG_DOMAIN ), date_i18n( get_option('time_format') ) ),
		/* translators: %s: example date and time rendered with the site formats */
		'datetime' => sprintf( __('Date and time (%s)', MICROBLOG_DOMAIN ), date_i18n( mbfun_get_live_datetime_format() ) ),
	);
}

/**
 * 条目时间的说明。
 *
 * 明确写出「格式来自哪里」，否则用户只看到一串看不懂的格式符，
 * 也不知道该去常规设置里改。
 *
 * @return string
 */
function microblog_live_entry_time_desc() {
	$link = sprintf(
		'<a href="%1$s">%2$s</a>',
		esc_url( admin_url('options-general.php') ),
		esc_html__( 'Settings → General', MICROBLOG_DOMAIN )
	);

	/* translators: 1: link to the General Settings screen, 2: date format, 3: time format */
	return sprintf(
		__('How each entry shows its time. Date and time formats come from %1$s (currently %2$s and %3$s).', MICROBLOG_DOMAIN ),
		$link,
		'<code>' . esc_html( get_option('date_format') ) . '</code>',
		'<code>' . esc_html( get_option('time_format') ) . '</code>'
	);
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
				'name'          => __('Supported', MICROBLOG_DOMAIN ),
				'desc'          => __('Published posts in these types can be liveblog sources; their list screens show the liveblog state and let you switch the display mode.', MICROBLOG_DOMAIN ),
				'type'          => 'multiple_checkbox',
				'options'       => $mlb_alltypes,
				'default_value' => array('post' ),
			),
			array(
				'id'            => 'ml_post_layout',
				'name'          => __('Layout', MICROBLOG_DOMAIN ),
				'desc'          => __('Information shown for each liveblog entry.', MICROBLOG_DOMAIN ),
				'type'          => 'multiple_checkbox',
				'options'       => array(
					'ml_layout_title' => __('Title', MICROBLOG_DOMAIN ),
					'ml_layout_author' => __('Author', MICROBLOG_DOMAIN ),
					'ml_layout_tshare' => __('Share buttons', MICROBLOG_DOMAIN ),
				),
				'default_value' => array('post' ),
			),
			array(
				'id'   => 'ml_display_revort_sort',
				'name' => __('Display Order', MICROBLOG_DOMAIN ),
				'desc' => __('Newest first by default; enable to show oldest first.', MICROBLOG_DOMAIN ),
				'type' => 'checkbox',
			),
			array(
				'id'   => 'ml_prefix_title',
				'name' => __('Show Prefix', MICROBLOG_DOMAIN ),
				// 前缀词本身走翻译：前台标题里显示的就是这个译词（zh_CN 为「连载」）。
				'desc' => sprintf(__('Automatically prefix the liveblog title with "%s".', MICROBLOG_DOMAIN), __('Liveblog', MICROBLOG_DOMAIN )),
				'type' => 'checkbox',
			),
			array(
				'id'      => 'ml_entry_date_format',
				'name'    => __('Entry time', MICROBLOG_DOMAIN ),
				'desc'    => microblog_live_entry_time_desc(),
				'type'    => 'select',
				'default_value' => 'human',
				'options' => microblog_live_entry_time_options(),
			),
			array(
				'id'            => 'ml_show_entries',
				'name'          => __('Entries per Page', MICROBLOG_DOMAIN ),
				'desc'          => __('Number of entries shown before "Load more" (3-50).', MICROBLOG_DOMAIN ),
				'type'          => 'number',
				'min'           => 3,
				'max'           => 50,
				'default_value' => 10,
			),
			array(
				'id'            => 'ml_auto_refresh_interval',
				'name'          => __('Auto Refresh Interval', MICROBLOG_DOMAIN ),
				'desc'          => __('Seconds between checks for new entries on liveblogs with auto refresh enabled (10-300).', MICROBLOG_DOMAIN ),
				'type'          => 'number',
				'min'           => 10,
				'max'           => 300,
				'default_value' => 30,
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
		esc_html__('The callback function used for the %s setting is missing.', MICROBLOG_DOMAIN ),
		'<strong>' . esc_html( $args['id'] ) . '</strong>'
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

	$html  = '<input type="checkbox" ' . $checked . ' id="microblog_liveblog_data[' . esc_attr( $args['id'] ) . ']" name="microblog_liveblog_data[' . esc_attr( $args['id'] ) . ']" value="1" />';
	$html .= '<label for="microblog_liveblog_data[' . esc_attr( $args['id'] ) . ']">' . wp_kses_post( $args['desc'] ) . '</label>';

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

	$html  = '<input type="text" id="microblog_liveblog_data[' . esc_attr( $args['id'] ) . ']" name="microblog_liveblog_data[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $value ) . '" />';
	$html .= '<label for="microblog_liveblog_data[' . esc_attr( $args['id'] ) . ']">' . wp_kses_post( $args['desc'] ) . '</label>';

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

	$value = isset( $mlb_options[ $args['id'] ] ) ? $mlb_options[ $args['id'] ] : null;

	// 旧版本把「相对时间」存成空串，还有已废弃的选项值。
	// 不归一化的话，<select> 会显示成第一项、实际喂给前台的却是空值——
	// 「看着选了相对时间，前台却按日期时间渲染」就是这么来的。
	$options = is_array( $args['options'] ) ? $args['options'] : array();
	$key     = is_scalar( $value ) ? (string) $value : '';

	if ( '' === $key || ! array_key_exists( $key, $options ) ) {
		$value = isset( $args['default_value'] ) ? $args['default_value'] : $key;
	}

	$html = '<select id="microblog_liveblog_data[' . esc_attr( $args['id'] ) . ']" name="microblog_liveblog_data[' . esc_attr( $args['id'] ) . ']" />';

	if ( ! empty( $options ) ) {
		foreach ( $options as $option_value => $option_name ) {
			$selected = selected( $value, $option_value, false );

			$html .= '<option value="' . esc_attr( $option_value ) . '" ' . $selected . '>' . esc_html( $option_name ) . '</option>';
		}
	}

	$html .= '</select>';

	// 说明单独占一行：长下拉框后面紧跟一段文字时会被挤到右边、还会被下拉面板盖住。
	if ( '' !== $args['desc'] ) {
		$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';
	}

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
			$html .= '<label class="microblog-admin-option-label">';
			$html .= '<input type="checkbox" id="microblog_liveblog_data[' . esc_attr( $args['id'] ) . '][' . esc_attr( $option_value ) . ']" name="microblog_liveblog_data[' . esc_attr( $args['id'] ) . '][]" value="' . esc_attr( $option_value ) . '" ' . $checked . '>';
			$html .= esc_html( $option_name );
			$html .= '</label>';
		}
	}
	$html .= '<label>' . '<br><br>' . wp_kses_post( $args['desc'] ) . '</label>';
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
	$min   = ! empty( $args['min'] ) ? 'min="' . (int) $args['min'] . '"' : null;
	$max   = ! empty( $args['max'] ) ? 'max="' . (int) $args['max'] . '"' : null;

	$html  = '<input type="number" ' . $min . ' ' . $max . ' id="microblog_liveblog_data[' . esc_attr( $args['id'] ) . ']" name="microblog_liveblog_data[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $value ) . '" />';
	$html .= '<label for="microblog_liveblog_data[' . esc_attr( $args['id'] ) . ']">' . wp_kses_post( $args['desc'] ) . '</label>';

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

    $html  = '<input type="text" id="microblog_liveblog_data[' . esc_attr( $args['id'] ) . ']" name="microblog_liveblog_data[' . esc_attr( $args['id'] ) . ']" value="' . esc_attr( $value ) . '" maxlength="20" style="width: 120px;" />';
	$html .= '<p class="description">' . wp_kses_post( $args['desc'] ) . '</p>';

	echo $html;
}



?>