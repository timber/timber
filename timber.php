<?php
/*
Plugin Name: TimberFramework
Description: The WordPress Timber Framework allows you to write themes using the power of MVT and Twig
Author: Jared Novack + Upstatement
Version: 0.8.1
Author URI: http://timber.upstatement.com/
*/

global $wp_version;
global $plugin_timber;
$exit_msg = 'Timber reqiures WordPress 3.0 or newer';
if (version_compare($wp_version, '3.0', '<')){
	exit ($exit_msg);
}

require_once(__DIR__.'/functions/functions-twig.php');
require_once(__DIR__.'/functions/functions-post-master.php');
require_once(__DIR__.'/functions/functions-php-helper.php');
require_once(__DIR__.'/functions/functions-wp-helper.php');

require_once(__DIR__.'/objects/timber-core.php');
require_once(__DIR__.'/objects/timber-post.php');
require_once(__DIR__.'/objects/timber-comment.php');
require_once(__DIR__.'/objects/timber-user.php');
require_once(__DIR__.'/objects/timber-term.php');
require_once(__DIR__.'/objects/timber-image.php');
require_once(__DIR__.'/objects/timber-menu.php');

$timber_loc = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', realpath(__DIR__));
define("TIMBER", $timber_loc);
define("TIMBER_URL", 'http://'.$_SERVER["HTTP_HOST"].TIMBER);
define("TIMBER_LOC", realpath(__DIR__));



/*

	Usage:

		$posts = Timber::get_posts();
		$posts = Timber::get_posts('post_type = article')
		$posts = Timber::get_posts(array('post_type' => 'article', 'category_name' => 'sports')); // uses wp_query format
		$posts = Timber::get_posts(array(23,24,35,67), 'InkwellArticle');

		$context = Timber::get_context(); // returns wp favorites!

		Timber::render('index.twig', $context);


*/
	
class Timber {

	var $router;

	function __construct(){
		add_action('init', array(&$this, 'init_routes'));
	}

	public function get_post($query = false, $PostClass = 'TimberPost'){
		if (is_int($query)){
			/* its a post id number */
			$query = array($query);
		}
		$posts = self::get_posts($query, $PostClass);
		if (count($posts) && is_array($posts)){
			return $posts[0];
		}
		return $posts;
	}

	public function get_posts($query = false, $PostClass = 'TimberPost'){
		if (self::is_post_class_or_class_map($query)){
			$PostClass = $query;
			$query = false;
		}

		if (WPHelper::is_array_assoc($query) || (is_string($query) && strstr($query, '='))) {
			// we have a regularly formed WP query string or array to use
			return self::get_posts_from_wp_query($query, $PostClass);
		} else if (is_string($query) && !is_integer($query)){
			// we have what could be a post name to pull out
			return self::get_posts_from_slug($query, $PostClass);
		} else if (is_array($query) && count($query) && (is_integer($query[0]) || is_string($query[0]))){
			// we have a list of pids (post IDs) to extract from
			return self::get_posts_from_array_of_ids($query, $PostClass);

		} else if(is_array($query) && count($query) && isset($query[0]) && is_object($query[0])){
			// maybe its an array of post objects that already have data
			return self::handle_post_results($query, $PostClass);

		} else if (have_posts()){
			//lets just use the default WordPress current query
			return self::get_posts_from_loop($PostClass);

		} else if (!$query){
			//okay, everything failed lets just return some posts so that the user has something to work with
			//this turns out to cause all kinds of awful behavior
			//return self::get_posts_from_wp_query(array(), $PostClass);
			return null;

		} else {
			error_log('I have failed you! in timber.php::94');
			WPHelper::error_log($query);
		}
		return $query;
	}

	// TODO: new interface for loop_to_ids
	public function get_pids($query = false) {
		$posts = get_posts($query);
		$pids = array();
		foreach($posts as $post){
			if ($post->ID){
				$pids[] = $post->ID;
			}
		}
		return $pids;
	}

	function get_posts_from_loop($PostClass){
		$results = self::get_pids_from_loop();
		return self::handle_post_results($results, $PostClass);
	}

	function get_posts_from_slug($slug, $PostClass){
		global $wpdb;
		$query = "SELECT ID FROM $wpdb->posts WHERE post_name = '$slug'";
		if (strstr($slug, '/')){
			//we have a post_type directive here
			$q = explode('/', $slug);
			$query = "SELECT ID FROM $wpdb->posts WHERE post_name = '$q[1]' AND post_type = '$q[0]'";
		}
		$results = $wpdb->get_col($query);
		return self::handle_post_results($results, $PostClass);
	}

	function get_posts_from_wp_query($query = array(), $PostClass = 'TimberPost'){
		$results = get_posts($query);
		return self::handle_post_results($results, $PostClass);
	}

	function get_posts_from_array_of_ids($query = array(), $PostClass = 'TimberPost'){
		if (!is_array($query) || !count($query)){
			return null;
		}
		global $wpdb;
		$query_list = implode(', ', $query);
		$results = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE ID IN ($query_list)");
		$results = array_intersect($query, $results);
		return self::handle_post_results($results, $PostClass);
	}

