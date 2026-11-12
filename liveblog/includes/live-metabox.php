<?php
/* 连载 */

if (! defined('ABSPATH')) {
	exit;
}

function mlb_add_meta_box()
{
	add_meta_box('mlb_entry_meta_box', __('Liveblog Entry - Source', MICROBLOG_DOMAIN), 'mlb_render_entry_meta_box', 'microlive', 'normal', 'high');
}
add_action('add_meta_boxes', 'mlb_add_meta_box');

/* 「后续」标题：仅对已有连载条目的原文显示文章级覆盖 */
function mlb_add_followup_title_meta_box($post_type, $post)
{
	if (! $post instanceof WP_Post) {
		return;
	}

	if (! in_array($post_type, (array) mbfun_get_live_supported_post_types(), true)) {
		return;
	}

	if (! mlb_has_liveblog_entries($post->ID)) {
		return;
	}

	add_meta_box(
		'mlb_followup_title_box',
		__('Follow-up heading', MICROBLOG_DOMAIN),
		'mlb_render_followup_title_meta_box',
		$post_type,
		'side',
		'default'
	);
}
add_action('add_meta_boxes', 'mlb_add_followup_title_meta_box', 10, 2);

function mlb_render_followup_title_meta_box($post)
{
	wp_nonce_field('mlb_followup_title_save_' . $post->ID, 'mlb_followup_title_nonce');

	$value    = get_post_meta($post->ID, '_mlb_followup_title', true);
	$max      = mlb_followup_title_max_length();
	$field_id = '_mlb_followup_title';

	printf(
		'<label class="screen-reader-text" for="%1$s">%2$s</label>',
		esc_attr($field_id),
		esc_html__('Custom heading', MICROBLOG_DOMAIN)
	);

	printf(
		'<input type="text" id="%1$s" name="%1$s" value="%2$s" maxlength="%3$d" class="widefat" placeholder="%4$s" />',
		esc_attr($field_id),
		esc_attr((string) $value),
		(int) $max,
		esc_attr(mlb_followup_title_default())
	);

	echo '<p class="description">'
		. esc_html__('Leave blank to use the heading from the Liveblog settings tab. Up to 45 characters.', MICROBLOG_DOMAIN)
		. '</p>';
}

function mlb_save_followup_title_meta_box($post_id, $post)
{
	if (! isset($_POST['mlb_followup_title_nonce'])
		|| ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mlb_followup_title_nonce'])), 'mlb_followup_title_save_' . $post_id)) {
		return;
	}

	if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || wp_is_post_revision($post_id)) {
		return;
	}

	if (! $post instanceof WP_Post || ! current_user_can('edit_post', $post_id)) {
		return;
	}

	if (! in_array($post->post_type, (array) mbfun_get_live_supported_post_types(), true)) {
		return;
	}

	$raw   = isset($_POST['_mlb_followup_title']) ? wp_unslash($_POST['_mlb_followup_title']) : '';
	$value = mlb_sanitize_followup_title($raw);

	if ('' === $value) {
		delete_post_meta($post_id, '_mlb_followup_title');
	} else {
		update_post_meta($post_id, '_mlb_followup_title', $value);
	}
}
add_action('save_post', 'mlb_save_followup_title_meta_box', 10, 2);

function mlb_entry_meta_box_fields()
{
	return apply_filters('mlb_entry_meta_box_fields_save', array('_micro_live_post_id'));
}

/* 渲染条目 metabox */
function mlb_render_entry_meta_box()
{
	global $post;

	do_action('mlb_entry_meta_box_fields', $post->ID);

	wp_nonce_field(basename(__FILE__), 'mlb_entry_meta_box_nonce');
}

function mlb_render_entry_options($post_id)
{
	$liveblog = get_post_meta($post_id, '_micro_live_post_id', true);

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

/* 保存条目的原文关联 */
function mlb_entry_meta_box_save($post_id, $post)
{
	if (! isset($_POST['mlb_entry_meta_box_nonce']) || ! wp_verify_nonce($_POST['mlb_entry_meta_box_nonce'], basename(__FILE__))) {
		return;
	}

	if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || (defined('DOING_AJAX') && DOING_AJAX) || isset($_REQUEST['bulk_edit'])) {
		return;
	}

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
		mlb_queue_entry_error_notice(
			__('Liveblog entry saved without a parent liveblog: pick one in the "Liveblog Entry - Source" box and update again, or the entry will not be displayed.', MICROBLOG_DOMAIN)
		);
	} elseif (! get_post($liveblog)
		|| ! in_array(get_post_type($liveblog), (array) mbfun_get_live_supported_post_types(), true)) {
		delete_post_meta($post_id, '_micro_live_post_id');

		mlb_queue_entry_error_notice(
			__('The selected liveblog no longer exists. The link has been cleared; please pick a liveblog again.', MICROBLOG_DOMAIN)
		);
	} else {
		delete_post_meta($post_id, '_micro_live_needs_parent');

		do_action('mlb_purge_feed_cache', $liveblog);

		if ($old_liveblog && $old_liveblog !== (int) $liveblog) {
			do_action('mlb_purge_feed_cache', $old_liveblog);
		}
	}
}
add_action('save_post', 'mlb_entry_meta_box_save', 10, 2);

/* 保存后显示一次错误提示 */
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
add_action('save_post_microlive', 'mlb_entry_purge_parent_feed', 20);

