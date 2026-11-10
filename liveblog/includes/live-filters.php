<?php

if (! defined('ABSPATH')) {
	exit;
}

/* Parse filter */
function mlb_liveblog_query_parse_filter($query)
{
	global $pagenow;

	static $busy = false;

	if ($busy
		|| ! is_admin()
		|| 'edit.php' !== $pagenow
		|| ! isset($_GET['micro_type'])
		|| ! isset($_GET['post_type'])) {
		return $query;
	}

	$post_type = sanitize_key(wp_unslash($_GET['post_type']));

	if (! in_array($post_type, (array) mbfun_get_live_supported_post_types(), true)) {
		return $query;
	}

	$busy = true;
	$ids  = mlb_get_displaying_source_ids($post_type);
	$busy = false;

	$query->query_vars['post__in'] = $ids ? $ids : array(0);

	return $query;
}
add_filter('parse_query', 'mlb_liveblog_query_parse_filter');

/* Add quicklinks */
function mlb_liveblogs_add_quicklinks($quicklinks)
{
	$screen    = function_exists('get_current_screen') ? get_current_screen() : null;
	$post_type = $screen ? $screen->post_type : get_query_var('post_type');

	if (! in_array($post_type, (array) mbfun_get_live_supported_post_types(), true)) {
		return $quicklinks;
	}

	$display_count = mbfun_get_live_display_count(array('post_type' => $post_type));

	if ($display_count > 0) {
		$current = isset($_GET['micro_type']) ? 'current' : null;

		$quicklinks['mlb_liveblogs'] = sprintf(
			'<a href="%1$s" class="%2$s">%3$s <span class="count">(%4$d)</span></a>',
			esc_url(admin_url('edit.php?post_type=' . $post_type . '&micro_type=liveblog')),
			esc_attr($current),
			esc_html__('Liveblog display', MICROBLOG_DOMAIN),
			(int) $display_count
		);
	}

	return $quicklinks;
}

function mlb_liveblog_register_quicklink_filters()
{
	foreach (mbfun_get_live_supported_post_types() as $post_type) {
		add_filter('views_edit-' . $post_type, 'mlb_liveblogs_add_quicklinks');
	}
}
add_action('init', 'mlb_liveblog_register_quicklink_filters');

function mlb_is_prefix_title_enabled()
{
	$mlb_options = mbfun_get_liveblog_settings();

	return apply_filters('mlb_prefix_title_enabled', ! empty($mlb_options['ml_prefix_title']) ? true : false);
}

function mlb_apply_live_title_prefix($title, $post_id = null)
{
	$livePrefix = mbfun_get_liveblog_option('ml_title_prefix', 'Liveblog');
	if (mlb_is_liveblog() && ! is_admin() && mlb_is_prefix_title_enabled()) {
		return __($livePrefix, MICROBLOG_DOMAIN) . ' - ' . $title;
	}

	return $title;
}

function mlb_apply_title_prefix_filter_condition($query)
{
	global $wp_query;

	if ($query === $wp_query) {
		add_filter('the_title', 'mlb_apply_live_title_prefix', 10, 2);
	} else {
		remove_filter('the_title', 'mlb_apply_live_title_prefix', 10, 2);
	}
}
add_action('loop_start', 'mlb_apply_title_prefix_filter_condition', 1, 10);

function mlb_set_show_liveblog_column($columns)
{
	$columns['mlb_liveblog'] = __('Source Liveblog', MICROBLOG_DOMAIN);

	return $columns;
}
add_filter('manage_microlive_posts_columns', 'mlb_set_show_liveblog_column');

function mlb_populate_herf_liveblog_column($column, $post_id)
{
	if ($column !== 'mlb_liveblog') {
		return;
	}
	$liveblog_id = get_post_meta($post_id, '_micro_live_post_id', true);

	if (! empty($liveblog_id)) {
		$url   = get_edit_post_link($liveblog_id);
		$title = get_the_title($liveblog_id);
		echo '<a href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
	} else {
		echo '-';
	}
}
add_action('manage_microlive_posts_custom_column', 'mlb_populate_herf_liveblog_column', 10, 2);

/* 「后续」标题 HTML；返回空串可用来彻底隐藏 */
function mlb_followup_title_html($post_id = null)
{
	if (! $post_id) {
		$post_id = get_the_ID();
	}

	$title = mlb_get_followup_title($post_id);

	if ('' === $title) {
		return '';
	}

	return '<h2 class="mlb-followup-title"' . mbfun_color_mode_attr() . '>' . esc_html($title) . '</h2>';
}

function mlb_maybe_add_liveblog($content)
{
	if (is_feed()) {
		return $content;
	}
	if (! mlb_is_liveblog()) {
		return $content;
	}
	$liveblog = MLB_Liveblog::mlb_fromId(get_the_ID());
	$content  = $content;
	$content .= mlb_followup_title_html(get_the_ID());
	$content .= $liveblog->mlb_content_render();

	return $content;
}
add_filter('the_content', 'mlb_maybe_add_liveblog');


add_action('restrict_manage_posts', 'mlb_add_post_dropdown_filter_to_manage_posts');
add_action('pre_get_posts', 'mlb_handle_query_vars_for_post_filter');
add_filter('query_vars', 'mlb_add_query_var_for_post_filter');

function mlb_add_query_var_for_post_filter($query_vars)
{
	$query_vars[] = 'live_id';
	return $query_vars;
}

function mlb_add_post_dropdown_filter_to_manage_posts()
{
	$current_screen = get_current_screen();

	if (! $current_screen || 'edit' !== $current_screen->base || 'microlive' !== $current_screen->post_type) {
		return;
	}

	$liveblogs = mbfun_get_live_liveblog_sources(true);

	echo '<select name="live_id">';
	echo '<option value="all">' . esc_html__('All liveblogs', MICROBLOG_DOMAIN) . '</option>';

	foreach ($liveblogs as $liveblog_id => $liveblog_title) {
		$display_title = mb_strlen($liveblog_title) > 8 ? mb_substr($liveblog_title, 0, 8) . '...' : $liveblog_title;
		$selected = selected($liveblog_id, get_query_var('live_id'), false);
		echo '<option value="' . esc_attr($liveblog_id) . '" ' . $selected . '>' . esc_html($display_title) . '</option>';
	}

	echo '</select>';
}


function mlb_handle_query_vars_for_post_filter($query)
{

	$state = $query->get('live_id');

	if (! $query->is_main_query() || empty($state)) {
		return;
	}

	if ('all' === $state) {
	} else {
		$new_meta_query_clause = array(
			'key'   => '_micro_live_post_id',
			'value' => $state,
		);
	}

	if (isset($new_meta_query_clause) || !empty($state)) {
		$meta_query = $query->get('meta_query');
		if (empty($meta_query)) {
			$meta_query = array();
		}
		array_push($meta_query, $new_meta_query_clause);
		$query->set('meta_query', $meta_query);
	}
}
