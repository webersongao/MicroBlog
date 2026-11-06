<?php

namespace MicroLiveblogs\API;

if (! defined('ABSPATH')) {
	exit;
}

class LiveFeed {
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
			'microlives/v1',
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
	 * 与页头结构化数据共用 mbfun_get_live_feed_local() 的 5 分钟缓存：
	 * 前台默认 30 秒轮询一次，不缓存的话每次都要重建整个条目列表。
	 *
	 * @param \WP_REST_Request $request
	 * @return array|\WP_Error
	 */
	public function feed( \WP_REST_Request $request ) {
		$feed = mbfun_get_live_feed_local( $request->get_param( 'id' ) );

		if ( ! is_array( $feed ) ) {
			return new \WP_Error(
				'mlb_liveblog_not_found',
				__( 'The liveblog does not exist.', MICROBLOG_DOMAIN ),
				array( 'status' => 404 )
			);
		}

		return $feed;
	}
}
