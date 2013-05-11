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

$timber = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', realpath(__DIR__));
define("TIMBER", $timber);
define("TIMBER_URL", 'http://'.$_SERVER["HTTP_HOST"].TIMBER);
define("TIMBER_LOC", realpath(__DIR__));

/*

	Usage:

		$posts = Timber::get_posts();
		$posts = Timber::get_posts('post_type = article')
		$posts = Timber::get_posts(array('post_type' => 'article', 'category_name' => 'sports')); // uses wp_query format
		$posts = Timber::get_posts(array(23,24,35,67), 'InkwellArticle');

		$posts = Timber::loop_to_posts();
		$posts = Timber::loop_to_posts('InkwellArticle');
		$posts = Timber::loop_to_posts(array('post' => 'InkwellArticle')); // wp_query format

		$pids = Timber::loop_to_ids();

		$context = Timber::get_context(); // returns wp favorites!


*/
	
class Timber {

	// TODO: homogenize this interface to combine get_posts with loop_to_posts
	function get_posts($query = false, $PostClass = 'TimberPost'){
		if(!$query) {
			error_log('--- Timber::get_posts is getting default WP posts');
			return get_posts();
		}

		if (is_array($query) && !PHPHelper::is_array_assoc($query)){
			$results = $query;
		} else {
			$results = get_posts($query);
		} 
		foreach($results as &$result){
			$rid = $result;
			if (isset($result->ID)){
				$rid = $result->ID;
			}
			$PostClassUse = $PostClass;
			if (is_array($PostClass)){
				$PostClassUse = $PostClass[$result->post_type];
			}
			$result = new $PostClassUse($rid);
		}
		return $results;
	}

	// TODO: new interface for loop_to_ids
	function get_pids() {

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

	function loop_to_posts($PostClass = 'TimberPost'){
		if (is_array($PostClass)){
			$map = $PostClass;
		}
		$posts = array();
		$i = 0;
		
		if ( have_posts() ){
			ob_start();
			while ( have_posts() && $i < 99999 ) {
				the_post(); 
				if (isset($map)){
					$pt = get_post_type();
					$PostClass = 'TimberPost';
					if (isset($map[$pt])){
						$PostClass = $map[$pt];
					} 
				}
				$posts[] = new $PostClass(get_the_ID());
				$i++;
			}
			ob_end_clean();
		}
		return $posts;
	}

	// returns ids of posts from current query
	function loop_to_ids(){
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