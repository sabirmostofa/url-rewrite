<?php   

/*
Plugin Name: WP-Custom-Rewrite-Url
Plugin URI: http://sabirul-mostofa.blogspot.com
Description: Rewrite Url for better tracking
Version: 1.0
Author: Sabirul Mostofa
Author URI: http://sabirul-mostofa.blogspot.com
*/


$wpCustomUrlRewrite = new wpCustomUrlRewrite();
if(isset($wpCustomUrlRewrite)) {	
	add_action('admin_menu', array($wpCustomUrlRewrite,'CreateMenu'),50);
}   
class wpCustomUrlRewrite{
	
	var $prefix='url_';
	
	function __construct(){
		$this->set_meta();
		add_action('template_redirect',array($this,'check_post'));	
		register_activation_hook(__FILE__, array($this, 'create_table'));
		
		
			// WP 3.0+
		add_action('add_meta_boxes', array($this,'add_custom_box'));
 
			// backwards compatible
		add_action('admin_init', array($this,'add_custom_box'));

		add_action('save_post', array($this,'url_save_postdata'));
		
		}
		
		
		function check_post(){
			global $wp_query,$wpdb;
			
			// redirect only if it's a page
			
			$raw_query=$_SERVER['REQUEST_URI'];
		
		//Change here if you have installed the plugin in a subdomain . if you have access the website by 
		// http://localhost/wordpress Replace the following line by	preg_match('!^/wordpress/([^/]*)?/?!',$raw_query,$matchArray);
		
			preg_match('!^/wordpress/([^/]*)?/?!',$raw_query,$matchArray);
		
			$post_name= trim($matchArray[1],'/');
			
		
			$postId = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts where post_name='$post_name';"));
			
			$query='page_id='.$postId;
			
			if( $this -> exists_in_table($postId) )
			$wp_query=new WP_Query( $query);
			
			
			}
		
		function add_scripts(){
		if(preg_match('/wpCustomUrlRewrite/',$_SERVER['REQUEST_URI']) ){
					
			wp_enqueue_script('jquery');
            wp_enqueue_script('custom_url_rewrite_script',plugins_url('/' , __FILE__).'js/script.js');	
            wp_localize_script('custom_url_rewrite_script', 'addVideoSettings',
					array(
					'ajaxurl'=>admin_url('admin-ajax.php'),
					'pluginurl' => plugins_url('/' , __FILE__)

)
);	

  wp_register_style('custom_url_rewrite_css', plugins_url('/' , __FILE__).'css/style.css', false, '1.0.0');
    wp_enqueue_style('custom_url_rewrite_css');
    
 }
	
		
			}
			
		

	function CreateMenu(){
		//add_submenu_page('options-general.php','Custom Url Rewrite','Custom Url Rewrite','activate_plugins','wpCustomUrlRewrite',array($this,'OptionsPage'));
	}
	
	
	/* ROCK SOLID META BOX  welcome to hack*/
	
		/***********************/
	
    //custom-field post meta
    
    function set_meta(){
			$this->meta_box = array(
		'id' => 'url-meta-box',
		'title' => 'Check To enable Custom Url Mapping',
		'page' => 'page',
		'context' => 'normal',
		'priority' => 'high',
		'fields' => array(
		array(
				'name' => 'Custom Url Box',
				'desc' => ' Check To enable Custom Url Mapping',
				'id' => $this->prefix . 'checkbox',
				'type' => 'checkbox',
				'std' => ''
			  )
			  )				   
		   );
		
		}
		
		
    function add_custom_box(){
		$meta_box=$this->meta_box;
		
		add_meta_box($meta_box['id'], $meta_box['title'], array($this,'show_box'), $meta_box['page'], $meta_box['context'], $meta_box['priority']);
		
		}
		
		
		
		
	function show_box(){
		$meta_box=$this->meta_box;
		global $post;
		if(get_post_meta($post->ID,'url_checkbox',true))
		echo '<input type="checkbox" name="url_checkbox" value="checked" checked="true"/>';
		else
		echo '<input type="checkbox" name="url_checkbox" value="checked"/>';			

			
			}
			



function url_save_postdata($post_id){
	global $wpdb;
	
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post_id;
	}
	$post_id= $_POST['post_ID'];
	
	update_post_meta($post_id,'url_checkbox',$_POST['url_checkbox']);
	
	if($_POST['url_checkbox'])
	  if( !$this -> exists_in_table($post_id) ):
	     $wpdb->insert( 'wp_custom_urls', 
					 array( 'post_id' => $post_id							
							 ),
							 array( '%d') );
	  endif;	
	else
	   if( $this -> exists_in_table($post_id) )
	     $wpdb->query("	DELETE FROM wp_custom_urls WHERE post_id = '$post_id'");
	   
}
	
	
	
	
						
						

		
		
	function create_table(){
	
   $sql = "CREATE TABLE IF NOT EXISTS `wp_custom_urls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT, 
  `post_id` int NOT NULL, 
   PRIMARY KEY (`id`),
   key `post_id`(`post_id`)
)";


global $wpdb;
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);

	}		
	
	
	
	// Options Page
	
	
	function OptionsPage( ){	
	
		
		//--------
	
		?>
			<div class="wrap"><h3>Custom Url Rewrite Options Page</h3>
		<form method="post" action="admin.php?page=wpCustomUrlRewrite">
		<table cellpadding=3>
		<tr><td>
		<input type="text" name="videoUserYouTube[]" value="<?php echo get_option('videoUserYouTube')?>" style="width:10em" />&nbsp;</td>
		<td>
		<p class="submit"><input type="submit" name="addVideoUnique" class="button-primary" value="<?php _e('List  Videos') ?>" /></p>
		</td>
		</tr>
		</table>	
		
		</form>
		</div>
		
		<?php
		
	
	}//endof options page
	

       
 
   
   
   
   //Crude functions
        function exists_in_table($post_id){
			global $wpdb;
			//$wpdb = new wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
			$result=$wpdb->get_results( "SELECT id FROM wp_custom_urls where  post_id='$post_id'" );
			if(empty($result))return false;
			else return true;			

			}
			
		function insert(){
			
			}
			
		function delete($post_id){
				
				}
				
		function suspend(){			
			
		}
		
	
	  


}


?>
