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
	//add_action('init', array($wpCustomUrlRewrite,'redirect'), 1);
	add_action('admin_menu', array($wpCustomUrlRewrite,'CreateMenu'),50);

}   
class wpCustomUrlRewrite{
	
	var $prefix='url_';
	
	function __construct(){
		$this->set_meta();
		add_action('wp',array($this,'check_post'));
		add_action('admin_enqueue_scripts' , array($this,'add_scripts'));	
		add_action( 'wp_ajax_myajax-submit', array($this,'ajax_handle' ));
		add_action( 'wp_ajax_ajax_toggle', array($this,'ajax_toggle' ));
		add_action( 'wp_ajax_ajax_remove', array($this,'ajax_remove' ));
		add_action( 'wp_ajax_show_next', array($this,'ajax_next_page_show'));
		add_action( 'wp_ajax_ajax_getId', array($this,'ajax_process_insert'));
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
			var_dump($raw_query);
			//exit;
			
			//preg_match('!/.*?/!',$raw_query,$matchArray);
			preg_match('!^/wordpress/([^/]*)?/?!',$raw_query,$matchArray);
			//var_dump($matchArray);
			//exit;
			$post_name= trim($matchArray[1],'/');
			
			//var_dump($post_name);
			//exit;
			$postId = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts where post_name='$post_name';"));
			
			$query='page_id='.$postId;
			//$wp_query=new WP_Query( 'p=18' );
			//var_dump($postId);
			//exit;
			//var_dump ($_SERVER['HTTP_HOST']);
			//var_dump ($_SERVER['QUERY_STRING']);
			
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
		add_submenu_page('options-general.php','Custom Url Rewrite','Custom Url Rewrite','activate_plugins','wpCustomUrlRewrite',array($this,'OptionsPage'));
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
			
			/*
	// Use nonce for verification
	echo '<input type="hidden" name="url_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';
	
	echo '<table class="form-table">';

    $meta = get_post_meta($post->ID, $field['id'], true);
		
		echo '<tr>','<td>';
		echo '<input type="checkbox" name="',$meta_box['fields'][0]['id'],  '" value="checked"/>';
		
	
		echo 	'</td>','<td>';
		echo 'Enable Custom url';
		echo '</td>';
			'</tr>';
	
	
	echo '</table>';
			*/
			
			}
			



function url_save_postdata($post_id){
	global $wpdb;
	/*
	$meta_box=$this->meta_box;
				
				// verify nonce
	if (!wp_verify_nonce($_POST['url_meta_box_nonce'], basename(__FILE__))) {
		return $post_id;
	}

	// check autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post_id;
	}

	// check permissions
	if ('page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)) {
			return $post_id;
		}
	} elseif (!current_user_can('edit_post', $post_id)) {
		return $post_id;
	}
	
	foreach ($meta_box['fields'] as $field) {
		$old = get_post_meta($post_id, $field['id'], true);
		$new = $_POST[$field['id']];
		
		if ($new && $new != $old) {
			update_post_meta($post_id, $field['id'], $new);
		} elseif ('' == $new && $old) {
			delete_post_meta($post_id, $field['id'], $old);
		}
	}
	*/
	
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
	
	
	
	/* */
	
	function ajax_handle(){
		$id = trim($_REQUEST['id']);
		$title = trim($_REQUEST['title']);
		global $wpdb;
		
		if(!preg_match('/[a-zA-Z0-9]/', $id))exit;
		if($this->exists_in_table($id))exit;
		 $wpdb->insert( 'wp_video_list', 
					 array( 'video_id' => $id,
					        'video_title' => $title							
						),
							 array( '%s', '%s') 
					);
		
		print $id;
		
		exit;
		
		}
		
		
		function ajax_toggle(){
			global $wpdb;	
			
		$id = $_REQUEST['id'];
			
			
			$result = $wpdb -> get_results("SELECT video_stat FROM wp_custom_urls where video_id='$id'",'ARRAY_N' );
			
			if ($result[0][0] == 1){		
			$wpdb -> update('wp_video_list',
			array(
			'video_stat' => 2
			),
			array(
			'video_id' => $id
			),
			array('%d'),
			array('%s')		
			);
		}
			
			elseif($result[0][0] == '2'){
			$wpdb -> update('wp_video_list',
			array(
			'video_stat' => 1
			),
			array(
			'video_id' => $id
			),
			array('%d'),
			array('%s')		
			);
		}
			//$result = $wpdb -> get_results("SELECT video_stat FROM wp_custom_urlswhere  video_id='$id'",'ARRAY_N' );
			
			exit;
			
			}
			
			function ajax_remove(){
				global $wpdb;
				$id = $_REQUEST['id'];
								
				echo $test = $wpdb -> query("delete from wp_custom_urls where video_id='$id'");
				
				
				exit;
				
				}
				
				
				
				function ajax_next_page_show(){
					
					$num = $_REQUEST['pagenum'];
					if($num <1)$num = 1;					
					
					$this -> OptionsPage($num,1);
					
					exit;
					}
					
					
					
					function ajax_process_insert(){
							
							echo $title = $_REQUEST['title'];
							global $wpdb;
							
							if($this->exists_in_table($id))exit;
		                    $wpdb->insert( 'wp_video_list', 
					       array( 'video_id' => $id,
					            'video_title' => $title							
						        ),
							 array( '%s', '%s') 
					           );
							
						
						exit;					
						}
						
						

		
		
	function create_table(){
	
   $sql = "CREATE TABLE IF NOT EXISTS `wp_custom_urls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT, 
  `post_id` int NOT NULL,
  `post_perma` text not null default '',
   PRIMARY KEY (`id`),
   key `post_id`(`post_id`)
)";


global $wpdb;
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);

	}	
		
		

	
	
	
	
	
	
	
	
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
