<?php
// If uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// Posts that were turned in to liveblogs are not removed,
// but the liveblog entries are removed.

global $wpdb;

$entries = get_posts( array( 'post_type' => 'elb_entry', 'post_status' => 'any', 'numberposts' => -1 ) );

foreach ( $entries as $entry ) {
	wp_delete_post( $entry->ID, true );
}

$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_elb_liveblog'" );
$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_elb_is_liveblog'" );
$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_elb_status'" );

delete_option( 'elb_settings' );
