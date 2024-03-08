<?php
/**
 * Metabox Functions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register all the meta boxes for the Download custom post type
 */
function elb_add_meta_box() {
	$post_types = elb_get_supported_post_types();

	foreach ( $post_types as $post_type ) {
		add_meta_box( 'elb_liveblog_meta_box', __( 'Liveblog', ELB_TEXT_DOMAIN ), 'elb_render_liveblog_meta_box', $post_type, 'normal', 'high' );
	}

	add_meta_box( 'elb_entry_meta_box', __( 'Liveblog', ELB_TEXT_DOMAIN ), 'elb_render_entry_meta_box', 'elb_entry', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'elb_add_meta_box' );

/**
 * Liveblog meta box fields
 *
 * @return array
 */
function elb_liveblog_meta_box_fields() {
	$fields = array(
		'_elb_is_liveblog',
		'_elb_status',
	);

	return apply_filters( 'elb_liveblog_meta_box_fields_save', $fields );
}

/**
 * Entry meta box fields
 *
 * @return array
 */
function elb_entry_meta_box_fields() {
	$fields = array(
		'_elb_liveblog',
	);

	return apply_filters( 'elb_entry_meta_box_fields_save', $fields );
}

/**
 * Render liveblog meta box
 */
function elb_render_liveblog_meta_box( $post ) {
	do_action( 'elb_liveblog_meta_box_fields', $post->ID );

	wp_nonce_field( basename( __FILE__ ), 'elb_liveblog_meta_box_nonce' );
}

/**
 * Render entry meta box
 */
function elb_render_entry_meta_box() {
	global $post;

	do_action( 'elb_entry_meta_box_fields', $post->ID );

	wp_nonce_field( basename( __FILE__ ), 'elb_entry_meta_box_nonce' );
}

/**
 * Render Liveblog Options
 *
 * @param int $post_id (Post) ID
 */
function elb_render_liveblog_options( $post_id ) {
	$is_liveblog = get_post_meta( $post_id, '_elb_is_liveblog', true );

	do_action( 'elb_render_before_liveblog_options', $post_id );
	?>
	<label for="elb-is-liveblog">
		<input type="checkbox" name="_elb_is_liveblog" value="1" <?php checked( $is_liveblog, '1', true ); ?> id="elb-is-liveblog">
		<?php _e( 'Enable liveblog', ELB_TEXT_DOMAIN ); ?>
	</label>
	<?php

	if ( ! empty( $is_liveblog ) ) {
		$status = get_post_meta( $post_id, '_elb_status', true );
		?>
		<div class="elb-input-group">
			<label for="elb_status"><?php _e( 'Status', ELB_TEXT_DOMAIN ); ?></label>
			<select name="_elb_status" id="elb_status">
				<?php foreach ( elb_get_liveblog_status_options() as $option_value => $option_name ) { ?>
					<option value="<?php echo $option_value; ?>" <?php selected( $option_value, $status, true ); ?>><?php echo $option_name; ?></option>
				<?php } ?>
			</select>
		</div>
		<div class="elb-input-group">
			<label for="elb-liveblog-endpoint"><?php _e( 'API-endpoint URL', ELB_TEXT_DOMAIN ); ?></label>
			<input type="text" id="elb-liveblog-endpoint" onclick="this.focus(); this.select()" value="<?php echo elb_get_liveblog_api_endpoint( $post_id ); ?>" readonly="readonly" class="widefat">
		</div>
		<?php
	}
	do_action( 'elb_render_after_liveblog_options', $post_id );
}
add_action( 'elb_liveblog_meta_box_fields', 'elb_render_liveblog_options', 1 );

/**
 * Render Entry Options
 *
 * @param int $post_id (Post) ID
 */
function elb_render_entry_options( $post_id ) {
	$liveblog = get_post_meta( $post_id, '_elb_liveblog', true );
	$status   = false;

	if ( ! empty( $liveblog ) ) {
		$status = elb_get_liveblog_status( $liveblog );
	}

	$liveblogs = elb_get_liveblogs_by_status( 'open' );

	do_action( 'elb_before_entry_options', $post_id );

	?>

	<?php if ( $status === 'closed' ) { ?>
		<p><?php printf( __( 'This item is attached to a <a href="%s">closed</a> liveblog.', ELB_TEXT_DOMAIN ), get_edit_post_link( $liveblog ) ); ?></p>
	<?php } elseif ( $liveblogs ) { ?>
		<div class="elb-input-group">
			<label for="elb-liveblog"><?php _e( 'Select liveblog', ELB_TEXT_DOMAIN ); ?></label>
			<select name="_elb_liveblog" id="elb-liveblog" class="elb-selectize">
				<?php foreach ( $liveblogs as $liveblog_id => $liveblog_title ) { ?>
					<option value="<?php echo $liveblog_id; ?>" <?php selected( $liveblog, $liveblog_id, true ); ?>><?php echo $liveblog_title; ?></option>
				<?php } ?>
			</select>
		</div>

		<?php if ( ! empty( $liveblog ) ) { ?>
			<div class="elb-input-group">
				<label for="elb-liveblog-entry-link"><?php _e( 'Direct link to entry', ELB_TEXT_DOMAIN ); ?></label>
				<input type="text" id="elb-liveblog-entry-link" onclick="this.focus(); this.select()" value="<?php echo elb_get_entry_url( $post_id ); ?>" readonly="readonly" class="widefat">
			</div>
		<?php } ?>
	<?php } else { ?>
		<p><?php _e( 'There is no liveblog created yet.', ELB_TEXT_DOMAIN ); ?></p>
	<?php } ?>

	<?php
	do_action( 'elb_after_entry_options', $post_id );
}
add_action( 'elb_entry_meta_box_fields', 'elb_render_entry_options', 1 );

/**
 * Liveblog meta box save
 *
 * @param  int    $post_id
 * @param  object $post
 */
function elb_liveblog_meta_box_save( $post_id, $post ) {

	if ( ! isset( $_POST['elb_liveblog_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['elb_liveblog_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return;
	}

	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return;
	}

	if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
		return;
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$fields = elb_liveblog_meta_box_fields();

	do_action( 'elb_liveblog_before_save', $post_id, $post, $fields );

	foreach ( $fields as $field ) {

		if ( ! empty( $_POST[ $field ] ) ) {
			$new = apply_filters( 'elb_liveblog_meta_box_save_' . $field, $_POST[ $field ] );
			update_post_meta( $post_id, $field, filter_var( $new, FILTER_SANITIZE_STRING ) );
		} else {
			delete_post_meta( $post_id, $field );
		}
	}

	// If no status is set, we default to 'open'.
	if ( empty( get_post_meta( $post_id, '_elb_status', true ) ) ) {
		update_post_meta( $post_id, '_elb_status', 'open' );
	}

	do_action( 'elb_liveblog_after_save', $post_id, $post, $fields );

	do_action( 'elb_purge_feed_cache', $post_id );
}
add_action( 'save_post', 'elb_liveblog_meta_box_save', 10, 2 );

/**
 * Hook in on liveblog deletion.
 *
 * @param int $post_id
 * @return void
 */
function elb_liveblog_delete( $post_id ) {
	if ( ! get_post_meta( $post_id, '_elb_is_liveblog', true ) ) {
		return;
	}

	do_action( 'elb_delete_cache', $post_id );
}

add_action( 'before_delete_post', 'elb_liveblog_delete', 10 );

/**
 * Liveblog meta box save
 *
 * @param  int    $post_id
 * @param  object $post
 */
function elb_entry_meta_box_save( $post_id, $post ) {

	if ( ! isset( $_POST['elb_entry_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['elb_entry_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return;
	}

	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return;
	}

	if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
		return;
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	if ( $post->post_type != 'elb_entry' ) {
		return;
	}

	$fields = elb_entry_meta_box_fields();

	foreach ( $fields as $field ) {

		if ( ! empty( $_POST[ $field ] ) ) {
			$new = apply_filters( 'elb_entry_meta_box_save_' . $field, $_POST[ $field ] );
			update_post_meta( $post_id, $field, $new );
		} else {
			delete_post_meta( $post_id, $field );
		}
	}

	do_action( 'elb_entry_save', $post_id, $post );

	$liveblog = get_post_meta( $post_id, '_elb_liveblog', true );

	if ( ! empty( $liveblog ) ) {
		do_action( 'elb_purge_feed_cache', $liveblog );
	}
}
add_action( 'save_post', 'elb_entry_meta_box_save', 10, 2 );

/**
 * Flush cache when entry is put in trash.
 *
 * @param int $post_id
 * @return void
 */
function elb_entry_trash( $post_id ) {
	if ( get_post_type( $post_id ) != 'elb_entry' ) {
		return;
	}

	$liveblog = get_post_meta( $post_id, '_elb_liveblog', true );

	if ( empty( $liveblog ) ) {
		return;
	}

	do_action( 'elb_purge_feed_cache', $liveblog );
}

add_action( 'trashed_post', 'elb_entry_trash', 10 );
