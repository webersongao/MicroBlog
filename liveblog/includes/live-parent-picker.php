<?php
/**
 * 连载条目「先选原文再进编辑器」：选择页 + 服务端硬门。
 *
 * 流程：连载 → 新建连载（及任何无参进入 post-new.php?post_type=microlive 的路径）
 * 一律先落到选择页；选中原文后才带 mlb_parent=ID 进编辑器，metabox 据此预填。
 * 兜底不变式由发布锁保证：未关联原文的条目发不出去。
 *
 * @package MicroBlog
 */

// Exit if accessed directly
if (! defined('ABSPATH')) {
	exit;
}

/**
 * 判断一篇文章是否可作为连载条目的原文（类型受支持且已开启连载）。
 *
 * @param int $post_id 文章 ID。
 * @return bool
 */
function mlb_is_valid_entry_parent($post_id)
{
	$post_id = absint($post_id);

	if (! $post_id || ! get_post($post_id)) {
		return false;
	}

	if (! in_array(get_post_type($post_id), (array) mbfun_get_live_supported_post_types(), true)) {
		return false;
	}

	return (bool) get_post_meta($post_id, '_micro_post_live_enable', true);
}

/**
 * 服务端硬门：新建连载条目必须携带合法的 mlb_parent，否则一律送回选择页。
 *
 * 直接敲 post-new.php?post_type=microlive 也会被送回去；唯一例外是
 * 选择页里带 nonce 的「暂不选择，先存草稿」链接。
 *
 * @return void
 */
function mlb_gate_new_entry_editor()
{
	if (! isset($_GET['post_type']) || 'microlive' !== $_GET['post_type']) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- 仅读取用于路由。
		return;
	}

	if (! current_user_can('edit_posts')) {
		return; // 无权限者由编辑器自行拒绝，这里不代劳。
	}

	$parent = isset($_GET['mlb_parent']) ? absint($_GET['mlb_parent']) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- 仅读取用于路由。

	// 与选择页/下拉框同标准：已开启连载且状态未关闭的原文才放行，
	// 否则预选会落到下拉框里不存在的选项上。
	if ($parent && mlb_is_valid_entry_parent($parent) && 'closed' !== mbfun_get_live_liveblog_status($parent)) {
		return; // 带合法原文，放行。
	}

	// 逃生门：仅接受来自选择页按钮的带 nonce 请求。
	if (isset($_GET['mlb_draft']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['mlb_draft'])), 'mlb_entry_without_parent')) {
		return;
	}

	wp_safe_redirect(admin_url('edit.php?post_type=microlive&page=mlb-pick-liveblog'));
	exit;
}
add_action('load-post-new.php', 'mlb_gate_new_entry_editor');

/**
 * 注册选择页，并把「新建连载」子菜单指向它。
 *
 * @return void
 */
function mlb_register_parent_picker()
{
	$parent_slug = 'edit.php?post_type=microlive';

	// 摘掉自动生成的「新建连载」（直连编辑器），换成同名的选择页。
	remove_submenu_page($parent_slug, 'post-new.php?post_type=microlive');

	add_submenu_page(
		$parent_slug,
		__('Select a liveblog source', MICROBLOG_DOMAIN),
		__('New Liveblog', MICROBLOG_DOMAIN), // 菜单文案不变，入口先选原文。
		'edit_posts',
		'mlb-pick-liveblog',
		'mlb_render_parent_picker'
	);
}
add_action('admin_menu', 'mlb_register_parent_picker', 20);

/**
 * 渲染选择页。
 *
 * @return void
 */
