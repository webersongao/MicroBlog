<?php

/**
 * Metabox Functions
 */

// Exit if accessed directly
if (! defined('ABSPATH')) {
	exit;
}

/**
 * Register all the meta boxes for the Download custom post type
 */
function mlb_add_meta_box()
{
	$live_regist = mbfun_get_general_option('msk_liveblog_regist', false);
	if ($live_regist) {
		$post_types = mbfun_get_live_supported_post_types();
		foreach ($post_types as $post_type) {
			add_meta_box('mlb_liveblog_meta_box', __('Liveblog', MICROBLOG_DOMAIN), 'mlb_render_liveblog_meta_box', $post_type, 'normal', 'high');
		}
	}
	add_meta_box('mlb_entry_meta_box', __('Liveblog Entry - Source', MICROBLOG_DOMAIN), 'mlb_render_entry_meta_box', 'microlive', 'normal', 'high');
}
add_action('add_meta_boxes', 'mlb_add_meta_box');

/**
 * Liveblog meta box fields
 *
 * @return array
 */
function mlb_liveblog_meta_box_fields()
{
	$fields = array(
		'_micro_post_live_enable',
		'_micro_post_live_autoPolling',
		'_micro_post_live_status',
	);

	return apply_filters('mlb_liveblog_meta_box_fields_save', $fields);
}

/**
 * Entry meta box fields
 *
 * @return array
 */
function mlb_entry_meta_box_fields()
{
	$fields = array(
		'_micro_live_post_id',
	);

	return apply_filters('mlb_entry_meta_box_fields_save', $fields);
}

/**
 * Render liveblog meta box
 */
function mlb_render_liveblog_meta_box($post)
{
	do_action('mlb_liveblog_meta_box_fields', $post->ID);

	wp_nonce_field(basename(__FILE__), 'mlb_liveblog_meta_box_nonce');
}

/**
 * Render entry meta box
 */
function mlb_render_entry_meta_box()
{
	global $post;

	do_action('mlb_entry_meta_box_fields', $post->ID);

	wp_nonce_field(basename(__FILE__), 'mlb_entry_meta_box_nonce');
}

/**
 * Render Liveblog Options
 *
 * @param int $post_id (Post) ID
 */
