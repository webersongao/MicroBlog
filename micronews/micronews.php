<?php
/*
Plugin Name: 微新闻 MicroNews
Description: Spread the news in shortest possible way. Use links to refer data and title to concise it.
Version: 1.0.0
Author: WebersonGao
Author Email: gao#btbk.org
Author URI: http://webersongao.com/
Plugin URI: https://github.com/webersongao/micronews
Text Domain: micro-news-lang
Domain Path: /lang/
Last Officially Updated: 02 Jun 2024
*/

define('K_MICRO_NEWS_DIRDIR', plugin_dir_path(__FILE__));
define('K_MICRO_NEWS_DIRURL', plugin_dir_url(__FILE__));
	

function kush_micronews_load_depen_reg(){
	wp_register_style( 'micronews_enqueue_style', K_MICRO_NEWS_DIRURL.'assets/css/style.css', array(), '24012024');	
	wp_register_script( 'micronews_enqueue_script', K_MICRO_NEWS_DIRURL.'assets/js/script.js', array('jquery'), '24012024');
	//importing stylesheet and js.
}
add_action('init','kush_micronews_load_depen_reg');
add_action('init','kush_micronews_create_shortcode');

add_action('wp_enqueue_scripts','kush_micronews_load_depen');
add_action('admin_enqueue_scripts','kush_micronews_load_depen');

function load_newsplugin_textdomain() {
  load_plugin_textdomain( 'micro-news-lang', FALSE, basename( dirname( __FILE__ ) ) . '/lang/' );
}
add_action( 'plugins_loaded', 'load_newsplugin_textdomain' );

