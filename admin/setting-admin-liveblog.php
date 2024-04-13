<?php
/*
PluName: 微博 MicroBlog
PluLink: https://www.webersongao.com/tag/microblog
Desc: 将您的WordPress网站用作微博；在小部件中显示微博或使用短代码显示微博。
Author: WebersonGao
AuthorLink: https://www.webersongao.com
Based on simple-microblogging plugin developed by Samuel Coskey, Victoria Gitman(http://boolesrings.org),Thanks to obaby(https://h4ck.org.cn/) Thanks to ChatGPT.
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}  
/*
 * Liveblog Panel
*/
// Function to display microlive settings section
function microblog_liveblog_settings_section() {
    ob_start();
    ?>
        <div class="microlive_settings_header">
            <?php microblog_liveblog_settings_section_header(); ?>
        </div>
            <?php settings_errors('microblog_liveblog_data'); ?>
        <form method="post" action="options.php">
            <?php if ( isset( $_GET['settings-updated'] ) ) { ?>
                <div class="updated"><p><?php _e( 'Plugin settings have been updated.', MICROBLOG_DOMAIN ); ?></p></div>
            <?php } ?>
            <?php settings_fields( 'microblog_liveblog_setting_field' ); ?>
            <?php do_settings_sections( 'microblog_liveblog_section_general' ); ?>
            <?php submit_button('保存 设置'); ?>
        </form>
    <?php
    echo ob_get_clean();
}

function microblog_liveblog_settings_section_header() {
    echo '</br>';
    // echo '<h3>' . esc_html__('MicroLive Tab', MICROBLOG_DOMAIN ) . '</h3>';
}


/**
 * Register settings
 *
 * @return void
 */
