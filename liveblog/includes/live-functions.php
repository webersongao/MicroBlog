<?php

if (! defined('ABSPATH')) {
	exit;
}

function mbfun_get_live_template_part($slug, $name = null, $load = true)
{
	do_action('get_template_part_' . $slug, $slug, $name);

	$templates = array();
	if (isset($name)) {
		$templates[] = $slug . '-' . $name . '.php';
	}
	$templates[] = $slug . '.php';

	$templates = apply_filters('mbfun_get_live_template_part', $templates, $slug, $name);

	return mlb_locate_template($templates, $load, false);
}

function mlb_locate_template($template_names, $load = false, $require_once = true)
{
	$located = false;

	foreach ((array) $template_names as $template_name) {

		if (empty($template_name)) {
			continue;
		}

		$template_name = ltrim($template_name, '/');

		if (file_exists(trailingslashit(get_stylesheet_directory()) . 'micro_live_theme/' . $template_name)) {
			$located = trailingslashit(get_stylesheet_directory()) . 'micro_live_theme/' . $template_name;
			break;

		} elseif (file_exists(trailingslashit(get_template_directory()) . 'micro_live_theme/' . $template_name)) {
			$located = trailingslashit(get_template_directory()) . 'micro_live_theme/' . $template_name;
			break;

		} elseif (file_exists(trailingslashit(mbfun_get_live_templates_dir()) . $template_name)) {
			$located = trailingslashit(mbfun_get_live_templates_dir()) . $template_name;
			break;
		}
	}

	if ((true == $load) && ! empty($located)) {
		load_template($located, $require_once);
	} else {
		return $located;
	}
}

function mbfun_get_live_templates_dir()
{
	return mbfun_get_plugin_path() . 'templates';
}

/* 连载原文候选池 */
function mbfun_get_live_liveblog_sources($only_with_entries = false)
{
	$args = array(
		'post_type'      => mbfun_get_live_supported_post_types(),
		'post_status'    => array('publish', 'private'),
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	);

	if ($only_with_entries) {
		$entries = get_posts(array(
			'post_type'      => 'microlive',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_key'       => '_micro_live_post_id',
		));

		if (! $entries) {
			return array();
		}

		update_meta_cache('post', $entries);

		$parents = array();

		foreach ($entries as $entry_id) {
			$parent_id = (int) get_post_meta($entry_id, '_micro_live_post_id', true);

			if ($parent_id) {
				$parents[$parent_id] = $parent_id;
			}
		}

		if (! $parents) {
			return array();
		}

		$args['post__in'] = array_values($parents);
	}

	$args  = apply_filters('mlb_liveblog_sources_args', $args, $only_with_entries);
	$posts = get_posts($args);

	$result = array();

	foreach ($posts as $source) {
		$result[$source->ID] = $source->post_title;
	}

	return apply_filters('mlb_liveblog_sources', $result, $only_with_entries);
}

/* 连载状态数据层 */

/* 读取前台显示模式 */
function mlb_get_display_mode($post_id)
{
	$value = get_post_meta($post_id, '_micro_post_live_enable', true);

	if ('auto' === $value) {
		return 'auto';
	}

	if ('default' === $value || '1' === $value) {
		return 'default';
	}

	return 'off';
}

function mlb_get_liveblog_source_ids()
{
	static $map = null;

	if (null !== $map) {
		return $map;
	}

	$entries = get_posts(array(
		'post_type'        => 'microlive',
		'post_status'      => 'any',
		'posts_per_page'   => -1,
		'fields'           => 'ids',
		'no_found_rows'    => true,
		'meta_key'         => '_micro_live_post_id',
		'suppress_filters' => true,
	));

	$map = array();

	if ($entries) {
		update_meta_cache('post', $entries);

		foreach ($entries as $entry_id) {
			$parent_id = (int) get_post_meta($entry_id, '_micro_live_post_id', true);

			if ($parent_id) {
				$map[$parent_id] = true;
			}
		}
	}

	return $map;
}

function mlb_has_liveblog_entries($post_id)
{
	$map = mlb_get_liveblog_source_ids();

	return ! empty($map[(int) $post_id]);
}

