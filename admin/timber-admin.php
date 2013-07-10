<?php

	class TimberAdmin {

		function __construct(){
			if (!is_admin()){
				return;
			}
			add_action( 'admin_menu', array(&$this, 'create_menu'));
  			add_action( 'admin_enqueue_scripts', array(&$this, 'load_styles'));
		}

		function create_menu(){
			add_menu_page('Timber', 'Timber', 'administrator', __FILE__, array(&$this, 'create_admin_page'), TIMBER_URL_PATH.'admin/timber-menu.png');
		}

		function create_admin_page(){
			$data = array();
			$data['timber_base'] = TIMBER_URL_PATH;
			Timber::render('timber-admin.twig', $data);
		}

		function load_styles(){
			wp_enqueue_style('timber-admin-css', TIMBER_URL_PATH.'admin/timber-admin.css');
		}

	}

	new TimberAdmin();