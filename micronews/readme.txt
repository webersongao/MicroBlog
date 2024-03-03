=== micro-micronews ===micro-micronewsmicro-micronews
Contributors: kushsharma Webersongao
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4BFA297YJX5QN
Tags: post,news,micro,short,share,link,kush,refer,concise,fast
Requires at least: 3.0.1
Tested up to: 4.5.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Spread the news in shortest possible way. Use links to refer complete article and title to concise it.

== Description ==

based on kush micro news plugin
Spread the news in shortest possible way. Use links to refer subjective data and title to concise it, a short excerpt of that news will be added with it to let user know what that news is about.

Don't you hate it when you want to publish something new that you have recently heard or read somewhere and you don't have enough time to write about the same topic again? When you can complete your message in a single sentence then why to write whole page without any sense?

For this, "MicroNews" comes to rescue. You just have to put title, little description of 1-2 lines and a reference link. Thats it!

All this data will not be published as posts in your wordpress because of which Google will not point you out for incomplete/less content on pages.
A separate table will be created while installing plugin which stores all your wordpress micro news. Advised to add in sidebar or small block, because thats how it is styled in css. No separate page will be created of micro news you are going to post, to avoid unnecessary cluttering of content.

Updates Coming soon !

How much better can a plugin be? Wait there is more :

*	You can add new news from your wordpress panel with easy GUI interface.
*	Edit/Delete old news if you have messed up something.
*	It is already styled with cool rainbow like colors(not kidding).
*	Can be installed anywhere, but looks good in sidebar.
*	Comes with widget, can be placed where you like.
*	Colors of text and title can be changed from settings.
*	Shortcode to output news in post and pages.
*	Developed and maintained by a single guy, so be kind while reporting bugs and don't forget to rate. Thats it!
    
== Installation ==

So finally you are ready to learn how to install this plugin, uhh? Don't worry, its a piece of cake.

1. Upload `micro-news-kush` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place `<?php if(function_exists('kush_micro_news_output')){echo kush_micro_news_output();} ?>` in your template file.
4. You can also use MicroNews Widget to place in your desired location.
5. Shortcode `[kushmicronews]` is also available to show news in a page or post. Read FAQ for available options to configure.

* The location where you gonna output this plugin should have atleast 200px width to present itself well.

== Frequently Asked Questions ==

= Can i limit number of micro news to display on output area? =

Yes you can Luke! You can find this in settings of Micro news. Also you can pass no. of values in function as a parameter. One more way is to use widget and change value their.

= Does this plugin slow down my website? =

Absolutely not, Its light like a feather.

= Where does all my news go? =

All of your data is stored in a seperate table inside wordpress database, you can download backup in (.sql) extension anytime you want. This feature is available in Settings page.

= How can i show news in a page or post? =

Use shortcode `[kushmicronews news="5" header="true" category="default" simple="false"]` inside wordpress editor where you want news to display itself. You can use this in any of your page or post but keep in mind that page should have enough free space for all the content.
*	'news' attribute will decide how many news should be displayed. Default: 5
*	'header' attribute will decide whether "MicroNews" header is visible. Valid options: "true" or "false". Default: "true"
*	'simple' attribute will toggle plugin visual behaviour. To show load more button and other effects. Valid options: "true" or "false". Default: "false"
*	'category' attribute will show that perticular group of news. Valid options: "default","cata","catb","catc","catd". Default: "default"

= Can i create category to seperate news with each other? =

Yes you can, use available 5 categories to choose from. If you don't, it is set to "default" automatically. This is helpful if you want to show different news in different places on same site.

= Will it look like the rest of my site? =

Many of your website styles will automatically blend in nicely. I am trying to make it look as cool as possible. However, you can make changes to styles by overiding your own CSS in your stylesheet.

= Do i have to do anything else? =

No. Just sit back and start adding micro news.

== Screenshots ==

1. Preview of output
2. Adding new micro news
3. Editing/Deleating old micro news.
4. Widget

== Changelog ==

= 1.0.0 =
* Added option: Modify date format.

