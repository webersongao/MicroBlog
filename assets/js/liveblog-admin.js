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

		var locked = !!(editorSelect && typeof editorSelect.isPostSavingLocked === 'function'
			&& editorSelect.isPostSavingLocked());

		if (parentSelected()) {
			if (locked) {
				editorDispatch.unlockPostSaving(lockName);
			}
		} else if (!locked) {
			editorDispatch.lockPostSaving(lockName);
		}
	}

	if ($select.length) {
		$select.on('change', applyGutenbergState);

		if (window.wp && window.wp.data && typeof window.wp.data.subscribe === 'function') {
			window.wp.data.subscribe(applyGutenbergState);
		}

		applyGutenbergState();
	}
});
