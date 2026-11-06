/**
 * MicroBlog Liveblog front-end.
 *
 * 纯原生 jQuery，无第三方运行时依赖（旧版是内嵌 moment.js 与上百语言包的
 * webpack 产物）。面向用户的文案统一由 wp_localize_script('micro_lb_data')
 * 提供，并在 languages/ 里翻译。
 *
 * 行为：
 *   1. 首次加载条目 → 超过 showEntries 的折入「Load more」→ 高亮条目滚动定位；
 *   2. 容器 data-autorefresh=1（列表页「开启中·自动刷新」）时按设置页统一间隔
 *      轮询 feed；发现新条目只显示一个按钮，由访客点击后再整表重建，
 *      不打断正在阅读的内容、也不重置「Load more」的展开状态；
 *   3. data-autorefresh=0（「开启中·默认展示」，即已完结）完全不轮询，
 *      页面上也不做任何已完结标记。
 *
 * 保留的扩展点：
 *   window.mlb_after_feed_load(feed)       条目渲染完成后
 *   window.mlb_after_load_more_callback()  点击「Load more」后
 */
(function ($) {
	'use strict';

	var l10n = window.micro_lb_data || {};

	var Liveblog = {
		root: null,
		renderedIds: [],
		polling: false,
		refreshed: false,
		loadMoreLabel: '',

		init: function () {
			this.root = $('.mlb-liveblog');

			if (!this.root.length) {
				return;
			}

			this.loadMoreLabel = this.$('#mlb-load-more').text();
			this.root.find('#mlb-load-more').on('click', $.proxy(this.loadMore, this));
			this.fetch();

			if (this.root.data('autorefresh')) {
				this.$('#mlb-show-new-posts').on('click', $.proxy(this.refresh, this));
				this.startPolling();
			}
		},

		/** 所有选择器都限定在连载容器内，避免同一页多个连载互相干扰。 */
		$: function (selector) {
			return this.root.find(selector);
		},

		list: function () {
			return this.$('.mlb-liveblog-list');
		},

		/**
		 * 始终在 feed 地址后附加时间戳，绕过 CDN / 代理缓存；
		 * 按分钟取整便于首屏在 CDN 侧共享 1 分钟缓存，
		 * 轮询强制用毫秒级时间戳，避免浏览器把旧 feed 还回来。
		 */
		endpoint: function (bust) {
			var url = this.root.data('endpoint');
			var sep = url.indexOf('?') === -1 ? '?' : '&';

			url += sep + '_=' + (bust ? Date.now() : Math.round(Date.now() / 60000));

			return url;
		},

		fetch: function () {
			var self = this;

			$.ajax({
				url: this.endpoint(),
				method: 'GET',
				dataType: 'json'
			}).done(function (feed) {
				self.render(feed);
			}).fail(function () {
				self.$('.mlb-loader').hide();
			});
		},

		/**
		 * 清空重建，而不是增量合并：feed 不论升序降序都能保持一致，
		 * 也不会出现重复条目。renderedIds 供轮询比较「有没有新条目」。
		 */
		render: function (feed) {
			var updates = (feed && feed.updates) || [];
			var showEntries = parseInt(this.root.data('showEntries'), 10) || 10;
			var highlighted = this.root.data('highlightedEntry');
			var $list = this.list();
			var self = this;

			this.renderedIds = $.map(updates, function (entry) {
				return String(entry.id);
			});

			$list.empty();
			this.$('.mlb-loader').hide();
			this.$('.mlb-no-liveblog-entries-message').hide();
			this.$('#mlb-load-more').text(this.loadMoreLabel).hide();

			$.each(updates, function (index, entry) {
				var $wrapper = $('<div>').html(entry.html);
				var $items = $wrapper.find('> li');

				if (index >= showEntries) {
					$items.addClass('mlb-hide mlb-liveblog-initial-post');
					self.$('#mlb-load-more').show();
				}

				if (String(entry.id) === String(highlighted)) {
					$items.addClass('mlb-liveblog-highlight').removeClass('mlb-hide');
				}

				$list.append($wrapper.html());
			});

			if (!$list.find('> li').length) {
				this.$('.mlb-no-liveblog-entries-message').show();
			} else if (highlighted && !this.refreshed) {
				var $target = $list.find('> li[data-mlb-post-id="' + highlighted + '"]');

				if ($target.length) {
					$(document).scrollTop($target.offset().top);
				}
			}

			if (typeof window.mlb_after_feed_load === 'function') {
				window.mlb_after_feed_load(feed);
			}
		},

		/** 按设置页统一间隔轮询；页面不可见或上一次请求未结束时跳过。 */
		startPolling: function () {
			var seconds = parseInt(this.root.data('refreshInterval'), 10) || 30;
			var self = this;

			window.setInterval(function () {
				self.poll();
			}, Math.max(10, seconds) * 1000);
		},

		poll: function () {
			if (document.hidden || this.polling) {
				return;
			}

			this.polling = true;

			var self = this;

			$.ajax({
				url: this.endpoint(true),
				method: 'GET',
				dataType: 'json',
				cache: false
			}).done(function (feed) {
				var updates = (feed && feed.updates) || [];
				var fresh = $.grep(updates, function (entry) {
					return self.renderedIds.indexOf(String(entry.id)) === -1;
				});

				if (fresh.length) {
					self.$('#mlb-show-new-posts')
						.text((l10n.show_new_posts || 'Show %s new updates').replace('%s', fresh.length))
						.show();
				}
			}).always(function () {
				self.polling = false;
			});
		},

		refresh: function () {
			this.refreshed = true;
			this.$('#mlb-show-new-posts').hide();
			this.fetch();
		},

		loadMore: function () {
			var $list = this.list();
			var showEntries = parseInt(this.root.data('showEntries'), 10) || 10;
			var $hidden = $list.find('> li.mlb-hide.mlb-liveblog-initial-post');

			$hidden.each(function (index) {
				if (index < showEntries) {
					$(this).removeClass('mlb-hide');
				}
			});

			if (!$list.find('> li.mlb-hide.mlb-liveblog-initial-post').length) {
				this.$('#mlb-load-more')
					.text(l10n.now_more_posts || '')
					.delay(2000)
					.fadeOut(1000);
			}

			if (typeof window.mlb_after_load_more_callback === 'function') {
				window.mlb_after_load_more_callback();
			}
		}
	};

	$(function () {
		Liveblog.init();
	});
})(jQuery);
