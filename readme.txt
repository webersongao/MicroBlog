=== MicroBlog ===
Contributors: webersongao
Tags: microblog, weibo, liveblog, shortcode
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.9.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Publish short posts in their own post type and show them with a shortcode, a page template, a widget or an optional liveblog stream.

== Description ==

MicroBlog adds a `micropost` post type so short updates never mix with regular articles. Each entry has its own permalink (for example `/microposts/123.html`), archive and feed, and can be displayed anywhere with a shortcode, a page template or a sidebar widget. It works with any theme; the KoneyGao theme only adds an optional home-page block. The plugin ships its own "Microblog list (plugin)" page template, which renders the feed without any shortcode in the page body.

Highlights

* Dedicated `micropost` post type with a configurable URL slug and REST support.
* `[microblog]` shortcode: latest entries with pagination, excerpts and a responsive single/two/nine-image grid.
* Built-in "Microblog list (plugin)" page template: choose it under Page Attributes > Template. It renders a centered title with date and view count, your own page body, and the feed, with no theme code required.
* "Microblog" sidebar widget and a "Quick Microblog" dashboard widget for publishing without leaving the dashboard.
* Fuzzy ("3 minutes ago") or fixed date formats, optional titles, avatars and an RSS link.
* Optional Liveblog module: attach a rolling update stream to a published post, with a four-state display column and per-post auto refresh (10-300 seconds).
* Auto / light / dark color mode; follows the host theme's `data-color-mode` when present, otherwise the visitor's device.
* Translation ready (text domain `microblog`) with a Simplified Chinese translation.

== Installation ==

1. Upload the `MicroBlog` folder to `/wp-content/plugins/`, or install it from Plugins > Add New.
2. Activate it on the Plugins screen. Activation writes three defaults and registers the rewrite rules.
3. Open Tools > MicroBlog Settings to configure display, date format, URL slug, entries per page and images.
4. Create a page and choose "Microblog list (plugin)" under Page Attributes > Template (the body can stay empty). This page template is the default and recommended way to show the feed. The `[microblog]` shortcode is still available for embedding the feed inside other content, and the "Microblog" widget can be added to a sidebar.
5. If `/microposts/...` returns 404, open Settings > Permalinks and save once.

== Configuration notes ==

* Settings are under Tools > MicroBlog Settings in three groups: Microblog (titles, date format, slug, entries per page, editor features, feed and lightbox markup), General (Liveblog switch, color mode, page template content order and single-page background), and Liveblog (only shown when that module is enabled).
* URL slug: 1-20 lowercase letters and numbers; empty means `microposts`. Changing it refreshes rewrite rules automatically.
* The "Microblog list (plugin)" page template is the default way to show the feed. It is registered by this plugin via `theme_page_templates` + `template_include`, calls `microblog_render_list()` directly rather than the shortcode, and no theme provides or wraps it.
* Page template content order: Tools > MicroBlog Settings > General > "Page template content order" chooses whether your page body appears before the feed (default) or after it. Only the "Microblog list (plugin)" page template uses this; the shortcode and widget are not affected.
* Single-page background: Tools > MicroBlog Settings > General > "Single-page background" decides whether the "Microblog list (plugin)" page template adopts the active theme's page card. Enabled (default) adds the KoneyGao single-page card background and its article-list typography; disabled gives a plain WordPress page with no card background and the plugin's default list styling. Other themes are not affected, and this switch never touches the shortcode or the widget.
* When the card shell is enabled under KoneyGao, microblog titles are 16px bold with a muted resting color and a strong hover color; the shortcode and widget keep the plugin defaults.
* Developer filters: `microblog_page_content_before_list` (bool) overrides the body position, and `microblog_page_background_enabled` (bool) overrides the single-page background switch. The template never injects content into the page body.
* `[microblog]` is retained as an optional shortcode for embedding the feed inside a post or page body. Attributes: `null_text` (default `(none)`), `use_excerpt`, and `q` for extra WP_Query arguments. With Liveblog enabled, `[micro_liveblog endpoint="..."]` or `[micro_liveblog live_id="..."]` renders a stream. WordPress.org permits plugins to register shortcodes (Plugin Developer Handbook > Shortcodes); themes may not, which is why the companion theme ships page templates instead.
* Liveblog is off by default. Turning it off does not delete entries or settings. The "Liveblog display" column on the post list switches between Not enabled / Hidden / Default display / Auto refresh; only the last state polls, and polling pauses while the tab is hidden.
* The image lightbox option only changes image markup to suit the optional Simple Lightbox plugin. That plugin is not a dependency.

== Frequently Asked Questions ==

= Where do microblogs appear? =
They are a separate post type. The default is the plugin's "Microblog list (plugin)" page template; `[microblog]`, the widget, and the archive at your configured slug (default `/microposts`) remain available as alternatives.

= Can I change the microblog URL? =
Yes: Tools > MicroBlog Settings > Microblog > URL slug. Save Settings > Permalinks once if links return 404.

= Does it work with the block editor and any theme? =
Yes on both. The post type follows your editor setting and the plugin ships its own styles and page template. KoneyGao only adds an optional home-page block; the plugin template follows the theme's `data-color-mode` and CSS variables when present, with neutral fallbacks elsewhere. Under KoneyGao the "Single-page background" setting lets you turn that theme-specific card shell off and fall back to a plain page.

