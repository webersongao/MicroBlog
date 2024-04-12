<?php if ( ! mlb_display_social_sharing() ) {
		return;
} ?>
<span class="mlb-liveblog-action-share">
	<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo mlb_get_entry_url(); ?>" target="_blank" title="<?php _e( 'Share via Facebook', MLB_TEXT_DOMAIN ); ?>">
		<?php echo mlb_get_social_logo( 'facebook' ); ?>
	</a>

	<a href="https://x.com/intent/tweet?text=<?php mlb_entry_title(); ?> <?php echo mlb_get_entry_url(); ?>" target="_blank" title="<?php _e( 'Share via X/Twitter', MLB_TEXT_DOMAIN ); ?>">
		<?php echo mlb_get_social_logo( 'x' ); ?>
	</a>

	<a href="mailto:?&subject=<?php mlb_entry_title(); ?>&body=<?php echo mlb_entry_title() .'。 原文链接:' . mlb_get_entry_url(); ?>" target="_blank" title="<?php _e( 'Share via email', MLB_TEXT_DOMAIN ); ?>">
		<?php echo mlb_get_social_logo( 'mail' ); ?>
	</a>
</span>

