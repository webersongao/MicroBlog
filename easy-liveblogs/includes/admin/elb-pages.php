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
function elb_add_options_link() {
	add_submenu_page( 'edit.php?post_type=elb_entry', __( 'Easy Liveblog Settings', ELB_TEXT_DOMAIN ), __( 'Settings', ELB_TEXT_DOMAIN ), 'manage_options', 'elb-settings', 'elb_options_page' );
}
add_action( 'admin_menu', 'elb_add_options_link' );

/**
 * Options page
 */
function elb_options_page() {
	ob_start();

	?>

	<div class="wrap">
		<h2><?php _e( 'Easy Liveblogs Settings', ELB_TEXT_DOMAIN ); ?></h2>

		<form method="post" action="options.php">

			<?php if ( isset( $_GET['settings-updated'] ) ) { ?>
				<div class="updated"><p><?php _e( 'Plugin settings have been updated.', ELB_TEXT_DOMAIN ); ?></p></div>
			<?php } ?>

			<?php settings_fields( 'elb_settings' ); ?>
			<?php do_settings_sections( 'elb_settings_general' ); ?>
			<?php submit_button(); ?>

		</form>

	</div>

	<?php

	echo ob_get_clean();
}
