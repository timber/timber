<?php
	class ACFTimber {

		function __construct(){
			add_filter('timber_term_get_custom', array($this, 'term_get_custom'), 10, 3);
		}

		function term_get_custom($fields, $term_id, $term){
			$searcher = $term->taxonomy . "_" . $term->ID; // save to a specific category
			$fds = get_fields($searcher);
			if (is_array($fds)) {
				foreach ($fds as $key => $value) {
					$key = preg_replace('/_/', '', $key, 1);
					$key = str_replace($searcher, '', $key);
					$key = preg_replace('/_/', '', $key, 1);
					$field = get_field($key, $searcher);
					$fields[$key] = $field;
				}
			}
			$fields = array_merge($fields, $fds);
			return $fields;
		}
	}

	if (class_exists('ACF')){
		new ACFTimber();
	}