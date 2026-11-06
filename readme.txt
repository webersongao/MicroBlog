=== MicroBlog ===
Contributors: webersongao
Tags: microblog, weibo, short content, liveblog
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.9.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Use WordPress as a microblog: publish short posts and show them with a shortcode or a widget.

== Description ==

MicroBlog turns your WordPress site into a microblog (similar to Weibo or Twitter). Short posts are stored in their own "Microblog" post type, so they never mix with your regular articles, and you can display them anywhere with a shortcode or a sidebar widget.

**Features**

* A dedicated Microblog post type with its own admin menu, list table and REST API endpoint.
* `[microblog]` shortcode to show the latest microblogs on any post or page, with pagination.
* A "Microblog" sidebar widget with optional excerpt and RSS link.
* A "Quick Microblog" dashboard widget for posting without leaving the dashboard.
* Custom URL slug (default `microposts`), e.g. `/microposts/123.html`, with its own feed.
* Fuzzy ("3 minutes ago") or fixed date formats, optional titles and avatars in the footer.
* Optional Liveblog module: attach a rolling update stream to any post in a supported post type, with a per-post auto refresh switch.
* Fully translatable; English is the source language.

**Shortcode attributes**

* `null_text` – text shown when no microblog is found. Default `(none)`.
* `use_excerpt` – use the excerpt instead of the full content.
* `q` – extra query arguments, e.g. `[microblog q="author_name=jack&order=ASC"]`.

**Liveblog module**

When enabled under Tools → MicroBlog Settings → General, every published post in a supported post type can be used as a liveblog source — there is no per-post "enable" flag to set first. Entries are separate posts attached to a source and rendered in a rolling list.

The "Liveblog display" column on the post list screen shows one of four states:

* Not enabled — no liveblog entry references this post. Derived automatically and read-only.
* Active · Hidden — entries reference the post, but the front-end block is hidden.
* Active · Default display — the block is shown and loaded once. This is the finished state; the front end does not label it as finished.
* Active · Auto refresh — the block is shown and checks for new entries at the interval set in Liveblog settings.

The three active states can be switched directly from that column. There is no editor meta box and no row action, and posts without entries never render an empty block. Themes can override the templates from a `micro_live_theme/` folder.

== Installation ==

1. Upload the `MicroBlog` folder to `/wp-content/plugins/`, or install the plugin through Plugins → Add New.
2. Activate the plugin through the Plugins screen.
3. Go to Tools → MicroBlog Settings to configure the post type, date format and display options.
4. Add the `[microblog]` shortcode to a page, or add the "Microblog" widget to a sidebar.

If microblog permalinks return 404, open Settings → Permalinks and save once to refresh the rewrite rules.

== Frequently Asked Questions ==

= Where do microblogs appear? =

Microblogs are a separate post type and are not mixed into your normal post stream. Use the `[microblog]` shortcode, the Microblog widget, or the microblog archive at your configured slug (default `/microposts`).

= Can I change the URL of microblogs? =

Yes. On Tools → MicroBlog Settings → Microblog, set "URL slug" to a lowercase alphanumeric value (1–20 characters). Leave it empty to keep `microposts`.

= Does it work with the block editor? =

Yes. The microblog post type follows your site's editor setting, and the quick publish form wraps plain text into paragraph blocks when the block editor is active.

= Is the Liveblog module required? =

No. It is optional and disabled by default. Turning it off does not delete any content or settings.

= Does the liveblog refresh automatically? =

Only for posts set to "Active · Auto refresh". The front end then checks the feed at a configurable interval (10–300 seconds, 30 by default) and shows a "Show new updates" button when new entries appear; the list is rebuilt only after the visitor clicks it. Checks are skipped while the browser tab is hidden or a previous check is still running.

"Active · Default display" loads the entries once and never polls — that is the finished state, without any finished label on the page. Hidden and not-enabled posts make no feed requests at all.

== Settings reference ==

Stored options and post meta, kept here as the contract for future data migration.

= Options =

* `microblog_general_data` (array)
  * `mb_color_mode` — `auto` | `light` | `dark`. Controls the color mode of the microblog shortcode, the widget and liveblog blocks. Missing value means `auto`: follow the host theme's `data-color-mode` when present, otherwise follow the visitor device (pure CSS).
