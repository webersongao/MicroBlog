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
function elb_register_settings() {
	register_setting( 'elb_settings', 'elb_settings', 'elb_settings_sanitize' );

	foreach ( elb_get_registered_settings() as $section => $settings ) {

		add_settings_section(
			'elb_settings_' . $section,
			__return_null(),
			'__return_false',
			'elb_settings_' . $section
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
				'elb_settings[' . $args['id'] . ']',
				$args['name'],
				function_exists( 'elb_' . $args['type'] . '_callback' ) ? 'elb_' . $args['type'] . '_callback' : 'elb_missing_callback',
				'elb_settings_' . $section,
				'elb_settings_' . $section,
				$args
			);
		}
	}

}
add_action( 'admin_init', 'elb_register_settings' );

/**
 * Get settings
 */
function elb_get_settings() {
	$settings = get_option( 'elb_settings', array() );

	return apply_filters( 'elb_settings', $settings );
}

/**
 * Get global options
 *
 * @return array
 */
function elb_get_options() {
	global $elb_options;

	return ! empty( $elb_options ) ? $elb_options : array();
}

/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @return mixed
 */
function elb_get_option( $key = '', $default = false ) {
	global $elb_options;

	$value = ! empty( $elb_options[ $key ] ) ? $elb_options[ $key ] : $default;
	$value = apply_filters( 'elb_get_option', $value, $key, $default );

	return apply_filters( 'elb_get_option_' . $key, $value, $key, $default );
}

/**
 * Get registered settings
 *
 * @return array
 */
function elb_get_registered_settings() {
	$elb_settings = array(
		'general' => array(
			array(
				'id'            => 'theme',
				'name'          => __( 'Theme', ELB_TEXT_DOMAIN ),
				'desc'          => __( 'Select a theme for your liveblog.', ELB_TEXT_DOMAIN ),
				'type'          => 'select',
				'options'       => array(
					'light'     => __( 'Light', ELB_TEXT_DOMAIN ),
					'dark'      => __( 'Dark', ELB_TEXT_DOMAIN ),
					'light-alt' => __( 'Light (Less theme dependent)', ELB_TEXT_DOMAIN ),
					'none'      => __( 'None', ELB_TEXT_DOMAIN ),
				),
				'default_value' => 'light',
			),
			array(
				'id'   => 'display_author',
				'name' => __( 'Display author', ELB_TEXT_DOMAIN ),
				'desc' => __( 'Display the author name on liveblog entries.', ELB_TEXT_DOMAIN ),
				'type' => 'checkbox',
			),
			array(
				'id'   => 'display_social_sharing',
				'name' => __( 'Display social sharing', ELB_TEXT_DOMAIN ),
				'desc' => __( 'Display the social sharing options.', ELB_TEXT_DOMAIN ),
				'type' => 'checkbox',
			),
			array(
				'id'            => 'update_interval',
				'name'          => __( 'Update interval', ELB_TEXT_DOMAIN ),
				'desc'          => __( 'Per how many seconds should be checked for new liveblog updates.', ELB_TEXT_DOMAIN ),
				'type'          => 'number',
				'min'           => 10,
				'max'           => 360,
				'default_value' => 30,
			),
			array(
				'id'            => 'show_entries',
				'name'          => __( 'Show entries', ELB_TEXT_DOMAIN ),
				'desc'          => __( 'The amount of entries visible before the load more button.', ELB_TEXT_DOMAIN ),
				'type'          => 'number',
				'min'           => 1,
				'max'           => 50,
				'default_value' => 10,
			),
			array(
				'id'            => 'post_types',
				'name'          => __( 'Post types', ELB_TEXT_DOMAIN ),
				'desc'          => __( 'Select the post types that need to support liveblogs.', ELB_TEXT_DOMAIN ),
				'type'          => 'multiple_select',
				'options'       => get_post_types(),
				'default_value' => array( 'post' ),
			),
			array(
				'id'   => 'prefix_title',
				'name' => __( 'Prefix title', ELB_TEXT_DOMAIN ),
				'desc' => __( 'Automatically puts "Liveblog" in front of your liveblogs titles.', ELB_TEXT_DOMAIN ),
				'type' => 'checkbox',
			),
			array(
				'id'      => 'entry_date_format',
				'name'    => __( 'Entry date format', ELB_TEXT_DOMAIN ),
				'desc'    => __( 'The format of the date displayed on liveblog entries.', ELB_TEXT_DOMAIN ),
				'type'    => 'select',
				'options' => array(
					''         => __( 'Human readable', ELB_TEXT_DOMAIN ),
					'datetime' => sprintf( __( 'Date and time format: %s', ELB_TEXT_DOMAIN ), elb_get_datetime_format() ),
					'date'     => sprintf( __( 'Date: %s', ELB_TEXT_DOMAIN ), get_option( 'date_format' ) ),
					'time'     => sprintf( __( 'Time: %s', ELB_TEXT_DOMAIN ), get_option( 'time_format' ) ),
				),
			),
			array(
				'id'      => 'cache_enabled',
				'name'    => __( 'Enable caching', ELB_TEXT_DOMAIN ),
				'desc'    => __( 'Caches the liveblog feed with the selected method.', ELB_TEXT_DOMAIN ),
				'type'    => 'select',
				'options' => array(
					''          => __( 'Disabled', ELB_TEXT_DOMAIN ),
					'transient' => __( 'Transient', ELB_TEXT_DOMAIN ),
					'object'    => __( 'Object', ELB_TEXT_DOMAIN ),
				),
			),
			array(
				'id'   => 'append_timestamp',
				'name' => __( 'Append timestamp', ELB_TEXT_DOMAIN ),
				'desc' => __( 'Appends a timestamp to the liveblog feed URL.', ELB_TEXT_DOMAIN ),
				'type' => 'checkbox',
			),
		),
	);

	return apply_filters( 'elb_registered_settings', $elb_settings );
}

