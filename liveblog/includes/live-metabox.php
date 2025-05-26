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
function mlb_add_meta_box() {
	$live_regist = mbfun_get_general_option('msk_liveblog_regist', false);
	if ($live_regist){
		$post_types = mbfun_get_live_supported_post_types();
		foreach ( $post_types as $post_type ) {
			add_meta_box( 'mlb_liveblog_meta_box', __( '连载Live', MICROBLOG_DOMAIN ), 'mlb_render_liveblog_meta_box', $post_type, 'normal', 'high' );
		}
	}
	add_meta_box( 'mlb_entry_meta_box', __( '连载Live-原文', MICROBLOG_DOMAIN ), 'mlb_render_entry_meta_box', 'microlive', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'mlb_add_meta_box' );

/**
 * Liveblog meta box fields
 *
 * @return array
 */
function mlb_liveblog_meta_box_fields() {
	$fields = array(
		'_micro_post_live_enable',
		'_micro_post_live_autoPolling',
		'_micro_post_live_status',
	);

	return apply_filters( 'mlb_liveblog_meta_box_fields_save', $fields );
}

/**
 * Entry meta box fields
 *
 * @return array
 */
function mlb_entry_meta_box_fields() {
	$fields = array(
		'_micro_live_post_id',
	);

	return apply_filters( 'mlb_entry_meta_box_fields_save', $fields );
}

/**
 * Render liveblog meta box
 */
function mlb_render_liveblog_meta_box( $post ) {
	do_action( 'mlb_liveblog_meta_box_fields', $post->ID );

	wp_nonce_field( basename( __FILE__ ), 'mlb_liveblog_meta_box_nonce' );
}

/**
 * Render entry meta box
 */
function mlb_render_entry_meta_box() {
	global $post;

	do_action( 'mlb_entry_meta_box_fields', $post->ID );

	wp_nonce_field( basename( __FILE__ ), 'mlb_entry_meta_box_nonce' );
}

/**
 * Render Liveblog Options
 *
 * @param int $post_id (Post) ID
 */
function mlb_render_liveblog_options( $post_id ) {
	$is_liveblog = get_post_meta( $post_id, '_micro_post_live_enable', true );

	do_action( 'mlb_render_before_liveblog_options', $post_id );
	?>
	<div class="mlb-input-group">
		<label for="mlb-show-liveblogs">
			<?php _e( 'Enable liveblog', MICROBLOG_DOMAIN ); ?>
			<input type="checkbox" name="_micro_post_live_enable" value="1" <?php checked( $is_liveblog, '1', true ); ?> id="mlb-show-liveblogs">
		</label>
	<?php

	if ( ! empty( $is_liveblog ) ) {
		$status = get_post_meta( $post_id, '_micro_post_live_status', true );
		$refresh = get_post_meta( $post_id, '_micro_post_live_autoPolling', true );
		?>
			<label for="mlb-refresh">
				<?php _e( 'Auto Refresh', MICROBLOG_DOMAIN ); ?>
				<input type="checkbox" name="_micro_post_live_autoPolling" value="1" <?php checked( $refresh, '1', true ); ?> id="mlb-refresh">
			</label>
			<label for="mlb_status"><?php _e( 'Live Status', MICROBLOG_DOMAIN ); ?>
				<select name="_micro_post_live_status" id="mlb_status">
					<?php foreach ( mbfun_get_live_liveblog_status_options() as $option_value => $option_name ) { ?>
						<option value="<?php echo $option_value; ?>" <?php selected( $option_value, $status, true ); ?>><?php echo $option_name; ?></option>
					<?php } ?>
				</select>
			</label>
		<?php
	}
	?> </div> <?php
	if ( ! empty( $is_liveblog ) ) {
		?>
		<!-- <div class="mlb-input-group">
			// 连载博客的 调用API
			<label for="mlb-liveblog-endpoint"><?php _e( 'API-endpoint URL', MICROBLOG_DOMAIN ); ?></label>
			<input type="text" id="mlb-liveblog-endpoint" onclick="this.focus(); this.select()" value="<?php echo mbfun_get_live_liveblog_api_endpoint( $post_id ); ?>" readonly="readonly" class="widefat">
		</div> -->
		<?php
	}
	do_action( 'mlb_render_after_liveblog_options', $post_id );
}
add_action( 'mlb_liveblog_meta_box_fields', 'mlb_render_liveblog_options', 1 );

/**
 * Render Entry Options
 *
 * @param int $post_id (Post) ID
 */
function mlb_render_entry_options( $post_id ) {
	$liveblog = get_post_meta( $post_id, '_micro_live_post_id', true );
	$status   = false;

	if ( ! empty( $liveblog ) ) {
		$status = mbfun_get_live_liveblog_status( $liveblog );
	}

	$liveblogs = mbfun_get_live_liveblogs_by_status( 'open' );

	do_action( 'mlb_before_entry_options', $post_id );

	?>

	<?php if ( $status === 'closed' ) { ?>
		<p><?php printf( __( 'This item is attached to a <a href="%s">closed</a> liveblog, Display this when it is open. ', MICROBLOG_DOMAIN ), get_edit_post_link( $liveblog ) ); ?></p>
	<?php } elseif ( $liveblogs ) { ?>
		<div class="mlb-input-group">
			<label for="mlb-liveblog"><?php _e( 'Select liveblog', MICROBLOG_DOMAIN ); ?></label>
			<select name="_micro_live_post_id" id="mlb-liveblog" class="mlb-selectize">
				<?php foreach ( $liveblogs as $liveblog_id => $liveblog_title ) { ?>
					<option value="<?php echo $liveblog_id; ?>" <?php selected( $liveblog, $liveblog_id, true ); ?>><?php echo $liveblog_title; ?></option>
				<?php } ?>
			</select>
		</div>

		<?php if ( ! empty( $liveblog ) ) { ?>
			<div class="mlb-input-group">
				<label for="mlb-liveblog-entry-link"><?php _e( 'Direct link to entry', MICROBLOG_DOMAIN ); ?></label>
				<input type="text" id="mlb-liveblog-entry-link" onclick="this.focus(); this.select()" value="<?php echo mbfun_get_live_entry_url( $post_id ); ?>" readonly="readonly" class="widefat">
			</div>
		<?php } ?>
	<?php } else { ?>
		<p><?php _e( 'There is no liveblog created yet.', MICROBLOG_DOMAIN ); ?></p>
		<?php if ( ! empty( $liveblog ) ) { ?>
		<p><?php printf( __( 'Alternatively, you can enable the <a href="%s">disabled</a> liveblog status ', MICROBLOG_DOMAIN ), get_edit_post_link( $liveblog ) ); ?></p>
		<?php } ?>
	<?php } ?>

	<?php
	do_action( 'mlb_after_entry_options', $post_id );
}
add_action( 'mlb_entry_meta_box_fields', 'mlb_render_entry_options', 1 );

/**
 * 所有类型文章 liveblog meta box save
 *
 * @param  int    $post_id
 * @param  object $post
 */
function mlb_liveblog_meta_box_save( $post_id, $post ) {

	if ( ! isset( $_POST['mlb_liveblog_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['mlb_liveblog_meta_box_nonce'], basename( __FILE__ ) ) ) {
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
	$fields = mlb_liveblog_meta_box_fields();

	do_action( 'mlb_liveblog_before_save', $post_id, $post, $fields );
	foreach ( $fields as $field ) {
		if ( ! empty( $_POST[ $field ] ) ) {
			$new = apply_filters( 'mlb_liveblog_meta_box_save_' . $field, $_POST[ $field ] );
			update_post_meta( $post_id, $field, filter_var( $new, FILTER_SANITIZE_STRING ) );
		} else {
			delete_post_meta( $post_id, $field );
		}
	}
	// If no status is set, we default to 'open'.
	if ( empty( get_post_meta( $post_id, '_micro_post_live_status', true ) ) ) {
		update_post_meta( $post_id, '_micro_post_live_status', 'open' );
	}

	do_action( 'mlb_liveblog_after_save', $post_id, $post, $fields );

	do_action( 'mlb_purge_feed_cache', $post_id );
}
add_action( 'save_post', 'mlb_liveblog_meta_box_save', 10, 2 );

/**
 * Hook in on liveblog deletion.
 *
 * @param int $post_id
 * @return void
 */
function mlb_liveblog_delete( $post_id ) {
	if ( ! get_post_meta( $post_id, '_micro_post_live_enable', true ) ) {
		return;
	}

	do_action( 'mlb_delete_cache', $post_id );
}

add_action( 'before_delete_post', 'mlb_liveblog_delete', 10 );

/**
 * Liveblog 类型的文章 meta box save
 *
 * @param  int    $post_id
 * @param  object $post
 */
function mlb_entry_meta_box_save( $post_id, $post ) {

	if ( ! isset( $_POST['mlb_entry_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['mlb_entry_meta_box_nonce'], basename( __FILE__ ) ) ) {
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

	if ( $post->post_type != 'microlive' ) {
		return;
	}

	$fields = mlb_entry_meta_box_fields();

	foreach ( $fields as $field ) {
		if ( ! empty( $_POST[ $field ] ) ) {
			$new = apply_filters( 'mlb_entry_meta_box_save_' . $field, $_POST[ $field ] );
			update_post_meta( $post_id, $field, $new );
		} else {
			delete_post_meta( $post_id, $field );
		}
	}

	do_action( 'mlb_entry_save', $post_id, $post );

	$liveblog = get_post_meta( $post_id, '_micro_live_post_id', true );

	if ( ! empty( $liveblog ) ) {
		do_action( 'mlb_purge_feed_cache', $liveblog );
	} else {
		// wp_die( 'MicroLive 不能没有 原始文章Post_id，保存失败。', 'Error' );
		echo '<script>alert("MicroLive 原始文章Post_id不能为空，保存失败。");</script>';
	}
}
add_action( 'save_post', 'mlb_entry_meta_box_save', 10, 2 );

/**
 * Flush cache when entry is put in trash.
 *
 * @param int $post_id
 * @return void
 */
function mlb_entry_trash( $post_id ) {
	if ( get_post_type( $post_id ) != 'microlive' ) {
		return;
	}

	$liveblog = get_post_meta( $post_id, '_micro_live_post_id', true );

	if ( empty( $liveblog ) ) {
		return;
	}

	do_action( 'mlb_purge_feed_cache', $liveblog );
}

add_action( 'trashed_post', 'mlb_entry_trash', 10 );
