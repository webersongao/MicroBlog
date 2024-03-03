<?php

//handle ajax request
if ( is_admin() ) {
add_action("wp_ajax_kush_micronews_ajaxcallback", "kush_micronews_ajaxcallback");
add_action("wp_ajax_nopriv_kush_micronews_ajaxcallback", "kush_micronews_ajaxcallback");
}
function kush_micronews_ajaxcallback()
{
	if(isset($_POST['what']) && $_POST['what']!=""){
		if ($_POST['what'] == "loadmore" || $_POST['what'] == "loadhome") {
			$what = $_POST['what'];
			$number = $_POST['numnews'];
			$cat = (empty($_POST['cat'])) ? 'default' : $_POST['cat'];

			$loops="0";
			if(isset($_POST['loop']))
				$loops = $_POST['loop'];
			
			$limit = $number * $loops; //from where to start

			$newsdata = kush_micro_news_output($number,"false",$limit,"true",$cat);

			if($newsdata != '<div class="data-holder"></div>')
				echo $newsdata;
			else
				echo '0';

			die();
		}
	}	
}


function kush_micro_news_output($no_of_news=0, $header="true", $limit=0, $onlyNews="false", $category="default"){
	//this is responsible for displaying the final output to user site in widgets or anywhere this function is called!
	//$header attribute will decide whether to show MicroNews Header or not
	//$limit variable will decide where to start news from
	//$onlyNews variable will output only wrapNews rows.
global $wpdb;
$table_name = $wpdb->prefix . "micronews-table"; 

	$color = array('#55A4F2','#8bbf36','#fff2a8','#33363B','#F25555','#666666','#999966','#FF66FF');
	$i=0;//counter for multiple colors.
	if($no_of_news==0)
		{$no_of_news=get_option('mn_num_news', '5');}
	$showBorder=get_option('mn_show_lborder', 'false');
	$cleanHov=get_option('mn_show_linkclean', 'false');

	$widgetName = get_option('mn_widget_name', 'MicroNews');
	$headTextColor = get_option('mn_head_textColor','#FFFFFF');
	$headHighlightColor = get_option('mn_head_highlightColor','#808080');
	$headBack = get_option('mn_head_back','default');
	
	$titleColor = get_option('mn_color_title','#0066CC');
	$textColor = get_option('mn_color_text', '#666666');
	$linkColor = get_option('mn_color_link', '#000000');

	$date_format = get_option('mn_date_format','d M Y');
	
	$loadNav = get_option('mn_load_nav','true');
	$loadNavSwap = get_option('mn_load_nav_swap','true');
	$loadNewTab = get_option('mn_load_newtab','true');
	$readStoryText = get_option('mn_read_story_text','Read Full story &raquo;');
	$dbver = get_option('mn_db_version','0');
	$category = strtolower ($category);
	
	//way of updating news in navigation
	$navStyle = ($loadNavSwap == 'true') ? 'swap' : 'append';
	
	$count_no_news = 0;
	$rows = "";

	if($dbver == '0' || $dbver == '1.0')
	{//database without category column
		//for dynamic navigation
		$count_no_news = $wpdb->get_var("SELECT COUNT(`id`) FROM `$table_name`");
		$rows = $wpdb->get_results( "SELECT * FROM `$table_name` ORDER BY `time` DESC LIMIT $limit, $no_of_news ");
	}
	else
	{
		//for dynamic navigation
		$count_no_news = $wpdb->get_var("SELECT COUNT(`id`) FROM `$table_name` WHERE `category` = '$category'");
		$rows = $wpdb->get_results( "SELECT * FROM `$table_name` WHERE `category` = '$category' ORDER BY `time` DESC LIMIT $limit, $no_of_news ");	
	}
	
	$output_html = "";//this will contain final output
	$last_news_id = "";//this will hold last news id

	if($onlyNews == "false")
		$output_html .= '<div id="micro-news" class="clearfix">';

	if($header=="true"){
		$output_html .= '<h2 class="head"';
		if($headBack != "default")//background custom color if any
			$output_html .= 'style="background:'.$headBack.'"';
		$output_html .= '>';
		
		$issantahere = date('m-d');//hidden santa hat
		if( $issantahere == "12-24" || $issantahere == "12-25")
			$output_html .= '<span class="hiddensanta"></span>';
		
		//custom highlight and text color
		$output_html .= '<strong style="background:'.$headHighlightColor.'; color:'.$headTextColor.'">';
		$output_html .= $widgetName.'</strong></h2>';
	}//header if closed 

	//if anything changed in this div tag name, update javascript stripping as well
	$output_html .='<div class="data-holder" data-cat="'.$category.'">';//container for rows
	
	foreach ( $rows as $row ) 		
	{	
		$date=strtotime($row->time);

		$show_date = ($date_format == "hide") ? false : true;
		$formateddate = date($date_format ,$date); //d M Y
	
		$output_html .='<div class="wrapNews '.$row->id.'" style="border-color:';
		if($showBorder=='true')//check border color
			{$output_html .=$color[$i];}
		$output_html .='">';

			//show title
	    	$output_html .='<h3 class="title" style="color:'.$titleColor.'">'.$row->name.'</h3>';  	
	    	
	    	//show text
	    	$output_html .='<div class="text" style="color:'.$textColor.'">'.$row->text.' ';
    		if($show_date) //show only if user wants to
    			$output_html .='<span class="postedOn">'.__('on', 'micro-news-lang').' '.$formateddate.'</span>';
	    	$output_html .='</div>';
	        
	        //show url if present
	        if($row->url):
				$output_html .='<span class="link ';
				
				if($cleanHov!='true')//check if show link color inverted
					$output_html .='clean';
				
				$output_html .='"><a href="'.$row->url.'" title="'.$row->name.'" ';
				
				if($loadNewTab == 'true')
					$output_html .=  'target="_blank"';

				$output_html .= 'style="color:'.$linkColor.'">'.$readStoryText.'</a></span>';
			endif;

	    $output_html .='</div>';//wrapNews ends

		//this will reloop border color
		if($i>=7)
			$i=0;
		else
			$i++;

		//store last news id
		$last_news_id = $row->id;				
	}//foreach loop

	$output_html .= '</div>';//data-holder ends

	if($onlyNews == "false" && $count_no_news > $no_of_news && $loadNav == "true") //whether to display nav
	{
	$output_html .='<div class="load-nav clearfix" data-total="'.$count_no_news.'" data-style="'.$navStyle.'">';
		
		//calculate symbol to put in load more
		$loadmore_symbol = ($navStyle == 'swap') ? '&raquo;':'&darr;';

		$output_html .='<div class="loadMore" data-num="'.$no_of_news.'" data-lastnews="'.$last_news_id.'" data-loops="1">Load More '.$loadmore_symbol.'</div>';//load more news
		$output_html .='<div class="loadHome" data-num="'.$no_of_news.'">Home</div>';//load more news
	$output_html .='</div>';
	}

if($onlyNews == "false")
	$output_html .='</div>';//micro news ends

/*
$url = plugin_dir_url( __FILE__ );
*/

return $output_html;
}//kush_micro_news_output function ends

