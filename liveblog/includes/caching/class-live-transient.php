<?php

namespace MicroLiveblogs\Caching;

class TransientCaching {
	public static function init() {
		return new self();
	}

	public function __construct() {
		add_filter( 'mlb_feed_from_cache', array( $this, 'get_feed' ), 10, 2 );
		add_action( 'mlb_cache_feed', array( $this, 'set_feed' ), 10, 2 );
		add_action( 'mlb_purge_feed_cache', array( $this, 'purge_feed' ) );
	}

	public function get_feed( $contents, $liveblog_id ) {
		return get_transient( 'mlb_' . $liveblog_id . '_cache' );
	}

	public function set_feed( $liveblog_id, $contents ) {
		return set_transient( 'mlb_' . $liveblog_id . '_cache', $contents, $this->get_lifespan_in_seconds() );
	}

	public function purge_feed( $liveblog_id ) {
		return delete_transient( 'mlb_' . $liveblog_id . '_cache' );
	}

	public function get_lifespan_in_seconds() {
		return apply_filters( 'mlb_transient_cache_lifespan', 0 );
	}
}
