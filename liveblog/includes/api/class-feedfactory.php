<?php

namespace MicroLiveblogs\API;

class FeedFactory {

	/**
	 * Make feed for liveblog.
	 *
	 * @param int $liveblog_id
	 * @return array
	 */
	public static function make( $liveblog_id ) {
		$liveblog = mbfun_get_live_liveblog( $liveblog_id );

		$entries = self::get_entries( $liveblog_id );

		$feed = array(
			'id'          => $liveblog->ID,
			'title'       => $liveblog->post_title,
			'url'         => get_permalink( $liveblog->ID ),
			'status'      => get_post_meta( $liveblog->ID, '_micro_post_live_status', true ),
			'last_update' => $entries[0]->datetime ?? get_post_modified_time( 'Y-m-d H:i:s', false, $liveblog ),
			'updates'     => $entries,
		);

		return apply_filters( 'mlb_api_feed', $feed, $liveblog, $liveblog->ID );
	}

	/**
	 * Get the liveblog entries.
	 *
	 * @param int $liveblog_id
	 * @return array
	 */
	public static function get_entries( $liveblog_id ) {
		$args = array(
			'post_type'  => 'microlive',
			'showposts'  => -1,
			'meta_key'   => '_micro_live_post_id',
			'meta_value' => $liveblog_id,
		);

		return array_map(
			function( $post ) {
				return LiveEntry::fromPost( $post );
			},
			get_posts( apply_filters( 'mlb_api_get_entries_args', $args, $liveblog_id ) )
		);
	}
}
