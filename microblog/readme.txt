
=== 微博 MicroBlog===

Contributors: WebersonGao sgcoskey, vgitman, VegetarianZombie
Donate link: https://www.webersongao.com/ https://boolesrings.org
Tags: tweet, tweets, microblog, microblog, micropost
Requires at least: 6.0
Tested up to: 6.4.3
Stable tag: 1.6
License: GPLv2 or later

Add a microblog to your site; display the microposts in a widget or using a shortcode.

== Description ==

This simple plugin allows you to easily post short messages such as thoughts and updates.  These messages will not appear in your stream of posts; instead you can display them in a widget in yours sidebar.  You can also display them in any post or page by using the `[microblog]` shortcode.

To get started, just look for the new `Microposts` administration panel in your dashboard.  Click `Add new` and then compose a short message in the same way that you normally compose your posts.  If you give the micropost a title, then it will be displayed in bold and used as the first few words of the micropost.

Then, either add the widget to your sidebar or add the `[microblog]` shortcode into your site, and that's it!


The `[microblog]` shortcode supports several options:

* **null_text**: If no results are returned, shows this text.  Defaults to `(none)`.

* **date_format**: If showing the date, this php date format will be used.  The default is the Date Format value from the General Settings page.  I recommend `"F j"`, which displays as "May 12".

* **use_excerpt**: If defined, use the post excerpt instead of the entire contents.  We recommend writing short microposts, but if you prefer to write longer ones, this can be used to truncate them.  Unfortunately, Wordpress excerpts don't allow links or other html, use the plugin [Advanced Excerpt](http://wordpress.org/extend/plugins/advanced-excerpt/) to remedy this!

* **q**: Arbitrary &-separated arguments to add to the query.  See the [WP_Query](http://codex.wordpress.org/Class_Reference/WP_Query/#Parameters) page for available syntax.  For example, to show only posts from author `jack` in ascending instead of descending order, you would write `[microblog q="author_name=jack&order=ASC"]`.

mayb you can adjust number and show_date on Pannel,

* **num**: The number of microposts to show.  Defaults to `5`.  min 3 to max 20 : [3,20]

* **show_date**: If checked, the creation date will precede the microposts.

then The output can then be further formatted using CSS. 

== Installation ==

1、Manual extraction and upload: Attempt to manually extract the plugin zip package, and then upload the extracted plugin folder to the wp-content/plugins directory of your WordPress installation using FTP or a file manager.

2、##Special note: 
Due to file compression issues, installing the plugin via uploading the zip package through the WordPress backend is temporarily unsupported.

== Screenshots ==

1. A rendered widget containing my two microposts
2. The widget configuration box
3. MicroBlog Administration Panel

== Other notes ==

If you are having trouble viewing your microposts, try visiting your permalinks preference pane and clicking `Save changes`.

== Changelog ==

1.5
Optimization: Optimized code structure to improve query performance and reduce URL refresh.

1.4
Optimization: Adjusted abnormal spacing when the bottom toolbar of the widget is not displayed.
Bug Fix: Fixed a bug where the comment button still appeared when comments were disabled for a microblog.

1.3
Additions:
Support for customizing URL slug. For example, changing it to "microblog" would result in the microblog URL being microblog/post_id.html and the RSS feed URL being microblog/feed. The default slug is microposts.

1.2
New:
- Editor follows site configuration (Gutenberg or classic editor).
- Editor feature options support customization (author, featured image, microblog excerpt).
Optimization:
- Fixed bug of uninitialized configuration items when plugin is activated.

1.1
New: Supports microblog image grid, with support for lightbox toggle.

1.0
Initial release (basic functions including editing/publishing, shortcode insertion, widget insertion, control panel).

In simple terms:
1. Upload the file package - install and activate - configure the plugin - enter the control panel (use server-side file management - upload).
2. Create a new page, copy and paste `[microblog]` into the "page" where you want it displayed, then save and publish.
3. If you want to use widgets, add them in your backend, and view them on the frontend.

Not satisfied yet? Open the microblog control panel, adjust the necessary configurations, and if there are still bugs, contact me.

Future development plans:

1. Move all parameters to the control panel (completed).
2. Support switching between Chinese and English + external comments (in progress).
3. Support image grid (completed).
4. Still thinking~

More features await your suggestions!

Feel free to provide feedback, and if you're interested in tinkering, feel free to contact me~

Email: Gao@btbk.org
Twitter: https://twitter.com/WebersonGao
More information: https://www.webersongao.com/tag/microblog

