<?php

namespace EasyLiveblogs\Caching;

class ObjectCaching {
	public static function init() {
		return new self();
	}

	public function __construct() {
		add_filter( 'elb_feed_from_cache', array( $this, 'get_feed' ), 10, 2 );
		add_action( 'elb_cache_feed', array( $this, 'set_feed' ), 10, 2 );
		add_action( 'elb_purge_feed_cache', array( $this, 'purge_feed' ) );
	}

	public function get_feed( $contents, $liveblog_id ) {
		return wp_cache_get( 'elb_' . $liveblog_id, 'easy-liveblogs' );
	}

	public function set_feed( $liveblog_id, $contents ) {
		return wp_cache_set( 'elb_' . $liveblog_id, $contents, 'easy-liveblogs', $this->get_lifespan_in_seconds() );
	}

	public function purge_feed( $liveblog_id ) {
		return wp_cache_delete( 'elb_' . $liveblog_id, 'easy-liveblogs' );
	}

	public function get_lifespan_in_seconds() {
		return apply_filters( 'elb_object_cache_lifespan', 0 );
	}
}
