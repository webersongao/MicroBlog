<?php
/**
 * 连载：条目原文关联 + 列表页「连载显示」四态列。
 *
 * 编辑页只保留「Liveblog Entry - Source」metabox（microlive 用）。
 * 文章列表页的「连载显示」列显示四态并提供三态切换，不再有行操作，
 * 也不占用文章编辑器。状态数据层见 live-functions.php，原文资格见
 * live-parent-picker.php。
 *
 * @package MicroBlog
 */

// Exit if accessed directly
if (! defined('ABSPATH')) {
	exit;
}

/**
 * 注册条目编辑页的原文关联 metabox。
 *
 * @return void
 */
function mlb_add_meta_box()
{
	add_meta_box('mlb_entry_meta_box', __('Liveblog Entry - Source', MICROBLOG_DOMAIN), 'mlb_render_entry_meta_box', 'microlive', 'normal', 'high');
}
add_action('add_meta_boxes', 'mlb_add_meta_box');

/**
 * 条目 metabox 需要保存的字段。
 *
 * @return array
 */
function mlb_entry_meta_box_fields()
{
	return apply_filters('mlb_entry_meta_box_fields_save', array('_micro_live_post_id'));
}

/**
 * 渲染条目 metabox。
 *
 * @return void
 */
function mlb_render_entry_meta_box()
{
	global $post;

	do_action('mlb_entry_meta_box_fields', $post->ID);

	wp_nonce_field(basename(__FILE__), 'mlb_entry_meta_box_nonce');
}

/**
 * 渲染「选择原文」下拉框。
 *
 * 候选池 = 支持类型下所有已发布/私密文章，无需逐篇开启。
 *
 * @param int $post_id 条目 ID。
 * @return void
 */
