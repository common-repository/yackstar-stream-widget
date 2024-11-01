<?php
/*
Plugin Name: Yackstar Stream
Plugin URI: http://www.yackstar.com/
Description: A simple Yackstar stream plugin.
Version: 1.1
*/

if($auth = get_option('yackstar_auth_info')) {
	define('YACK_USER', $auth['yack_username']);
	define('YACK_PASS', $auth['yack_password']);
	define('YACK_URL', 'https://' . $auth['yack_account'] . '.yackstar.com');
}

if(!get_option('yackstar_auth_cookie') && isset($auth)) {
	require_once('redirect.php');
} 
if(isset($_GET['wpyack']) && $_GET['wpyack'] == 'logout') {
	delete_option('yackstar_auth_info');
	delete_option('yackstar_auth_cookie');
	header('Location: ' . preg_replace('/&wpyack=[^&]*/', '', $_SERVER['REQUEST_URI']));
} elseif(isset($_POST['yack_username']) && isset($_POST['yack_password']) && isset($_POST['yack_account'])) {
	update_option('yackstar_auth_info', array('yack_username' => $_POST['yack_username'], 'yack_password' => $_POST['yack_password'], 'yack_account' => $_POST['yack_account']));
	header('Location: ' . preg_replace('/&wpyack=[^&]*/', '', $_SERVER['REQUEST_URI']). '&wpyack=login');
}
add_action('admin_menu', 'yackstar_setup');

function yackstar_setup() {
	add_options_page('Yackstar Stream Authentication', 'Yackstar Stream', 8, 'yackauth', 'yackstar_options_page');	
}

function yackstar_options_page() {
?>
<div class="wrap">
   	<h2>Yackstar Stream Authentication Page</h2>
   	<?php
	if(isset($_GET['wpyack']) && ($_GET['wpyack'] == 'login') && !get_option('yackstar_auth_cookie')) {
	?>
		<div id="message" class="updated fade">
			<p><strong>
				You have entered invalid username or password. Please check your input and try again.
			</strong></p>
		</div>
	<?php
	}
	if(!defined('YACK_USER') && !defined('YACK_PASS') && !defined('YACK_URL') || !get_option('yackstar_auth_cookie')) {
	?>
		<h3>Enter Account Information</h3>
		<form action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" method="post">
			<label for="yack_account" style="font-weight:bold;display:block;width:150px;">Company:</label>
			<input type="text" value="" id="yack_account" name="yack_account" /><span><b>.yackstar.com</b></span>
			<label for="yack_username" style="font-weight:bold;display:block;width:150px;">Email:</label>
			<input type="text" value="" id="yack_username" name="yack_username" />
			<label for="yack_password" style="font-weight:bold;display:block;width:150px;margin-top:5px;">Password:</label>
			<input type="password" value="" id="yack_password" name="yack_password" />
			<input type="submit" value="Save" style="display:block;margin-top:10px;" />
		</form>
	<?php
	} else {
	?>
		<h3>Yackstar Stream Authorized!</h3>
		<h3>What Do I Do Now?</h3>
		<p>The easiest way to use Yackstar Stream is to add it via the widgets. Just go to the widgets page and add the Yackstar Stream widget to one of your widget areas. The alternative is to use the function by including &lt;?php yackstar_stream(); ?&gt; in your template somewhere.
		<h3>I Need To Change My Login Information!</h3>
		<p>If you ever need to change your login information for whatever reason click <a href="<?php echo preg_replace('/&wpyack=[^&]*/', '', $_SERVER['REQUEST_URI']) . '&wpyack=logout'; ?>" style="color: #aa0000;">REMOVE</a> to remove them.</p>
	<?php
	}
	?>
</div>
<?php
}

function yackstar_stream() {
	wp_register_style( 'yackstar-style', plugins_url( '/yack.css', __FILE__ ), array(), '0', 'all' );
	wp_register_script('nicescroll', plugins_url( '/jquery.nicescroll.min.js', __FILE__ ),array('jquery'));
	wp_register_script('yackstar-script-language', plugins_url( '/yack_res.en.js', __FILE__ ),array('jquery'));
	wp_register_script('yackstar-script', plugins_url( '/yack.js', __FILE__ ),array('jquery'));
    wp_enqueue_style( 'yackstar-style' ); 
    wp_enqueue_script('nicescroll');
    wp_enqueue_script('yackstar-script-language');
    wp_enqueue_script('yackstar-script');

    echo '<div id="yackstar-stream"><div class="loading"><img src="'.plugins_url( '/ajax-loader.gif', __FILE__ ).'" width="16" height="16" alt="..."/></div></div>';		
}

