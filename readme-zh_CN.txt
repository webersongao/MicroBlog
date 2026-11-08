=== MicroBlog ===
Contributors: webersongao
Tags: microblog, weibo, liveblog, shortcode
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.9.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

用独立内容类型发布短内容（微博 / 说说），通过短代码、小工具或可选的连载流展示；不依赖特定主题。

本文件是项目中文文档。提交 WordPress.org 官方插件库时，请使用同目录的英文 readme.txt。

== 简介 ==

MicroBlog 增加 `micropost` 内容类型，让短动态不与正式文章混在一起。每条微博都有独立固定链接（例如 `/microposts/123.html`）、归档页和 feed，也能用短代码或小工具放到任意位置。插件可用于任何主题；KoneyGao 主题额外提供可选的首页微博区块。

亮点

* 独立的 `micropost` 内容类型，URL 前缀可配置，并支持 REST。
* `[microblog]` 短代码：最新微博列表，带分页、摘要和单图 / 双图 / 九宫格自适应图片布局。
* 「微博」侧栏小工具，以及不必离开后台就能发布的「快速微博」仪表盘小工具。
* 模糊时间（「3 分钟前」）或固定日期格式，可选标题、头像与 RSS 链接。
* 可选连载（Liveblog）模块：给已发布的文章挂一条滚动更新流，文章列表提供四态显示列，并可按篇设置 10–300 秒自动刷新。
* 自动 / 浅色 / 深色色彩模式：主题输出 `data-color-mode` 时跟随主题，否则跟随访客设备。
* 完整国际化（文本域 `microblog`），自带简体中文语言包。

== 安装与启用 ==

1. 把 `MicroBlog` 目录上传到 `/wp-content/plugins/`，或在「插件 → 安装插件」中安装。
2. 在插件列表页启用。启用时会写入三组默认设置并注册重写规则。
3. 打开「工具 → MicroBlog 设置」，配置显示方式、日期格式、URL 前缀、每页条数与图片。
4. 新建页面插入 `[microblog]`，或把「微博」小工具拖到侧栏。
5. 若 `/microposts/...` 返回 404，打开「设置 → 固定链接」保存一次即可。

== 配置要点 ==

* 设置位于「工具 → MicroBlog 设置」，分三组：微博（标题、日期格式、URL 前缀、每页条数、编辑器特性、feed 与灯箱标记）、常规（连载开关与色彩模式）、连载（仅在模块开启后显示）。
* URL 前缀：1–20 位小写字母或数字，留空则使用 `microposts`；修改后会自动刷新重写规则。
* `[microblog]` 可用属性：`null_text`（默认 `(none)`）、`use_excerpt`、`q`（附加 WP_Query 参数）。开启连载后，`[micro_liveblog endpoint="..."]` 或 `[micro_liveblog live_id="..."]` 用于渲染更新流。
* 连载默认关闭；关闭不会删除已有条目和设置。文章列表的「连载显示」列有四种状态：未启用 / 隐藏 / 默认显示 / 自动刷新，只有最后一种会轮询，且浏览器标签页隐藏时自动暂停。
* 「图片灯箱」选项只改变图片标记，以适配可选的 Simple Lightbox 插件；该插件不是依赖。
* `[microarchive]` 是 KoneyGao 主题的短代码，不属于本插件。

== 常见问题 ==

= 微博显示在哪里？ =
它们是独立内容类型。用 `[microblog]`、小工具，或访问你设置的 URL 前缀归档页（默认 `/microposts`）。

= 能修改微博的网址吗？ =
可以：「工具 → MicroBlog 设置 → 微博 → URL 前缀」。若链接出现 404，再保存一次「设置 → 固定链接」。

= 支持区块编辑器和任意主题吗？ =
都支持。内容类型跟随你的编辑器设置，样式由插件自带。KoneyGao 只额外提供可选的首页区块，并共享 `data-color-mode` 色彩约定。

= 卸载会删除内容吗？ =
不会。`uninstall.php` 只删五个设置项，微博、连载内容、post meta 和 transient 都会保留，详见下一节。

= 连载模块需要 WP Cron 吗？ =
不需要。前端只对设为「自动刷新」的条目轮询，且标签页隐藏时跳过检查。

== 停用、卸载与数据清理 ==

1. 在插件列表页先「停用」；仅停用不会删除任何数据。
2. 再点击「删除」触发 `uninstall.php`。
3. 卸载脚本删除这五个 option：`microblog_micropost_data`、`microblog_general_data`、`microblog_liveblog_data`、`microblog_rewrite_signature`、`widget_microblog_widget`。
4. **刻意保留**：全部 `micropost` / `microlive` 条目、相关 post meta 与 transient —— 它们是你的内容，插件不会替你删除。
5. 若希望站点彻底干净，请先手动删除这些内容和 post meta，备份数据库后再执行卸载。

== 截图 ==

1. `[microblog]` 短代码在页面中展示最新微博。
2. 默认 `/microposts` 前缀的微博归档页。
3. 单条微博页，含作者卡片与评论表单。

这三张图对应 `screenshot-1.png` / `screenshot-2.png` / `screenshot-3.png`（1200×900）。它们属于 WordPress.org 插件 SVN **根目录的 `assets/`**，用于官方插件页展示；与插件运行时目录里的 `assets/`（css / js / images）不是同一个位置，不要混放。

== 更新日志 ==

= 1.9.1 =
* 基于 CSS 自定义属性实现自动 / 浅色 / 深色色彩模式。
* 连载 feed 地址一律带时间戳，避免 CDN 返回过期内容。
* 修复深色模式对比度；「写连载」页面默认列出已有来源。

== 升级提示 ==

= 1.9.1 =
无数据库迁移，更新后清理一次页面缓存即可。

== 致谢与许可 ==

* selectize.js 0.12.4 及 Sifter.js、MicroPlugin.js —— Apache-2.0，(c) Brian Reavis 及贡献者；随连载后台界面打包。
* 社交图标 SVG 路径来自 Automattic/social-logos。
* 基于 Samuel Coskey 与 Victoria Gitman 的 simple-microblogging 插件，并感谢 obaby（h4ck.org.cn）。
* 插件代码采用 GPLv2 或更高版本。