function mlb_render_parent_picker()
{
	if (! current_user_can('edit_posts')) {
		wp_die(esc_html__('You are not allowed to do that.', MICROBLOG_DOMAIN), 403);
	}

	$liveblogs = mbfun_get_live_liveblogs_by_status('open');
	$search    = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- 仅读取用于列表过滤。

	// 与下拉框同源（mbfun_get_live_liveblogs_by_status('open')），搜索只是其上的标题/类型过滤。
	if ('' !== $search && $liveblogs) {
		$filtered = array();

		foreach ($liveblogs as $liveblog_id => $liveblog_title) {
			$type_obj = get_post_type_object(get_post_type($liveblog_id));
			$type_key = $type_obj ? $type_obj->name : '';

			$haystack = $liveblog_title . ' ' . ($type_obj ? $type_obj->labels->singular_name : '') . ' ' . $type_key;

			if (false !== stripos($haystack, $search) || (string) $liveblog_id === $search) {
				$filtered[$liveblog_id] = $liveblog_title;
			}
		}

		$liveblogs = $filtered;
	}

	$draft_url = wp_nonce_url(
		admin_url('post-new.php?post_type=microlive&mlb_draft=1'),
		'mlb_entry_without_parent',
		'mlb_draft'
	);

	// 直达「连载」标签页：Supported（支持类型）就在那一屏，避免用户还要自己找。
	$settings_url = class_exists('MB_Admin_Page')
		? MB_Admin_Page::url('liveblog')
		: admin_url('tools.php?page=' . MICROBLOG_PLUGIN_BASENAME);
?>
	<div class="wrap">
		<h1><?php esc_html_e('Write Liveblog', MICROBLOG_DOMAIN); ?></h1>

		<p class="description">
			<?php esc_html_e('A liveblog entry must be attached to a liveblog source (publish or private). Pick one below to start writing.', MICROBLOG_DOMAIN); ?>
		</p>

		<form method="get" class="mlb-picker-search" style="margin:12px 0;">
			<input type="hidden" name="post_type" value="microlive" />
			<input type="hidden" name="page" value="mlb-pick-liveblog" />
			<p class="search-box">
				<label class="screen-reader-text" for="mlb-picker-search-input"><?php esc_html_e('Search Liveblogs', MICROBLOG_DOMAIN); ?></label>
				<input type="search" id="mlb-picker-search-input" name="s" value="<?php echo esc_attr($search); ?>" />
				<?php submit_button(__('Search Liveblogs', MICROBLOG_DOMAIN), '', '', false, array('id' => 'mlb-picker-search-submit')); ?>
			</p>
		</form>

		<?php if ($liveblogs) { ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th class="column-primary"><?php esc_html_e('Liveblog source', MICROBLOG_DOMAIN); ?></th>
						<th><?php esc_html_e('Type', MICROBLOG_DOMAIN); ?></th>
						<th><?php esc_html_e('Status', MICROBLOG_DOMAIN); ?></th>
						<th><?php esc_html_e('Date', MICROBLOG_DOMAIN); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($liveblogs as $liveblog_id => $liveblog_title) { ?>
					<?php
					$liveblog_post = get_post($liveblog_id);
					$type_obj      = $liveblog_post ? get_post_type_object($liveblog_post->post_type) : null;
					$write_url     = admin_url('post-new.php?post_type=microlive&mlb_parent=' . (int) $liveblog_id);
					?>
					<tr>
						<td class="column-primary">
							<a href="<?php echo esc_url($write_url); ?>"><strong><?php echo esc_html($liveblog_title); ?></strong></a>
						</td>
						<td><?php echo esc_html($type_obj ? $type_obj->labels->singular_name : ($liveblog_post ? $liveblog_post->post_type : '')); ?></td>
						<td><?php esc_html_e('Open', MICROBLOG_DOMAIN); ?></td>
						<td><?php echo esc_html($liveblog_post ? get_the_date('', $liveblog_post) : ''); ?></td>
						<td>
							<a class="button button-primary" href="<?php echo esc_url($write_url); ?>">
								<?php esc_html_e('Write entry', MICROBLOG_DOMAIN); ?>
							</a>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		<?php } else { ?>
			<div class="notice notice-info inline" style="margin-top:12px;">
				<p>
					<strong><?php esc_html_e('No liveblog sources found.', MICROBLOG_DOMAIN); ?></strong>
					<?php esc_html_e('Open the article you want to follow and check "Enable liveblog" in its Liveblog box.', MICROBLOG_DOMAIN); ?>
				</p>
				<p style="color:#646970;">
					<?php
					printf(
						wp_kses_post(__('The source pool is limited to the post types chosen under "Supported" in the %s.', MICROBLOG_DOMAIN)),
						'<a href="' . esc_url($settings_url) . '">' . esc_html__('MicroBlog settings', MICROBLOG_DOMAIN) . '</a>'
					);
					?>
				</p>
			</div>
		<?php } ?>

		<p style="margin-top:16px;">
			<a class="button" href="<?php echo esc_url($draft_url); ?>">
				<?php esc_html_e('Pick none for now — save as draft', MICROBLOG_DOMAIN); ?>
			</a>
			<span class="description" style="margin-left:8px;">
				<?php esc_html_e('The entry stays a draft and cannot be published until a liveblog source is linked.', MICROBLOG_DOMAIN); ?>
			</span>
		</p>
	</div>
<?php
}
