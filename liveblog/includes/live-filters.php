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
function mlb_liveblog_query_parse_filter( $query ) {
	global $pagenow, $mlb_options;

	$current_page = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';

	if ( ! is_admin() ) {
		return $query;
	}

	if ( ! in_array( $current_page, mlb_get_supported_post_types() ) && 'edit.php' !== $pagenow ) {
		return $query;
	}

	if ( isset( $_GET['is-mlb-liveblog'] ) ) {
		$query->query_vars['meta_key']     = '_micro_post_live_enable';
		$query->query_vars['meta_value']   = '1';
		$query->query_vars['meta_compare'] = '=';
	}

	return $query;
}
add_filter( 'parse_query', 'mlb_liveblog_query_parse_filter' );

/**
 * Add quicklinks
 *
 * @param  array $quicklinks
 * @return array
 */
function mlb_liveblogs_add_quicklinks( $quicklinks ) {

	if ( ! in_array( get_query_var( 'post_type' ), mlb_get_supported_post_types() ) ) {
		return $quicklinks;
	}

	$liveblog_count = mlb_get_liveblogs_count( array( 'post_type' => get_query_var( 'post_type' ) ) );

	if ( $liveblog_count > 0 ) {

		$current = isset( $_GET['is-mlb-liveblog'] ) ? 'current' : null;

		$quicklinks['mlb_liveblogs'] = sprintf(
			'<a href="%s" class="' . $current . '">' . __( 'Liveblogs', MICROBLOG_DOMAIN ) . ' <span class="count">(%d)</span></a>',
			admin_url( 'edit.php?post_type=' . get_query_var( 'post_type' ) ) . '&amp;is-mlb-liveblog=1',
			$liveblog_count
		);
	}

	return $quicklinks;
}

/**
 * Register quicklink filters
 */
function mlb_liveblog_register_quicklink_filters() {
	foreach ( mlb_get_supported_post_types() as $post_type ) {
		add_filter( 'views_edit-' . $post_type, 'mlb_liveblogs_add_quicklinks' );
	}
}
add_action( 'init', 'mlb_liveblog_register_quicklink_filters' );

/**
 * Liveblog post state
 *
 * Maybe sets liveblog status after post title in admin area.
 *
 * @param  array   $post_states
 * @param  WP_Post $post
 * @return array
 */
function mlb_liveblog_post_state( $post_states, $post ) {
	if ( mlb_is_liveblog() && mlb_get_liveblog_status() == 'closed' ) {
		$post_states[] = __( 'Closed' );
	}

	return $post_states;
}
add_filter( 'display_post_states', 'mlb_liveblog_post_state', 2, 10 );

/**
 * Is prefix title enabled
 *
 * @return boolean
 */
function mlb_is_prefix_title_enabled() {
	global $mlb_options;

	return apply_filters( 'mlb_prefix_title_enabled', ! empty( $mlb_options['prefix_title'] ) ? true : false );
}

/**
 * Apply title prefix
 *
 * @param  string $title
 * @param  int    $post_id
 * @return string
 */
function mlb_apply_title_prefix( $title, $post_id = null ) {
	if ( mlb_is_liveblog() && ! is_admin() && mlb_is_prefix_title_enabled() ) {
		return mlb_get_liveblog_title_prefix() . $title;
	}

	return $title;
}

/**
 * Apply title prefix filter condition
 *
 * @param  WP_Query $query
 * @return void
 */
function mlb_apply_title_prefix_filter_condition( $query ) {
	global $wp_query;

	if ( $query === $wp_query ) {
		add_filter( 'the_title', 'mlb_apply_title_prefix', 10, 2 );
	} else {
		remove_filter( 'the_title', 'mlb_apply_title_prefix', 10, 2 );
	}
}
add_action( 'loop_start', 'mlb_apply_title_prefix_filter_condition', 1, 10 );

/**
 * Adds the liveblog column to the entries overview.
 *
 * @param array $columns
 * @return array
 */