= What happens when I uninstall? =
Deactivate the plugin on the Plugins screen, then click Delete. `uninstall.php` removes the five plugin options (settings groups, rewrite signature and the widget option) but deliberately keeps all microblog and liveblog entries, post meta and transients -- they are your content. Delete that content manually first if you want a completely clean site.

= Does the Liveblog module need WP Cron? =
No. The front end only polls for posts set to "Auto refresh" and skips checks while the browser tab is hidden.

== Screenshots ==

1. The microblog list page showing the latest entries, rendered by the plugin's "Microblog list (plugin)" page template (or by `[microblog]`).
2. The microblog archive at the default `/microposts` slug.
3. A single microblog entry with its author card and comment form.

== Changelog ==

= 1.9.6 =
* Liveblog WeChat share cards now carry a longer excerpt. The old wp_trim_words() call treated a whole Chinese paragraph as one word and produced only about 40 characters, leaving most of the card body empty; the card now supplies enough text for the theme's five-line clamp.
* Requires the matching KoneyGao update: the share-card stylesheet trims the header and spacing and reduces the title and body font sizes, so the extra text fits without growing the card.

= 1.9.5 =
* The sidebar Microblog widget now follows the same global settings as the `[microblog]` shortcode and the "Microblog list (plugin)" page template: title visibility, number of items, image lightbox markup and author avatar. The per-widget "Number of items" field was removed in favour of the global value.
* Suppressed the_post broadcasts while liveblog entry HTML is generated. Previously the WPJAM related-posts extension registered a new the_content callback on every broadcast, which produced duplicate "Related posts" blocks on liveblog articles for logged-in users. Related posts now appear once, tied to the article only.
* Bumped the stylesheet version so browsers and CDNs pick up the new widget styles.

= 1.9.4 =
* Replaced the Facebook icon on liveblog entries with a bare WeChat glyph that matches the X and Weibo icons in color, size and alignment; clicking it opens the same QR-code share card as the article page.
* The "Edit" link on liveblog entries is now shown only to administrators or the entry author. Other visitors, including logged-in subscribers, see the "Live" link.
* Removed the "Title" checkbox from Layout: liveblog entry titles are always shown. Fixed the Layout default value and added server-side clamping for "Entries per Page".
* The follow-up heading defaults to 后续： (no brackets) and now uses the same spacing as two body paragraphs.
* Chinese copy fix: the Show Prefix description now reads 自动在连载原文前加上「连载」.
* Audited all settings groups: normalized micropost checkbox saves, unified the time-format default, clamped the microblog count, and aligned the single-page background default with its reader.
* Versioned the liveblog feed cache key so template changes take effect without a manual cache purge. Logged-in feed requests now carry a REST nonce, are never read from or written to the shared cache, and send no-cache headers, so entry "Edit" links render for the right user and never leak to anonymous visitors.
* Bumped the stylesheet version so browsers and CDNs pick up the new CSS.

= 1.9.3 =
* Added a configurable follow-up heading between the article body and its liveblog entries. The default text is 【后续：】 and both the global and per-article values are capped at 45 characters.
* The heading can be set on the Liveblog settings tab, or overridden for a single article from the post editor. Leaving a field blank falls back to the next level.
* Bumped the stylesheet version so browsers and CDNs pick up the new CSS.

= 1.9.2 =
* Renamed the two General settings in the Chinese admin UI to "微博List页面顺序" and "微博List单页背景"; shortened their Chinese descriptions. English strings are unchanged.
* Swapped the resting and hover colors of microblog list titles under KoneyGao and set them to 16px bold.
* Bumped the stylesheet version so browsers and CDNs pick up the new CSS.

= 1.9.1 =
* Auto / light / dark color mode driven by CSS custom properties.
* Liveblog feed URLs always carry a timestamp so CDNs cannot serve stale entries.
* Dark-mode contrast fixes; the "Write Liveblog" screen now lists existing sources by default.
* Built-in "Microblog list (plugin)" page template with a centered header, configurable body position and centered title/date/view count; the theme card shell plus article-list typography can be switched off with "Single-page background".
* Page template cards match a standard page under KoneyGao in light and dark mode across six widths; the microblog list title, body text, date and divider now follow the theme's article list when the shell is enabled.

== Upgrade Notice ==

= 1.9.6 =
No database migration. Update the KoneyGao theme together with this plugin: the longer excerpt is capped by the theme's five-line clamp and tighter font sizes, so an older stylesheet will make the card taller. Clear the page cache after updating.

= 1.9.5 =
No database migration. Existing widget instances keep working; their saved item count is ignored from now on. Clear the page cache after updating so the new stylesheet version takes effect.

= 1.9.4 =
No database migration. Clear the page cache after updating so the new stylesheet version takes effect; the liveblog feed cache is versioned and switches over automatically, so no manual transient cleanup is needed. If you use the WeChat share button, keep the KoneyGao theme installed: its share-card script and QR endpoint are reused.

= 1.9.3 =
No database migration. Clear the page cache after updating so the new stylesheet version takes effect.

= 1.9.2 =
No database migration. Purge the page cache after updating so the new stylesheet version is served.

= 1.9.1 =
No database migration. Purge the page cache after updating.

== Credits ==

* selectize.js 0.12.4 with Sifter.js and MicroPlugin.js -- Apache-2.0, (c) Brian Reavis & contributors; bundled for the Liveblog admin screens.
* Social logo SVG paths from Automattic/social-logos.
* Based on the simple-microblogging plugin by Samuel Coskey and Victoria Gitman; thanks to obaby (h4ck.org.cn).
