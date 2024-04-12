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
		$liveblog = MLB_Liveblog::fromEndpoint( $atts['endpoint'] );
	} elseif ( ! empty( $atts['id'] ) ) {
		$liveblog = MLB_Liveblog::fromId( $atts['id'] );
	} else {
		return;
	}

	return $liveblog->render();
}

add_shortcode( 'micro_liveblog', 'mlb_liveblog_shortcode' );