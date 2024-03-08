<?php

namespace EasyLiveblogs\API;

class FeedFactory {

	/**
	 * Make feed for liveblog.
	 *
	 * @param int $liveblog_id
	 * @return array
	 */
	public static function make( $liveblog_id ) {
		$liveblog = elb_get_liveblog( $liveblog_id );

		$entries = self::get_entries( $liveblog_id );

		$feed = array(
			'id'          => $liveblog->ID,
			'title'       => $liveblog->post_title,
			'url'         => get_permalink( $liveblog->ID ),
			'status'      => get_post_meta( $liveblog->ID, '_elb_status', true ),
			'last_update' => $entries[0]->datetime ?? get_post_modified_time( 'Y-m-d H:i:s', false, $liveblog ),
			'updates'     => $entries,
		);

		return apply_filters( 'elb_api_feed', $feed, $liveblog, $liveblog->ID );
	}

	/**
	 * Get the liveblog entries.
	 *
	 * @param int $liveblog_id
	 * @return array
	 */
	public static function get_entries( $liveblog_id ) {
		$args = array(
			'post_type'  => 'elb_entry',
			'showposts'  => -1,
			'meta_key'   => '_elb_liveblog',
			'meta_value' => $liveblog_id,
		);

		return array_map(
			function( $post ) {
				return Entry::fromPost( $post );
			},
			get_posts( apply_filters( 'elb_api_get_entries_args', $args, $liveblog_id ) )
		);
	}
}
