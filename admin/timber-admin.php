<?php

class TimberAdmin {

	function __construct() {
		if (!is_admin()) {
			return;
		}
		if (isset($_POST['hide_timber_admin_menu'])){
			update_option('hide_timber_admin_menu', true);
			header('Location: '.get_admin_url());
		}
		add_action('admin_menu', array(&$this, 'create_menu'));
		add_action('admin_enqueue_scripts', array(&$this, 'load_styles'));
		add_filter( 'plugin_action_links', array(&$this, 'settings_link'), 10, 2 );
	}

	function settings_link( $links, $file ) {
		if (strstr($file, 'timber/timber.php')){
		    return array_merge(
		        array(
		            'settings' => '<a href="' . get_bloginfo( 'wpurl' ) . '/wp-admin/themes.php?page=timber-getting-started">Starter Guide</a>'
		        ),
		        $links
		    );
		}
		return $links;
	}

	function create_menu() {
		//$hide = get_option('hide_timber_admin_menu');
		if (true){
			add_submenu_page( 'themes.php', 'Timber', 'Timber Starter Guide', 'administrator', 'timber-getting-started', array(&$this, 'create_admin_page') );
		} else {
			add_menu_page('Timber', 'Timber', 'administrator', 'timber-getting-started', array(&$this, 'create_admin_page'), TIMBER_URL_PATH . 'admin/timber-menu.png');
		}
	}

	function create_admin_page() {
		$data = array();
		$data['theme_dir'] = get_stylesheet_directory();
		$home = get_home_template();
		$home = pathinfo($home);
		$data['home_file']['name'] = $home['basename'];
		$data['timber_base'] = TIMBER_URL_PATH;
		$data['home_file']['path'] = trailingslashit(get_stylesheet_directory()) . $data['home_file']['name'];
		$data['home_file']['contents'] = htmlentities(file_get_contents(realpath($data['home_file']['path'])));
		$data['home_file']['location'] = str_replace(ABSPATH, '', trailingslashit(get_stylesheet_directory()));
		Timber::render('timber-admin.twig', $data);
	}

	function load_styles() {
		wp_enqueue_style('timber-admin-css', TIMBER_URL_PATH . 'admin/timber-admin.css');
	}

}

new TimberAdmin();