/**
 * Missing callback
 *
 * @param  array $args
 * @return void
 */
function elb_missing_callback( $args ) {
	printf(
		__( 'The callback function used for the %s setting is missing.', ELB_TEXT_DOMAIN ),
		'<strong>' . $args['id'] . '</strong>'
	);
}

/**
 * Checkbox callback
 *
 * @param  array $args
 * @return void
 */
function elb_checkbox_callback( $args ) {
	global $elb_options;

	$checked = checked( isset( $elb_options[ $args['id'] ] ) ? $elb_options[ $args['id'] ] : '', '1', false );

	$html  = '<input type="checkbox" ' . $checked . ' id="elb_settings[' . $args['id'] . ']" name="elb_settings[' . $args['id'] . ']" value="1" />';
	$html .= '<label for="elb_settings[' . $args['id'] . ']">' . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Text callback
 *
 * @param  array $args
 * @return void
 */
function elb_text_callback( $args ) {
	global $elb_options;

	$value = isset( $elb_options[ $args['id'] ] ) ? $elb_options[ $args['id'] ] : '';

	$html  = '<input type="text" id="elb_settings[' . $args['id'] . ']" name="elb_settings[' . $args['id'] . ']" value="' . $value . '" />';
	$html .= '<label for="elb_settings[' . $args['id'] . ']">' . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Select callback
 *
 * @param  array $args
 * @return void
 */
function elb_select_callback( $args ) {
	global $elb_options;

	$value = isset( $elb_options[ $args['id'] ] ) ? $elb_options[ $args['id'] ] : ( $args['default_value'] ?? null );

	$html = '<select id="elb_settings[' . $args['id'] . ']" name="elb_settings[' . $args['id'] . ']" />';

	if ( ! empty( $args['options'] ) ) {
		foreach ( $args['options'] as $option_value => $option_name ) {
			$selected = selected( $value, $option_value, false );

			$html .= '<option value="' . $option_value . '" ' . $selected . '>' . $option_name . '</option>';
		}
	}

	$html .= '</select>';

	$html .= '<label for="elb_settings[' . $args['id'] . ']">' . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Multiple Select callback
 *
 * @param  array $args
 * @return void
 */
function elb_multiple_select_callback( $args ) {
	global $elb_options;

	$value = isset( $elb_options[ $args['id'] ] ) ? $elb_options[ $args['id'] ] : $args['default_value'];

	$html = '<select multiple id="elb_settings[' . $args['id'] . ']" name="elb_settings[' . $args['id'] . '][]" />';

	if ( ! empty( $args['options'] ) ) {
		foreach ( $args['options'] as $option_value => $option_name ) {

			$selected = in_array( $option_value, is_array( $value ) ? $value : array() ) ? 'selected' : null;

			$html .= '<option value="' . $option_value . '" ' . $selected . '>' . $option_name . '</option>';
		}
	}

	$html .= '</select>';

	$html .= '<label for="elb_settings[' . $args['id'] . ']">' . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Number callback
 *
 * @param  array $args
 * @return void
 */
function elb_number_callback( $args ) {
	global $elb_options;

	$value = isset( $elb_options[ $args['id'] ] ) ? $elb_options[ $args['id'] ] : $args['default_value'];
	$min   = ! empty( $args['min'] ) ? 'min="' . $args['min'] . '"' : null;
	$max   = ! empty( $args['max'] ) ? 'max="' . $args['max'] . '"' : null;

	$html  = '<input type="number" ' . $min . ' ' . $max . ' id="elb_settings[' . $args['id'] . ']" name="elb_settings[' . $args['id'] . ']" value="' . $value . '" />';
	$html .= '<label for="elb_settings[' . $args['id'] . ']">' . $args['desc'] . '</label>';

	echo $html;
}