function mlb_render_liveblog_options($post_id)
{
	$is_liveblog = get_post_meta($post_id, '_micro_post_live_enable', true);
	$status      = get_post_meta($post_id, '_micro_post_live_status', true);
	$refresh     = get_post_meta($post_id, '_micro_post_live_autoPolling', true);

	do_action('mlb_render_before_liveblog_options', $post_id);
?>
	<div class="mlb-input-group">
		<label for="mlb-show-liveblogs">
			<?php esc_html_e('Enable liveblog', MICROBLOG_DOMAIN); ?>
			<input type="checkbox" name="_micro_post_live_enable" value="1" <?php checked($is_liveblog, '1', true); ?> id="mlb-show-liveblogs">
		</label>
		<span class="mlb-liveblog-extra" id="mlb-liveblog-extra"<?php echo empty($is_liveblog) ? ' hidden' : ''; ?>>
			<label for="mlb-refresh">
				<?php esc_html_e('Auto Refresh', MICROBLOG_DOMAIN); ?>
				<input type="checkbox" name="_micro_post_live_autoPolling" value="1" <?php checked($refresh, '1', true); ?> id="mlb-refresh">
			</label>
			<label for="mlb_status"><?php esc_html_e('Live Status', MICROBLOG_DOMAIN); ?>
				<select name="_micro_post_live_status" id="mlb_status">
					<?php foreach (mbfun_get_live_liveblog_status_options() as $option_value => $option_name) { ?>
						<option value="<?php echo esc_attr($option_value); ?>" <?php selected($option_value, $status, true); ?>><?php echo esc_html($option_name); ?></option>
					<?php } ?>
				</select>
			</label>
		</span>
	</div>
	<?php
	do_action('mlb_render_after_liveblog_options', $post_id);
}
add_action('mlb_liveblog_meta_box_fields', 'mlb_render_liveblog_options', 1);

		/**
		 * Render Entry Options
		 *
		 * @param int $post_id (Post) ID
		 */
		function mlb_render_entry_options($post_id)
		{
			$liveblog = get_post_meta($post_id, '_micro_live_post_id', true);
			$status   = false;

			if (! empty($liveblog)) {
				$status = mbfun_get_live_liveblog_status($liveblog);
			}

			$liveblogs = mbfun_get_live_liveblogs_by_status('open');

			do_action('mlb_before_entry_options', $post_id);

	?>

	<?php if ($status === 'closed') { ?>
		<p><?php printf(wp_kses_post(__('This item is attached to a <a href="%s">closed</a> liveblog, Display this when it is open. ', MICROBLOG_DOMAIN)), esc_url(get_edit_post_link($liveblog))); ?></p>
	<?php } elseif ($liveblogs) { ?>
		<div class="mlb-input-group">
			<label for="mlb-liveblog"><?php esc_html_e('Select liveblog', MICROBLOG_DOMAIN); ?></label>
			<select name="_micro_live_post_id" id="mlb-liveblog" class="mlb-selectize" data-required="1">
				<option value=""><?php esc_html_e('— Select an entry liveblog —', MICROBLOG_DOMAIN); ?></option>
				<?php foreach ($liveblogs as $liveblog_id => $liveblog_title) { ?>
					<option value="<?php echo esc_attr($liveblog_id); ?>" <?php selected($liveblog, $liveblog_id, true); ?>><?php echo esc_html($liveblog_title); ?></option>
				<?php } ?>
			</select>
		</div>

		<?php if (! empty($liveblog)) { ?>
			<div class="mlb-input-group">
				<label for="mlb-liveblog-entry-link"><?php esc_html_e('Direct link to entry', MICROBLOG_DOMAIN); ?></label>
				<input type="text" id="mlb-liveblog-entry-link" onclick="this.focus(); this.select()" value="<?php echo esc_url(mbfun_get_live_entry_url($post_id)); ?>" readonly="readonly" class="widefat">
			</div>
		<?php } ?>
	<?php } else { ?>
		<p><?php esc_html_e('There is no liveblog created yet.', MICROBLOG_DOMAIN); ?></p>
		<?php if (! empty($liveblog)) { ?>
			<p><?php printf(wp_kses_post(__('Alternatively, you can enable the <a href="%s">disabled</a> liveblog status ', MICROBLOG_DOMAIN)), esc_url(get_edit_post_link($liveblog))); ?></p>
		<?php } ?>
	<?php } ?>

<?php
			do_action('mlb_after_entry_options', $post_id);
		}
		add_action('mlb_entry_meta_box_fields', 'mlb_render_entry_options', 1);

		/**
		 * 所有类型文章 liveblog meta box save
		 *
		 * @param  int    $post_id
		 * @param  object $post
		 */
		function mlb_liveblog_meta_box_save($post_id, $post)
		{

			if (! isset($_POST['mlb_liveblog_meta_box_nonce']) || ! wp_verify_nonce($_POST['mlb_liveblog_meta_box_nonce'], basename(__FILE__))) {
				return;
			}

			if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || (defined('DOING_AJAX') && DOING_AJAX) || isset($_REQUEST['bulk_edit'])) {
				return;
			}

			if (isset($post->post_type) && 'revision' == $post->post_type) {
				return;
			}

			if (! current_user_can('edit_posts')) {
				return;
			}
		$fields = mlb_liveblog_meta_box_fields();

		do_action('mlb_liveblog_before_save', $post_id, $post, $fields);
		foreach ($fields as $field) {
			if (! empty($_POST[$field])) {
				$new = apply_filters('mlb_liveblog_meta_box_save_' . $field, $_POST[$field]);
				update_post_meta($post_id, $field, sanitize_text_field($new));
			} else {
				delete_post_meta($post_id, $field);
			}
		}
			// If no status is set, we default to 'open'.
			if (empty(get_post_meta($post_id, '_micro_post_live_status', true))) {
				update_post_meta($post_id, '_micro_post_live_status', 'open');
			}

			do_action('mlb_liveblog_after_save', $post_id, $post, $fields);

			do_action('mlb_purge_feed_cache', $post_id);
		}
		add_action('save_post', 'mlb_liveblog_meta_box_save', 10, 2);

		/**
		 * Hook in on liveblog deletion.
		 *
		 * @param int $post_id
		 * @return void
		 */
		function mlb_liveblog_delete($post_id)
		{
			if (! get_post_meta($post_id, '_micro_post_live_enable', true)) {
				return;
			}

			do_action('mlb_delete_cache', $post_id);
		}

		add_action('before_delete_post', 'mlb_liveblog_delete', 10);

		/**
		 * Liveblog 类型的文章 meta box save
		 *
		 * @param  int    $post_id
		 * @param  object $post
		 */
		function mlb_entry_meta_box_save($post_id, $post)
		{

			if (! isset($_POST['mlb_entry_meta_box_nonce']) || ! wp_verify_nonce($_POST['mlb_entry_meta_box_nonce'], basename(__FILE__))) {
				return;
			}

			if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || (defined('DOING_AJAX') && DOING_AJAX) || isset($_REQUEST['bulk_edit'])) {
				return;
			}

			if (isset($post->post_type) && 'revision' == $post->post_type) {
				return;
			}

			if (! current_user_can('edit_posts')) {
				return;
			}

			if ($post->post_type != 'microlive') {
				return;
			}

		$fields = mlb_entry_meta_box_fields();

		foreach ($fields as $field) {			if (! empty($_POST[$field])) {
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
		} elseif (! get_post($liveblog) || 'microlive' !== get_post_type($liveblog)) {
			// 指向不存在的连载（可能已被删除）。
			delete_post_meta($post_id, '_micro_live_post_id');

			mlb_queue_entry_error_notice(
				__('The selected liveblog no longer exists. The link has been cleared; please pick a liveblog again.', MICROBLOG_DOMAIN)
			);
		} else {
			do_action('mlb_purge_feed_cache', $liveblog);
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
		 * Flush cache when entry is put in trash.
		 *
		 * @param int $post_id
		 * @return void
		 */
		function mlb_entry_trash($post_id)
		{
			if (get_post_type($post_id) != 'microlive') {
				return;
			}

			$liveblog = get_post_meta($post_id, '_micro_live_post_id', true);

			if (empty($liveblog)) {
				return;
			}

			do_action('mlb_purge_feed_cache', $liveblog);
		}

		add_action('trashed_post', 'mlb_entry_trash', 10);

	/**
	 * 列表页行操作：一键开启/关闭连载（免进编辑页）。
	 *
	 * @param array   $actions 行操作。
	 * @param WP_Post $post    文章。
	 * @return array
	 */
	function mlb_add_liveblog_row_toggle($actions, $post)
	{
		if (! $post instanceof WP_Post
			|| ! in_array($post->post_type, (array) mbfun_get_live_supported_post_types(), true)
			|| ! current_user_can('edit_post', $post->ID)) {
			return $actions;
		}

		$enabled = (bool) get_post_meta($post->ID, '_micro_post_live_enable', true);
		$label   = $enabled
			? __('Disable liveblog', MICROBLOG_DOMAIN)
			: __('Enable liveblog', MICROBLOG_DOMAIN);

		$url = wp_nonce_url(
			admin_url('admin-post.php?action=mlb_toggle_liveblog&post=' . (int) $post->ID),
			'mlb_toggle_liveblog_' . $post->ID
		);

		// 插在「编辑」之后。
		$result = array();

		foreach ($actions as $key => $value) {
			$result[$key] = $value;

			if ('edit' === $key) {
				$result['mlb_toggle'] = '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
			}
		}

		if (! isset($result['mlb_toggle'])) {
			$result['mlb_toggle'] = '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
		}

		return $result;
	}
	add_filter('post_row_actions', 'mlb_add_liveblog_row_toggle', 20, 2);

	/**
	 * 处理列表页的连载开关。
	 *
	 * @return void
	 */
	function mlb_handle_toggle_liveblog()
	{
		$post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;

		if (! $post_id || ! current_user_can('edit_post', $post_id)) {
			wp_die(esc_html__('You are not allowed to do that.', MICROBLOG_DOMAIN), 403);
		}

		check_admin_referer('mlb_toggle_liveblog_' . $post_id);

		$post = get_post($post_id);

		if (! $post || ! in_array($post->post_type, (array) mbfun_get_live_supported_post_types(), true)) {
			wp_die(esc_html__('Invalid post.', MICROBLOG_DOMAIN), 400);
		}

		if (get_post_meta($post_id, '_micro_post_live_enable', true)) {
			delete_post_meta($post_id, '_micro_post_live_enable');
		} else {
			update_post_meta($post_id, '_micro_post_live_enable', '1');

			if (! get_post_meta($post_id, '_micro_post_live_status', true)) {
				update_post_meta($post_id, '_micro_post_live_status', 'open');
			}
		}

		do_action('mlb_purge_feed_cache', $post_id);

		$back = wp_get_referer();

		if (! $back) {
			$back = admin_url('edit.php?post_type=' . $post->post_type);
		}

		wp_safe_redirect($back);
		exit;
	}
	add_action('admin_post_mlb_toggle_liveblog', 'mlb_handle_toggle_liveblog');