add_action( 'admin_init', 'microblog_liveblog_setting_admin' );
function microblog_liveblog_setting_admin() {
	register_setting( 'microblog_liveblog_setting_field', 'microblog_liveblog_data', 'microblog_liveblog_data_sanitize' );

	foreach ( mbfun_get_registered_settings() as $section => $settings ) {

		add_settings_section(
			'microblog_liveblog_section_' . $section,
			'微连载设置',
			'__return_false',
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
				function_exists( 'microblog_live_' . $args['type'] . '_callback' ) ? 'microblog_live_' . $args['type'] . '_callback' : 'microblog_live_missing_callback',
				'microblog_liveblog_section_' . $section,
				'microblog_liveblog_section_' . $section,
				$args
			);
		}
	}
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
				'id'            => 'theme_style',
				'name'          => __( '主题', MICROBLOG_DOMAIN ),
				'desc'          => __( 'Select a theme for your liveblog.', MICROBLOG_DOMAIN ),
				'type'          => 'select',
				'options'       => array(
					'light'     => __( 'Light', MICROBLOG_DOMAIN ),
					'dark'      => __( 'Dark', MICROBLOG_DOMAIN ),
					'light-alt' => __( 'Light (Less theme dependent)', MICROBLOG_DOMAIN ),
					'none'      => __( 'None', MICROBLOG_DOMAIN ),
				),
				'default_value' => 'light',
			),
			array(
				'id'   => 'display_title',
				'name' => __( '显示标题', MICROBLOG_DOMAIN ),
				'desc' => __( ' Display the title on liveblog entries.', MICROBLOG_DOMAIN ),
				'type' => 'checkbox',
			),
			array(
				'id'   => 'display_author',
				'name' => __( '显示作者', MICROBLOG_DOMAIN ),
				'desc' => __( ' Display the author name on liveblog entries.', MICROBLOG_DOMAIN ),
				'type' => 'checkbox',
			),
			array(
				'id'   => 'display_social_share',
				'name' => __( '社交分享', MICROBLOG_DOMAIN ),
				'desc' => __( ' Display the social sharing options.', MICROBLOG_DOMAIN ),
				'type' => 'checkbox',
			),
			array(
				'id'            => 'update_interval',
				'name'          => __( '刷新间隔', MICROBLOG_DOMAIN ),
				'desc'          => __( ' Per how many seconds should be checked for new liveblog updates. (10,360)', MICROBLOG_DOMAIN ),
				'type'          => 'number',
				'min'           => 10,
				'max'           => 360,
				'default_value' => 30,
			),
			array(
				'id'            => 'show_entries',
				'name'          => __( '每页连载数', MICROBLOG_DOMAIN ),
				'desc'          => __( ' The amount of entries visible before the load more button.', MICROBLOG_DOMAIN ),
				'type'          => 'number',
				'min'           => 1,
				'max'           => 50,
				'default_value' => 10,
			),
			array(
				'id'            => 'post_types',
				'name'          => __( '支持文章类型', MICROBLOG_DOMAIN ),
				'desc'          => __( ' Select the post types that need to support liveblogs.', MICROBLOG_DOMAIN ),
				'type'          => 'multiple_checkbox',
				'options'       => $mlb_alltypes,
				'default_value' => array( 'post' ),
			),
			array(
				'id'   => 'prefix_title',
				'name' => __( '显示前缀', MICROBLOG_DOMAIN ),
				'desc' => __( ' Automatically puts "Liveblog" in front of your liveblogs titles.', MICROBLOG_DOMAIN ),
				'type' => 'checkbox',
			),
			array(
				'id'      => 'entry_date_format',
				'name'    => __( '时间格式', MICROBLOG_DOMAIN ),
				'desc'    => __( ' The format of the date displayed on liveblog entries.', MICROBLOG_DOMAIN ),
				'type'    => 'select',
				'options' => array(
					''         => __( 'Human Readable', MICROBLOG_DOMAIN ),
					'date'     => sprintf( __( 'Date: %s', MICROBLOG_DOMAIN ), get_option( 'date_format' ) ),
					'time'     => sprintf( __( 'Time: %s', MICROBLOG_DOMAIN ), get_option( 'time_format' ) ),
					'datetime' => sprintf( __( 'Date and time: %s', MICROBLOG_DOMAIN ), mbfun_get_live_datetime_format() ),
				),
			),
			array(
				'id'      => 'cache_enabled',
				'name'    => __( '启用缓存', MICROBLOG_DOMAIN ),
				'desc'    => __( ' Caches the liveblog feed with the selected method.', MICROBLOG_DOMAIN ),
				'type'    => 'select',
				'options' => array(
					''          => __( 'Disabled', MICROBLOG_DOMAIN ),
					'object'    => __( 'Object', MICROBLOG_DOMAIN ),
					'transient' => __( 'Transient', MICROBLOG_DOMAIN ),
				),
			),
			array(
				'id'   => 'append_timestamp',
				'name' => __( 'feed时间戳', MICROBLOG_DOMAIN ),
				'desc' => __( ' Appends a timestamp to the liveblog feed URL.', MICROBLOG_DOMAIN ),
				'type' => 'checkbox',
			),
		),
	);

	return apply_filters( 'mlb_registered_settings', $mlb_sections );
}

/**
 * Missing callback
 *
 * @param  array $args
 * @return void
 */
function microblog_live_missing_callback( $args ) {
	printf(
		__( 'The callback function used for the %s setting is missing.', MICROBLOG_DOMAIN ),
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
	global $mlb_options;

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
	global $mlb_options;

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
	global $mlb_options;

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
	global $mlb_options;
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
	global $mlb_options;

	$value = isset( $mlb_options[ $args['id'] ] ) ? $mlb_options[ $args['id'] ] : $args['default_value'];
	$min   = ! empty( $args['min'] ) ? 'min="' . $args['min'] . '"' : null;
	$max   = ! empty( $args['max'] ) ? 'max="' . $args['max'] . '"' : null;

	$html  = '<input type="number" ' . $min . ' ' . $max . ' id="microblog_liveblog_data[' . $args['id'] . ']" name="microblog_liveblog_data[' . $args['id'] . ']" value="' . $value . '" />';
	$html .= '<label for="microblog_liveblog_data[' . $args['id'] . ']">' . $args['desc'] . '</label>';

	echo $html;
}




?>