/* 列表页四态的状态键 */
function mlb_get_liveblog_state($post_id)
{
	if (! mlb_has_liveblog_entries($post_id)) {
		return 'none';
	}

	return mlb_get_display_mode($post_id);
}

/* 四态的完整文案 */
function mlb_get_liveblog_state_labels()
{
	return array(
		'none'    => __('Not enabled', MICROBLOG_DOMAIN),
		'off'     => __('Active · Hidden', MICROBLOG_DOMAIN),
		'default' => __('Active · Default display', MICROBLOG_DOMAIN),
		'auto'    => __('Active · Auto refresh', MICROBLOG_DOMAIN),
	);
}

/* 可切换三态的短标签 */
function mlb_get_display_mode_labels()
{
	return array(
		'off'     => __('Hidden', MICROBLOG_DOMAIN),
		'default' => __('Default display', MICROBLOG_DOMAIN),
		'auto'    => __('Auto refresh', MICROBLOG_DOMAIN),
	);
}

function mlb_get_displaying_source_ids($post_type = null)
{
	$sources = mbfun_get_live_liveblog_sources(true);

	if (! $sources) {
		return array();
	}

	update_meta_cache('post', array_keys($sources));

	$ids = array();

	foreach (array_keys($sources) as $source_id) {
		if ('off' === mlb_get_display_mode($source_id)) {
			continue;
		}

		if ($post_type && get_post_type($source_id) !== $post_type) {
			continue;
		}

		$ids[] = (int) $source_id;
	}

	return $ids;
}

/* 前台显示连载区块的文章数 */
function mbfun_get_live_display_count($args = array())
{
	$post_type = ! empty($args['post_type']) ? $args['post_type'] : null;

	$count = count(mlb_get_displaying_source_ids($post_type));

	return (int) apply_filters('mlb_display_count', $count, $args);
}

/* 自动刷新轮询间隔 */
function mbfun_get_live_update_interval()
{
	$seconds = (int) mbfun_get_live_option('ml_auto_refresh_interval', 30);

	if ($seconds < 10 || $seconds > 300) {
		$seconds = 30;
	}

	return (int) apply_filters('mlb_update_interval', $seconds);
}

function mlb_has_auto_refresh($post_id)
{
	return mlb_has_liveblog_entries($post_id) && 'auto' === mlb_get_display_mode($post_id);
}

function mlb_is_liveblog()
{
	global $post;

	$is_liveblog = false;

	if (! empty($post->ID)
		&& mlb_has_liveblog_entries($post->ID)
		&& 'off' !== mlb_get_display_mode($post->ID)) {
		$is_liveblog = true;
	}

	return apply_filters('mlb_is_liveblog', $is_liveblog);
}

/* Get liveblog */
function mbfun_get_live_liveblog($post_id = null)
{
	if (! $post_id) {
		global $post;
		return $post;
	}
	return get_post($post_id);
}

function mbfun_get_live_liveblog_api_endpoint($id)
{
	return apply_filters('mlb_liveblog_api_endpoint', get_rest_url(null, "microlives/v1/liveblog/{$id}"), $id);
}

function mbfun_get_live_all_post_types()
{

	$all_types = get_post_types(array('public' => true), 'names', 'and');
	$specific_types = array('post', 'page', 'micropost');
	$intersected_post_types = array_intersect($all_types, $specific_types);

	return apply_filters('mlb_intersected_post_types', $intersected_post_types);
}


function mbfun_get_live_supported_post_types()
{
	$mlb_options = mbfun_get_liveblog_settings();

	$post_types = !empty($mlb_options['ml_post_types']) ? $mlb_options['ml_post_types'] : array('post');

	return apply_filters('mlb_post_types', $post_types);
}

function mlb_display_liveblog_layout($layout)
{
	$mlb_options = mbfun_get_liveblog_settings();

	if (!empty($mlb_options['ml_post_layout']) && is_array($mlb_options['ml_post_layout'])) {
		$livelayout = in_array($layout, $mlb_options['ml_post_layout']);
	} else {
		$livelayout = false;
	}
	return apply_filters('mlb_display_live_layout', $livelayout, $layout);
}

