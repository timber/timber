<?php

class TimberPost extends TimberCore {

	var $ImageClass = 'TimberImage';
	var $PostClass = 'TimberPost';
	var $_can_edit;
	var $_get_terms;

	var $_custom_imported = false;

	public static $representation = 'post';

	/**
	*  If you send the contructor nothing it will try to figure out the current post id based on being inside The_Loop
	* @param mixed $pid
	* @return \TimberPost TimberPost object -- woo!
	*/
	function __construct($pid = null) {
		if ($pid === null && get_the_ID()){
			$pid = get_the_ID();
			$this->ID = $pid;
		} else if ($pid === null && have_posts()) {
			ob_start();
			the_post();
			$pid = get_the_ID();
			$this->ID = $pid;
			ob_end_clean();
		}
		if (is_numeric($pid)) {
			$this->ID = $pid;
		}
		$this->init($pid);
	}

	function init($pid = false) {
		if ($pid === false) {
			$pid = get_the_ID();
		}
		$post_info = $this->get_info($pid);
		$this->import($post_info);
	}

	/**
	*  Get the URL that will edit the current post/object
	*/
	function get_edit_url() {
		if ($this->can_edit()) {
			return '/wp-admin/post.php?post=' . $this->ID . '&action=edit';
		}
		return false;
	}

	/**
	*  updates the post_meta of the current object with the given value
	*
	* @param string $field
	* @param mixed $value
	* @nodoc
	*/
	function update($field, $value) {
		if (isset($this->ID)) {
			update_post_meta($this->ID, $field, $value);
			$this->$field = $value;
		}
	}


  /**
   *  takes a mix of integer (post ID), string (post slug), or object to return a WordPress post object from WP's built-in get_post() function
   *
   * @param mixed $pid
   * @return WP_Post on success
   */
	private function prepare_post_info($pid = 0) {
		if (is_string($pid) || is_numeric($pid) || (is_object($pid) && !isset($pid->post_title)) || $pid === 0) {
			$pid = self::check_post_id($pid);
			$post = get_post($pid);
			if ($post) {
				return $post;
			} else {
				$post = get_page($pid);
				return $post;
			}
		}
		//we can skip if already is WP_Post
		return $pid;
	}


	/**
	*  helps you find the post id regardless of whetehr you send a string or whatever
	*
	* @param mixed $pid;
	* @return integer ID number of a post
	*/
	private function check_post_id($pid) {
		if (is_numeric($pid) && $pid === 0) {
			$pid = get_the_ID();
			return $pid;
		}
		if (!is_numeric($pid) && is_string($pid)) {
			$pid = self::get_post_id_by_name($pid);
			return $pid;
		}
		if (!$pid) {
			return null;
		}
		return $pid;
	}


	/**
	*  get_post_id_by_name($post_name)
	* @nodoc
	*/

	function get_post_id_by_name($post_name) {
		global $wpdb;
		$query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s LIMIT 1", $post_name);
		$result = $wpdb->get_row($query);
		return $result->ID;
	}


  /**
   *  ## get a preview of your post, if you have an excerpt it will use that,
   *  ## otherwise it will pull from the post_content
   *  <p>{{post.get_preview(50)}}</p>
   */

	function get_preview($len = 50, $force = false, $readmore = 'Read More', $strip = true) {
		$text = '';
		$trimmed = false;
		if (isset($this->post_excerpt) && strlen($this->post_excerpt)) {
			if ($force) {
				$text = TimberHelper::trim_words($this->post_excerpt, $len);
				$trimmed = true;
			} else {
				$text = $this->post_excerpt;
			}
		}
		if (!strlen($text)) {
			$text = TimberHelper::trim_words($this->get_content(), $len, false);
			$trimmed = true;
		}
		if (!strlen(trim($text))) {
			return $text;
		}
		if ($strip) {
			$text = trim(strip_tags($text));
		}
		if (strlen($text)) {
			$text = trim($text);
			$last = $text[strlen($text) - 1];
			if ($last != '.' && $trimmed) {
				$text .= ' &hellip; ';
			}
			if (!$strip){
				$last_p_tag = strrpos($text, '</p>');
				$text = substr($text, 0, $last_p_tag);
				if ($last != '.' && $trimmed) {
					$text .= ' &hellip; ';
				}
			}

			if($readmore) {
				$text .= ' <a href="' . $this->get_permalink() . '" class="read-more">' . $readmore . '</a>';
			}
			if (!$strip){
				$text .= '</p>';
			}
		}
		return $text;
	}