function mlb_set_show_liveblog_column( $columns ) {
	$columns['mlb_liveblog'] = __( '连载原文', MICROBLOG_DOMAIN );

	return $columns;
}
add_filter( 'manage_microlive_posts_columns', 'mlb_set_show_liveblog_column' );

/**
 * Adds the liveblog link in the Liveblog column.
 *
 * @param string  $column
 * @param integer $post_id
 * @return void
 */
function mlb_populate_herf_liveblog_column( $column, $post_id ) {
	if ( $column !== 'mlb_liveblog' ) {
		return;
	}
	$liveblog_id = get_post_meta( $post_id, '_micro_live_post_id', true );

	if ( ! empty( $liveblog_id ) ) {
		$url   = get_edit_post_link( $liveblog_id );
		$title = get_the_title( $liveblog_id );
		echo '<a href="' . $url . '">' . $title . '</a>';
	} else {
		echo '-';
	}
}
add_action( 'manage_microlive_posts_custom_column', 'mlb_populate_herf_liveblog_column', 10, 2 );

/**
 * Maybe append the liveblog to the post contnet.
 *
 * @param string $content
 * @return string
 */
function mlb_maybe_add_liveblog( $content ) {
	if ( ! mlb_is_liveblog() ) {
		return $content;
	}

	$liveblog = MLB_Liveblog::fromId( get_the_ID() );
	$content  = $content;
	$content .= $liveblog->render();

	return $content;
}
add_filter( 'the_content', 'mlb_maybe_add_liveblog' );


// ===========   增加过滤项  ===============//

add_action('restrict_manage_posts', 'mlb_add_post_dropdown_filter_to_manage_posts');
add_action('pre_get_posts', 'mlb_handle_query_vars_for_post_filter');
add_filter('query_vars', 'mlb_add_query_var_for_post_filter');

/**
 * Register the query_var for filtering posts by liveblog state
 *
 * @param array $query_vars
 * @return array
 * @filter query_vars
 */
function mlb_add_query_var_for_post_filter( $query_vars ) {
	$query_vars[] = 'live_id';
	return $query_vars;
}

/**
 * Render the liveblog state select to filter posts in the post table
 *
 * @action restrict_manage_posts
 */
function mlb_add_post_dropdown_filter_to_manage_posts() {
    $current_screen = get_current_screen();
	$current_screen = get_current_screen();
	if ( 'edit' !== $current_screen->base || 'microlive' !== $current_screen->post_type ) {
		return;
	}
    // 直接渲染下拉菜单
	$liveblogs = mlb_get_liveblogs_by_status( 'all' );

	echo '<select name="live_id">';
		echo sprintf( '<option value="%s" %s>%s</option>', esc_attr( 'all' ), $true, esc_html( '全部直播' ) );
		foreach ( $liveblogs as $liveblog_id => $liveblog_title ) {
			$display_title = mb_strlen( $liveblog_title ) > 6 ? mb_substr( $liveblog_title, 0, 6 ) . '...' : $liveblog_title;
			$selected = selected( $liveblog_id, get_query_var( 'live_id' ), false );
			echo sprintf( '<option value="%s" %s>%s</option>', esc_attr( $liveblog_id ), $selected, esc_html( $display_title ) );
		}
	echo '</select>';
}


/**
 * Translate the live_id query_var into a meta_query
 *
 * @param WP_Query $query
 */
function mlb_handle_query_vars_for_post_filter( $query ) {
	
	$state = $query->get( 'live_id' );

	if ( ! $query->is_main_query() || empty($state)) {
		return;
	}
	
	if ( 'all' === $state ) {

	} else {
		$new_meta_query_clause = array(
			'key'   => '_micro_live_post_id',
			'value' => $state,
		);
	}

	if ( isset( $new_meta_query_clause ) || !empty($state) ) {
		$meta_query = $query->get( 'meta_query' );
		if ( empty( $meta_query ) ) {
			$meta_query = array();
		}
		array_push( $meta_query, $new_meta_query_clause );
		$query->set( 'meta_query', $meta_query );
	}
}