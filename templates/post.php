<?php
/**
 * Template for liveblog post.
 */

do_action( 'mlb_before_liveblog_post', $post );
?>

<div class="mlb-liveblog-head">
    <p class="mlb-liveblog-post-time">
    	<?php mbfun_get_live_entry_display_date(); ?>
    </p>
    <?php if ( mlb_display_author_name() ) { ?>
        <div class="mlb-liveblog-post-author">
			<div class="mlb-liveblog-post-author-avatar">
                <img src="<?php echo esc_url( mlb_entry_author_avatar(60) ); ?>" alt="<?php echo esc_attr( get_the_author() ); ?>">
            </div>
            <span class="mlb-liveblog-post-author-name"><?php echo esc_html( get_the_author() ); ?></span>
        </div>
    <?php } ?>
</div>


<?php if ( mlb_display_title() ) { ?>
<h3 class="mlb-liveblog-post-title"><?php mlb_entry_title(); ?></h3>
<?php } ?>
<div class="mlb-liveblog-content"><?php mlb_entry_content(); ?></div>

<div class="mlb-liveblog-bottom">
<?php echo mbfun_get_live_template_part( 'sharing' ); ?>
<span class="mlb-liveblog-action-edit">
<?php if ( is_user_logged_in() ) {
        mlb_edit_entry_link(); 
    } else { ?>
        <a class="comments-highlighted" rel="nofollow">Live</a>
        <!-- <a class="comments-highlighted" href="<?php echo mbfun_get_live_entry_url(); ?>" rel="nofollow">Live</a> -->
<?php } ?>
</span>
</div>

<?php do_action( 'mlb_after_liveblog_post', $post ); ?>
