<?
/*
Plugin Name: Myspace Events Widget
Plugin URI: http://code.google.com/p/myspaceeventswidget/
Description: Display your myspace events on your sidebar in wordpress 
Version: 0.9.8
Author: Andrea Pola
*/

/*******************************************************************************
Licensed under The MIT License
Redistributions of files must retain the above copyright notice.
*******************************************************************************/

// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
// **********************************************************************

// Changelog:
// 0.9 Inital Release
// 0.9.5 Introduced Google Maps Link
// 0.9.8 Introduced a cache

/* version 0.9 no-api */

require(dirname( __FILE__ )."/simple_html_dom.php");

class MyspaceEvents extends WP_Widget {

   function MyspaceEvents() {
	   $widget_ops = array('description' => 'Add your Myspace events on wordpress.' );
       parent::WP_Widget(false, __('Myspace Events', 'myspace_events_widget'),$widget_ops);      
   }
   
   function widget($args, $instance) {  
    extract( $args );
   	$title = $instance['title'];
    $limit = $instance['limit']; if (!$limit) $limit = 5;
	$username = $instance['username'];
	$noevent_text = $instance['noevent_text'];
	$cachetime = $instance['cachetime'];if (!$cachetime) $cachetime = 6000;
	?>
		<?php echo $before_widget; ?>
        <?php if ($title) echo $before_title . $title . $after_title; ?>
        <ul id="my_space_events_list_<?php echo $unique_id; ?>">	
        <?php echo $this->myspace_events($username,$noevent_text,$limit,$bg,$color,$cachetime); ?>	 
        </ul>
        <?php echo $after_widget; ?>
        
   		
	<?php
   }
   
   /*Core*/
   
   function myspace_events($user,$noevent_text,$limit,$bg,$color,$cachetime) {
	   
	   $page_id = "my_space_events_widget.php";
	   $timeout = $cachetime;
	   $path = "./cached/".$page_id;

		if(!file_exists("./cached/"))
		   mkdir("./cached/");
		
		if(file_exists($path) and filemtime($path) + $timeout > time()) {
		   $result = readfile($path);
		   if($result) return;
		}
		set_time_limit(0);
		ob_start();
		
		/*FUNCTION TO CACHE*/
	  	echo $this->myspace_events_simple($user,$noevent_text,$limit,$bg,$color);
		
		$output = ob_get_flush();
		$fp = fopen($path, "w");
		fwrite($fp, $output, strlen($output));
		fclose($fp); 

   }
   
   /*simple html dom v*/
   function myspace_events_simple($user,$noevent_text,$limit,$bg,$color){
	  
	function remove_link($element) {
        if ($element->tag=='href')
                $element->outertext = $this->get_map($this->get_address($addressurl));
	}    
	   
	$html = file_get_html('http://www.myspace.com/'.$user.'/shows');
	$lives = $html->find('.eventsContainer',0);		
	$addressurl;	
				if (sizeof($lives) < "1" ) {
					$output = "<li class='noevent'>".$noevent_text."</li>";
				}
				else{
					$i=0;
					foreach($lives->find('li.event') as $live){
						$output .= "<li>";
						$output .= $live->find('.entryDate',0);
						$dettagli = $live->find('.details',0);
						$output .= "<h4>".$dettagli->find('h4',0)->first_child()."</h4>";
						$tmp = $dettagli->find('h4 a',0);
						$addressurl = $tmp->href;
						//$output .= "<span class='desc'>".$dettagli->find('p',0)->plaintext."</span>";	
						
						$output .="<span class='location map'><b>Map: </b><a class='map' href='".$this->get_map($this->get_address($addressurl))."' target='_blank' title='".__('See in the Map:','myspace_events_widget')."'>".$this->get_address($addressurl)."</a></span>";
						$output .= "</li>";				
						$i++;
						flush();
						if ($i == $limit) break;
					}
				}
				return $output;
	   
   }
   
   /*func to retrieve the address on myspace*/ 
   function get_address($urltoparse){
	$html = file_get_html($urltoparse);
	$location = $html->find('div.location div',0);	
	
		foreach($location->find('span') as $addrpiece){
			if(isset($addrpiece->itemprop)) $output .= $addrpiece->plaintext." ";	
		}
				
	return $output;
	   
   }
   /*Google map support func*/ 
   function get_map($address){
		$local=trim($address);
		$local=str_replace(" ","+",$local);
		$url="http://maps.google.com/maps?f=q&source=s_q&hl=it&q=";
		$url.=$local;
		$url.="&ie=UTF8&";
		return $url;
   }
       
	/*Wordpress Widget*/
	
   function update($new_instance, $old_instance) {                
       return $new_instance;
   }

   function form($instance) {        
   
       $title = esc_attr($instance['title']);
       $limit = esc_attr($instance['limit']);
	   $username = esc_attr($instance['username']);
	   $noevent_text = esc_attr($instance['noevent_text']);
	   $cachetime = esc_attr($instance['cachetime']);
       ?>
       <p>
	   	   <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:','myspace_events_widget'); ?></label><br />
	       <input type="text" name="<?php echo $this->get_field_name('title'); ?>"  value="<?php echo $title; ?>" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" />
       </p>
       <p>
	   	   <label for="<?php echo $this->get_field_id('username'); ?>"><?php _e('Username:','myspace_events_widget'); ?></label><br />
	       <input type="text" name="<?php echo $this->get_field_name('username'); ?>"  value="<?php echo $username; ?>" class="widefat" id="<?php echo $this->get_field_id('username'); ?>" />
       </p>
       <p>
	   	   <label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Limit:','myspace_events_widget'); ?></label><br />
	       <input type="text" name="<?php echo $this->get_field_name('limit'); ?>"  value="<?php echo $limit; ?>" class="" size="3" id="<?php echo $this->get_field_id('limit'); ?>" />

       </p>
       <p>
	   	   <label for="<?php echo $this->get_field_id('noevent_text'); ?>"><?php _e('No Event Text:','myspace_events_widget'); ?></label><br />
	       <input type="text" name="<?php echo $this->get_field_name('noevent_text'); ?>"  value="<?php echo $noevent_text; ?>" class="" size="widefat" id="<?php echo $this->get_field_id('noevent_text'); ?>" />

       </p>
       <p>
	   	   <label for="<?php echo $this->get_field_id('cachetime'); ?>"><?php _e('Cache Time (s):','myspace_events_widget'); ?></label><br />
	       <input type="text" name="<?php echo $this->get_field_name('cachetime'); ?>"  value="<?php echo $cachetime; ?>" class="" size="6" id="<?php echo $this->get_field_id('cachetime'); ?>" />

       </p>
      <?php
   }
   
}

function myspace_events_widget_css() { ?>
	<?php $x = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));?>
	<style type="text/css">
		/* <![CDATA[ */
		/* iPhone CSS */
		<?php $css = dirname( __FILE__ ) . '/myspace_events_widget.css';
		if ( is_file( $css ) ) require $css; ?>
		/* ]]> */
	</style> 
<?php }

add_action( 'wp_head', 'myspace_events_widget_css' );
add_action('widgets_init', 'register_myspace_widget');

function register_myspace_widget() {
    register_widget('MyspaceEvents');
}


?>