function kush_micronews_load_depen(){
//load dependent libraries
	if(is_admin())
	{//load admin files only in admin
		wp_enqueue_script('micronews_enqueue_script');
	}
	else if(get_option('mn_load_nav') != "false")
	{//load only if user wants load more navigation

		wp_enqueue_script('micronews_enqueue_script');
		
		//$arr =array('url'=>K_MICRO_NEWS_DIRURL);		
		//can let me access js var from php
		//wp_localize_script( 'micronews_enqueue_script', 'micronews_enqueue_script_object', $arr );

		wp_localize_script( 'micronews_enqueue_script', 'micronews_enqueue_ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));  
	}
	
	wp_enqueue_style('micronews_enqueue_style');
}


function kush_micronews_load(){
	
    if(is_admin()) //load admin files only in admin
        {require_once(K_MICRO_NEWS_DIRDIR.'includes/admin.php');
		 include_once(K_MICRO_NEWS_DIRDIR.'includes/backup.php');
        }
    require_once(K_MICRO_NEWS_DIRDIR.'includes/core.php');
	
}
kush_micronews_load();

function kush_micronews_create_shortcode(){
	// this will create shortcode [kushmicronews news="5" header="true"]
	function micronews_shortcode( $atts ) {
	    $a = shortcode_atts( array( 'news' => '5', 'header' => 'true', 'category' => 'default', 'simple' => 'false' ), $atts );

	    return kush_micro_news_output($a['news'], $a['header'], 0, $a['simple'], $a['category']);
	}
	add_shortcode( 'kushmicronews', 'micronews_shortcode' );
}


register_activation_hook(__FILE__, 'kush_micronews_activation');
register_deactivation_hook(__FILE__, 'kush_micronews_deactivation');

class micronews_widget extends WP_Widget {
        
        public function __construct() {
            parent::__construct( 'micro-news-lang', 'micro-micronews', array( 'description' => __('Micro news data will output where this widget resides.', 'micro-news-lang') ) );
        }

		function widget( $args, $instance ) {
			// Widget output
			extract($args, EXTR_SKIP);
			
			$no_news = empty($instance['no_news']) ? get_option('mn_num_news', '5') : apply_filters('no_news', $instance['no_news']);
			$news_cat = empty($instance['news_cat']) ? 'default' : apply_filters('news_cat', $instance['news_cat']);
			$news_header = empty($instance['news_header']) ? get_option('mn_head_enable', 'true') : apply_filters('news_header', $instance['news_header']);

			echo $before_widget;
			echo kush_micro_news_output($no_news, $news_header, "0", "false", $news_cat);			
			echo $after_widget;
		}

		function update( $new_instance, $old_instance ) {
			// Save widget options
			$instance = $old_instance;
		
			$instance['no_news'] = strip_tags(stripslashes($new_instance['no_news']));
			$instance['news_cat'] = strip_tags(stripslashes($new_instance['news_cat']));
			$instance['news_header'] = (strip_tags(stripslashes($new_instance['news_header'])) == 'on') ? "true" : "false";

			return $instance;
		}

		function form( $instance ) {
			// Output admin widget options form
			
			
		$instance = wp_parse_args((array)$instance,	array('no_news' => '5', 'news_cat' => 'default', 'news_header' => 'true'));

		$no_news = strip_tags(stripslashes($instance['no_news']));
		$news_cat =  strip_tags(stripslashes($instance['news_cat'])); 	
		$news_header =  strip_tags(stripslashes($instance['news_header'])); 	

		$dbver = get_option('mn_db_version','0');
?>
			
			  	<div class="option">
				  <input type="text" size="5" id="<?php echo $this->get_field_id('no_news'); ?>" name="<?php echo $this->get_field_name('no_news'); ?>" value="<?php echo $instance['no_news']; ?>" />
				  <label for="no_news">
					<?php _e('Number of news', 'micro-news-lang'); ?>
				  </label>
				  <h6>~Default: 5</h6>
				</div>
				<?php if($dbver != '0' && $dbver != '1.0'): ?> 
					<div class="option">
	<!-- 				  <input type="text" id="<?php echo $this->get_field_id('news_cat'); ?>" name="<?php echo $this->get_field_name('news_cat'); ?>" value="<?php echo $news_cat; ?>" />
	 -->				<select name="<?php echo $this->get_field_name('news_cat'); ?>" id="<?php echo $this->get_field_id('news_cat'); ?>">
						  <option value="default" <?php if($news_cat == "default") echo 'selected';?> >Default</option>
						  <option value="cata" <?php if($news_cat == "cata") echo 'selected';?> >CatA</option>
						  <option value="catb" <?php if($news_cat == "catb") echo 'selected';?> >CatB</option>
						  <option value="catc" <?php if($news_cat == "catc") echo 'selected';?> >CatC</option>
						  <option value="catd" <?php if($news_cat == "catd") echo 'selected';?> >CatD</option>
						</select>
					  <label for="news_cat">
						<?php _e('Category', 'micro-news-lang'); ?>
					  </label>
					  <h6>~Default: "Default"</h6>
					</div>
				<?php endif;?>
				<div class="option">
				  <input type="checkbox" id="<?php echo $this->get_field_id('news_header'); ?>" name="<?php echo $this->get_field_name('news_header');?>" <?php echo ($instance['news_header'] == 'true')? 'checked':''; ?> />
				  <label for="news_header">
					<?php _e('Header', 'micro-news-lang'); ?>
				  </label>
				</div>
				
			
<?php
		}//form ends		
}//class ends

//this will add micro news widget along with all wordpress widgets
function micronews_mn_reg_widget(){
	register_widget( "micronews_widget" );
}
add_action('widgets_init', 'micronews_mn_reg_widget');	
	

function kush_micronews_activation() {    
	//actions to perform once on plugin activation go here  	
	
	function micronews_mn_install(){
		global $wpdb;
		$table_name = $wpdb->prefix . "micronews-table"; 
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$ver=get_option('mn_db_version');
		
	if($wpdb->get_var("SHOW TABLES LIKE '".$table_name."';")!=$table_name)   
	{	
		$query = "CREATE TABLE `$table_name` (
		  id mediumint(9) PRIMARY KEY AUTO_INCREMENT,
		  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  name mediumtext NOT NULL,
		  text text NOT NULL,
		  url tinytext,
		  category varchar(20) DEFAULT 'default'
		);";
		
		dbDelta( $query );



	  $welcome_name = "Ms. WordPress";
	  $welcome_text = "Congratulations, you just completed the installation! Delete or edit this news.";
	  $welcome_link = "http://www.webersongao.com";
	  $rows_affected = $wpdb->insert( $table_name, array( 'time' => current_time('mysql'), 'name' => $welcome_name, 'text' =>$welcome_text, 'url' => $welcome_link ) );
		
	  //update charset
	  $rows_affected = $wpdb->query("ALTER TABLE `$table_name` CONVERT TO CHARACTER SET utf8");

	}
	
		//setting default values
		add_option('mn_db_version', "1.1" );
		//db : 1.0 without category column

		add_option('mn_num_news',"5"); 
		add_option('mn_show_lborder','true');
		add_option('mn_show_linkclean','true');
		add_option('mn_parse_html','false');
		add_option('mn_widget_name','MicroNews');
		add_option('mn_color_title','#0066cc');
		add_option('mn_color_text','#666666');
		add_option('mn_color_link','#000000');
		add_option('mn_head_textColor','#FFFFFF');
		add_option('mn_head_highlightColor','#808080');
		add_option('mn_head_back','default');
		add_option('mn_head_enable','true');
	  	add_option('mn_load_nav','false');
	  	add_option('mn_load_nav_swap','true');
	  	add_option('mn_editor_access','false');
	  	add_option('mn_read_story_text','Read Full story &raquo;');
	  	add_option('mn_load_newtab','true');
	  	add_option('mn_date_format','d M Y');
	}
	micronews_mn_install();

}

function kush_micronews_deactivation() {    
	// actions to perform once on plugin deactivation go here	
		
	unregister_widget('micronews_widget');
}


// misc functions
function sanitize($data){
	return htmlentities($data);	
}
?>