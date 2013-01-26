<?php
	//if (!isset(THEME_URI)){
		define("THEME_URI", __DIR__);
	//}
	$theme = str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__);
	define("THEME_URL", 'http://'.$_SERVER["HTTP_HOST"].$theme);
	
	add_filter('get_twig', 'add_to_twig');

	function add_to_twig($twig){
		/* this is where you can add your own fuctions to twig */
		$twig->addExtension(new Twig_Extension_StringLoader());
		return $twig;
	}	

	function load_scripts(){
		//wp_enqueue_script('jquery');
		//wp_enqueue_script('pjax', THEME_URL.'/js/libs/jquery.pjax.js', array('jquery'), false, true);
	}

	function load_styles(){
		//wp_register_style( 'screen', THEME_URL.'/style.css', '', '', 'screen' );
       // wp_enqueue_style( 'screen' );
	}

	add_action('wp_enqueue_scripts', 'load_scripts');
	add_action('wp_enqueue_scripts', 'load_styles');

