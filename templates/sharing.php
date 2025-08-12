<?php if ( ! mlb_display_liveblog_layout('ml_layout_tshare')) {
		return;
} ?>
<span class="mlb-liveblog-action-share">
	<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_url( mbfun_get_live_entry_url() ); ?>&quote=<?php echo urlencode( mlb_entry_title() ); ?>" target="_blank" title="<?php echo esc_attr( __( 'Share via Facebook', MICROBLOG_DOMAIN ) ); ?>">
    	<?php echo mbfun_get_live_social_logo( 'facebook' ); ?>
	</a>

	<a href="https://x.com/intent/post?text=<?php mlb_entry_title(); ?> <?php echo mbfun_get_live_entry_url(); ?>" target="_blank" title="<?php _e( 'Share via X/Twitter', MICROBLOG_DOMAIN ); ?>">
		<?php echo mbfun_get_live_social_logo( 'x' ); ?>
	</a>

	<a href="https://service.weibo.com/share/share.php?url=<?php echo esc_url( mbfun_get_live_entry_url() ); ?>&title=<?php echo esc_attr( mlb_entry_title() ); ?>" target="_blank" title="<?php _e( 'Share via Weibo', MICROBLOG_DOMAIN ); ?>">
    	<?php echo mbfun_get_live_social_logo( 'weibo' ); ?>
	</a>
</span>

