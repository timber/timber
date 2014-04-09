<?php

	add_theme_support('post-formats');
	add_theme_support('post-thumbnails');
	add_theme_support('menus');

	add_filter('get_twig', 'add_to_twig');
	add_filter('timber_context', 'add_to_context');

	add_action('wp_enqueue_scripts', 'load_scripts');

	define('THEME_URL', get_template_directory_uri());
	function add_to_context($data){
		/* this is where you can add your own data to Timber's context object */
		$data = array(
			// WP conditionals
			'is_home' => $isHome,
			'is_front_page' => is_front_page(),
			'is_admin' => is_admin(),
			'is_single' => is_single(),
			'is_sticky' => is_sticky(),
			'get_post_type' => get_post_type(),
			'is_single' => is_single(),
			'is_post_type_archive' => is_post_type_archive(),
			'comments_open' => comments_open(),
			'is_page' => is_page(),
			'is_page_template' => is_page_template(),
			'is_category' => is_category(),
			'is_tag' => is_tag(),
			'has_tag' => has_tag(),
			'is_tax' => is_tax(),
			'has_term' => has_term(),
			'is_author' => is_author(),
			'is_date' => is_date(),
			'is_year' => is_year(),
			'is_month' => is_month(),
			'is_day' => is_day(),
			'is_time' => is_time(),
			'is_archive' => is_archive(),
			'is_search' => is_search(),
			'is_404' => is_404(),
			'is_paged' => is_paged(),
			'is_attachment' => is_attachment(),
			'is_singular' => is_singular(),
			'template_uri' => get_template_directory_uri(),
			'single_cat_title' => single_cat_title()
		);
		$data['qux'] = 'I am a value set in your functions.php file';
		$data['menu'] = new TimberMenu();
		return $data;
	}

	function add_to_twig($twig){
		/* this is where you can add your own fuctions to twig */
		$twig->addExtension(new Twig_Extension_StringLoader());
		$twig->addFilter('myfoo', new Twig_Filter_Function('myfoo'));
		return $twig;
	}

	function myfoo($text){
    	$text .= ' bar!';
    	return $text;
	}

	function load_scripts(){
		wp_enqueue_script('jquery');
	}
