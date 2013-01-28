<?php
	//if (!isset(THEME_URI)){
		define("THEME_URI", __DIR__);
	//}
	$theme = str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__);
	define("THEME_URL", 'http://'.$_SERVER["HTTP_HOST"].$theme);
	
	include('functions-prologue.php');
	include('functions-prologue-api.php');

	include('functions/functions-prologue-layout-chooser.php');

	if (function_exists('register_options_page')){
		
	}

	add_filter('get_twig', 'add_to_twig');

	function add_to_twig($twig){
		/* this is where you can add your own fuctions to twig */
		$twig->addExtension(new Twig_Extension_StringLoader());
		return $twig;
	}

	function get_post_info($pid = 0){
		PostMaster::get_post_info($pid);
	}

	function get_portfolio_info($pi){
		if (is_numeric($pi)){
			$pi = get_post_info($pi);
		}
		$pi->layouts = get_field('layouts', $pi->ID);
		return $pi;
	}

	function twitterify($ret) {
		$ret = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $ret);
		$ret = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $ret);
		$pattern = '#([0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.';
		$pattern .= '[a-wyz][a-z](fo|g|l|m|mes|o|op|pa|ro|seum|t|u|v|z)?)#i';
		$ret = preg_replace($pattern, '<a href="mailto:\\1">\\1</a>', $ret);
		$ret = preg_replace("/\B@(\w+)/", " <a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $ret);
		$ret = preg_replace("/\B#(\w+)/", " <a href=\"http://search.twitter.com/search?q=\\1\" target=\"_blank\">#\\1</a>", $ret);
		return $ret;
	}

	function load_scripts(){
		wp_enqueue_script('jquery');
		wp_enqueue_script('pjax', THEME_URL.'/js/libs/jquery.pjax.js', array('jquery'), false, true);
		wp_enqueue_script('prologue', THEME_URL.'/js/prologue.js', array('jquery', 'less'), false, true);

		wp_enqueue_script('less', THEME_URL.'/js/libs/less-1.3.3.min.js', array(), false, true);

		//<script type='text/javascript' src='http://insight.randomhouse.com/widget/viewer.js'>
		wp_enqueue_script('insight', 'http://insight.randomhouse.com/widget/viewer.js');
		//wp_enqueue_script('goodreads-status', 'http://www.goodreads.com/javascripts/widgets/update_status.js');
		
	}

	function load_styles(){
		
		wp_register_style( 'screen', THEME_URL.'/style.css', '', '', 'screen' );
        wp_enqueue_style( 'screen' );
	}

	function osort(&$array, $prop) {
    	usort($array, function($a, $b) use ($prop) {
        	return $a->$prop > $b->$prop ? 1 : -1;
    	}); 
	}

	register_activation_hook(__FILE__, 'my_activation');

	function my_activation() {
		wp_schedule_event( time(), 'hourly', 'ups_cron_hour');
	}
	

	function get_resized_image($src, $w, $h = 0, $ratio = 0, $append = ''){
		if (isset($w) && $h == 0){
			$base = basename($src);
			$src = '/wp-content/timthumb.php/'.$base.'?src='.$src.'&w='.$w . $append;
			if ($ratio){
				$ratio = explode(':', $ratio);
				$h = ($w/$ratio[0]) * $ratio[1];
				$src .= '&h='.$h;
			}
		} else if (isset($w) && isset($h)){
			$base = basename($src);
			$src = '/wp-content/timthumb.php/'.$base.'?src='.$src.'&w='.$w.'&h='.$h.'&'.$append;
		}
		return $src;
	}

	add_action('wp_enqueue_scripts', 'load_scripts');
	add_action('wp_enqueue_scripts', 'load_styles');

