<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register settings
 *
 * @return void
 */
function mlb_register_settings() {
	register_setting( 'microlive_settings_data', 'microlive_settings_data', 'mlb_settings_sanitize' );

	foreach ( mlb_get_registered_settings() as $section => $settings ) {

		add_settings_section(
			'mlb_settings_' . $section,
			__return_null(),
			'__return_false',
			'mlb_settings_' . $section
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
				'microlive_settings_data[' . $args['id'] . ']',
				$args['name'],
				function_exists( 'mlb_' . $args['type'] . '_callback' ) ? 'mlb_' . $args['type'] . '_callback' : 'mlb_missing_callback',
				'mlb_settings_' . $section,
				'mlb_settings_' . $section,
				$args
			);
		}
	}
}
add_action( 'admin_init', 'mlb_register_settings' );

/**
 * Get settings
 */
function mlb_get_settings() {
	$settings = get_option( 'microlive_settings_data', array() );

	return apply_filters( 'mlb_settings', $settings );
}

/**
 * Get global options
 *
 * @return array
 */
function mlb_get_options() {
	global $mlb_options;

	return ! empty( $mlb_options ) ? $mlb_options : array();
}

/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @return mixed
 */
function mlb_get_option( $key = '', $default = false ) {
	global $mlb_options;

	$value = ! empty( $mlb_options[ $key ] ) ? $mlb_options[ $key ] : $default;
	$value = apply_filters( 'mlb_get_option', $value, $key, $default );

	return apply_filters( 'mlb_get_option_' . $key, $value, $key, $default );
}

/**
 * Get registered settings
 *
 * @return array
 */
function mlb_get_registered_settings() {

	$mlb_alltypes = mlb_get_all_post_types();
	$mlb_settings = array(
		'general' => array(
			array(
				'id'            => 'theme_style',
				'name'          => __( 'Theme', MLB_TEXT_DOMAIN ),
				'desc'          => __( 'Select a theme for your liveblog.', MLB_TEXT_DOMAIN ),
				'type'          => 'select',
				'options'       => array(
					'light'     => __( 'Light', MLB_TEXT_DOMAIN ),
					'dark'      => __( 'Dark', MLB_TEXT_DOMAIN ),
					'light-alt' => __( 'Light (Less theme dependent)', MLB_TEXT_DOMAIN ),
					'none'      => __( 'None', MLB_TEXT_DOMAIN ),
				),
				'default_value' => 'light',
			),
			array(
				'id'   => 'display_title',
				'name' => __( 'Display title', MLB_TEXT_DOMAIN ),
				'desc' => __( 'Display the title on liveblog entries.', MLB_TEXT_DOMAIN ),
				'type' => 'checkbox',
			),
			array(
				'id'   => 'display_author',
				'name' => __( 'Display author', MLB_TEXT_DOMAIN ),
				'desc' => __( 'Display the author name on liveblog entries.', MLB_TEXT_DOMAIN ),
				'type' => 'checkbox',
			),
			array(
				'id'   => 'display_social_share',
				'name' => __( 'Display social sharing', MLB_TEXT_DOMAIN ),
				'desc' => __( 'Display the social sharing options.', MLB_TEXT_DOMAIN ),
				'type' => 'checkbox',
			),
			array(
				'id'            => 'update_interval',
				'name'          => __( 'Update interval', MLB_TEXT_DOMAIN ),
				'desc'          => __( 'Per how many seconds should be checked for new liveblog updates.', MLB_TEXT_DOMAIN ),
				'type'          => 'number',
				'min'           => 10,
				'max'           => 360,
				'default_value' => 30,
			),
			array(
				'id'            => 'show_entries',
				'name'          => __( 'Show entries', MLB_TEXT_DOMAIN ),
				'desc'          => __( 'The amount of entries visible before the load more button.', MLB_TEXT_DOMAIN ),
				'type'          => 'number',
				'min'           => 1,
				'max'           => 50,
				'default_value' => 10,
			),
			array(
				'id'            => 'post_types',
				'name'          => __( 'Post types', MLB_TEXT_DOMAIN ),
				'desc'          => __( 'Select the post types that need to support liveblogs.', MLB_TEXT_DOMAIN ),
				'type'          => 'multiple_select',
				'options'       => $mlb_alltypes,
				'default_value' => array( 'post' ),
			),
			array(
				'id'   => 'prefix_title',
				'name' => __( 'Prefix title', MLB_TEXT_DOMAIN ),
				'desc' => __( 'Automatically puts "Liveblog" in front of your liveblogs titles.', MLB_TEXT_DOMAIN ),
				'type' => 'checkbox',
			),
			array(
				'id'      => 'entry_date_format',
				'name'    => __( 'Entry date format', MLB_TEXT_DOMAIN ),
				'desc'    => __( 'The format of the date displayed on liveblog entries.', MLB_TEXT_DOMAIN ),
				'type'    => 'select',
				'options' => array(
					''         => __( 'Human readable', MLB_TEXT_DOMAIN ),
					'datetime' => sprintf( __( 'Date and time format: %s', MLB_TEXT_DOMAIN ), mlb_get_datetime_format() ),
					'date'     => sprintf( __( 'Date: %s', MLB_TEXT_DOMAIN ), get_option( 'date_format' ) ),
					'time'     => sprintf( __( 'Time: %s', MLB_TEXT_DOMAIN ), get_option( 'time_format' ) ),
				),
			),
			array(
				'id'      => 'cache_enabled',
				'name'    => __( 'Enable caching', MLB_TEXT_DOMAIN ),
				'desc'    => __( 'Caches the liveblog feed with the selected method.', MLB_TEXT_DOMAIN ),
				'type'    => 'select',
				'options' => array(
					''          => __( 'Disabled', MLB_TEXT_DOMAIN ),
					'transient' => __( 'Transient', MLB_TEXT_DOMAIN ),
					'object'    => __( 'Object', MLB_TEXT_DOMAIN ),
				),
			),
			array(
				'id'   => 'append_timestamp',
				'name' => __( 'Append timestamp', MLB_TEXT_DOMAIN ),
				'desc' => __( 'Appends a timestamp to the liveblog feed URL.', MLB_TEXT_DOMAIN ),
				'type' => 'checkbox',
			),
		),
	);

	return apply_filters( 'mlb_registered_settings', $mlb_settings );
}

