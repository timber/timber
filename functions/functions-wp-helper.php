<?php

	class WPHelper {

		function __construct(){
			add_filter('cron_schedules', array($this, 'cron_add_quarterly'));
		}

		function is_url($url){
			$url = strtolower($url);
			if (strstr('http', $url)){
				return true;
			}
			return false;
		}	

		function get_wp_title(){
			return wp_title('|', false, 'right'); 
		}

		function force_update_option($option, $value){
			global $wpdb;
			$wpdb->query("UPDATE $wpdb->options SET option_value = '$value' WHERE option_name = '$option'");
		}

		/* this $args thing is a fucking mess, fix at some point: 

		http://codex.wordpress.org/Function_Reference/comment_form */

		function get_comment_form( $post_id = null, $args = array()) {
			ob_start();
			comment_form( $args, $post_id );
			$ret = ob_get_contents();
			ob_end_clean();
			return $ret;
		}

	}

	new WPHelper();
