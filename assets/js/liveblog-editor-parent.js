/* 区块编辑器文档侧栏：显示本条连载所属的原文
   依赖 wp-plugins / wp-edit-post / wp-element，由 admin_enqueue_scripts 按需加载。 */
(function () {
	'use strict';

	var data = window.mlbEditorParent;

	if (!data || !data.title || !window.wp) {
		return;
	}

	var wp = window.wp;

	if (!wp.plugins || !wp.editPost || !wp.editPost.PluginPostStatusInfo || !wp.element) {
		return;
	}

	var el = wp.element.createElement;
	var Slot = wp.editPost.PluginPostStatusInfo;

	wp.plugins.registerPlugin('mlb-editor-parent', {
		render: function () {
			return el(
				Slot,
				{ className: 'mlb-editor-parent' },
				[
					el('span', {
						key: 'label',
						className: 'mlb-editor-parent__label'
					}, data.label || 'Liveblog source:'),
					el('a', {
						key: 'link',
						className: 'mlb-editor-parent__link',
						href: data.url || '#',
						target: '_blank',
						rel: 'noopener noreferrer',
						title: data.title
					}, data.title)
				]
			);
		}
	});
})();
