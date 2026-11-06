<?php

// Exit if accessed directly
if (! defined('ABSPATH')) {
	exit;
}

/**
 * Get template part
 *
 * @see https://pippinsplugins.com/template-file-loaders-plugins/
 *
 * @param  string $view
 * @return string
 */
function mbfun_get_live_template_part($slug, $name = null, $load = true)
{
	// Execute code for this part
	do_action('get_template_part_' . $slug, $slug, $name);

	// Setup possible parts
	$templates = array();
	if (isset($name)) {
		$templates[] = $slug . '-' . $name . '.php';
	}
	$templates[] = $slug . '.php';

	// Allow template parts to be filtered
	$templates = apply_filters('mbfun_get_live_template_part', $templates, $slug, $name);

	// Return the part that is found
	return mlb_locate_template($templates, $load, false);
}

/**
 * Locate template
 *
 * @param  string|array $template_names
 * @param  boolean      $load
 * @param  boolean      $require_once
 */
function mlb_locate_template($template_names, $load = false, $require_once = true)
{
	// No file found yet
	$located = false;

	// Try to find a template file
	foreach ((array) $template_names as $template_name) {

		// Continue if template is empty
		if (empty($template_name)) {
			continue;
		}

		// Trim off any slashes from the template name
		$template_name = ltrim($template_name, '/');

		// Check child theme first
		if (file_exists(trailingslashit(get_stylesheet_directory()) . 'micro_live_theme/' . $template_name)) {
			$located = trailingslashit(get_stylesheet_directory()) . 'micro_live_theme/' . $template_name;
			break;

			// Check parent theme next
		} elseif (file_exists(trailingslashit(get_template_directory()) . 'micro_live_theme/' . $template_name)) {
			$located = trailingslashit(get_template_directory()) . 'micro_live_theme/' . $template_name;
			break;

			// Check theme compatibility last
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

/**
 * Get templates dir 
 * 对应文件夹名称，慎改
 * @return string
 */
function mbfun_get_live_templates_dir()
{
	return mbfun_get_plugin_path() . 'templates';
}

/**
 * 连载原文候选池：支持类型下所有已发布/私密文章。
 *
 * 原文资格只由「文章类型 + 发布状态」决定，不再逐篇勾选启用。
 * $only_with_entries 供后台筛选下拉使用，只返回已被条目引用的文章。
 *
 * @param bool $only_with_entries 是否只保留已关联条目的原文。
 * @return array 文章 ID => 标题。
 */
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

		// 一次预取 meta，避免循环里逐条查询。
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

/**
 * 连载状态数据层
 * ---------------------------------------------------------------------
 * 文章列表页呈现四种状态：
 *   none    未开启           —— 没有任何连载条目把它作为原文（派生，不可修改）
 *   off     开启中·不展示    —— 有条目引用，但前台不显示连载区块
 *   default 开启中·默认展示  —— 有条目引用，前台静态展示，不轮询
 *                              （即「已完结」，前台不做单独标记）
 *   auto    开启中·自动刷新  —— 有条目引用，前台展示并轮询新条目
 *
 * 落库只用一个 meta：_micro_post_live_enable，取值 'default' / 'auto'，
 * 不存值即 off。旧版二态开关的 '1' 等价 'default'，无需迁移数据。
 * 「开启中」由条目引用关系派生，不落库，避免两处状态不同步。
 */

/**
 * 读取前台显示模式。
 *
 * @param int $post_id 原文 ID。
 * @return string off|default|auto
 */
function mlb_get_display_mode($post_id)
{
	$value = get_post_meta($post_id, '_micro_post_live_enable', true);

	if ('auto' === $value) {
		return 'auto';
	}

	// '1' 是旧版二态开关的显示值，按「默认展示」平滑升级。
	if ('default' === $value || '1' === $value) {
		return 'default';
	}

	return 'off';
}

/**
 * 一次性取出所有「已被连载条目引用」的原文 ID。
 *
 * 列表页与前台循环都会反复问「这篇有没有条目」，逐篇查会产生 N+1；
 * 这里同请求内只做一次条目查询 + 一次 meta 预取，之后纯内存判断。
 * 含草稿/待审/私密条目；回收站条目不算（WP 'any' 语义）。
 *
 * @return array 以原文 ID 为键的 map。
 */
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

/**
 * 这篇文章是否已被连载条目作为原文引用。
 *
 * @param int $post_id 文章 ID。
 * @return bool
 */
function mlb_has_liveblog_entries($post_id)
{
	$map = mlb_get_liveblog_source_ids();

	return ! empty($map[(int) $post_id]);
}

/**
 * 列表页四态的状态键。
 *
 * @param int $post_id 文章 ID。
 * @return string none|off|default|auto
 */
function mlb_get_liveblog_state($post_id)
{
	if (! mlb_has_liveblog_entries($post_id)) {
		return 'none';
	}

	return mlb_get_display_mode($post_id);
}

/**
 * 四态的完整文案。
 *
 * @return array
 */
function mlb_get_liveblog_state_labels()
{
	return array(
		'none'    => __('Not enabled', MICROBLOG_DOMAIN),
		'off'     => __('Active · Hidden', MICROBLOG_DOMAIN),
		'default' => __('Active · Default display', MICROBLOG_DOMAIN),
		'auto'    => __('Active · Auto refresh', MICROBLOG_DOMAIN),
	);
}

/**
 * 可切换三态的短标签（列内链接用）。
 *
 * @return array
 */
function mlb_get_display_mode_labels()
{
	return array(
		'off'     => __('Hidden', MICROBLOG_DOMAIN),
		'default' => __('Default display', MICROBLOG_DOMAIN),
		'auto'    => __('Auto refresh', MICROBLOG_DOMAIN),
	);
}

/**
 * 已在前台展示连载区块的原文 ID 列表。
 *
 * 快速筛选的数字与筛选结果共用这里，保证两者一致。
 *
 * @param string|null $post_type 只保留该文章类型。
 * @return array
 */
function mlb_get_displaying_source_ids($post_type = null)
{
	$sources = mbfun_get_live_liveblog_sources(true);

	if (! $sources) {
		return array();
	}

	// 一次预取所有原文的 meta，避免逐条 get_post_meta 查询。
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

/**
 * 前台显示连载区块的文章数（列表页快速筛选用）。
 *
 * @param array $args 可带 post_type。
 * @return int
 */
function mbfun_get_live_display_count($args = array())
{
	$post_type = ! empty($args['post_type']) ? $args['post_type'] : null;

	$count = count(mlb_get_displaying_source_ids($post_type));

	return (int) apply_filters('mlb_display_count', $count, $args);
}

/**
 * 自动刷新轮询间隔（秒），全局统一，范围 10–300。
 *
 * @return int
 */
function mbfun_get_live_update_interval()
{
	$seconds = (int) mbfun_get_live_option('ml_auto_refresh_interval', 30);

	if ($seconds < 10 || $seconds > 300) {
		$seconds = 30;
	}

	return (int) apply_filters('mlb_update_interval', $seconds);
}

/**
 * 这篇文章是否启用了前台自动刷新。
 *
 * @param int $post_id 文章 ID。
 * @return bool
 */
function mlb_has_auto_refresh($post_id)
{
	return mlb_has_liveblog_entries($post_id) && 'auto' === mlb_get_display_mode($post_id);
}

/**
 * 前台是否在这篇文章下方显示连载区块。
 *
 * 同时要求「有条目引用」与「显示模式非 off」：没有条目的文章不再挂空区块。
 * 一篇文章能否作为连载原文不受影响：原文资格只看类型与发布状态。
 *
 * @return boolean
 */
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

/**
 * Get liveblog
 *
 * @param int|null $post_id
 * @return mixed
 */
function mbfun_get_live_liveblog($post_id = null)
{
	if (! $post_id) {
		global $post;
		return $post;
	}
	return get_post($post_id);
}

/**
 * Get liveblog API endpoint URL.
 *
 * @param int $id
 * @return string
 */
function mbfun_get_live_liveblog_api_endpoint($id)
{
	return apply_filters('mlb_liveblog_api_endpoint', get_rest_url(null, "microlives/v1/liveblog/{$id}"), $id);
}

// get_post_types
/**
 * Get all post types
 *
 * @return array
 */
function mbfun_get_live_all_post_types()
{

	$all_types = get_post_types(array('public' => true), 'names', 'and');
	// $specific_types = array( 'post', 'page' );  
	$specific_types = array('post', 'page', 'micropost');
	$intersected_post_types = array_intersect($all_types, $specific_types);

	return apply_filters('mlb_intersected_post_types', $intersected_post_types);
}


/**
 * Get supported post types
 *
 * @return array
 */
function mbfun_get_live_supported_post_types()
{
	$mlb_options = mbfun_get_liveblog_settings();

	$post_types = !empty($mlb_options['ml_post_types']) ? $mlb_options['ml_post_types'] : array('post');

	return apply_filters('mlb_post_types', $post_types);
}

/**
 * Display post layout.
 *
 * @return boolean
 */
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

/**
 * Display sort.
 *
 * @return boolean
 */
function mlb_display_revert_sort()
{
	$mlb_options = mbfun_get_liveblog_settings();

	$display_share = ! empty($mlb_options['ml_display_revort_sort']) ? true : false;

	return apply_filters('mlb_display_revort_timesort', $display_share);
}

/**
 * Get show entries
 *
 * @return string
 */
function mbfun_get_live_show_entries()
{
	$mlb_options = mbfun_get_liveblog_settings();

	$show_entries = ! empty($mlb_options['ml_show_entries']) ? $mlb_options['ml_show_entries'] : 10;

	return apply_filters('mlb_show_entries', $show_entries);
}

/**
 * Get theme
 *
 * @return string
 */
/**
 * 连载区块的配色（历史函数名保留）。
 *
 * 配色自 1.9.1 起由插件级「颜色模式」统一管理，返回 light / dark / auto，
 * 仅用于输出 mlb-theme-* 类名供第三方 CSS 定位；实际颜色走 --mlb-* 令牌，
 * 见 assets/css/liveblog.css。
 *
 * @return string
 */
function mbfun_get_live_theme()
{
    return apply_filters('mlb_theme', mbfun_get_color_mode());
}

/**
 * Custom the_content alternative
 *
 * @return string
 */
function mbfun_get_live_entry_content()
{
	global $post, $wp_embed;

	$content = apply_filters('mlb_entry_content', $post->post_content);

	return apply_filters('the_content', $content);
}

/**
 * Entry content
 *
 * @return void
 */
function mlb_entry_content()
{
	echo mbfun_get_live_entry_content();
}

/**
 * Get entry title
 *
 * @return string
 */
function mbfun_get_live_entry_title()
{
	global $post;

	return apply_filters('mlb_entry_title', $post->post_title);
}

/**
 * Entry author avatar
 *
 * @return void
 */
function mbfun_get_live_author_avatar($size = 30)
{
	global $post;
	$author_id = $post->post_author;
	$avatar_url = get_avatar_url($author_id, array('size' => $size)); // 获取头像的 URL
	return $avatar_url;
}


/**
 * Entry title
 *
 * @return void
 */
function mlb_entry_title()
{
	echo mbfun_get_live_entry_title();
}

/**
 * Entry author avatar
 *
 * @return void
 */
function mlb_entry_author_avatar($size = 30)
{
	echo mbfun_get_live_author_avatar($size);
}


/**
 * Get highlited entry id.
 *
 * @return mixed
 */
function mbfun_get_live_highlighted_entry_id()
{
	$entry_id = apply_filters('mlb_highlighted_live_id', filter_input(INPUT_GET, 'entry', FILTER_SANITIZE_NUMBER_INT));

	return $entry_id;
}

/**
 * Get entry URL.
 *
 * @param WP_Post|int|null $post
 * @return string
 */
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

/**
 * Get edit entry url.
 *
 * @param int $post_id
 * @return string
 */
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

/**
 * Edit entry link.
 *
 * @param int $post_id
 * @return void
 */
function mlb_edit_entry_link()
{
	global $post;

	if (empty($post->ID)) {
		return;
	}

	echo '<a href="' . esc_url(mbfun_get_live_edit_entry_url($post->ID)) . '" rel="nofollow">' . esc_html__('Edit This', MICROBLOG_DOMAIN) . '</a>';
}

/**
 * Determine if assets should be enqueued.
 *
 * @return bool
 */
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

/**
 * Maybe add body class.
 *
 * @param array $classes
 * @return array
 */
function mlb_add_theme_body_class($classes)
{
	// 只在单篇页生效：首页/归档的全局 $post 恰好可能是开启了连载的文章。
	if (is_singular() && mlb_is_liveblog()) {
		$classes[] = 'mlb-theme-' . mbfun_get_live_theme();
	}

	return $classes;
}
add_filter('body_class', 'mlb_add_theme_body_class');

/**
 * Add meta data.
 *
 * @return void
 */
function mlb_add_meta_data()
{
	global $post;

	// 结构化数据只输出在单篇页：首页/归档的全局 $post 可能是开启了连载的文章，
	// 仅凭 mlb_is_liveblog() 判断会在首页误输出 LiveBlogPosting。
	if (! is_singular() || ! mlb_is_liveblog()) {
		return;
	}

	$liveblog_url = get_permalink();

	$items = mbfun_get_live_feed_local($post->ID);

	// Feed 构建失败时不输出半截结构化数据。
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
			// FeedFactory 可能返回 LiveEntry 对象，统一转数组后再按键取值。
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

/**
 * 构建 liveblog feed（不回源 HTTP 请求自己）。
 *
 * 旧实现用 file_get_contents 请求自身 REST 端点并禁用 SSL 校验：
 * 每次 wp_head 都产生一次自 HTTP 请求（单进程 FPM 有死锁风险）。
 *
 * 全站只有这一层缓存：按 liveblog ID 存 5 分钟 transient，
 * 前台轮询（默认 30 秒一次）与页头结构化数据共用同一份。
 * 条目增删改会触发 mlb_purge_feed_cache 立即失效，5 分钟只是兜底上限——
 * 万一漏了某条失效路径，最多脏 5 分钟，不会像固定 TTL 那样一直错下去。
 *
 * @param int $liveblog_id
 * @return array|null
 */
function mbfun_get_live_feed_local($liveblog_id)
{
	$liveblog_id = absint($liveblog_id);

	if (! $liveblog_id) {
		return null;
	}

	$cache_key = 'mlb_feed_' . $liveblog_id;

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

/**
 * 清除 feed 缓存：条目变更、切换连载状态时触发。
 *
 * @param int $liveblog_id
 * @return void
 */
function mbfun_purge_live_feed_cache($liveblog_id)
{
	delete_transient('mlb_feed_' . absint($liveblog_id));
}
add_action('mlb_purge_feed_cache', 'mbfun_purge_live_feed_cache');

/**
 * Return the site datetime format.
 *
 * @return void
 */
function mbfun_get_live_datetime_format()
{
	return get_option('date_format') . ' ' . get_option('time_format');
}

/**
 * Get entry display date.
 *
 * @return void
 */
function mbfun_get_live_entry_display_date()
{
	global $post;

	setup_postdata($post);

	$display = mbfun_get_live_option('ml_entry_date_format', 'human');

	// 白名单判断，不用「不等于 human 就当日历」那种写法：
	// 旧版本把「相对时间」存成空串，那种写法会让它落到「日期与时间」分支。
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
	// 构建 WP_Query 参数数组
	$args = array(
		'post_type'	=> array('post', 'page', 'micropost'),	// 文章类型
		'posts_per_page' => -1,	// 获取所有符合条件的文章
		'meta_query' => array(	// 元数据查询参数
			array(
				'key' => $meta_key, // 已知的 meta_key
				'value' => strval($post_id) // 已知的 meta_value
			)
		)
	);
	// 实例化 WP_Query 对象
	$query = new WP_Query($args);

	// 获取查询结果中的所有文章
	$posts = $query->posts;

	// 返回文章数组
	return $posts;
}