//For the widget to work you must have WP 2.8 or higher.
if(get_bloginfo('version') >= '2.8') {
	class YackstarStreamWidget extends WP_Widget {
	 
		function YackstarStreamWidget() {
			parent::WP_Widget(FALSE, $name = 'Yackstar Stream');    
		}
	 
		function widget($args, $instance) {        
			extract( $args );
			if(empty($instance['title']))
				$instance['title'] = 'Yackstar Mini Stream';
			?>
				  <?php echo $before_widget; ?>
					  <?php echo $before_title . $instance['title'] . $after_title; ?>
						  <?php 
						  unset($instance['title']);
						  echo yackstar_stream();
						  
						  ?>
				  <?php echo $after_widget; ?>
			<?php
		}
	
		function update($new_instance, $old_instance) {                
			return $new_instance;
		}
	 
		function form($instance) {                
			$title = esc_attr($instance['title']);
			if(empty($title))
				$title = 'Yackstar Mini Stream';
			?>
				<p>
                  <label for="<?php echo $this->get_field_id('title'); ?>">
				    <?php _e('Title:'); ?>
                    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
                  </label>
                </p>
			<?php 
		}
	}
	
	function yack_template_redirect() {
		if ((stripos($_SERVER['REQUEST_URI'], '?yackstream=') === FALSE) && (stripos($_SERVER['REQUEST_URI'], '/yackstream/') === FALSE)) {
			return;
		}
		global $wp_query;
	    if ($wp_query->is_404) {
	        $wp_query->is_404 = false;
	        $wp_query->is_archive = true;
	    }
	    	    	    
	    if(defined('YACK_USER')) {
	    	$cache_path = dirname(__FILE__).'/'.YACK_USER.'.cache';
	    } else {
	    	$cache_path = dirname(__FILE__).'/default.cache';
	    }
	    
	    if(file_exists($cache_path)) {
	    	$modtime = filemtime($cache_path);
	    	$thirtyago = time() - 30;
	    	if($modtime < $thirtyago) {
	    		$content =FALSE;
	    	}
	    	else
	    	{
	    		$data = unserialize(file_get_contents($cache_path));
	    
	    		if($data !== FALSE) {
	    			$content =$data;
	    		}
	    	}
	    	if($content !== FALSE) {
	    		$cache = TRUE;
	    	} else {
	    		$cache = FALSE;
	    		unset($content);
	    		if( function_exists('wp_cache_clear_cache') ) {
	    			wp_cache_clear_cache();
	    		} elseif ( function_exists('prune_super_cache') ) {
	    			prune_super_cache(WP_CONTENT_DIR.'/cache/', true );
	    		}
	    	}
	    } else {
	    	$cache = FALSE;
	    	if( function_exists('wp_cache_clear_cache') ) {
	    		wp_cache_clear_cache();
	    	} elseif ( function_exists('prune_super_cache') ) {
	    		prune_super_cache(WP_CONTENT_DIR.'/cache/', true );
	    	}
	    }
	    
	    if(!isset($content)) {
			if(!get_option('yackstar_auth_info')) {
				header("HTTP/1.1 401 OK");
				exit;
			}
	    	$cookie =get_option('yackstar_auth_cookie');
	    	if(empty($cookie) || $cookie === FALSE) {
	    		require_once('redirect.php');
	    		$cookie =get_option('yackstar_auth_cookie');
	    		if(empty($cookie) || $cookie === FALSE) {
	    			header("HTTP/1.1 401 OK");
	    			exit;
	    		}
	    	}
	    
	    	$json_obj=json_encode(array( 'CommandName' => 'ministream', 'ParentObjectID' => -1, 'ObjectData' => '604800', 'ObjectID' => -1, 'ApplicationID' => -1 ));
	    	$response = wp_remote_get( YACK_URL.'/services/MiniStream'.'?data='.$json_obj, array(
	    			'sslverify' => false,
	    			'cookies'=>$cookie
	    	));
	    
	    	if( is_wp_error( $response ) ) {
				header("HTTP/1.1 500 OK");
				print_r($response);
				exit;
	    	} else {
	    		$json_obj=json_decode(wp_remote_retrieve_body($response));
	    		$json_obj->Site=YACK_URL;
	    		if($json_obj->Success === TRUE) {
	    			$content=json_encode($json_obj);
	    		}
	    	}
	    }
	    
	    if($cache === FALSE) {
	    	$fp = fopen($cache_path, 'wb');
	    	if(flock($fp, LOCK_EX)) {
	    		fwrite($fp, serialize ($content));
	    		flock($fp, LOCK_UN);
	    	}
	    	fclose($fp);
	    }
	    
	    header("HTTP/1.1 200 OK");
	    print_r($content);	    
	    exit;
	}
	
	function yackstar_init() {
		if (version_compare(PHP_VERSION, '5.2.0', '<')) {
			echo "<div id=\"yackstar-warning\" class=\"updated fade\"><p>Sorry, Yackstar requires PHP version 5.2 or greater.</p></div>";
			return;
		}
		add_action('template_redirect', 'yack_template_redirect', 1);
	}
	add_action('widgets_init', create_function('', 'return register_widget("YackstarStreamWidget");'));
	add_action('init', 'yackstar_init');
}
?>