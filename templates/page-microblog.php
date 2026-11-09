<?php
/* 页面模板 */

if (!defined('ABSPATH')) {
    exit;
}

$mb_content_before = microblog_page_content_before_list();

$mb_header  = '';
$mb_content = '';
if (have_posts()) {
    while (have_posts()) {
        the_post();
        $mb_header = microblog_page_header_html(get_the_ID());
        ob_start();
        the_content();
        $mb_content = ob_get_clean();
    }
}

$mb_header_tpl = microblog_locate_theme_shell('header');
$mb_footer_tpl = microblog_locate_theme_shell('footer');

if ($mb_header_tpl) {
    load_template($mb_header_tpl, false);
} else {
    get_header();
}
?>
<div class="container">
	<div class="row">
		<div class="col-md-12">
			<div class="mb-page-template<?php echo mbfun_page_background_enabled() ? ' mb-page-theme-shell' : ''; ?>">
				<?php echo $mb_header; // phpcs:ignore WordPress.Security.EscapeOutput -- 内部已逐项转义。 ?>
				<?php if ($mb_content_before) : ?>
					<div class="mb-page-content"><?php echo $mb_content; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content() 输出。 ?></div>
				<?php endif; ?>
				<?php
				echo microblog_render_list(array()); // phpcs:ignore WordPress.Security.EscapeOutput -- 插件内部已转义。
				?>
				<?php if (! $mb_content_before) : ?>
					<div class="mb-page-content"><?php echo $mb_content; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content() 输出。 ?></div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<?php
if ($mb_footer_tpl) {
    load_template($mb_footer_tpl, false);
} else {
    get_footer();
}
