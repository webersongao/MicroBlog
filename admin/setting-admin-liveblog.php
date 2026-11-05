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
				'name'          => __('Supported', MICROBLOG_DOMAIN ),
				'desc'          => __('Select the post types that can be used as liveblogs.', MICROBLOG_DOMAIN ),
				'type'          => 'multiple_checkbox',
				'options'       => $mlb_alltypes,
				'default_value' => array('post' ),
			),
			array(
				'id'            => 'ml_theme_style',
				'name'          => __('Theme', MICROBLOG_DOMAIN ),
				'desc'          => __('Color theme of the liveblog block.', MICROBLOG_DOMAIN ),
				'type'          => 'select',
				'options'       => array(
					'light'     => __('Light', MICROBLOG_DOMAIN ),
					'dark'      => __('Dark', MICROBLOG_DOMAIN ),
				),
				'default_value' => 'light',
			),
			array(
				'id'   => 'ml_display_autopolling',
				'name' => __('Auto Refresh', MICROBLOG_DOMAIN ),
				'desc' => __('Automatically check for and load new liveblog entries.', MICROBLOG_DOMAIN ),
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
				'id'            => 'ml_update_interval',
				'name'          => __('Refresh Interval', MICROBLOG_DOMAIN ),
				'desc'          => __('Seconds between checks for new entries when auto refresh is on (10-360).', MICROBLOG_DOMAIN ),
				'type'          => 'number',
				'min'           => 10,
				'max'           => 360,
				'default_value' => 30,
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
				'id'   => 'ml_prefix_title',
				'name' => __('Show Prefix', MICROBLOG_DOMAIN ),
				'desc' => __('Automatically prefix the liveblog title with "Liveblog".', MICROBLOG_DOMAIN ),
				'type' => 'checkbox',
			),
			array(
				'id'      => 'ml_entry_date_format',
				'name'    => __('Time Format', MICROBLOG_DOMAIN ),
				'desc'    => __('How entry dates are displayed.', MICROBLOG_DOMAIN ),
				'type'    => 'select',
				'options' => array(
					''         => __('Relative time (e.g. 3 minutes ago)', MICROBLOG_DOMAIN ),
					'date'     => sprintf( __('Date: %s', MICROBLOG_DOMAIN ), get_option('date_format' ) ),
					'time'     => sprintf( __('Time: %s', MICROBLOG_DOMAIN ), get_option('time_format' ) ),
					'datetime' => sprintf( __('Date and time: %s', MICROBLOG_DOMAIN ), mbfun_get_live_datetime_format() ),
				),
			),
			array(
				'id'      => 'ml_cache_enabled',
				'name'    => __('Enable Cache', MICROBLOG_DOMAIN ),
				'desc'    => __('Cache liveblog content with an object cache or transients.', MICROBLOG_DOMAIN ),
				'type'    => 'select',
				'options' => array(
					''          => __('Disabled', MICROBLOG_DOMAIN ),
					'object'    => __('Object', MICROBLOG_DOMAIN ),
					'transient' => __('Transient', MICROBLOG_DOMAIN ),
				),
			),
			array(
				'id'   => 'ml_append_timestamp',
				'name' => __('Feed Timestamp', MICROBLOG_DOMAIN ),
				'desc' => __('Append a timestamp to the liveblog feed URL to avoid stale caches.', MICROBLOG_DOMAIN ),
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

	$value = isset( $mlb_options[ $args['id'] ] ) ? $mlb_options[ $args['id'] ] : ( $args['default_value'] ?? null );

	$html = '<select id="microblog_liveblog_data[' . esc_attr( $args['id'] ) . ']" name="microblog_liveblog_data[' . esc_attr( $args['id'] ) . ']" />';

	if ( ! empty( $args['options'] ) ) {
		foreach ( $args['options'] as $option_value => $option_name ) {
			$selected = selected( $value, $option_value, false );

			$html .= '<option value="' . esc_attr( $option_value ) . '" ' . $selected . '>' . esc_html( $option_name ) . '</option>';
		}
	}

	$html .= '</select>';

	$html .= '<label for="microblog_liveblog_data[' . esc_attr( $args['id'] ) . ']">' . wp_kses_post( $args['desc'] ) . '</label>';

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