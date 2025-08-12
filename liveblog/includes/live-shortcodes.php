<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Liveblog shortcode.
 *
 * @param array $atts
 * @return string
 */
function mlb_liveblog_shortcode( $atts ) {
	if ( ! empty( $atts['endpoint'] ) ) {
		$liveblog = MLB_Liveblog::mlb_fromEndpoint( $atts['endpoint'] );
	} elseif ( ! empty( $atts['live_id'] ) ) {
		$liveblog = MLB_Liveblog::mlb_fromId( $atts['live_id'] );
	} else {
		return '<p> MicroLive连载 运行异常：live_id 或 endpoint 不可为空 </p>';
	}

	return $liveblog->mlb_content_render();
}

add_shortcode( 'micro_liveblog', 'mlb_liveblog_shortcode' );