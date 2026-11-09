jQuery(function ($) {
	var $select = $('#mlb-liveblog');

	function parentSelected() {
		return !$select.length || !$select.data('required') || !!$select.val();
	}

	$('form#post').on('submit', function (e) {
		if (!parentSelected()) {
			e.preventDefault();

			window.alert(window.mlbAdminL10n ? window.mlbAdminL10n.selectLiveblog : 'Please select a liveblog before saving.');
			$select.focus();
		}
	});

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

	if ($select.length) {
		$select.on('change', applyGutenbergState);

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