  /**
   *  gets the post custom and attaches it to the current object
   * @param integer $pid a post ID number
   * @nodoc
   */
  	function import_custom($pid = false){
  		if (!$pid){
  			$pid = $this->ID;
  		}
  		$customs = $this->get_post_custom($pid);
  		$this->import($customs);
  	}

	function get_post_custom($pid) {
		$customs = get_post_custom($pid);
		if (!is_array($customs) || empty($customs)){
			return;
		}
		foreach ($customs as $key => $value) {
			$v = $value[0];
			$customs[$key] = maybe_unserialize($v);
		}
		return $customs;
	}

	/**
	*  ## get the featured image as a TimberImage
	*  <img src="{{post.get_thumbnail.get_src}}" />
	*/

	function get_thumbnail() {
		if (function_exists('get_post_thumbnail_id')) {
			$tid = get_post_thumbnail_id($this->ID);
		if ($tid) {
			return new $this->ImageClass($tid);
			}
		}
		return null;
	}

	function get_permalink() {
		if (isset($this->permalink)){
			return $this->permalink;
		}
		$this->permalink = get_permalink($this->ID);
		return $this->permalink;
	}

	function get_link() {
		return $this->get_permalink();
	}

	function get_next() {
		if (!isset($this->next)){
			$this->next = new $this->PostClass(get_adjacent_post( false, "", false ));
		}
		return $this->next;
	}

	function get_prev() {
		if (!isset($this->prev)){
			$this->prev = new $this->PostClass(get_adjacent_post( false, "", true ));
		}
		return $this->prev;
	}

	function get_parent() {
		if (!$this->post_parent) {
			return false;
		}
		return new $this->PostClass($this->post_parent);
	}

	/**
	*  ## Gets a User object from the author of the post
	*  <p class="byline">{{post.get_author.name}}</p>
	*/

	function get_author() {
		if (isset($this->post_author)) {
			return new TimberUser($this->post_author);
		}
		return false;
	}

	function get_info($pid) {
		$post = $this->prepare_post_info($pid);
		if (!isset($post->post_status)) {
			return null;
		}
		$post->slug = $post->post_name;
		$post->status = $post->post_status;
		$customs = $this->get_post_custom($post->ID);
		$post = (object) array_merge((array) $post, (array) $customs);
		return $post;
	}

	function get_display_date($use = 'post_date') {
		return date(get_option('date_format'), strtotime($this->$use));
	}

	function get_children($post_type = 'any', $childPostClass = false) {
		if ($childPostClass == false) {
			$childPostClass = $this->PostClass;
		}
		if ($post_type == 'parent') {
			$post_type = $this->post_type;
		}
		$children = get_children('post_parent=' . $this->ID . '&post_type=' . $post_type);
		foreach ($children as &$child) {
			$child = new $childPostClass($child->ID);
		}
		$children = array_values($children);
		return $children;
	}

	/**
	*  {% for comment in post.get_comments %}
	*    <p>{{comment.content}}</p>
	*  {% endfor %}
	*/

	function get_comments($ct = 0, $order = 'wp', $type = 'comment', $status = 'approve', $CommentClass = 'TimberComment') {
		$args = array('post_id' => $this->ID, 'status' => $status, 'order' => $order);
		if ($ct > 0) {
			$args['number'] = $ct;
		}
		if ($order == 'wp'){
			$args['order'] = get_option('comment_order');
		}
		$comments = get_comments($args);
		foreach ($comments as &$comment) {
			$comment = new $CommentClass($comment);
		}
		return $comments;
	}

	/**
	*  <ul class="categories">
	*  {% for category in post.get_categories %}
	*    <li>{{category.name}}</li>
	*  {% endfor %}
	*  </ul>
	*/


	function get_categories() {
		return $this->get_terms('category');
	}