/* Display sort */
function mlb_display_revert_sort()
{
	$mlb_options = mbfun_get_liveblog_settings();

	$display_share = ! empty($mlb_options['ml_display_revort_sort']) ? true : false;

	return apply_filters('mlb_display_revort_timesort', $display_share);
}

function mbfun_get_live_show_entries()
{
	$mlb_options = mbfun_get_liveblog_settings();

	$show_entries = ! empty($mlb_options['ml_show_entries']) ? $mlb_options['ml_show_entries'] : 10;

	return apply_filters('mlb_show_entries', $show_entries);
}

/* Get theme */
/* 连载区块的配色 */
function mbfun_get_live_theme()
{
    return apply_filters('mlb_theme', mbfun_get_color_mode());
}

function mbfun_get_live_entry_content()
{
	global $post, $wp_embed;

	$content = apply_filters('mlb_entry_content', $post->post_content);

	return apply_filters('the_content', $content);
}

/* Entry content */
function mlb_entry_content()
{
	echo mbfun_get_live_entry_content();
}

function mbfun_get_live_entry_title()
{
	global $post;

	return apply_filters('mlb_entry_title', $post->post_title);
}

function mbfun_get_live_author_avatar($size = 30)
{
	global $post;
	$author_id = $post->post_author;
	$avatar_url = get_avatar_url($author_id, array('size' => $size));
	return $avatar_url;
}


/* Entry title */
function mlb_entry_title()
{
	echo mbfun_get_live_entry_title();
}

function mlb_entry_author_avatar($size = 30)
{
	echo mbfun_get_live_author_avatar($size);
}


function mbfun_get_live_highlighted_entry_id()
{
	$entry_id = apply_filters('mlb_highlighted_live_id', filter_input(INPUT_GET, 'entry', FILTER_SANITIZE_NUMBER_INT));

	return $entry_id;
}

/* Get entry URL */
function mbfun_get_live_entry_url($post = null)
{
	if (is_null($post)) {
		global $post;
	} elseif (is_numeric($post)) {
		$post = get_post($post);
	}

	$liveblog_id = get_post_meta($post->ID, '_micro_live_post_id', true);

	$url = add_query_arg('entry', $post->ID, get_permalink($liveblog_id));

	return apply_filters('mlb_entry_url', $url, $liveblog_id, $post);
}

function mbfun_get_live_edit_entry_url($post_id)
{
	return add_query_arg(
		array(
			'post'   => $post_id,
			'action' => 'edit',
		),
		admin_url('post.php')
	);
}

function mlb_edit_entry_link()
{
	global $post;

	if (empty($post->ID)) {
		return;
	}

	echo '<a href="' . esc_url(mbfun_get_live_edit_entry_url($post->ID)) . '" rel="nofollow">' . esc_html__('Edit This', MICROBLOG_DOMAIN) . '</a>';
}

function mlb_page_contains_liveblog()
{
	if (! is_singular()) {
		return false;
	}

	global $post;

	if (strpos(apply_filters('the_content', $post->post_content), 'mlb-liveblog') === false) {
		return false;
	}

	return true;
}

function mlb_add_theme_body_class($classes)
{
	if (is_singular() && mlb_is_liveblog()) {
		$classes[] = 'mlb-theme-' . mbfun_get_live_theme();
	}

	return $classes;
}
add_filter('body_class', 'mlb_add_theme_body_class');

