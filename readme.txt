=== MicroBlog ===
Contributors: webersongao
Tags: microblog, weibo, liveblog, shortcode
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.9.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Publish short posts in their own post type and show them with a shortcode, a widget or an optional liveblog stream.

== Description ==

MicroBlog adds a `micropost` post type so short updates never mix with regular articles. Each entry has its own permalink (for example `/microposts/123.html`), archive and feed, and can be displayed anywhere with a shortcode or a sidebar widget. It works with any theme; the KoneyGao theme adds an optional home-page block.

Highlights

* Dedicated `micropost` post type with a configurable URL slug and REST support.
* `[microblog]` shortcode: latest entries with pagination, excerpts and a responsive single/two/nine-image grid.
* "Microblog" sidebar widget and a "Quick Microblog" dashboard widget for publishing without leaving the dashboard.
* Fuzzy ("3 minutes ago") or fixed date formats, optional titles, avatars and an RSS link.
* Optional Liveblog module: attach a rolling update stream to a published post, with a four-state display column and per-post auto refresh (10-300 seconds).
* Auto / light / dark color mode; follows the host theme's `data-color-mode` when present, otherwise the visitor's device.
* Translation ready (text domain `microblog`) with a Simplified Chinese translation.

== Installation ==

1. Upload the `MicroBlog` folder to `/wp-content/plugins/`, or install it from Plugins > Add New.
2. Activate it on the Plugins screen. Activation writes three defaults and registers the rewrite rules.
3. Open Tools > MicroBlog Settings to configure display, date format, URL slug, entries per page and images.
4. Create a page and insert `[microblog]` into its content, or add the "Microblog" widget to a sidebar.
5. If `/microposts/...` returns 404, open Settings > Permalinks and save once.

== Configuration notes ==

* Settings are under Tools > MicroBlog Settings in three groups: Microblog (titles, date format, slug, entries per page, editor features, feed and lightbox markup), General (Liveblog switch and color mode), and Liveblog (only shown when that module is enabled).
* URL slug: 1-20 lowercase letters and numbers; empty means `microposts`. Changing it refreshes rewrite rules automatically.
* `[microblog]` attributes: `null_text` (default `(none)`), `use_excerpt`, and `q` for extra WP_Query arguments. With Liveblog enabled, `[micro_liveblog endpoint="..."]` or `[micro_liveblog live_id="..."]` renders a stream.
* Liveblog is off by default. Turning it off does not delete entries or settings. The "Liveblog display" column on the post list switches between Not enabled / Hidden / Default display / Auto refresh; only the last state polls, and polling pauses while the tab is hidden.
* The image lightbox option only changes image markup to suit the optional Simple Lightbox plugin. That plugin is not a dependency.
* `[microarchive]` is a KoneyGao theme shortcode, not a plugin shortcode.

== Frequently Asked Questions ==

= Where do microblogs appear? =
They are a separate post type. Use `[microblog]`, the widget, or the archive at your configured slug (default `/microposts`).

= Can I change the microblog URL? =
Yes: Tools > MicroBlog Settings > Microblog > URL slug. Save Settings > Permalinks once if links return 404.

= Does it work with the block editor and any theme? =
Yes on both. The post type follows your editor setting and the plugin ships its own styles. KoneyGao only adds an optional home-page block and shares the `data-color-mode` color contract.

= What happens when I uninstall? =
Deactivate the plugin on the Plugins screen, then click Delete. `uninstall.php` removes the five plugin options (settings groups, rewrite signature and the widget option) but deliberately keeps all microblog and liveblog entries, post meta and transients -- they are your content. Delete that content manually first if you want a completely clean site.

= Does the Liveblog module need WP Cron? =
No. The front end only polls for posts set to "Auto refresh" and skips checks while the browser tab is hidden.

== Screenshots ==

1. The `[microblog]` shortcode showing the latest entries on a page.
2. The microblog archive at the default `/microposts` slug.
3. A single microblog entry with its author card and comment form.

== Changelog ==

= 1.9.1 =
* Auto / light / dark color mode driven by CSS custom properties.
* Liveblog feed URLs always carry a timestamp so CDNs cannot serve stale entries.
* Dark-mode contrast fixes; the "Write Liveblog" screen now lists existing sources by default.

== Upgrade Notice ==

= 1.9.1 =
No database migration. Purge the page cache after updating.

== Credits ==

* selectize.js 0.12.4 with Sifter.js and MicroPlugin.js -- Apache-2.0, (c) Brian Reavis & contributors; bundled for the Liveblog admin screens.
* Social logo SVG paths from Automattic/social-logos.
* Based on the simple-microblogging plugin by Samuel Coskey and Victoria Gitman; thanks to obaby (h4ck.org.cn).
