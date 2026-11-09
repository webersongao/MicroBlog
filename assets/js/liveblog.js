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

		$: function (selector) {
			return this.root.find(selector);
		},

		list: function () {
			return this.$('.mlb-liveblog-list');
		},

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