	function handle_post_results($results, $PostClass = 'TimberPost'){
		$posts = array();
		foreach($results as $rid){
			$PostClassUse = $PostClass;
			if (is_array($PostClass)){
				$post_type = get_post_type($rid);
				$PostClassUse = 'TimberPost';
				if (isset($PostClass[$post_type])){
					$PostClassUse = $PostClass[$post_type];
				} else {
					if (is_array($PostClass)){
						error_log($post_type.' not found in '.print_r($PostClass, true));
					} else {
						error_log($post_type.' not found in '.$PostClass);
					}
				}
			}
			$post = new $PostClassUse($rid);
			if (isset($post->post_title)){
				$posts[] = $post;
			}
		}
		return $posts;
	}

	public function get_sidebar($sidebar = '', $data = array()){
		if ($sidebar == ''){
			$sidebar = 'sidebar.php';
		}
		if (strstr(strtolower($sidebar), '.php')){
			return self::get_sidebar_from_php($sidebar, $data);
		} 
		return self::render($sidebar, $data, false);
	}

	function get_sidebar_from_php($sidebar = '', $data){
		$context = $data;
		$uris = self::get_dirs();
		ob_start();
		foreach($uris as $uri){
			if (file_exists($uri.$sidebar)){
				include(trailingslashit($uri).$sidebar);
				break;
			}
		}
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}

	//this function is deprecated in favor of:
	//Timber::get_posts(false, $PostClass);
	function loop_to_posts($PostClass = 'TimberPost'){
		return self::get_posts(false, $PostClass);
	}
	

	// TODO: new interface for loop_to_id
	function get_pid() {

	}

	// shortcut function for common wordpress things
	function get_wp_context() {

	}

	/* ----

		"private"

	*/

	// returns ids of posts from current query
	function get_pids_from_loop(){
		$posts = array();
		$i = 0;
		ob_start();
		while ( have_posts() && $i < 99999 ) {
			the_post(); 
			$posts[] = get_the_ID();
			$i++;
		}
		wp_reset_query();
		ob_end_clean();
		return $posts;
	}

	function loop_to_id(){
		if (have_posts()){
			the_post();
			wp_reset_query();
			return get_the_ID();
		}
		return false;
	}

	function get_calling_script_path(){
		$dir = self::get_calling_script_dir();
		return str_replace($_SERVER['DOCUMENT_ROOT'], '', $dir);
	}

	function get_calling_script_dir(){
		$backtrace = debug_backtrace();
		foreach($backtrace as $trace){
			if ($trace['file'] != __FILE__){
				$caller = $trace['file'];
				break;
			}
		}
		$pathinfo = pathinfo($caller);
		$dir = $pathinfo['dirname'];
		return $dir;
	}

	function get_dirs(){
		$uris = array();
		$uris[] = get_stylesheet_directory();
		$uri_parent = get_template_directory();

		if ($uris[0] != $uri_parent){
			$uris[] = $uri_parent;
		}
		$uris[] = self::get_calling_script_dir();
		/* make sure all directories have trailing slash */
		foreach($uris as &$uri){
			$uri = trailingslashit($uri);
		}
		return $uris;
	}

	function render($filenames, $data = array(), $echo = true){
		$uri = self::get_dirs();
		$twig = get_twig($uri);
		
		$filename = twig_choose_template($filenames, $uri);
		$output = '';
		if (strlen($filename)){
			$output = $twig->render($filename, $data);
		}
		if ($echo){
			echo $output;
		}
		return $output;
	}

	// TODO: move into wp shortcut function
	function get_context(){
		$data = array();
		$data['http_host'] = 'http://'.$_SERVER['HTTP_HOST'];
		$data['wp_title'] = get_bloginfo('name');
		$data['wp_head'] = self::get_wp_head();
		$data['wp_footer'] = self::get_wp_footer();
		if (function_exists('wp_nav_menu')){
			$data['wp_nav_menu'] = wp_nav_menu( array( 'container_class' => 'menu-header', 'theme_location' => 'primary' , 'echo' => false) );
		}
		return $data;
	}


	function is_post_class_or_class_map($arg){
		if (is_string($arg) && class_exists($arg)){
			return true;
		}
		if (is_array($arg)){
			foreach($arg as $item){
				if (is_string($item) && class_exists($item)){
					return true;
				}
			}
		}
		return false;
	}

	/* Routes 				*/
	/* ==================== */

	function init_routes(){
		global $timber;
		if (isset($timber)){
			$route = $timber->router->matchCurrentRequest();
			if ($route){
				$callback = $route->getTarget();
				$params = $route->getParameters();
				$callback($params);
			}
		}
	}

	function add_route($route, $callback){
		global $timber;
		if (!isset($timber)){
			require_once('router/Router.php');
			require_once('router/Route.php');
			$timber = new Timber();
			$timber->router = new Router();
			$timber->router->setBasePath('/');
		} 
		$timber->router->map($route, $callback);
	}

	function get_template($template){
		header('HTTP/1.1 200 OK');
		wp_redirect(load_template(locate_template('home.php')));
		return;
	}

	// TODO: move into wp shortcut function
	function get_wp_footer(){
		ob_start();
		wp_footer();
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}

	// TODO: move into wp shortcut function
	function get_wp_head(){
		ob_start();
		wp_head();
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}
}