function mlb_render_entry_options($post_id)
{
	$liveblog = get_post_meta($post_id, '_micro_live_post_id', true);

	// 新建条目：从选择页带 ?mlb_parent=ID 进入时预选原文（仅读取，不落库）。
	if (empty($liveblog) && isset($_GET['mlb_parent'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- 仅读取用于预填。
		$prefill = absint($_GET['mlb_parent']);

		if (mlb_is_valid_entry_parent($prefill)) {
			$liveblog = $prefill;
		}
	}

	$liveblogs = mbfun_get_live_liveblog_sources();

	do_action('mlb_before_entry_options', $post_id);
?>
	<?php if (get_post_meta($post_id, '_micro_live_needs_parent', true)) { ?>
		<div class="notice notice-error inline">
			<p><strong><?php esc_html_e('The last publish attempt was blocked.', MICROBLOG_DOMAIN); ?></strong>
				<?php esc_html_e('A liveblog entry must be linked to a parent liveblog before it can be published. Pick a liveblog below, then publish again.', MICROBLOG_DOMAIN); ?></p>
		</div>
	<?php } ?>

	<?php if ($liveblogs) { ?>
		<div class="mlb-fields">
			<p class="mlb-field">
				<label for="mlb-liveblog"><?php esc_html_e('Select liveblog', MICROBLOG_DOMAIN); ?></label>
				<select name="_micro_live_post_id" id="mlb-liveblog" class="mlb-selectize" data-required="1">
					<option value=""><?php esc_html_e('— Select an entry liveblog —', MICROBLOG_DOMAIN); ?></option>
					<?php foreach ($liveblogs as $liveblog_id => $liveblog_title) { ?>
						<option value="<?php echo esc_attr($liveblog_id); ?>" <?php selected($liveblog, $liveblog_id, true); ?>><?php echo esc_html($liveblog_title); ?></option>
					<?php } ?>
				</select>
			</p>

			<?php
			// 直销链接只在条目已落库后显示：新建（含从选择页预填进来的 auto-draft）还没有固定 URL，
			// 早显示出来的是一个会变的临时 ID，等于死链。
			$entry_link_ready = ! empty($liveblog) && ! in_array(get_post_status($post_id), array('auto-draft', 'new'), true);
			?>
			<?php if ($entry_link_ready) { ?>
				<p class="mlb-field">
					<label for="mlb-liveblog-entry-link"><?php esc_html_e('Direct link to entry', MICROBLOG_DOMAIN); ?></label>
					<input type="text" id="mlb-liveblog-entry-link" onclick="this.focus(); this.select()" value="<?php echo esc_url(mbfun_get_live_entry_url($post_id)); ?>" readonly="readonly" class="widefat">
				</p>
			<?php } ?>
		</div>
	<?php } else { ?>
		<p><?php esc_html_e('No liveblog sources found.', MICROBLOG_DOMAIN); ?></p>
	<?php } ?>
<?php
	do_action('mlb_after_entry_options', $post_id);
}
add_action('mlb_entry_meta_box_fields', 'mlb_render_entry_options', 1);

/**
 * 保存条目的原文关联。
 *
 * @param int     $post_id 文章 ID。
 * @param WP_Post $post    文章对象。
 * @return void
 */
function mlb_entry_meta_box_save($post_id, $post)
{
	if (! isset($_POST['mlb_entry_meta_box_nonce']) || ! wp_verify_nonce($_POST['mlb_entry_meta_box_nonce'], basename(__FILE__))) {
		return;
	}

	if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || (defined('DOING_AJAX') && DOING_AJAX) || isset($_REQUEST['bulk_edit'])) {
		return;
	}

	// 必须按具体文章判权：edit_posts 只保证"能编辑某些文章"，
	// 拿着别人条目的 post_id 提交也能通过；edit_post 才校验这一篇。
	if ('revision' === $post->post_type || ! current_user_can('edit_post', $post_id) || 'microlive' !== $post->post_type) {
		return;
	}

	$old_liveblog = (int) get_post_meta($post_id, '_micro_live_post_id', true);

	foreach (mlb_entry_meta_box_fields() as $field) {
		if (! empty($_POST[$field])) {
			$new = apply_filters('mlb_entry_meta_box_save_' . $field, $_POST[$field]);
			update_post_meta($post_id, $field, absint($new));
		} else {
			delete_post_meta($post_id, $field);
		}
	}

	do_action('mlb_entry_save', $post_id, $post);

	$liveblog = get_post_meta($post_id, '_micro_live_post_id', true);

	if (empty($liveblog)) {
		// 未选择原始连载文章：给出后台提示（不做 alert/跳转）。
		mlb_queue_entry_error_notice(
			__('Liveblog entry saved without a parent liveblog: pick one in the "Liveblog Entry - Source" box and update again, or the entry will not be displayed.', MICROBLOG_DOMAIN)
		);
	} elseif (! get_post($liveblog)
		|| ! in_array(get_post_type($liveblog), (array) mbfun_get_live_supported_post_types(), true)) {
		// 指向不存在或类型不受支持的文章（可能已被删除）。
		// 父级可以是支持类型下的任意文章，不能限定 microlive 类型，
		// 否则下拉框给出的合法选项会被这里清除。
		delete_post_meta($post_id, '_micro_live_post_id');

		mlb_queue_entry_error_notice(
			__('The selected liveblog no longer exists. The link has been cleared; please pick a liveblog again.', MICROBLOG_DOMAIN)
		);
	} else {
		// 已关联原文：清掉历史拦截标记。
		delete_post_meta($post_id, '_micro_live_needs_parent');

		do_action('mlb_purge_feed_cache', $liveblog);

		// 改绑到另一篇原文时，旧原文的缓存同样要清，否则条目会一直留在旧页面。
		if ($old_liveblog && $old_liveblog !== (int) $liveblog) {
			do_action('mlb_purge_feed_cache', $old_liveblog);
		}
	}
}
add_action('save_post', 'mlb_entry_meta_box_save', 10, 2);

/**
 * 保存后显示一次错误提示（同一次请求内只登记一条）。
 *
 * @param string $message 提示文本。
 * @return void
 */
function mlb_queue_entry_error_notice($message)
{
	static $registered = false;

	if ($registered) {
		return;
	}

	$registered = true;

	add_action('admin_notices', function () use ($message) {
		printf(
			'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
			esc_html($message)
		);
	});
}

/**
 * 条目进出回收站、改绑原文、被彻底删除时清掉所属原文的 feed 缓存。
 *
 * 还原的条目要立刻可见，不能等 5 分钟兜底过期；改绑时旧原文也要清，
 * 否则条目会一直留在旧的连载页上。
 *
 * @param int $post_id 条目 ID。
 * @return void
 */
function mlb_entry_purge_parent_feed($post_id)
{
	if ('microlive' !== get_post_type($post_id)) {
		return;
	}

	$liveblog = get_post_meta($post_id, '_micro_live_post_id', true);

	if (empty($liveblog)) {
		return;
	}

	do_action('mlb_purge_feed_cache', $liveblog);
}
add_action('trashed_post', 'mlb_entry_purge_parent_feed', 10);
add_action('untrashed_post', 'mlb_entry_purge_parent_feed', 10);
add_action('before_delete_post', 'mlb_entry_purge_parent_feed', 10);
// 内容/状态改动：后台表单由 mlb_entry_meta_box_save() 处理，
// 这里兜住 REST、WP-CLI、导入、批量编辑等不经过 metabox 的写入。
add_action('save_post_microlive', 'mlb_entry_purge_parent_feed', 20);

/**
 * 连载条目不能脱离原文存在：发布（含绕过前端的请求）时若未关联原文，回退为草稿。
 *
 * @param int     $post_id 文章 ID。
 * @param WP_Post $post    文章对象。
 * @return void
 */
function mlb_enforce_entry_parent($post_id, $post)
{
	static $busy = false;

	if ($busy || wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
		return;
	}

	if ('microlive' !== get_post_type($post_id) || 'publish' !== get_post_status($post_id)) {
		return;
	}

	// 仅拦截编辑器表单提交：REST / 程序化创建的条目允许先建后关联。
	if (! isset($_POST['mlb_entry_meta_box_nonce'])) {
		return;
	}

	if (! empty(get_post_meta($post_id, '_micro_live_post_id', true))) {
		return;
	}

	// 正在发布但未选原文：拦回草稿并提示（metabox 的常规保存已在保存时给出提示）。
	$busy = true;

	wp_update_post(array(
		'ID'          => $post_id,
		'post_status' => 'draft',
	));

	// 古腾堡下本次拦截发生在 metabox 同步请求里（响应被编辑器丢弃），
	// 当次提示用户看不到：把原因写进 post meta，下次打开编辑页时醒目展示。
	update_post_meta($post_id, '_micro_live_needs_parent', '1');

	$busy = false;

	if (is_admin()) {
		mlb_queue_entry_error_notice(
			__('A liveblog entry must be linked to a parent liveblog before publishing. The post was saved as a draft; pick a liveblog in the "Select liveblog" box and publish again.', MICROBLOG_DOMAIN)
		);
	}
}
// 注意：save_post_microlive 早于 save_post 触发，必须挂在 save_post 上、
// 且优先级高于 metabox 保存（10），否则 meta 尚未写入就会被误判为无原文。
add_action('save_post', 'mlb_enforce_entry_parent', 20, 2);

/**
 * 编辑页展示「发布被拦截」的持久提示：古腾堡下拦截请求的响应会被丢弃，
 * 当次提示不可见，故通过 post meta 在下次打开编辑页时补告。
 *
 * @return void
 */
function mlb_render_needs_parent_notice()
{
	if (! function_exists('get_current_screen')) {
		return;
	}

	$screen = get_current_screen();

	if (! $screen || 'post' !== $screen->base || 'microlive' !== $screen->post_type) {
		return;
	}

	$post_id = isset($_GET['post']) ? absint($_GET['post']) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- 仅读取用于展示。

	if (! $post_id || ! get_post_meta($post_id, '_micro_live_needs_parent', true)) {
		return;
	}

	printf(
		'<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
		esc_html(__('The last publish attempt was blocked.', MICROBLOG_DOMAIN)),
		esc_html(__('A liveblog entry must be linked to a parent liveblog before it can be published. Pick a liveblog in the "Liveblog Entry - Source" box below, then publish again.', MICROBLOG_DOMAIN))
	);
}
add_action('admin_notices', 'mlb_render_needs_parent_notice');

/**
 * 列表页新增「连载显示」列。
 *
 * @param array $columns 现有列。
 * @return array
 */
function mlb_add_liveblog_display_column($columns)
{
	$columns['mlb_liveblog_display'] = __('Liveblog display', MICROBLOG_DOMAIN);

	return $columns;
}

/**
 * 生成某个状态的切换链接。
 *
 * @param int    $post_id 文章 ID。
 * @param string $state   off|default|auto。
 * @return string
 */
function mlb_get_liveblog_state_url($post_id, $state)
{
	return wp_nonce_url(
		admin_url('admin-post.php?action=mlb_toggle_liveblog&post=' . (int) $post_id . '&state=' . rawurlencode($state)),
		'mlb_toggle_liveblog_' . (int) $post_id
	);
}

/**
 * 渲染「连载显示」列：当前状态 + 三态切换链接。
 *
 * 「未开启」由条目引用关系派生，只显示文字，不提供切换。
 *
 * @param string $column  列名。
 * @param int    $post_id 文章 ID。
 * @return void
 */
function mlb_render_liveblog_display_column($column, $post_id)
{
	if ('mlb_liveblog_display' !== $column) {
		return;
	}

	$state  = mlb_get_liveblog_state($post_id);
	$labels = mlb_get_liveblog_state_labels();

	$icons = array(
		'none'    => 'dashicons-minus',
		'off'     => 'dashicons-hidden',
		'default' => 'dashicons-visibility',
		'auto'    => 'dashicons-update',
	);

	printf(
		'<div class="mlb-state-current mlb-state-%1$s"><span class="dashicons %2$s" aria-hidden="true"></span><span>%3$s</span></div>',
		esc_attr($state),
		esc_attr($icons[$state]),
		esc_html($labels[$state])
	);

	if ('none' === $state || ! current_user_can('edit_post', $post_id)) {
		return;
	}

	$links = array();

	foreach (mlb_get_display_mode_labels() as $mode => $label) {
		if ($mode === $state) {
			$links[] = '<span class="mlb-state-current-mode" aria-current="true">' . esc_html($label) . '</span>';
		} else {
			$links[] = sprintf(
				'<a href="%1$s" title="%2$s">%3$s</a>',
				esc_url(mlb_get_liveblog_state_url($post_id, $mode)),
				esc_attr__('Set liveblog display state', MICROBLOG_DOMAIN),
				esc_html($label)
			);
		}
	}

	echo '<div class="mlb-state-switch">' . implode(' <span class="mlb-state-sep">·</span> ', $links) . '</div>';
}

/**
 * 只在支持类型的列表页挂列，避免污染其它文章类型。
 *
 * @return void
 */
function mlb_register_liveblog_display_column()
{
	foreach ((array) mbfun_get_live_supported_post_types() as $post_type) {
		add_filter('manage_' . $post_type . '_posts_columns', 'mlb_add_liveblog_display_column');
		add_action('manage_' . $post_type . '_posts_custom_column', 'mlb_render_liveblog_display_column', 10, 2);
	}
}
add_action('init', 'mlb_register_liveblog_display_column');

/**
 * 四态列样式：只在该列真正出现的文章列表页加载。
 *
 * @param string $hook 当前页面 hook。
 * @return void
 */
function mlb_enqueue_display_column_assets($hook)
{
	if ('edit.php' !== $hook || ! function_exists('get_current_screen')) {
		return;
	}

	$screen = get_current_screen();

	if (! $screen || ! in_array($screen->post_type, (array) mbfun_get_live_supported_post_types(), true)) {
		return;
	}

	wp_enqueue_style(
		'mlb-display-column',
		mbfun_asset_url('css/liveblog-admin-column.css'),
		array(),
		mbfun_get_plugin_version()
	);
}
add_action('admin_enqueue_scripts', 'mlb_enqueue_display_column_assets');

/**
 * 处理列表页的三态切换。
 *
 * 未开启（无条目引用）的文章不允许切换：该状态由引用关系派生。
 *
 * @return void
 */
function mlb_handle_toggle_liveblog()
{
	$post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
	$state   = isset($_GET['state']) ? sanitize_key(wp_unslash($_GET['state'])) : '';

	if (! $post_id || ! current_user_can('edit_post', $post_id)) {
		wp_die(esc_html__('You are not allowed to do that.', MICROBLOG_DOMAIN), 403);
	}

	check_admin_referer('mlb_toggle_liveblog_' . $post_id);

	$post = get_post($post_id);

	if (! $post || ! in_array($post->post_type, (array) mbfun_get_live_supported_post_types(), true)) {
		wp_die(esc_html__('Invalid post.', MICROBLOG_DOMAIN), 400);
	}

	if (! in_array($state, array('off', 'default', 'auto'), true)) {
		wp_die(esc_html__('Invalid liveblog state.', MICROBLOG_DOMAIN), 400);
	}

	if (! mlb_has_liveblog_entries($post_id)) {
		wp_die(esc_html__('This post has no liveblog entries.', MICROBLOG_DOMAIN), 400);
	}

	if ('off' === $state) {
		delete_post_meta($post_id, '_micro_post_live_enable');
	} else {
		update_post_meta($post_id, '_micro_post_live_enable', $state);
	}

	do_action('mlb_purge_feed_cache', $post_id);

	$back = wp_get_referer();

	// 连续切换时 Referer 可能指向上一次的 admin-post 链接，跳回去会重复执行动作。
	if (! $back || false !== strpos($back, 'admin-post.php')) {
		$back = admin_url('edit.php?post_type=' . $post->post_type);
	}

	wp_safe_redirect($back);
	exit;
}
add_action('admin_post_mlb_toggle_liveblog', 'mlb_handle_toggle_liveblog');