	function get_category() {
		$cats = $this->get_categories();
		if (count($cats) && isset($cats[0])) {
			return $cats[0];
		}
		return null;
	}

	/** # get terms is good
	*
	*/

	function get_terms($tax = '', $merge = true, $TermClass = 'TimberTerm') {
		if (is_string($tax)){
			if (isset($this->_get_terms) && isset($this->_get_terms[$tax])){
				return $this->_get_terms[$tax];
			}
		}
		if (!strlen($tax) || $tax == 'all' || $tax == 'any') {
			$taxs = get_object_taxonomies($this->post_type);
		} else if (is_array($tax)) {
			$taxs = $tax;
		} else {
			$taxs = array($tax);
		}
		$ret = array();
		foreach ($taxs as $tax) {
			if ($tax == 'tags' || $tax == 'tag') {
				$tax = 'post_tag';
			} else if ($tax == 'categories') {
				$tax = 'category';
			}
			$terms = wp_get_post_terms($this->ID, $tax);
			foreach ($terms as &$term) {
				$term = new $TermClass($term->term_id);
			}
			if ($merge && is_array($terms)) {
				$ret = array_merge($ret, $terms);
			} else if (count($terms)) {
				$ret[$tax] = $terms;
			}
		}
		if (!isset($this->_get_terms)){
			$this->_get_terms = array();
		}
		$this->_get_terms[$tax] = $ret;
		return $ret;
	}

	function get_image($field) {
		return new $this->ImageClass($this->$field);
	}

	/**
	*  ## Gets an array of tags for you to use
	*  <ul class="tags">
	*  {% for tag in post.tags %}
	*    <li>{{tag.name}}</li>
	*  {% endfor %}
	*  </ul>
	*/

	function get_tags() {
		return $this->get_terms('tags');
	}

	/**
	*  ## Outputs the title with filters applied
	*  <h1>{{post.get_title}}</h1>
	*/

	function get_title() {
		$title = $this->post_title;
		return apply_filters('the_title', $title);
	}

	/**
	*  ## Displays the content of the post with filters, shortcodes and wpautop applied
	*  <div class="article-text">{{post.get_content}}</div>
	*/

	function get_content($len = 0, $page = 0) {
		$content = $this->post_content;
		if ($len) {
			$content = wp_trim_words($content, $len);
		}
		if ($page) {
			$contents = explode('<!--nextpage-->', $content);
			$page--;
			if (count($contents) > $page) {
				$content = $contents[$page];
			}
		}
		return apply_filters('the_content', ($content));
	}

	function get_post_type() {
		return get_post_type_object($this->post_type);
	}

	function get_comment_count() {
		if (isset($this->ID)) {
			return get_comments_number($this->ID);
		} else {
			return 0;
		}
	}

	//This is for integration with Elliot Condon's wonderful ACF
	function get_field($field_name) {
		if (function_exists('get_field')){
			return get_field($field_name, $this->ID);
		}
		return get_post_meta($this->ID, $field, true);
	}

	function import_field($field_name) {
		$this->$field_name = $this->get_field($field_name);
	}

	//Aliases
	function author() {
		return $this->get_author();
	}

	function categories() {
		return $this->get_terms('category');
	}

	function category() {
		return $this->get_category();
	}

	function children() {
		return $this->get_children();
	}

	function comments(){
		return $this->get_comments();
	}

	function content() {
		return $this->get_content();
	}

	function display_date(){
		return date(get_option('date_format'), strtotime($this->post_date));
	}

	function edit_link(){
		return $this->get_edit_url();
	}

	function link() {
		return $this->get_permalink();
	}

	function next() {
		return $this->get_next();
	}

	function path() {
		$path = TimberHelper::get_rel_url($this->get_permalink());
		return TimberHelper::preslashit($path);
	}

	function permalink() {
		return $this->get_permalink();
	}

	function prev() {
		return $this->get_prev();
	}

	function terms($tax = '') {
		return $this->get_terms($tax);
	}

	function tags() {
		return $this->get_tags();
	}

	function thumbnail() {
		return $this->get_thumbnail();
	}

	function title() {
		return $this->get_title();
	}

	//Deprecated
	function get_path() {
		return TimberHelper::get_rel_url($this->get_link());
	}

}
