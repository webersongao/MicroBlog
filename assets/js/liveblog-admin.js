/**
 * Liveblog admin: metabox interactions.
 *
 * 1. 连载条目（microlive）未选择原始连载时，禁止发布：
 *    - 古腾堡：lockPostSaving 原生锁定发布按钮（含预发布面板，无法绕过）；
 *    - 经典编辑器：拦截表单提交并弹窗提示。
 */
jQuery(function ($) {
	var $select = $('#mlb-liveblog');

	function parentSelected() {
		return !$select.length || !$select.data('required') || !!$select.val();
	}

	// 经典编辑器：表单提交前拦截。
	$('form#post').on('submit', function (e) {
		if (!parentSelected()) {
			e.preventDefault();

			window.alert(window.mlbAdminL10n ? window.mlbAdminL10n.selectLiveblog : 'Please select a liveblog before saving.');
			$select.focus();
		}
	});

	// 古腾堡：用官方 lockPostSaving 原生锁定（按钮 disabled，预发布面板同样被拦）。
	function applyGutenbergState() {
		if (!$select.length || typeof $select.prop !== 'function') {
			return;
		}

		if (!window.wp || !window.wp.data) {
			return;
		}

		var lockName = 'mlb-entry-parent';
		var editorDispatch, editorSelect;

		try {
			editorDispatch = window.wp.data.dispatch('core/editor');
			editorSelect = window.wp.data.select('core/editor');
		} catch (err) {
			return;
		}

		if (!editorDispatch || typeof editorDispatch.lockPostSaving !== 'function') {
			return;
		}

		if (parentSelected()) {
			if (editorSelect && typeof editorSelect.isPostSavingLocked === 'function'
				&& editorSelect.isPostSavingLocked()) {
				editorDispatch.unlockPostSaving(lockName);
			}
		} else {
			editorDispatch.lockPostSaving(lockName);
		}
	}

	// 原文切换时立即刷新锁定状态；页面加载后按初始状态应用。
	if ($select.length) {
		$select.on('change', applyGutenbergState);

		// 古腾堡脚本与数据 store 就绪有先后，轮询到可用为止。
		var tries = 0;

		(function waitStore() {
			applyGutenbergState();

			var locked = false;

			try {
				locked = window.wp && window.wp.data
					&& window.wp.data.select('core/editor')
					&& typeof window.wp.data.select('core/editor').isPostSavingLocked === 'function'
					&& window.wp.data.select('core/editor').isPostSavingLocked();
			} catch (err) {
				locked = false;
			}

			if (!locked && tries++ < 40) {
				setTimeout(waitStore, 250);
			}
		})();
	}
});
