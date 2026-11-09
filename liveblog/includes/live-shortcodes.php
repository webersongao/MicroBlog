<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mlb_liveblog_shortcode( $atts ) {
	if ( ! empty( $atts['endpoint'] ) ) {
		$liveblog = MLB_Liveblog::mlb_fromEndpoint( $atts['endpoint'] );
	} elseif ( ! empty( $atts['live_id'] ) ) {
		$liveblog = MLB_Liveblog::mlb_fromId( $atts['live_id'] );
	} else {
		return '<p>' . esc_html__('MicroLive liveblog error: live_id or endpoint cannot be empty.', MICROBLOG_DOMAIN) . '</p>';
	}

	return $liveblog->mlb_content_render();
}

add_shortcode( 'micro_liveblog', 'mlb_liveblog_shortcode' );