/**
 * Missing callback
 *
 * @param  array $args
 * @return void
 */
function mlb_missing_callback( $args ) {
	printf(
		__( 'The callback function used for the %s setting is missing.', MLB_TEXT_DOMAIN ),
		'<strong>' . $args['id'] . '</strong>'
	);
}

/**
 * Checkbox callback
 *
 * @param  array $args
 * @return void
 */
function mlb_checkbox_callback( $args ) {
	global $mlb_options;

	$checked = checked( isset( $mlb_options[ $args['id'] ] ) ? $mlb_options[ $args['id'] ] : '', '1', false );

	$html  = '<input type="checkbox" ' . $checked . ' id="microlive_settings_data[' . $args['id'] . ']" name="microlive_settings_data[' . $args['id'] . ']" value="1" />';
	$html .= '<label for="microlive_settings_data[' . $args['id'] . ']">' . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Text callback
 *
 * @param  array $args
 * @return void
 */
function mlb_text_callback( $args ) {
	global $mlb_options;

	$value = isset( $mlb_options[ $args['id'] ] ) ? $mlb_options[ $args['id'] ] : '';

	$html  = '<input type="text" id="microlive_settings_data[' . $args['id'] . ']" name="microlive_settings_data[' . $args['id'] . ']" value="' . $value . '" />';
	$html .= '<label for="microlive_settings_data[' . $args['id'] . ']">' . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Select callback
 *
 * @param  array $args
 * @return void
 */
function mlb_select_callback( $args ) {
	global $mlb_options;

	$value = isset( $mlb_options[ $args['id'] ] ) ? $mlb_options[ $args['id'] ] : ( $args['default_value'] ?? null );

	$html = '<select id="microlive_settings_data[' . $args['id'] . ']" name="microlive_settings_data[' . $args['id'] . ']" />';

	if ( ! empty( $args['options'] ) ) {
		foreach ( $args['options'] as $option_value => $option_name ) {
			$selected = selected( $value, $option_value, false );

			$html .= '<option value="' . $option_value . '" ' . $selected . '>' . $option_name . '</option>';
		}
	}

	$html .= '</select>';

	$html .= '<label for="microlive_settings_data[' . $args['id'] . ']">' . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Multiple Select callback
 *
 * @param  array $args
 * @return void
 */
function mlb_multiple_select_callback( $args ) {
	global $mlb_options;

	$value = isset( $mlb_options[ $args['id'] ] ) ? $mlb_options[ $args['id'] ] : $args['default_value'];

	$html = '<select multiple id="microlive_settings_data[' . $args['id'] . ']" name="microlive_settings_data[' . $args['id'] . '][]" />';

	if ( ! empty( $args['options'] ) ) {
		foreach ( $args['options'] as $option_value => $option_name ) {

			$selected = in_array( $option_value, is_array( $value ) ? $value : array() ) ? 'selected' : null;

			$html .= '<option value="' . $option_value . '" ' . $selected . '>' . $option_name . '</option>';
		}
	}

	$html .= '</select>';

	$html .= '<label for="microlive_settings_data[' . $args['id'] . ']">' . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Number callback
 *
 * @param  array $args
 * @return void
 */
function mlb_number_callback( $args ) {
	global $mlb_options;

	$value = isset( $mlb_options[ $args['id'] ] ) ? $mlb_options[ $args['id'] ] : $args['default_value'];
	$min   = ! empty( $args['min'] ) ? 'min="' . $args['min'] . '"' : null;
	$max   = ! empty( $args['max'] ) ? 'max="' . $args['max'] . '"' : null;

	$html  = '<input type="number" ' . $min . ' ' . $max . ' id="microlive_settings_data[' . $args['id'] . ']" name="microlive_settings_data[' . $args['id'] . ']" value="' . $value . '" />';
	$html .= '<label for="microlive_settings_data[' . $args['id'] . ']">' . $args['desc'] . '</label>';

	echo $html;
}
