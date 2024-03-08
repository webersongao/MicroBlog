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
function elb_liveblog_shortcode( $atts ) {
	if ( ! empty( $atts['endpoint'] ) ) {
		$liveblog = ELB_Liveblog::fromEndpoint( $atts['endpoint'] );
	} elseif ( ! empty( $atts['id'] ) ) {
		$liveblog = ELB_Liveblog::fromId( $atts['id'] );
	} else {
		return;
	}

	return $liveblog->render();
}

add_shortcode( 'elb_liveblog', 'elb_liveblog_shortcode' );