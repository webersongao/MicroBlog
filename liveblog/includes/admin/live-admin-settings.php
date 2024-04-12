<?php
/**
 * Admin Pages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add options link
 */
function mlb_add_options_link() {
	add_submenu_page( 'edit.php?post_type=microlive', __( '连载直播 | 配置项', MLB_TEXT_DOMAIN ), __( '连载Settings', MLB_TEXT_DOMAIN ), 'manage_options', 'live-settings', 'mlb_options_page' );
}
add_action( 'admin_menu', 'mlb_add_options_link' );

/**
 * Options page
 */
function mlb_options_page() {
	ob_start();
	?>
	<div class="wrap">
		<h2><?php _e( '连载直播 | 配置项', MLB_TEXT_DOMAIN ); ?></h2>
		<form method="post" action="options.php">
			<?php if ( isset( $_GET['settings-updated'] ) ) { ?>
				<div class="updated"><p><?php _e( 'Plugin settings have been updated.', MLB_TEXT_DOMAIN ); ?></p></div>
			<?php } ?>
			<?php settings_fields( 'microlive_settings_data' ); ?>
			<?php do_settings_sections( 'mlb_settings_general' ); ?>
			<?php submit_button(); ?>

		</form>
	</div>

	<?php

	echo ob_get_clean();
}
