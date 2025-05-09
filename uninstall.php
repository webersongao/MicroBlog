<?php
// If uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// Posts that were turned in to liveblogs are not removed,
// but the liveblog entries are removed.

/*  卸载时清空所有数据 请 慎重使用

global $wpdb;

$entries = get_posts( array( 'post_type' => 'microlive', 'post_status' => 'any', 'numberposts' => -1 ) );

foreach ( $entries as $entry ) {
	wp_delete_post( $entry->ID, true );
}

$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_micro_live_post_id'" );

$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_micro_post_live_enable'" );
$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_micro_post_live_autoPolling'" );
$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_micro_post_live_status'" );

delete_option( 'microblog_liveblog_data' );

delete_option( 'theme_microblog_data' );
delete_option( 'widget_microblog_widget' );
// 还可以执行其他清理操作，如删除数据库条目等

*/
