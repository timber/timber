<?php

	class TimberPost extends TimberCore {

		var $ImageClass = 'TimberImage';
		var $PostClass = 'TimberPost';

		var $_can_edit;

		/**
		*	If you send the contructor nothing it will try to figure out the current post id based on being inside The_Loop
		*	@param mixed $pid	
		*	@return a TimberPost object -- woo!
		*/
		function __construct($pid = null){
			if ($pid === null && have_posts()){
				ob_start();
				the_post();
				$pid = get_the_ID();
				$this->ID = $pid;
				ob_end_clean();
			}
			if (is_numeric($pid)){
				$this->ID = $pid;
			}

			$this->init($pid);
			return $this;
		}

		function can_edit(){
			if (isset($this->_can_edit)){
				return $this->_can_edit;
			}
			$this->_can_edit = false;
			if (!function_exists('current_user_can')){
				return false;
			}
			if (current_user_can('edit_post', $this->ID)){
				$this->_can_edit = true;
  			}
  			return $this->_can_edit;
 		}

 		function get_edit_url(){
 			if ($this->can_edit()){
 				return '/wp-admin/post.php?post='.$this->ID.'&action=edit';
 			} 
 		}

		/*
		*/
		function init($pid = false){
			if ($pid === false){
				$pid = get_the_ID();
			}
			$this->import_info($pid);
		}


		/**
		*	updates the post_meta of the current object with the given value
		*
		*	@param string $field
		*	@param mixed $value
		*/
		function update($field, $value){
			if (isset($this->ID)) {
				update_post_meta($this->ID, $field, $value);
				$this->$field = $value;
			} 
		}


		/**
		*	takes a mix of integer (post ID), string (post slug), or object to return a WordPress post object from WP's built-in get_post() function
		*
		*	@param mixed $pid
		*	@return WP_Post on success
		*/
		private function prepare_post_info($pid = 0){
			if (is_string($pid) || is_numeric($pid) || (is_object($pid) && !isset($pid->post_title)) || $pid === 0){
				$pid = self::check_post_id($pid);
				$post = get_post($pid);
				if ($post){
					return $post;
				} else {
					$post = get_page($pid);
					return $post;
				}
			} 
			return $pid;
		}


		/**
		*	helps you find the post id regardless of whetehr you send a string or whatever
		*	
		*	@param mixed $pid;
		*	@return integer ID number of a post
		*/
		private function check_post_id($pid){
			if (is_numeric($pid) && $pid === 0){
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


		/** 
		*	get_post_id_by_name($post_name)
		*
		*/
		function get_post_id_by_name($post_name){
			global $wpdb;
			$query = "SELECT ID FROM $wpdb->posts WHERE post_name = '$post_name'";
			$result = $wpdb->get_row($query);
			return $result->ID;
		}

		function get_preview(){
			if (isset($this->post_content)){
				$pos = strpos($this->post_content, '<!--more');
				if ($pos > 0){
					return trim(substr($this->post_content, 0, $pos));
				} else if ($this->post_excerpt){
					return $this->post_excerpt;
				}
			}
		}

		/** 
		*	gets the post custom and attaches it to the current object
		*	@param integer $pid a post ID number
		*/
		function import_custom($pid){
			$customs = get_post_custom($pid);
			foreach($customs as $key => $value){
				$v = $value[0];
				$this->$key = $v;
				if (is_serialized($v)){
					if (gettype(unserialize($v)) == 'array'){
						$this->$key = unserialize($v);
					}
				}
			}
		}

		function get_thumbnail(){
			if (function_exists('get_post_thumbnail_id')){
				$tid = get_post_thumbnail_id($this->ID);
				if ($tid){
					return new $this->ImageClass($tid);
				}
			}
			return null;
		}

		function get_peramlink(){
			return get_permalink( $this->ID );
		}

		function get_path(){
			if (isset($this->path)){
				return $this->path;
			}
		}

		function import_info($pid){
			$post_info = $this->get_info($pid);
			$this->import($post_info);			
		}

		function get_parent(){
			if (!$this->post_parent){
				return false;
			}
			return new $this->PostClass($this->post_parent);
		}

		function get_info($pid){
			global $wp_rewrite;
			if (is_array($pid)){
				//print_r(debug_backtrace());
			}
			$post = $this->prepare_post_info($pid);
			if (!$post){
				//print_r(debug_backtrace());
				//print_r($post);
			}
			if (!isset($post->post_title)){
				return;
			}
			$post->title = $post->post_title;
			$post->slug = $post->post_name;
			$post->display_date = date(get_option('date_format'), strtotime($post->post_date));

			$this->import_custom($post->ID);
			
			if (isset($post->post_author)){
				$post->author = new TimberUser($post->post_author); 
			}
			
			$post->status = $post->post_status;	
			if (!isset($wp_rewrite)){
				return $post;
			} else {
				$post->permalink = get_permalink($post->ID);
				$post->path = $this->url_to_path($post->permalink);
			}
			return $post;
		}

		function get_display_date(){
			return date(get_option('date_format'), strtotime($this->post_date));
		}

		function get_children(){
			if (isset($this->children)){
				return $this->children;
			}
			$this->children = get_children('post_parent='.$this->ID.'&post_type='.$this->post_type);
			return $this->children;
		}

		function children(){
			return $this->get_children();
		}

		function get_comments($ct = -1, $type = 'comment', $status = 'approve', $CommentClass = 'TimberComment'){

			$args = array('post_id' => $this->ID, 'status' => $status);
			if ($ct >= 0){
				$args['number'] = $ct;
			}
			$comments = get_comments($args);
			WPHelper::error_log($comments);
			foreach($comments as &$comment){
				$comment = new $CommentClass($comment);
			}
			return $comments;
		}

		function terms($tax = ''){
			if (strlen($tax)){
				$terms = wp_get_post_terms($this->ID, $tax);
				$ret = array();
				foreach($terms as &$term){
					$ret[] = new TimberTerm($term->term_id);
				}
				return $ret;
			}
			return $this->terms || PostMaster::get_post_terms($this->ID);
		}

		function tags(){
			return $this->get_tags();
		}

		function get_image($field){
			error_log('field='.$this->$field);
			return new $ImageClass($this->$field);
		}

		function get_tags(){
			$tags = get_the_tags($this->ID);
			if (is_array($tags)){
				$tags = array_values($tags);
			} else {
				$tags = array();
			}
			return $tags;
		}

		function get_content($len = 0, $page = 0){
			$content = $this->post_content;
			if ($len){
				wp_trim_words($content, $len);
			}
			if ($page){
				$contents = explode('<!--nextpage-->', $content);
				$page--;
				if (count($contents) > $page){
					$content = $contents[$page];
				}
			}
			return apply_filters('the_content', ($content));
		}

		function get_post_type(){
			return get_post_type_object($this->post_type);
		}

		function get_comment_count(){
			if (isset($this->ID)){
				return get_comments_number($this->ID);
			} else {
				return 0;
			}
		}

		//This is for integration with Elliot Condon's wonderful ACF
		function get_field($field_name){
			return get_field($field_name, $this->ID);
		}
	}
