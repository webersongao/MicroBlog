=== MicroBlog ===
Contributors: webersongao
Tags: microblog, weibo, liveblog, shortcode
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.9.15
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
5. Since 1.9.8 missing rewrite rules are rebuilt automatically. If `/microposts/...` still returns 404, open Settings > Permalinks and save once, then purge the page cache.

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
* The image lightbox option keeps every image from an entry, wraps unlinked images in a media-file link, and, when Simple Lightbox is active, passes the markup through its official slb_activate() template tag. This is required because the page template and widget output their HTML directly instead of going through the_content. Simple Lightbox is still not a dependency.

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

= 1.9.15 =
* Stylesheet and script URLs now use a short hash of the file contents as their `ver` parameter instead of the plugin version constant. Changing a CSS or JS file changes its URL immediately, without having to bump the plugin version; unchanged files keep their URL so CDN and browser caches still hit. Verified against the live CDN: the full URL including `?ver=` is the cache key, so a new hash triggers a fresh origin fetch.
* No markup, settings, database or functional changes. The KoneyGao theme applies the same content-hash rule to its own assets.
* The version string still participates in the rewrite-rule signature and the liveblog feed cache key, so this update rebuilds rewrite rules once on the next admin request and rebuilds the liveblog feed cache under a new key. Clear the opcode cache plus the LiteSpeed / CDN page cache after updating: cached HTML pages contain the old resource URLs and must be purged for visitors to see the new ones.

= 1.9.14 =
* Removed a stale source comment that still described the retired front-end 404 self-heal (`template_redirect`); that handler was already removed in 1.9.9. No functional, markup, settings or database changes in this release.
* The version string is part of the rewrite-rule signature and the liveblog feed cache key, so updating rebuilds rewrite rules once on the next admin request and rebuilds the liveblog feed cache under a new key. Clear the LiteSpeed / CDN page cache and any opcode cache after updating.

= 1.9.13 =
* Fixed duplicate "related posts" blocks on articles with liveblog entries. Liveblog entries now set up their post data on a dedicated WP_Query instance instead of the main query, so the core `the_post` broadcast no longer makes third-party plugins (WPJAM related posts) register the same `the_content` callback once per entry. The main article renders exactly one related-posts block, whether the liveblog feed is served from cache or built on the fly.
* This is a correctness fix for custom-loop handling: no hook table manipulation, no core prototype patching and no rewriting of third-party HTML. Liveblog entry content, titles, dates and authors are unchanged.

= 1.9.12 =
* The microblog list now shows at most three images (one row) per entry. When an entry has more than three images, the third image is left completely untouched: no overlay, no darkening, so it stays fully visible.
* The third image stays fully previewable: it keeps its original lightbox link, so clicking it opens the lightbox like any other image. The hidden-image count (`+1`, `+2`, ...) is a separate small pill in the bottom-right corner and is the only indicator that more images are hidden; clicking that pill opens the microblog entry's own page. The entry title and the comment/more link already lead there as well.
* Entries with three or fewer images are unchanged; existing media/attachment/custom links are untouched. Added an accessible label and a Chinese translation for the pill, and bumped the asset version so browsers and CDNs pick up the new stylesheet.

= 1.9.10 =
* Fixed the microblog feed dropping images without a link. With the image lightbox option enabled, the shortcode, page template and widget previously kept only `<a><img></a>` fragments, so every image block set to "Link to: None" or the block editor's "Expand on click" disappeared from the list. Images are now collected in document order, regardless of their link setting.
* Unlinked images are wrapped in a link to the media file (processing query strings stripped, WordPress' own image extension list used, including HEIC/AVIF) so Simple Lightbox can activate every image. Existing media/attachment/custom links are left untouched. With the lightbox option disabled, plain `<img>` tags are output as before.
* Bumped the asset version so browsers and CDNs pick up the changes.

= 1.9.9 =
* Liveblog detection now checks only the current article for entries instead of loading every liveblog entry and its meta on each request; the per-post result is cached for the request.
* Removed the dead admin script and its stylesheet rule left over from the old quick-publish message, plus unused compatibility aliases.
* Replaced the 40-step polling that applied the block-editor save lock with one wp.data.subscribe() listener, and stop re-locking when the editor is already locked.
* The Microblog widget and shortcode now share one settings/query helper.
* Fixed a latent undefined-variable branch in the Liveblog admin filter when "All liveblogs" is selected.
* Rewrite rules are no longer validated by reading the full `rewrite_rules` option on every request. They are checked only when the plugin is activated, updated, or a setting that affects them is saved; a 404 on a microblog URL still triggers a one-time self-heal.
* Bumped the asset version so browsers and CDNs pick up the changes.

= 1.9.8 =
* Fixed microblog images not opening in the lightbox. The "Microblog list (plugin)" page template and the sidebar widget output their markup directly, bypassing the_content; Simple Lightbox only scans the_content and therefore never loaded its main script on those pages. With the image lightbox option enabled, image markup is now passed through Simple Lightbox's official slb_activate() template tag, so the shortcode, page template and widget behave the same.
* Fixed microblog archive and single-entry links returning 404 after an update. Rewrite rules are now validated in addition to the version signature: if the persisted rules lost the micropost archive or the /slug/ID.html rule (saving Settings > Permalinks while the plugin was inactive, an object-cache rollback, or a flush that failed during deployment), they are rebuilt automatically. A five-minute guard prevents repeated rebuilds.
* The version bump also forces one rewrite-rule rebuild on update, so existing 404s heal without a manual Permalinks save.

= 1.9.7 =
* The block editor's document sidebar now shows the parent liveblog source for a liveblog entry as a single "Liveblog source:" row, with a link back to the source article. On post-new.php it reads the mlb_parent query argument; on post.php it reads the saved _micro_live_post_id meta. Read-only, no database writes.
* Added the "Liveblog source:" Chinese translation and rebuilt the .mo catalogue.

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

= 1.9.15 =
No database migration and no settings change. This update rebuilds rewrite rules once on the next admin request and changes the liveblog feed cache key; clear the opcode cache and, most importantly, purge the LiteSpeed full-page cache (and the CDN page cache if used) so cached HTML no longer points at old `?ver=` URLs.

= 1.9.14 =
No database migration, no settings change and no functional change. The version bump rebuilds rewrite rules once on the next admin request and changes the liveblog feed cache key; clear the opcode cache plus the LiteSpeed / CDN page cache so every node serves the same PHP.

= 1.9.13 =
No database migration and no settings change. The liveblog feed cache key is derived from the plugin version, so the cached feed is rebuilt automatically after updating; clear any opcode cache (and the LiteSpeed / CDN page cache) so the new PHP is served everywhere.

= 1.9.12 =
No database migration and no settings change. Clear the page cache (LiteSpeed / CDN) after updating so the new stylesheet and image markup are served.

= 1.9.10 =
No database migration. Clear the page cache (LiteSpeed / CDN) after updating so the new image markup and lightbox links are served; rewrite rules are rebuilt once on the next admin visit.

= 1.9.8 =
No database migration. The plugin rebuilds rewrite rules once on update; if microblog links still return 404, open Settings > Permalinks and save once, then purge the LiteSpeed / CDN cache. Clear the page cache so the lightbox script is picked up.

= 1.9.7 =
No database migration and no editor settings change. Clear any page or opcode cache after updating so the new sidebar script, stylesheet and language catalogue are picked up.

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