//////----------------------///////

//this will handle the admin page of Micro news
function kush_micro_news_output_admin(){
//this will check if update required
kush_micronews_check_dbupdate();

global $wpdb;
	$table_name = $wpdb->prefix . "micronews-table"; 	
	$what='';
	
if(isset($_POST['nTitle']) & empty($_POST['nTitle'])===false)
		{
		if(get_option('mn_parse_html')=='false')
		{
			$title= sanitize($_POST['nTitle']);
			$id= sanitize($_POST['nId']);
			$content= sanitize($_POST['nContent']);
			$link= sanitize($_POST['nLink']);
			$cat= (isset($_POST['nCat'])) ? trim($_POST['nCat']) : '';
		}
		else
		{
			$title=$_POST['nTitle'];
			$id=$_POST['nId'];
			$content=$_POST['nContent'];
			$link=$_POST['nLink'];
			$cat= (isset($_POST['nCat'])) ? trim($_POST['nCat']) : '';
		}
		//different query if cat column is not present
		if($cat == "")
			$query="UPDATE `$table_name` SET `name`='$title' , `text`='$content' , `url`='$link', `time`='".date('Y-m-d H:i:s')."' WHERE `id`='$id';";
		else
			$query="UPDATE `$table_name` SET `name`='$title' , `text`='$content' , `category`='$cat', `url`='$link', `time`='".date('Y-m-d H:i:s')."' WHERE `id`='$id';";

			$chk=$wpdb->query($query);
			
			if($chk)
				$what= __('Updated successfully.', 'micro-news-lang');
			else
				$what=__('Serious error occured.', 'micro-news-lang');
		
		}
if(isset($_POST['dId']))
	{$id=sanitize($_POST['dId']);
		$query="DELETE FROM $table_name WHERE `id`='$id';";
		
			$chk=$wpdb->query($query);
			
			if($chk)
				$what= __('Deleted successfully.', 'micro-news-lang');
			else
				$what=__('Serious error occured.', 'micro-news-lang');
	}

if(is_admin())
	{	
		
	?>
	<?php 
		$totalpage=$wpdb->get_var( "SELECT COUNT(id) FROM `$table_name`;" );
		
		$perpage=10;		
		$totalpage=ceil($totalpage/$perpage);
		
		$page=(isset($_GET['pgno']))? (int)$_GET['pgno'] :1;
		$start=($page-1)*$perpage;
		
		$rows = $wpdb->get_results( "SELECT * FROM `$table_name` ORDER BY `time` DESC LIMIT $start,$perpage;" );
	?>
	<div class="wrap">
		<h3></h3>
		<div class="icon32" id="icon-edit"> <br /> </div>
		<h2><?php _e('MicroNews Posts', 'micro-news-lang');?></h2>
	
	<?php if($what!=''){echo '<div class="updated"><p>'.$what.'</p></div>';}?>
	
		<div id="micro-news-board" class="clearfix widefat">	
		<?php $i=1;
		foreach ( $rows as $row ) 
		{	
		 ?>	
			<div class="wrapNews" data-id="<?php echo $row->id;?>">
				<span class="number"><?php echo $i;$i++;?>) </span>
				<h2 class="title" id="mn-title-<?php echo $row->id;?>">
					<?php echo $row->name;?>
				</h2>
				
				<div class="text" id="mn-text-<?php echo $row->id;?>">
					<?php echo $row->text;?>
				</div>
				<div class="container-admin-meta-link">
					<span> <strong><?php _e('on', 'micro-news-lang');?></strong> <?php $date=strtotime($row->time); echo date('d M Y',$date);?></span>
					|
					<?php if(empty($row->category) == false){
						echo '<strong>'.__('Category Key', 'micro-news-lang').': </strong><span id="mn-cat-'.$row->id.'">'.$row->category.'</span> | ';
					}?>
					<strong><?php _e('Reference Link', 'micro-news-lang'); ?>: </strong><span id="mn-link-<?php echo $row->id;?>"><?php echo $row->url;?></a></span>
				</div>
				<input type="button" value="Edit" class="button-primary editB" data-id="mn-edit-<?php echo $row->id;?>"/>
				<input type="button" value="X" class="button-primary closeB" />
				<input type="button" value="Delete" class="button-primary delB"/>
			</div>  
			<hr>
		<?php
			}//for each loop
		?>
		</div>
	</div>
	
	<?php //micro news ends
		if($totalpage>=1 && $page<$totalpage)	
			{?>
			<ul class="micro-news-post-nav"><li style="border:0;"><?php _e('Page', 'micro-news-lang');?> : </li>
			<?php			
			for($z=1;$z<=$totalpage;$z++)
				if($z!=$page)
				{echo '<li ><a href="?page=micro-news&pgno='.$z.'">'.$z.'</a></li>';}
			}
		if($page!=1)
			{echo '<li><a href="?page=micro-news&pgno=1">'.__('Home', 'micro-news-lang').'</a></li>';}
	}// if closed
	?>
	</ul>
	<?php
}

?>