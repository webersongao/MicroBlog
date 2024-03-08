<?php
// Twitter callback - save access token

require('../../../wp-blog-header.php');
$connection = new TwitterOAuth(LIVE_BLOGGING_TWITTER_CONSUMER_KEY, LIVE_BLOGGING_TWITTER_CONSUMER_SECRET, get_option('liveblogging_twitter_request_token'), get_option('liveblogging_twitter_request_secret'));
$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

update_option('liveblogging_twitter_token', $access_token['oauth_token']);
update_option('liveblogging_twitter_secret', $access_token['oauth_token_secret']);
update_option('liveblogging_enable_twitter', '1');

header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=live-blogging-options');
