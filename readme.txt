
=== 微博 Microblog===

Contributors: WebersonGao sgcoskey, vgitman, VegetarianZombie
Donate link: https://www.webersongao.com/ https://boolesrings.org
Tags: tweet, tweets, microblog, microblog, micropost
Requires at least: 6.0
Tested up to: 6.4.3
Stable tag: 1.0
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

Nothing unusual here!

== Screenshots ==

1. A rendered widget containing my two microposts
2. The widget configuration box

== Other notes ==

If you are having trouble viewing your microposts, try visiting your permalinks preference pane and clicking `Save changes`.

== Changelog ==

1.0 initial release


简单说就是，
1、上传文件包-安装激活-插件设置-进入控制面板，
2、新建页面，复制粘贴 [microblog] 这个内容，保存发布就可以了
3、如果想使用小工具，自己添加，前台查看即可

不满意？打开微博的控制面板，调整必要配置项就可以了，还有bug？联系我。

后续开发计划：

1、将所有参数移至控制面板 （限定作者，排序方式等）
2、支持中英文切换 + 评论外漏（目前仅展示数量）
3、支持图片九宫格
4、还没想好~ 

欢迎多提意见，如果你也想折腾，欢迎联系~

邮箱：Gao@btbk.org
推特：https://twitter.com/WebersonGao
更多介绍：https://www.webersongao.com/tag/microblog
