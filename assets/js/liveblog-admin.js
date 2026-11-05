/**
 * Liveblog admin: metabox interactions.
 *
 * 1. 「Enable liveblog」勾选后立即显示 Auto Refresh / Live Status，无需保存刷新。
 * 2. 保存连载条目前校验是否已选择原始连载文章。
 */
jQuery(function ($) {
	// 勾选「Enable liveblog」即时切换附加选项的显示。
	$(document).on('change', '#mlb-show-liveblogs', function () {
		$('#mlb-liveblog-extra').prop('hidden', !this.checked);
	});

	// 连载条目：未选择原始文章时阻止提交（经典编辑器表单）。
	$('form#post').on('submit', function (e) {
		var $select = $('#mlb-liveblog');

		if (!$select.length || !$select.data('required')) {
			return;
		}

		if (!$select.val()) {
			e.preventDefault();

			window.alert(window.mlbAdminL10n ? window.mlbAdminL10n.selectLiveblog : 'Please select a liveblog before saving.');
			$select.focus();
		}
	});
});
