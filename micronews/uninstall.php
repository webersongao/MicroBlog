<?php

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

//delete all registered options
delete_option('mn_db_version');
delete_option('mn_num_news');
delete_option('mn_show_lborder');
delete_option('mn_show_linkclean');
delete_option('mn_parse_html');
delete_option('mn_widget_name');
delete_option('mn_color_title');
delete_option('mn_color_text');
delete_option('mn_color_link');
delete_option('mn_head_enable');
delete_option('mn_head_textColor');
delete_option('mn_head_highlightColor');
delete_option('mn_head_back');
delete_option('mn_load_nav');
delete_option('mn_load_nav_swap');
delete_option('mn_read_story_text');
delete_option('mn_editor_access');
delete_option('mn_load_newtab');
delete_option('mn_date_format');


global $wpdb;
	$table_name = $wpdb->prefix . "micronews-table"; 
   
	$query = "DROP TABLE IF EXISTS $table_name";
	
	$wpdb->query($query);
	
?>