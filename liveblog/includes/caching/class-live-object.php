<?php

namespace MicroLiveblogs\Caching;

class ObjectCaching {
	public static function init() {
		return new self();
	}

	public function __construct() {
		add_filter( 'mlb_feed_from_cache', array( $this, 'get_feed' ), 10, 2 );
		add_action( 'mlb_cache_feed', array( $this, 'set_feed' ), 10, 2 );
		add_action( 'mlb_purge_feed_cache', array( $this, 'purge_feed' ) );
	}

	public function get_feed( $contents, $liveblog_id ) {
		return wp_cache_get( 'mlb_' . $liveblog_id, 'micro-liveblogs-cache' );
	}

	public function set_feed( $liveblog_id, $contents ) {
		return wp_cache_set( 'mlb_' . $liveblog_id, $contents, 'micro-liveblogs-cache', $this->get_lifespan_in_seconds() );
	}

	public function purge_feed( $liveblog_id ) {
		return wp_cache_delete( 'mlb_' . $liveblog_id, 'micro-liveblogs-cache' );
	}

	public function get_lifespan_in_seconds() {
		return apply_filters( 'mlb_object_cache_lifespan', 0 );
	}
}