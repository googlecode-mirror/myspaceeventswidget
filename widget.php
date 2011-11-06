<?
/*
Plugin Name: Myspace Events Widget
Plugin URI: http://code.google.com/p/myspaceeventswidget/
Description: Display your myspace events on your sidebar in wordpress 
Version: 0.9
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

/* version 0.9 no-api */
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
	?>
		<?php echo $before_widget; ?>
        <?php if ($title) echo $before_title . $title . $after_title; ?>
        <ul id="my_space_events_list_<?php echo $unique_id; ?>">	
        <?php echo $this->myspace_events($username,$noevent_text,$limit,$bg,$color); ?>	 
        </ul>
        <?php echo $after_widget; ?>
        
   		
	<?php
   }
   
   /*Core*/
   
   function myspace_events($user,$noevent_text,$limit,$bg,$color) {
	   echo $this->myspace_events_simple($user,$noevent_text,$limit,$bg,$color);
   }
   
   /*simple html dom v*/
   function myspace_events_simple($user,$noevent_text,$limit,$bg,$color){
	require(dirname( __FILE__ )."/simple_html_dom.php");
	$html = file_get_html('http://www.myspace.com/'.$user.'/shows');
	$lives = $html->find('.eventsContainer',0);
				
				if (sizeof($lives) < "1" ) {
					$output = "<li class='noevent'>".$noevent_text."</li>";
				}
				else{
					$i=0;
					foreach($lives->find('li.event') as $live){
						$output .= "<li>";
						$output .= $live->find('.entryDate',0);
						$dettagli = $live->find('.details',0);
						$output .= $dettagli->find('h4',0);
						$output .= "<span class='desc'>".$dettagli->find('p',0)->plaintext."</span>";	
						$output .= "</li>";				
						$i++;
						if ($i == $limit) break;
					}
				}
				return $output;
	   
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
      <?php
   }
   
}

function myspace_events_widget_css() { ?>

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