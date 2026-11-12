<?php

namespace MicroLiveblogs\API;

if (! defined('ABSPATH')) {
	exit;
}

class LiveEntry {
	public $id;
	public $title;
	public $content;
	public $time;
	public $datetime;

	public function __construct() {
	}

	public static function fromPost( $_post ) {
		global $post;

		/* 连载条目是自定义循环，postdata 应由独立的 WP_Query 承载。
		   WP_Query::setup_postdata() 会广播 the_post 并带上所属 query 对象；
		   若沿用主查询对象，第三方（如 WPJAM 相关文章）会误判为主循环，
		   反复往 the_content 追加回调，导致主文出现多份「相关文章」。
		   这里只用核心公开类与方法，不改任何 hook 表、不改第三方输出。 */
		$previous_post = $post;
		$context       = new \WP_Query();
		$context->post = $previous_post instanceof \WP_Post ? $previous_post : null;

		$post = new \WP_Post( (object) $_post );

		$context->setup_postdata($post);

		$instance           = new self();
		$instance->id       = $post->ID;
		$instance->title    = $post->post_title;
		$instance->content  = apply_filters( 'the_content', $post->post_content );
		$instance->time     = get_the_date( 'H:i', $post );
		$instance->date     = get_the_date( 'Y-m-d', $post );
		$instance->datetime = get_the_date( 'c', $post );
		$instance->html     = $instance->get_html();
		$instance->modified = get_the_modified_date( 'c' );
		$instance->author = get_the_author();

		/* 复位到进入前的文章；广播随 $context 走，不再影响主查询。 */
		if ( $context->post ) {
			$context->reset_postdata();
		} else {
			$GLOBALS['post'] = $previous_post;
		}
		$post = $GLOBALS['post'];

		return apply_filters( 'mlb_api_entry', $instance );
	}

	public function get_html() {
		global $post;

		$args = array(
			'class' => array( 'mlb-liveblog-post' ),
		);

		$classes = apply_filters( 'mlb_liveblog_list_item_classes', implode( ' ', $args['class'] ) );

		$content = '<li data-mlb-post-datetime="' . get_post_time( 'U' ) . '" data-mlb-post-id="' . esc_attr( $post->ID ) . '" class="' . esc_attr( $classes ) . '">';

		ob_start();

		mbfun_get_live_template_part( 'post' );

		$content .= ob_get_clean();

		$content .= '</li>';

		return $content;
	}
}