* `microblog_live_data` (array)
  * `ml_cache_enabled` — **removed.** The feed is always cached for 5 minutes per liveblog ID (`mlb_feed_{id}` transient) and purged on any entry change, so the on/off switch (and its `object` branch, which did nothing without a persistent object cache) is gone. Leftover values are ignored and dropped on the next settings save.
  * `ml_refresh_interval` — polling interval in seconds, 10–300.
  * `ml_append_timestamp` — **removed in 1.9.1.** The liveblog feed URL always carries a timestamp now: minute precision on first load (so a CDN can still share one minute of cache) and millisecond precision for polling. Stale values are ignored.
  * `ml_theme_style` — **removed in 1.9.0.** Replaced by `mb_color_mode`; the `mlb-theme-{mode}` CSS class is still printed for third-party selectors.

= Post meta =

* `_micro_post_live_enable` — `default` | `auto`, absent means "not enabled". `default` is the finished state (entries load once, no polling, no front-end label); `auto` polls for new entries.
* `_micro_live_post_id` — the liveblog source (article) an entry belongs to. An entry cannot be published without it.

== Screenshots ==

1. The Microblog settings screen.
2. The Microblog widget on the front end.

== Changelog ==

= 1.9.1 =
* Added a color mode setting for the shortcode, the widget and liveblog blocks: automatic (follow the host theme, or the visitor device when the theme has no color mode), light or dark. All plugin colors now come from CSS custom properties.
* The liveblog feed URL always carries a timestamp, so CDNs and proxies cannot serve stale entries. The "Feed timestamp" switch was removed.
* The "Write Liveblog" screen now lists articles that are already liveblog sources by default, with a post type filter and a keyword search in the top right corner.
* Fixed contrast issues in dark mode (liveblog edit button, microblog timestamps) and a self-referential CSS custom property on the microblog container.

= 1.9.0 =
* Removed microblog reposting and the microblog topics taxonomy.
* Removed the liveblog open/closed status; automatic refresh is now a per-post display state instead of a global setting.
* Any published post in a supported post type can now be a liveblog source; the post list screen shows a four-state "Liveblog display" column (Not enabled / Active · Hidden / Active · Default display / Active · Auto refresh) with direct switching, and no row action.
* "Active · Default display" loads the entries once (the finished state, without a front-end label); "Active · Auto refresh" polls at a configurable interval and offers a "Show new updates" button. Posts without entries no longer render an empty block.
* Replaced the bundled moment.js locale bundle with dependency-free JavaScript; all UI strings, including the refresh button, are translated through the plugin's language files.
* Added full internationalization: English source strings, `microblog` text domain and a `languages/` folder.
* Improved the Liveblog meta box experience and admin notices.
* Escaped settings output and hardened admin screens.
* Quick publish minimum length is now filterable with `microblog_quickpub_min_length`.
* Refreshed the readme and cleaned up bundled files.

= 1.8.0 =
* Fixed the Liveblog meta box behaviour and admin scripts.

= 1.7.0 =
* Added microblog topics.
* Added REST API support.
* Added microblog reposting (partially enabled).
* Replaced the JavaScript text replacement with native strings.

= 1.6.1 =
* Added time format options and an option to include microblogs in the main site feed.
* Default settings are written on activation and removed on uninstall.

= 1.6 =
* Added quick publishing from the dashboard.
* Renamed the admin labels from "post" wording to "microblog" wording.

= 1.5 =
* Refactored the code structure and improved query performance.

= 1.4 =
* Fixed spacing when the widget footer bar is hidden.
* Fixed the comment button still showing when comments are closed.

= 1.3 =
* Custom URL slug support (e.g. `/microblog/123.html` and `/microblog/feed`).

= 1.2 =
* The editor follows the site configuration (block or classic).
* Configurable editor features: author, featured image, excerpt.
* Fixed uninitialized settings on activation.

= 1.1 =
* Added the nine-grid image layout and a Lightbox switch.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.9.0 =
Text domain changed to `microblog`, with a new translation template and Chinese translation.
