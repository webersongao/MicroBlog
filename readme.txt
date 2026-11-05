=== MicroBlog ===
Contributors: webersongao
Tags: microblog, weibo, short content, liveblog
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.9.0
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
* Optional topics (hashtag-style taxonomy) to group microblogs by subject.
* Optional reposting: link a new microblog to the one it reposts.
* Custom URL slug (default `microposts`), e.g. `/microposts/123.html`, with its own feed.
* Fuzzy ("3 minutes ago") or fixed date formats, optional titles, avatars and topics in the footer.
* Optional Liveblog module: attach a rolling, auto-refreshing update stream to any post.
* Fully translatable; English is the source language.

**Shortcode attributes**

* `null_text` – text shown when no microblog is found. Default `(none)`.
* `use_excerpt` – use the excerpt instead of the full content.
* `q` – extra query arguments, e.g. `[microblog q="author_name=jack&order=ASC"]`.

**Liveblog module**

When enabled under Tools → MicroBlog Settings → General, any supported post type can be turned into a liveblog. Entries are separate posts attached to the liveblog, rendered in a rolling list that can poll for new updates. Themes can override the templates from a `micro_live_theme/` folder.

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

== Screenshots ==

1. The Microblog settings screen.
2. The Microblog widget on the front end.

== Changelog ==

= 1.9.0 =
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
