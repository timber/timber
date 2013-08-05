<?php

	class WordQuery {

		public function get_posts_multisite($query){
			add_action('posts_clauses', array(&$this, 'query_info'));
		}

		public function query_info($query){
			echo '----QUERY INFO----';
			print_r($query);
		}

		public function get_posts_multisite_old($query){
			if (is_string($query)){
				parse_str($query, $query);
			}
			if (!isset($query['blogs'])){
				return get_posts($query);
			}
			$ids = self::get_blog_ids_from_names($query['blogs']);
			$results = array();
			foreach($ids as $id){
				switch_to_blog($id);
				$results[] = get_posts($query);
			}
			return $results;
		}

		function get_blog_ids_from_names($blog_names){
			if (is_string($blog_names)){
				$blog_names = explode(',', $blog_names);
			}
			$ids = array();
			foreach($blog_names as $blog_name){
				if (is_numeric($blog_name)){
					$ids[] = $blog_name;
				} else (is_string($blog_name)) {
					$ids[] = get_id_from_blogname($blog_name);
				}
			}
			return $ids;
		}

	}