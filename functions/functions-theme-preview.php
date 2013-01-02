<?php
	
	class Blades_Customize {

		public static function register(){

		}

		public static function live_preview(){
			$file = '/wp-content/themes/blades/js/wp-theme-customizer.js';
			wp_enqueue_script('blades-theme-customizer', $file, array('jquery', 'customize-preview'), '', true);
		}

		public static function header_output(){
			echo '<style type="text/css">';
			echo self::generate_css('.inset', 'background-color', 'background_color', '#');
			echo '</style>';
		}

		public static function generate_css($selector, $style, $mod_name, $prefix='', $postfix=''){
			$return = '';
			$mod = get_theme_mod($mod_name);
			if (!empty($mod)){
				$return = sprintf('%s {%s: %s;}', $selector, $style, $prefix.$mod.$postfix);
			}
			return $return;
		}

		

	}
	add_action( 'customize_register' , array( 'Blades_Customize' , 'register' ) );

	add_action( 'wp_head' , array( 'Blades_Customize' , 'header_output' ) );

	add_action( 'customize_preview_init' , array( 'Blades_Customize' , 'live_preview' ) );