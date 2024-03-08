<?php

namespace EasyLiveblogs\API;

class Entry {
	public $id;
	public $title;
	public $content;
	public $time;
	public $datetime;

	public function __construct() {
	}

	public static function fromPost( $_post ) {
		global $post;

		$post = new \WP_Post( (object) $_post );

		setup_postdata($post);

		$instance           = new self();
		$instance->id       = $post->ID;
		$instance->title    = $post->post_title;
		$instance->content  = apply_filters( 'the_content', $post->post_content );
		$instance->time     = get_the_date( 'H:i', $post );
		$instance->datetime = get_the_date( 'c', $post );
		$instance->date     = get_the_date( 'Y-m-d', $post );
		$instance->html     = $instance->get_html();
		$instance->modified = get_the_modified_date( 'c' );
		$instance->author = get_the_author();

		wp_reset_postdata();

		return apply_filters( 'elb_api_entry', $instance );
	}

	public function get_html() {
		global $post;

		$args = array(
			'class' => array( 'elb-liveblog-post' ),
		);

		$classes = apply_filters( 'elb_liveblog_list_item_classes', implode( ' ', $args['class'] ) );

		$content = '<li data-elb-post-datetime="' . get_post_time( 'U' ) . '" data-elb-post-id="' . esc_attr( $post->ID ) . '" class="' . esc_attr( $classes ) . '">';

		ob_start();

		elb_get_template_part( 'post' );

		$content .= ob_get_clean();

		$content .= '</li>';

		return $content;
	}
}
