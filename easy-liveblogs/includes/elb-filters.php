<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parse filter.
 *
 * @param WP_Query $query The WP Query.
 * @return void
 */
function elb_liveblogs_parse_filter( $query ) {
	global $pagenow, $elb_options;

	$current_page = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';

	if ( ! is_admin() ) {
		return $query;
	}

	if ( ! in_array( $current_page, elb_get_supported_post_types() ) && 'edit.php' !== $pagenow ) {
		return $query;
	}

	if ( isset( $_GET['is-elb-liveblog'] ) ) {
		$query->query_vars['meta_key']     = '_elb_is_liveblog';
		$query->query_vars['meta_value']   = '1';
		$query->query_vars['meta_compare'] = '=';
	}

	return $query;
}
add_filter( 'parse_query', 'elb_liveblogs_parse_filter' );

/**
 * Add quicklinks
 *
 * @param  array $quicklinks
 * @return array
 */
function elb_liveblogs_add_quicklinks( $quicklinks ) {

	if ( ! in_array( get_query_var( 'post_type' ), elb_get_supported_post_types() ) ) {
		return $quicklinks;
	}

	$liveblog_count = elb_get_liveblogs_count( array( 'post_type' => get_query_var( 'post_type' ) ) );

	if ( $liveblog_count > 0 ) {

		$current = isset( $_GET['is-elb-liveblog'] ) ? 'current' : null;

		$quicklinks['elb_liveblogs'] = sprintf(
			'<a href="%s" class="' . $current . '">' . __( 'Liveblogs', ELB_TEXT_DOMAIN ) . ' <span class="count">(%d)</span></a>',
			admin_url( 'edit.php?post_type=' . get_query_var( 'post_type' ) ) . '&amp;is-elb-liveblog=1',
			$liveblog_count
		);
	}

	return $quicklinks;
}

/**
 * Register quicklink filters
 */
function elb_liveblog_register_quicklink_filters() {
	foreach ( elb_get_supported_post_types() as $post_type ) {
		add_filter( 'views_edit-' . $post_type, 'elb_liveblogs_add_quicklinks' );
	}
}
add_action( 'init', 'elb_liveblog_register_quicklink_filters' );

/**
 * Liveblog post state
 *
 * Maybe sets liveblog status after post title in admin area.
 *
 * @param  array   $post_states
 * @param  WP_Post $post
 * @return array
 */
function elb_liveblog_post_state( $post_states, $post ) {
	if ( elb_is_liveblog() && elb_get_liveblog_status() == 'closed' ) {
		$post_states[] = __( 'Closed' );
	}

	return $post_states;
}
add_filter( 'display_post_states', 'elb_liveblog_post_state', 2, 10 );

/**
 * Is prefix title enabled
 *
 * @return boolean
 */
function elb_is_prefix_title_enabled() {
	global $elb_options;

	return apply_filters( 'elb_prefix_title_enabled', ! empty( $elb_options['prefix_title'] ) ? true : false );
}

/**
 * Apply title prefix
 *
 * @param  string $title
 * @param  int    $post_id
 * @return string
 */
function elb_apply_title_prefix( $title, $post_id = null ) {
	if ( elb_is_liveblog() && ! is_admin() && elb_is_prefix_title_enabled() ) {
		return elb_get_liveblog_title_prefix() . $title;
	}

	return $title;
}

/**
 * Apply title prefix filter condition
 *
 * @param  WP_Query $query
 * @return void
 */
function elb_apply_title_prefix_filter_condition( $query ) {
	global $wp_query;

	if ( $query === $wp_query ) {
		add_filter( 'the_title', 'elb_apply_title_prefix', 10, 2 );
	} else {
		remove_filter( 'the_title', 'elb_apply_title_prefix', 10, 2 );
	}
}
add_action( 'loop_start', 'elb_apply_title_prefix_filter_condition', 1, 10 );

/**
 * Adds the liveblog column to the entries overview.
 *
 * @param array $columns
 * @return array
 */
function elb_set_elb_entry_liveblog_column( $columns ) {
	$columns['elb_liveblog'] = __( 'Liveblog', ELB_TEXT_DOMAIN );

	return $columns;
}
add_filter( 'manage_elb_entry_posts_columns', 'elb_set_elb_entry_liveblog_column' );

/**
 * Adds the liveblog link in the Liveblog column.
 *
 * @param string  $column
 * @param integer $post_id
 * @return void
 */
function elb_populate_elb_entry_liveblog_column( $column, $post_id ) {
	if ( $column !== 'elb_liveblog' ) {
		return;
	}

	$liveblog_id = get_post_meta( $post_id, '_elb_liveblog', true );

	if ( ! empty( $liveblog_id ) ) {
		$url   = get_edit_post_link( $liveblog_id );
		$title = get_the_title( $liveblog_id );

		echo '<a href="' . $url . '">' . $title . '</a>';
	} else {
		echo '-';
	}
}
add_action( 'manage_elb_entry_posts_custom_column', 'elb_populate_elb_entry_liveblog_column', 10, 2 );

/**
 * Maybe append the liveblog to the post contnet.
 *
 * @param string $content
 * @return string
 */
function elb_maybe_add_liveblog( $content ) {
	if ( ! elb_is_liveblog() ) {
		return $content;
	}

	$liveblog = ELB_Liveblog::fromId( get_the_ID() );
	$content  = $content;
	$content .= $liveblog->render();

	return $content;
}
add_filter( 'the_content', 'elb_maybe_add_liveblog' );