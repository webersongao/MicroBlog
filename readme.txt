
=== 微博 MicroBlog===

Contributors: WebersonGao
Donate link: https://www.webersongao.com 
Tags: tweet, tweets, microblog, microblog, micropost
Requires at least: 6.0
Tested up to: 6.4.3
Stable tag: 1.7.0
License: GPLv2 or later

== 核心 ==

将 微博 添加到您的网站；在小部件中显示微博或使用短代码显示微博。

== 描述 ==

这个简单的插件使您可以轻松发布短消息，如微博、说说。 这些消息不会出现在您的帖子流中； 相反，您可以在侧边栏的小部件中显示它们。 您还可以在任何帖子或页面中使用`[microblog]`短代码来显示它们。

使用时，请在您的仪表板中查找新的`微博`管理面板。 点击`发微博`，然后以您通常撰写文章的方式撰写微博。 如果您给微博添加标题，它将以粗体显示，或者你可以直接隐藏微博。

然后，要么将小部件添加到侧边栏，要么将`[microblog]`短代码添加到您的网站中，就这样！

`[microblog]`短代码支持几个选项：

* **null_text**：如果未返回结果，则显示此文本。 默认为`(none)`。

* **use_excerpt**：如果定义，则使用帖子摘录而不是整个内容。 我们建议撰写简短的微博，但如果您更喜欢撰写较长的微博，则可以使用此选项将其截断。 不幸的是，Wordpress摘录不允许链接或其他html，请使用插件[高级摘录](http://wordpress.org/extend/plugins/advanced-excerpt/)来解决此问题！

* **q**：要添加到查询中的任意＆分隔参数。 有关可用语法，请参阅[WP_Query](http://codex.wordpress.org/Class_Reference/WP_Query/#Parameters)页面。 例如，要仅按升序显示来自作者`jack`的帖子，您将写入`[microblog q="author_name=jack&order=ASC"]`。

也许您可以在面板上调整`num`和`show_date`，

* **num**：要显示的微博数量。 默认为`5`。 最小3到最大20：[3,20]

* **show_date**：如果选中，则创建日期将显示在微博之前。

然后输出可以使用CSS进一步格式化。

== 安装 ==

1、手动解压并上传： 尝试手动解压插件 zip 包，并将解压后的插件文件夹通过 FTP 或文件管理器上传到 WordPress 的 wp-content/plugins 目录中。
2、特别说明：如果发现后台上传插件的 zip 包的方式安装，提示；“不支持的归档格式”，请自行压缩或FTP上传


== 其他说明 ==

如果您无法查看您的微博，请尝试访问您的固定链接首选项或者修改微博slug，然后点击`保存更改`。

简单说就是：

1、上传文件包-安装激活
2、新建页面，复制粘贴 [microblog] 到你期待展示的“页面”中，保存发布就可以了
3、如果想使用小工具，自己后台添加，前台查看即可
4、如果想修改默认配置，请通过 ’工具‘-> ’微博设置‘， 进入 微博控制面板

还不满意？还有bug？不要犹豫，底部找我邮箱 联系我。


== 后续开发计划 ==

1、参考easy-liveblogs（doumao team开发） 和 liveblog（WordPress.com VIP, Big Bite Creativ...开发）实现弹窗评论，以及评论外露
2、支持在编辑器内嵌入 微博话题 及 @功能，被@的人会受到邮件通知（类似于分布式微博）
3、远期规划：
     更漂亮，更美观的短代码UI，甚至开发微博专属主题

暂时想到这些，更多功能，等你来提~


== 更新日志 ==

1.7.0
新增：1、微博话题 
     2、支持 REST API
     3、微博转发（暂未全部开放）
优化：调整后台js替换文案逻辑，改为原生字符串文案~

1.6.1
新增：时间格式 及 是否开启全站Feed订阅
优化：插件激活写入默认配置，插件卸载清理所有配置项，不留垃圾，不拉屎~

1.6
新增：新增仪表盘快捷发布
优化：按钮 写文章，改为 发微博等文案

1.5
优化：优化代码结构，提升查询性能 减少URl刷新

1.4
优化：小工具底部工具条不展示时，调整异常的间距
修复bug：当微博关闭评论后，评论按钮依然显示的bug

1.3 
新增：
URL slug 支持自定义，例如改为microblog，则微博地址为 microblog/post_id.html ，Rss地址为microblog/feed， slug默认为microposts 

1.2 
新增：
.1编辑器跟随站点配置（古腾堡或经典编辑器）
.2编辑器特性选项支持自定义（作者，特色图片 微博摘要）
优化：
.1插件激活时，未初始化配置项的bug

1.1 
新增支持微博图片九宫格，支持lightbox开关

1.0 
初次发布（编辑/发布，短代码插入，小工具插入，控制面板等基本功能）


欢迎多提意见，如果你也想参与折腾，欢迎联系~

邮箱：Gao@btbk.org
推特：https://twitter.com/WebersonGao
更多介绍：https://www.webersongao.com/tag/microblog




ToDo：

is_user_logged_in 不可用
mlb_get_liveblogs_by_status( 'used' ); 不可用