/* 连载条目不能脱离原文存在 */
function mlb_enforce_entry_parent($post_id, $post)
{
	static $busy = false;

	if ($busy || wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
		return;
	}

	if ('microlive' !== get_post_type($post_id) || 'publish' !== get_post_status($post_id)) {
		return;
	}

	if (! isset($_POST['mlb_entry_meta_box_nonce'])) {
		return;
	}

	if (! empty(get_post_meta($post_id, '_micro_live_post_id', true))) {
		return;
	}

	$busy = true;

	wp_update_post(array(
		'ID'          => $post_id,
		'post_status' => 'draft',
	));

	update_post_meta($post_id, '_micro_live_needs_parent', '1');

	$busy = false;

	if (is_admin()) {
		mlb_queue_entry_error_notice(
			__('A liveblog entry must be linked to a parent liveblog before publishing. The post was saved as a draft; pick a liveblog in the "Select liveblog" box and publish again.', MICROBLOG_DOMAIN)
		);
	}
}
add_action('save_post', 'mlb_enforce_entry_parent', 20, 2);

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

function mlb_add_liveblog_display_column($columns)
{
	$columns['mlb_liveblog_display'] = __('Liveblog display', MICROBLOG_DOMAIN);

	return $columns;
}

/* 生成某个状态的切换链接 */
function mlb_get_liveblog_state_url($post_id, $state)
{
	return wp_nonce_url(
		admin_url('admin-post.php?action=mlb_toggle_liveblog&post=' . (int) $post_id . '&state=' . rawurlencode($state)),
		'mlb_toggle_liveblog_' . (int) $post_id
	);
}

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

function mlb_register_liveblog_display_column()
{
	foreach ((array) mbfun_get_live_supported_post_types() as $post_type) {
		add_filter('manage_' . $post_type . '_posts_columns', 'mlb_add_liveblog_display_column');
		add_action('manage_' . $post_type . '_posts_custom_column', 'mlb_render_liveblog_display_column', 10, 2);
	}
}
add_action('init', 'mlb_register_liveblog_display_column');

/* 四态列样式 */
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
		mbfun_asset_version('css/liveblog-admin-column.css')
	);
}
add_action('admin_enqueue_scripts', 'mlb_enqueue_display_column_assets');

/* 处理列表页的三态切换 */
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

	if (! $back || false !== strpos($back, 'admin-post.php')) {
		$back = admin_url('edit.php?post_type=' . $post->post_type);
	}

	wp_safe_redirect($back);
	exit;
}
add_action('admin_post_mlb_toggle_liveblog', 'mlb_handle_toggle_liveblog');

/* ------------------------------------------------------------------
 * 区块编辑器文档侧栏：显示本条连载所属的原文
 * 仅渲染，不写入任何数据。新建时读 mlb_parent 查询参数，
 * 编辑既有条目时优先读 _micro_live_post_id。
 * ------------------------------------------------------------------ */

function mlb_get_editor_parent_source($post = null)
{
	$parent_id = 0;

	if ($post instanceof WP_Post) {
		$parent_id = (int) get_post_meta($post->ID, '_micro_live_post_id', true);
	}

	if (! $parent_id && isset($_GET['mlb_parent'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- 仅读取用于显示。
		$parent_id = absint($_GET['mlb_parent']);
	}

	if (! $parent_id || ! function_exists('mlb_is_valid_entry_parent') || ! mlb_is_valid_entry_parent($parent_id)) {
		return null;
	}

	$title = wp_strip_all_tags(html_entity_decode(get_the_title($parent_id), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

	if ('' === $title) {
		return null;
	}

	$url = get_edit_post_link($parent_id, '');

	return array(
		'id'    => $parent_id,
		'title' => $title,
		'url'   => $url ? esc_url_raw($url) : '',
	);
}

function mlb_enqueue_editor_parent_info($hook)
{
	if (! in_array($hook, array('post.php', 'post-new.php'), true)) {
		return;
	}

	$screen = function_exists('get_current_screen') ? get_current_screen() : null;

	if (! $screen || 'microlive' !== $screen->post_type) {
		return;
	}

	/* 经典编辑器没有文档侧栏，SlotFill 也无从挂载 */
	if (! function_exists('use_block_editor_for_post_type') || ! use_block_editor_for_post_type('microlive')) {
		return;
	}

	$source = mlb_get_editor_parent_source(get_post());

	if (! $source) {
		return;
	}

	wp_enqueue_style(
		'mlb-editor-parent',
		mbfun_asset_url('css/liveblog-editor-parent.css'),
		array(),
		mbfun_asset_version('css/liveblog-editor-parent.css')
	);

	wp_enqueue_script(
		'mlb-editor-parent',
		mbfun_asset_url('js/liveblog-editor-parent.js'),
		array('wp-plugins', 'wp-edit-post', 'wp-element', 'wp-i18n'),
		mbfun_asset_version('js/liveblog-editor-parent.js'),
		true
	);

	wp_localize_script('mlb-editor-parent', 'mlbEditorParent', array(
		'label' => __('Liveblog source:', MICROBLOG_DOMAIN),
		'title' => $source['title'],
		'url'   => $source['url'],
		'id'    => (int) $source['id'],
	));
}
add_action('admin_enqueue_scripts', 'mlb_enqueue_editor_parent_info', 20);