= 1.6.6 =
* Bug Fix: Language slug

= 1.6.5 = 
* Plugin Internationalized.
* Added option: Open link in new/self tab.

= 1.6.4 =
* Bug fix: css opacity in header.
* Added option: Enable/disable header from widget.

= 1.6.3 =
* Added option: Enable editors to access plugin. 

= 1.6.2 =
* Added option: Change background,text color of Title "MicroNews".

= 1.6.1 =
* Small bug fixes.

= 1.6.0 =
* Added Feature: To categories news into 5 different groups.
* Updated database to handle new column. Please hit update button in Settings.
* Shorcode: Option to enable or disable visual attributes through "simple" tag.

= 1.5.3 =
* Added option: Update database table to charset UTF8.

= 1.5.2 =
* Added option to change text of "Read Full Story".

= 1.5.1 =
* Set default value of some variables[minor bug fix]

= 1.5 =
* New Feature: Load more news by appending instead of swapping
* Bug fix 

= 1.4.5 =
* Minor Bug fix

= 1.4.4 =
* Minor bug fix[improper stylesheet version shows cached copy]

= 1.4.3 =
* Minor CSS fix

= 1.4.2 =
* New Feature: Option to load more news.

= 1.4.1 =
* Added feature of shortcode to output news in post & pages
* Can change link color
* Minor Visual Bug Fix.

= 1.4.0 =
* Improved UI of configuration page

= 1.3.5 =
* Misc bug fix.

= 1.3.4 =
* Option to change title and text color.

= 1.3.3 =
* Added option to change widget title.

= 1.3.2 =
* Some CSS fix

= 1.3.1 =
* Minor Bug Fixes
* Fix of overwriting admin menu.

= 1.3 =
* Added close button while updating news.

= 1.2.3 =
* Updated Readme file [Not a major update by the way.]

= 1.2.2 =
* Added html parsing.

= 1.2.1 =
* Small bug fix, forgot to add backup file required for it.

= 1.2 =
* Backup of micronews in a (.sql) extension file is added in Settings.

= 1.1 =
* Reference link hover effect can be disabled.
* Borders can be turned off now(for non fancy themes).

= 1.0.1 =
* Database connectivity fixed.

= 1.0 =
* This is the first version of this plugin, tried and tested.
* Reference Link will only visible when you input something in input field otherwise it will be hidden.
* Widget Added

== Upgrade Notice ==

= 1.6.0 =
* URGENT : Database update required, use button in Settings page.

= 1.2 =
* Backup feature added.

= 1.0.1 =
* URGENT : Database error fixed.

== Arbitrary section ==

In settings section you have multiple choices to opt, each of them serve a purpose which are :

* Number of news to display --
The number of micro news which is going to be displayed on output. This can be manually given in function too : For eg - kush_micro_news_output(5);

* Enable Colorful Borders --
Uused to enable and disable borders comes on the left side of each news.

* Enable link hover effect --
Enable and disable hover effect comes over Refer Link.

* Text and Title color --
You can change the color of news text and title from available options but if you want to add your own then use hexadecimal format. Eg. #E2E2E2

* Allow HTML parsing while adding news --
This enable and disable HTML parsing when you are going to add new news or update an old one. If the box is checked, then all html tags will be parsed as DOM. But if it is disabled then HTML tags will treat as HTML entities. Try not to use improper markup if HTML parsing is enabled otherwise it could break up your whole site.
**Note:** Also avoid using <h1-6> heading tags in title, because it will overide my default heading tag and mess up markup.
**Note:** When HTML parsing is disabled, any new line or line break will be converted to <br> tag automatically. When you try to edit this news, you will see <br> tags along with excerpt. To make them work, enable HTML parsing again just before updating.

* Access --
Sites with multiple type of user access, i.e. administrator and editor, can use plugin. Once administrator enables this option, editors has full control over the plugin posts as well as settings.

* Styling --
If you want to customize the look of this plugin, feel free to do so by editing CSS file present in "assets/css/style.css". For now you have to do it manually, later i will add GUI interface of customizing it.