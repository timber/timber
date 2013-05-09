<?php
	
	class TimberTerm extends TimberCore {

		var $taxonomy;
		var $PostClass;

		function __construct($tid = null){
			if ($tid === null){
				$tid = $this->get_term_from_query();
			}
			$this->init($tid);
		}

		function get_term_from_query(){
			global $wp_query;
			$qo = $wp_query->queried_object;
			return $qo->term_id;
		}

		function has_termmeta(){
			if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'wp_termmeta'"))==1){
				return true;
			}
			return false;
		}

		function get_page($i){
			return $this->get_path().'/page/'.$i;
		}

		function init($tid){
			global $wpdb;
			$term = $this->get_term($tid);
			if (isset($term->id)){
				$term->ID = $term->id;
			} else if (isset($term->term_id)){
				$term->ID = $term->term_id;
			} else {
				echo 'bad call';
				print_r(debug_backtrace());
			}
			
			if(function_exists('get_term_custom')){
				$term->custom = get_term_custom($result->term_id);
				if ($term->custom){
					foreach($term->custom as $key => $value){
						$term->$key = $value[0];
					}
				}
			} else if (self::has_termmeta()){
				$query = "SELECT * FROM $wpdb->termmeta WHERE term_id = $tid";
				$results = $wpdb->get_results($query);
				foreach($results as $result){
					$key = $result->meta_key;
					$value = $result->meta_value;
					$term->$key = $value;
				}
			}
			$this->import($term);
		}

		function get_term($tid){
			if (is_object($tid) || is_array($tid)){
				return $tid;
			}
			$tid = self::get_tid($tid);
			global $wpdb;
			$query = "SELECT * FROM $wpdb->term_taxonomy WHERE term_id = '$tid'";
			$tax = $wpdb->get_row($query);
			if ($tax->taxonomy){
				$term = get_term($tid, $tax->taxonomy);
				return $term;
			} 
			return null;
		}

		function get_tid($tid){
			global $wpdb;
			if (is_numeric($tid)){
				return $tid;
			}
			if (gettype($tid) == 'object'){
				$tid = $tid->term_id;
			}
			if (is_numeric($tid)){
				$query = "SELECT * FROM $wpdb->terms WHERE term_id = '$tid'";
			} else {
				$query = "SELECT * FROM $wpdb->terms WHERE slug = '$tid'";
			}
			
			$result = $wpdb->get_row($query);
			if (isset($result->term_id)){
				$result->ID = $result->term_id;
				return $result->ID;
			} 
			return 0;	
		}

		function get_path(){
			return '/'.$this->get_url();
		}

		function get_url(){
			$base = $this->taxonomy;
			if ($base == 'post_tag'){
				$base = 'tag';
			}
			return $base.'/'.$this->slug;
		}

		function get_posts($numberposts = 10, $post_type = 'any', $PostClass = ''){
			if (!strlen($PostClass)){
				$PostClass = $this->PostClass;
			}
			$args = array(
					'numberposts' => $numberposts,
					'tax_query' => array(array(
						'field' => 'id',
						'terms' => $this->ID,
						'taxonomy' => $this->taxonomy,
					)),
					'post_type' => $post_type
				);
			return Timber::get_posts($args, $PostClass);
		}

	}