<?php
/**
 * Template for liveblog post.
 */

do_action( 'elb_before_liveblog_post', $post );
?>

<p class="elb-liveblog-post-time">
	<?php elb_get_entry_display_date(); ?>
</p>

<?php if ( elb_display_author_name() ) { ?>
<p class="elb-liveblog-post-author"><?php printf( __( 'By %s', ELB_TEXT_DOMAIN ), get_the_author() ); ?></p>
<?php } ?>

<h2 class="elb-liveblog-post-heading"><?php elb_entry_title(); ?></h2>

<div class="elb-liveblog-post-content"><?php elb_entry_content(); ?></div>

<?php echo elb_get_template_part( 'sharing' ); ?>

<div class="elb-liveblog-actions">
	<?php elb_edit_entry_link(); ?>
</div>

<?php do_action( 'elb_after_liveblog_post', $post ); ?>
