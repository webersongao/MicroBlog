=== Live Blogging Plus ===
Contributors: vidyut, chrisnorthwood, cheche
Donate link: http://vidyut.net/
Tags: live, micro, JavaScript, blogging, event, automatic updating, auto updating
Requires at least: 4.4
Tested up to: 4.9.5
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


== Description ==
Live Blogging is a plugin that allows you to insert micro/live blogs into posts with automatic updating of the content.

Note: I am not really a good coder. More like a copy-paste hacker. I keep this plugin updated, but adding functionality and such may not be so prompt - if at all it happens. I am mostly updating this to include bugfixes.

Special thanks to <a href="https://wordpress.org/support/users/willjw/">Will Woodward</a> for bugfixes offered in the support section.

This plugin is based on the <a href="http://wordpress.org/extend/plugins/live-blogging/" target = "_blank">Live Blogging plugin by Chris Northwood</a>. Fixes some outdated code and allows adding links to the live blog in Twitter updates.

To see this plugin in use, it's probably easiest to watch [this screencast](http://www.pling.org.uk/static/Live_Blogging_for_WordPress_2.0.swf).

Live Blogging is a plugin developed to support blogs that are doing live micro blogging of running events.

Using WordPress 3.0's custom post types, the plugin allows you to create these micro blog entries in a stripped down version of the normal post edit screen. These micro blog entries then get included in a post which has been activated as the microblog.

