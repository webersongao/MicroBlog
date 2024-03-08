<?php

namespace EasyLiveblogs\API;

class Feed {
	/**
	 * Setup.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register' ) );
	}

	/**
	 * Register route.
	 *
	 * @return void
	 */
	public function register() {
		register_rest_route(
			'easy-liveblogs/v1',
			'/liveblog/(?P<id>\d+)',
			array(
				'methods'  => 'GET',
				'permission_callback' => '__return_true',
				'callback' => array( $this, 'feed' ),
			)
		);
	}

	/**
	 * Construct the feed.
	 *
	 * @param \WP_REST_Request $request
	 * @return array
	 */
	public function feed( \WP_REST_Request $request ) {
		if ( $feed = apply_filters( 'elb_feed_from_cache', false, $request->get_param( 'id' ) ) ) {
			return $feed;
		}

		$feed = FeedFactory::make( $request->get_param( 'id' ) );

		do_action( 'elb_cache_feed', $request->get_param( 'id' ), $feed );

		return $feed;
	}
}
