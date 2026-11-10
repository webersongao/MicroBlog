<?php

namespace MicroLiveblogs\API;

if (! defined('ABSPATH')) {
	exit;
}

class LiveFeed {
	/* Setup */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register' ) );
	}

	/* Register route */
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

	public function feed( \WP_REST_Request $request ) {
		/* 登录用户的条目 HTML 含权限相关的「编辑」链接，禁止被 CDN / 页面缓存共享 */
		if ( is_user_logged_in() ) {
			nocache_headers();
		}

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