By default, the plugin uses AJAX polling to allow your readers to instantly update what they are viewing, including any new comments posted on the post. For professional bloggers, Meteor is supported, which gives the smoothest updating experience for your viewers and also significantly reduces the load on your server. (Note: This meteor part of the code has not been updated since the original. I don't know how well it will work now.)

Additionally, the plugin also supports integration with Twitter - if activated, the first 239 characters of each live blog entry (including permalink to live blog page) will be posted to Twitter when they are made.

Using this plugin will give you an advantage over your competitors if you're blogging from live events - your readers will get updates quicker, and it will integrate into your blog better than competing systems, such as CoverItLive.

== Installation ==

Live Blogging v2, like version 1, requires PHP version 5.2 and WordPress 4.4 or later. Please make sure you have these before you attempt to use the plugin. If you get errors whilst activating, it is likely that you are using a version of PHP before version 5.2.

Live Blogging is installed and activated as per normal plugins. Once it has been activated, please read the FAQ about how to use it.

Once you have installed Live Blogging, then it is most sensible to proceed to
the Live Blogging settings, which is included in your 'Settings' panel in the
admin area as 'Live Blogging'. From here, you can enable functionality such as
posting to Twitter, changing the method used for live blog updating and
customising the style of the live blog entries.

If you have installed a Meteor server and intend to use it to stream updates,
you must also enter your Meteor server details and set the live blog update
method to 'Stream using Meteor'.

== Frequently Asked Questions ==

= How do I start live blogging? =

The very first thing you need to do is select a post which to use as the shell
for your live blog. Either create a new post, or select an existing one to edit,
and then to the right of your post screen, there should be an option entitled:
"Enable live blogging on this post". Without this option being enabled, you
will be unable to add new live blog entries to this post!

The final step is very important. You must insert the shortcode [liveblog]
somewhere within your post which indicates where the liveblog is to appear.

Now, your live blog is set up! You can select 'Add New' from the sidebar under
'Live Blog Entries', choose the live blog you want the update to apply to from
the list in the sidebar, and then create your entry as per usual.

Once you have finished with your live blog, untick the ``Enable live blogging on
this post'' box. This will stop automatic updating and any new updates to be
added to the live blog, but will preserve any existing live blogging
entries for posterity.

= How do I upgrade my live blogs from version 1.x of the old plugin? =

If the plugin detects that old data from the plugin exists, then it will prompt
you to upgrade on the options screen. Also, a 'Live Blogging Migration' option
will exist under the Tools menu. Run that tool and follow the prompts to upgrade
all your old entries to the new version.

Please note that live blogs created in previous versions of the software can
not have new entries added to them.

Please note that it is recommended that you disable posting to Twitter when
running a migration, as the migration will cause all legacy entries to be
posted to Twitter.

= If I change the time on a live blog entry to appear in the past, then it doesn't appear in the right order during automatic updating? =

Unfortunately this is a limitation on the system - any new entries must appear
at the top of the live blog. Readers who come in after the blog was posted, or
users who subsequentally refresh will see the blog posts in the correct order.

= I'm using TwentyTen and comment updating doesn't look right =

Add:

`add_filter('live_blogging_build_comments', 'twentyten_liveblogging_comments');
function twentyten_liveblogging_comments($a)
{
	$a['callback'] = 'twentyten_comment';
	return $a;
}`

to your functions.php in your theme.

= Comment updating does not appear to work on my theme =

For comment updating to work, you must be using a reasonably standard comment
setup. That is, your theme must generate comments using `wp_list_comments` and
put them in an element with an ID of `commentlist`. If your theme does not do
these things, it is recommended that you disable comment updating in the option
panel.

If your theme does support this, but comment updating still does not work
correctly, it is possible that your theme is calling `wp_list_comments` with
non-default arguments. It is possible to account for this using a filter.

Using the `live_blogging_build_comments` filter, you can return the arguments
which your theme uses to call to `wp_list_comments` to get the same effect as
your default theme. For an example of how to do this, see above.

= I imported my live blog from version 1 of the software, and am continuing to use the same live blog, but comment auto-updating isn't working? =

Sorry, this is a design issue with the software. It is recommended that you do
not re-use any imported live blogs in this way.

= What is Meteor, and how do I use it? =

Meteor is a streaming web server that allows you to instantly "push" updates out
to your readers. This is the technology Twitterfall uses, and the advice of the
Twitterfall creators was useful in developing this functionality. Using Meteor
allows you to lower your server load, as well as quicker updates for your
readers.

There is a catch, however. Meteor is a web server separate to what servers your
normal website and requires special configuration to set up, and your own server
or VPS to run it on. Shared hosting is, typically, unsuitable.

If you are a professional blogger, running your blog from a server where you
can install things as root, and are wishing to reduce server load, using the
plugin with Meteor is highly recommended.

For more about Meteor, please see the [Meteor website](http://meteorserver.org/).

= Does deleting a post from inside WordPress also delete it from Twitter? =

Yes, it does!

= Editing a post does not change the tweet the software generated =

It is impossible to edit tweets. A decision was made not to implement this
functionality, as it may result in out-of-order or duplicate tweets in a user's
timeline. If you really need to delete a tweet, it is recommended that you
either delete the tweet manually, or delete the post and create a new one.

= Can I get an RSS feed containing just my live blog entries? =

Yes - if permalinks are enabled. Navigate to: WP-URL/feed/?post_type=liveblog_entry&liveblog=ID
where WP-URL is the address of your WordPress site, and replace ID (right
at the end) with the ID of the post containing that live blog.

= I'm getting multiple bookmarking icons or similar on every live blog post =

Some bookmarking plugins work by adding their icons at the end of every post.
Obviously, when you're microblogging and adding multiple entries per page, this
can get very tiresome. The options screen allows you to workaround this.

In 'Advanced Settings' at the bottom is a list of actions which are unhooked
from the `the_content` display filter, so they do not show up. To hide a
bookmarking plugin, you must add the name of the function which is called by the
`the_content` filter to this list. The green + allows you to add more text
boxes, and then red - allows you to remove a function from being unhooked.

= Can I add a hashtag to my tweets? =

Sure! Just create a custom field on the post/page which contains the live blog
called 'liveblogging_hashtag' and it will be appended to all of your tweets.

(Note that this custom field should not contain the # character, that will be
added automatically)

= AJAX updating doesn't work with multi-site and domain mapping =

This forum topic (http://wordpress.org/support/topic/ajax-in-subsite) should
resolve the issue for you.

== Changelog ==
= 1.3 =

* Increased the length of tweet to 240 to take advantage of the longer tweets now allowed on Twitter.

= 1.2 =

* Added several fixes for broken functionality
* Bugfixes offered by <a href="https://wordpress.org/support/users/willjw/">Will Woodward</a> in the support section.

= 1.1 =

* Fixed issue with plugin appearing twice in the plugin administration page.

= 1.0 =

* Continuing from Live Blogging plugin
* Changed Twitter OAuth tokens
* Add url to Tweeted update

== Credits ==

= Original Plugin =

* <a href="http://wordpress.org/extend/plugins/live-blogging/" target = "_blank">Live Blogging plugin by Chris Northwood</a>
* <a href="http://wordpress.org/support/topic/update-twitteroauth-to-publish-twitter" target = "_blank">Update for Twitter OAuth contributed by cheche</a>

= Internationalisation =

* Chinese language: Haoxian Zeng
* Persian language: Rasoul Moshrefizadeh
* Lithuanian language: Vincent G from http://www.host1free.com/
