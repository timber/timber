<?php
	class PostMaster {

		function __construct(){

		}
		
		function loop_to_array($limit = 99999){
			$posts = array();
			$i = 0;
			while ( have_posts() && $i < $limit ) {
				the_post(); 
				$posts[] = PostMaster::get_post_info(get_the_ID());
				$i++;
			}
			return $posts;
		}

		function loop_to_post(){
			if (have_posts()){
				the_post(); 
				return PostMaster::get_post_info(get_the_ID());
			}
			return false;
		}

		function get_post_id_by_name($post_name){
			global $wpdb;
			$query = "SELECT ID FROM $wpdb->posts WHERE post_name = '$post_name'";
			$result = $wpdb->get_row($query);
			return $result->ID;
		}

		function check_post_id($pid){
			if ($pid == 0 && is_numeric($pid)){
				$pid = get_the_ID();
				return $pid;
			}
			if (!is_numeric($pid) && is_string($pid)){
				$pid = self::get_post_id_by_name($pid);
				return $pid;
			}
			if (!$pid){
				return;
			}
			return $pid;
		}

		function get_posts_info($query){
			if (is_array($query) && !PHPHelper::is_array_assoc($query)){
				$results = $query;
			} else {
				$results = get_posts($query);
			} 
			$ret = array();
			foreach($results as $result){
				$ret[] = self::get_post_info($result);
			}
			return $ret;
		}

		function get_path($url){
			$url_info = parse_url($url);
			return $url_info['path'];
		}

		function get_post_info($pid){
			if (is_string($pid) || is_numeric($pid) || !$pid->post_title){
				$pid = self::check_post_id($pid);
				$post = get_post($pid);
			} else {
				$post = $pid;
			}
			$post->title = $post->post_title;
			$post->body = wpautop($post->post_content);
			$post->excerpt = $post->post_excerpt;
			$post->slug = $post->post_name;
			$post->custom = get_post_custom($post->ID);
			$post->permalink = get_permalink($post->ID);
			$post->author_data = get_userdata($post->post_author); 
			$post->path = str_replace($_SERVER['HTTP_HOST'], '', $post->permalink);
			$post->path = str_replace('http://', '', $post->path);
			$post->thumb_src = self::get_post_thumbnail_src($post->ID);
			$post->display_date = date(get_option('date_format'), strtotime($post->post_date));
			if ($post->_thumbnail_id){
				$post->thumb_src = self::get_image_path($post->_thumbnail_id);
			}
			/*
			if ($post->thumb_src){
				$thumb_path = parse_url($post->thumb_src);
				$post->thumb_path = $thumb_path['path'];
			}
			*/
			if ($post->custom){
				foreach($post->custom as $key => $value){
					$v = $value[0];
					$post->$key = $v;
					if (is_serialized($v)){
						if (gettype(unserialize($v)) == 'array'){
							$post->$key = unserialize($v);
						}
					}
				}
			}
			$post->status = $post->post_status;
			$post->children = get_children('post_parent='.$post->ID.'&post_type='.$post->post_type);
			$post->parent = $post->post_parent;
			$post->post_type_info = get_post_type_object($post->post_type);
			return $post;
		}

		function get_posts_by_meta($key, $value){
			
			global $wpdb;
			$query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '$key' AND meta_value = '$value'";
			$results = $wpdb->get_results($query);
			$pids = array();
			foreach($results as $result){
				if (get_post($result->post_id)){
					$pids[] = $result->post_id;
				}
			}
			if (count($pids)){
				return $pids;
			}
			return 0;

		}
		

		function get_post_by_meta($key, $value){
			
			global $wpdb;
			$query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '$key' AND meta_value = '$value' ORDER BY post_id";
			$result = $wpdb->get_row($query);
			if ($result && get_post($result->post_id)){
				return $result->post_id;
			}
			return 0;
		}

		function get_related_posts_on_field($arr, $field){
			$ret = array();
			$upload_info = wp_upload_dir();
			$upload_path = str_replace($_SERVER['HTTP_HOST'], '', $upload_info['url']);
			$upload_path = str_replace($upload_info['subdir'], '', $upload_path);
			$upload_path = str_replace('http://', '', $upload_path);
			$upload_path .= '/';
			foreach($arr as $post){
				$related_id = $post->$field;
				if ($related_id){
					$related = self::get_post_info($related_id);
					$post->$field = $related;
					$post->$field->path = $upload_path . $post->$field->_wp_attached_file;
					$ret[] = $post;
				}
			}
			return $ret;
		}

		function get_image_path($iid){
			$size = 'full';
			$src = wp_get_attachment_image_src($iid, $size);
			$src = $src[0];
			return self::get_path($src);
		}

		function get_post_thumbnail_src($pid){
			$src = false;
			if (function_exists('get_post_thumbnail_id')){
				$tid = get_post_thumbnail_id($pid);
				$size = 'full';
				$src = wp_get_attachment_image_src($tid, $size);
				$src = $src[0];
			}
			return $src;
		}
		
	}
?>