/* Add meta data */
function mlb_add_meta_data()
{
	global $post;

	if (! is_singular() || ! mlb_is_liveblog()) {
		return;
	}

	$liveblog_url = get_permalink();

	$items = mbfun_get_live_feed_local($post->ID);

	if (empty($items) || ! is_array($items)) {
		return;
	}

	$organization = array(
		'@type' => 'Organization',
		'name'  => get_bloginfo('name'),
	);

	$metadata = array(
		'@type'             => 'LiveBlogPosting',
		'@context'          => 'https://schema.org',
		'@id'               => $liveblog_url,
		'headline'          => get_the_title(),
		'description'       => trim(preg_replace('/\s+/', ' ', strip_tags(get_the_content()))),
		'coverageStartTime' => get_the_date('c', $post),
		'coverageEndTime'   => wp_date('c', strtotime(($items['last_update'] ?? '') . ' + 1 hour')),
		'dateModified'      => $items['last_update'] ?? '',
		'url'               => $liveblog_url,
		'publisher'         => $organization
	);

	$entries = array();

	if (! empty($items['updates'])) {
		foreach ($items['updates'] as $entry) {
			$entry = (array) $entry;

			$entry_url = add_query_arg('entry', $entry['id'], $liveblog_url);

			$_entry = array(
				'@type'            => 'BlogPosting',
				'headline'         => $entry['title'],
				'url'              => $entry_url,
				'mainEntityOfPage' => $entry_url,
				'datePublished'    => $entry['datetime'],
				'dateModified'     => $entry['modified'],
				'articleBody'      => trim(preg_replace('/\s+/', ' ', strip_tags($entry['content']))),
			);

			if (mlb_display_liveblog_layout('ml_layout_author')) {
				$_entry['author'] = $organization;
			}

			$entries[] = $_entry;
		}
	}

	$metadata['liveBlogUpdate'] = $entries;

	$metadata = apply_filters('mlb_liveblog_metadata', $metadata, $post);
?>
	<script type="application/ld+json">
		<?php echo wp_json_encode($metadata); ?>
	</script>
	<?php
}
add_action('wp_head', 'mlb_add_meta_data');

function mbfun_get_live_feed_local($liveblog_id)
{
	$liveblog_id = absint($liveblog_id);

	if (! $liveblog_id) {
		return null;
	}

	$cache_key = 'microblog_liveblog_feed_' . $liveblog_id;

	$feed = get_transient($cache_key);

	if (is_array($feed)) {
		return $feed;
	}

	$liveblog = get_post($liveblog_id);

	if (! $liveblog) {
		return null;
	}

	$feed = \MicroLiveblogs\API\FeedFactory::make($liveblog_id);

	if (! is_array($feed)) {
		return null;
	}

	set_transient($cache_key, $feed, 5 * MINUTE_IN_SECONDS);

	return $feed;
}

/* 清除 feed 缓存 */
function mbfun_purge_live_feed_cache($liveblog_id)
{
	delete_transient('microblog_liveblog_feed_' . absint($liveblog_id));
}
add_action('mlb_purge_feed_cache', 'mbfun_purge_live_feed_cache');

function mbfun_get_live_datetime_format()
{
	return get_option('date_format') . ' ' . get_option('time_format');
}

function mbfun_get_live_entry_display_date()
{
	global $post;

	setup_postdata($post);

	$display = mbfun_get_live_option('ml_entry_date_format', 'human');

	if (! in_array($display, array('date', 'time', 'datetime'), true)) {
	?>
		<time class="mlb-js-update-time" datetime="<?php echo get_the_time('Y-m-d H:i'); ?>"><?php printf(_x('%s ago', '%s = human-readable time difference', MICROBLOG_DOMAIN), human_time_diff(get_the_time('U'), current_time('timestamp'))); ?></time>
	<?php
	} else {
		if ($display === 'time') {
			$format = get_option('time_format');
		} elseif ($display === 'date') {
			$format = get_option('date_format');
		} else {
			$format = mbfun_get_live_datetime_format();
		}
	?>
		<time datetime="<?php echo get_the_time('Y-m-d H:i'); ?>"><?php echo get_the_time($format); ?></time>
<?php
	}
}

function mbfun_get_live_liveblogs_with_post_id($post_id)
{

	$meta_key = '_micro_live_post_id';
	$args = array(
		'post_type'	=> array('post', 'page', 'micropost'),
		'posts_per_page' => -1,
		'meta_query' => array(
			array(
				'key' => $meta_key,
				'value' => strval($post_id)
			)
		)
	);
	$query = new WP_Query($args);

	$posts = $query->posts;

	return $posts;
}
