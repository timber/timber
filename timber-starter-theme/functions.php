<?php

	add_filter('get_twig', 'add_to_twig');
	define('THEME_URL', get_template_directory_uri());

	function add_to_twig($twig){
		/* this is where you can add your own fuctions to twig */
		$twig->addExtension(new Twig_Extension_StringLoader());
		return $twig;
	}

	function load_scripts(){
		wp_enqueue_script('jquery');		
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

	add_action('wp_enqueue_scripts', 'load_scripts');
	add_action('wp_enqueue_scripts', 'load_styles');

	add_theme_support('post-formats');
	add_theme_support('post-thumbnails');



