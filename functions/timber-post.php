<?php

class TimberPost extends TimberCore {

	var $ImageClass = 'TimberImage';
	var $PostClass = 'TimberPost';
	var $_can_edit;
	var $_get_terms;
	var $_content;
	var $object_type = 'post';

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

    /**
     * @param int|bool $pid
     */
    function init($pid = false) {
		if ($pid === false) {
			$pid = get_the_ID();
		}
		$post_info = $this->get_info($pid);
		$this->import($post_info);
		//cant have a function, so gots to do it this way
		$this->class = $this->post_class();
	}

	/**
	 * Get the URL that will edit the current post/object
     *
     * @return bool|string
     */
    function get_edit_url() {
		if ($this->can_edit()) {
			return get_edit_post_link($this->ID);
			return '/wp-admin/post.php?post=' . $this->ID . '&action=edit';
		}
		return false;
	}

	/**
	 * updates the post_meta of the current object with the given value
	 *
	 * @param string $field
	 * @param mixed $value
	 */
	public function update($field, $value) {
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
     *
     * @param $post_name
     * @return int
     */
    public static function get_post_id_by_name($post_name) {
		global $wpdb;
		$query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s LIMIT 1", $post_name);
		$result = $wpdb->get_row($query);
		if (!$result) {
			return null;
		}
		return $result->ID;
	}


  /**
   *  ## get a preview of your post, if you have an excerpt it will use that,
   *  ## otherwise it will pull from the post_content.
   *  ## If there's a <!-- more --> tag it will use that to mark where to pull through.
   *  <p>{{post.get_preview(50)}}</p>
   */

    /**
     * @param int $len
     * @param bool $force
     * @param string $readmore
     * @param bool $strip
     * @return string
     */
    function get_preview($len = 50, $force = false, $readmore = 'Read More', $strip = true) {
		$text = '';
		$trimmed = false;
		if (isset($this->post_excerpt) && strlen($this->post_excerpt)) {
			if ($force) {
				$text = TimberHelper::trim_words($this->post_excerpt, $len, false);
				$trimmed = true;
			} else {
				$text = $this->post_excerpt;
			}
		}
		if (!strlen($text) && strpos($this->post_content, '<!--more-->') !== false) {
			$pieces = explode('<!--more-->',$this->post_content);
			$text = $pieces[0];
			if ($force) {
				$text = TimberHelper::trim_words($text, $len, false);
				$trimmed = true;
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
				if ($last_p_tag !== false){
					$text = substr($text, 0, $last_p_tag);
				}
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
     * @param bool|int $pid a post ID number
     * @nodoc
     */
  	function import_custom($pid = false){
  		if (!$pid){
  			$pid = $this->ID;
  		}
  		$customs = $this->get_post_custom($pid);
  		$this->import($customs);
  	}

    /**
     * @param int $pid
     * @return array
     */
    function get_post_custom($pid) {
		$customs = apply_filters('timber_post_get_meta_pre', array(), $pid, $this);
		$customs = get_post_custom($pid);
		if (!is_array($customs) || empty($customs)){
			return;
		}
		foreach ($customs as $key => $value) {
			if (is_array($value) && count($value) == 1 && isset($value[0])){
				$value = $value[0];
			}
			$customs[$key] = maybe_unserialize($value);
		}
		$customs = apply_filters('timber_post_get_meta', $customs, $pid, $this);
		return $customs;
	}

	/**
	*  ## get the featured image as a TimberImage
	*  <img src="{{post.get_thumbnail.get_src}}" />
	*/

    /**
     * @return null|TimberImage
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

    /**
     * @return string
     */
    function get_permalink() {
		if (isset($this->permalink)){
			return $this->permalink;
		}
		$this->permalink = get_permalink($this->ID);
		return $this->permalink;
	}

    /**
     * @return string
     */
    function get_link() {
		return $this->get_permalink();
	}

    /**
     * @param bool $by_taxonomy
     * @return mixed
     */
    function get_next($by_taxonomy = false) {
		if (!isset($this->_next) || !isset($this->_next[$by_taxonomy])){
			global $post;
			$this->_next = array();
			$old_global = $post;
			$post = $this;
			if ($by_taxonomy == 'category' || $by_taxonomy == 'categories'){
				$in_same_cat = true;
			} else {
				$in_same_cat = false;
			}
			$adjacent = get_adjacent_post($in_same_cat, '', false);
			if ($adjacent){
				$this->_next[$by_taxonomy] = new $this->PostClass($adjacent);
			} else {
				$this->_next[$by_taxonomy] = false;
			}
			$post = $old_global;
		}
		return $this->_next[$by_taxonomy];
	}

    /**
     * @return array
     */
    public function get_pagination(){
		global $post, $page, $numpages, $multipage, $more, $pagenow;
		$old_global_post = $post;
		$post = $this;
		$ret = array();
		if ($multipage){
			for ( $i = 1; $i <= $numpages; $i++ ) {
				$link = self::get_wp_link_page($i);
				$data = array('name' => $i, 'title' => $i, 'text' => $i, 'link' => $link);
				if ($i == $page){
					$data['current'] = true;
				}
				$ret['pages'][] = $data;
			}
			$i = $page - 1;
			if ( $i ) {
				$link = self::get_wp_link_page( $i );
				$ret['prev'] = array('link' => $link);
			}
			$i = $page + 1;
			if ( $i <= $numpages ) {
				$link = self::get_wp_link_page( $i );
				$ret['next'] = array('link' => $link);
			}
		}
		return $ret;
	}

    /**
     * @param int $i
     * @return string
     */
    private static function get_wp_link_page($i){
		$link = _wp_link_page($i);
		$link = new SimpleXMLElement($link.'</a>');
		if (isset($link['href'])){
			return $link['href'];
		}
		return '';
	}

    /**
     * @return string
     */
    function get_path() {
		return TimberURLHelper::get_rel_url($this->get_link());
	}

    /**
     * @param bool $by_taxonomy
     * @return mixed
     */
    function get_prev($by_taxonomy = false) {
		if (!isset($this->_prev) || !isset($this->_prev[$by_taxonomy])){
			global $post;
			$this->_prev = array();
			$old_global = $post;
			$post = $this;
			if ($by_taxonomy == 'category' || $by_taxonomy == 'categories'){
				$in_same_cat = true;
			} else {
				$in_same_cat = false;
			}
			$adjacent = get_adjacent_post($in_same_cat, '', true);
			if ($adjacent){
				$this->_prev[$by_taxonomy] = new $this->PostClass($adjacent);
			} else {
				$this->_prev[$by_taxonomy] = false;
			}
			$post = $old_global;
		}
		return $this->_prev[$by_taxonomy];
	}

    /**
     * @return bool|TimberPost
     */
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

    /**
     * @return bool|TimberUser
     */
    function get_author() {
		if (isset($this->post_author)) {
			return new TimberUser($this->post_author);
		}
		return false;
	}

    /**
     * @param int $pid
     * @return null|object|WP_Post
     */
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

    /**
     * @param string $use
     * @return bool|string
     */
    function get_display_date($use = 'post_date') {
		return date(get_option('date_format'), strtotime($this->$use));
	}

    /**
     * @param string $post_type
     * @param bool $childPostClass
     * @return array
     */
    function get_children($post_type = 'any', $childPostClass = false) {
		if ($childPostClass == false) {
			$childPostClass = $this->PostClass;
		}
		if ($post_type == 'parent') {
			$post_type = $this->post_type;
		}
		$children = get_children('post_parent=' . $this->ID . '&post_type=' . $post_type . '&numberposts=-1&orderby=menu_order title&order=ASC');
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

    /**
     * @param int $ct
     * @param string $order
     * @param string $type
     * @param string $status
     * @param string $CommentClass
     * @return mixed
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

    /**
     * @return array
     */
    function get_categories() {
		return $this->get_terms('category');
	}

    /**
     * @return mixed
     */
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

    /**
     * @param string $tax
     * @param bool $merge
     * @param string $TermClass
     * @return array
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
			if (!is_array($terms) && is_object($terms) && get_class($terms) == 'WP_Error'){
				//something is very wrong
				TimberHelper::error_log('You have an error retrieving terms on a post in timber-post.php:367');
				TimberHelper::error_log('tax = '.$tax);
				TimberHelper::error_log($terms);

			} else {
				foreach ($terms as &$term) {
					$term = new $TermClass($term->term_id, $tax);
				}
				if ($merge && is_array($terms)) {
					$ret = array_merge($ret, $terms);
				} else if (count($terms)) {
					$ret[$tax] = $terms;
				}
			}
		}
		if (!isset($this->_get_terms)){
			$this->_get_terms = array();
		}
		$this->_get_terms[$tax] = $ret;
		return $ret;
	}

    /**
     * @param string|int $term_name_or_id
     * @param string $taxonomy
     * @return bool
     */
    function has_term($term_name_or_id, $taxonomy = 'all'){
		if ($taxonomy == 'all' || $taxonomy == 'any'){
			$taxes = get_object_taxonomies($this->post_type, 'names');
			$ret = false;
			foreach($taxes as $tax){
				if (has_term($term_name_or_id, $tax, $this->ID)){
					$ret = true;
					break;
				}
			}
			return $ret;
		}
		return has_term($term_name_or_id, $taxonomy, $this->ID);
	}

    /**
     * @param string $field
     * @return TimberImage
     */
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

    /**
     * @return array
     */
    function get_tags() {
		return $this->get_terms('tags');
	}

	/**
	*  ## Outputs the title with filters applied
	*  <h1>{{post.get_title}}</h1>
	*/

    /**
     * @return string
     */
    function get_title() {
		$title = $this->post_title;
		return apply_filters('the_title', $title);
	}

	/**
	*  ## Displays the content of the post with filters, shortcodes and wpautop applied
	*  <div class="article-text">{{post.get_content}}</div>
	*/

    /**
     * @param int $len
     * @param int $page
     * @return string
     */
    function get_content($len = 0, $page = 0) {
		if ($len == 0 && $page == 0 && $this->_content){
			return $this->_content;
		}
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
		$content = apply_filters('the_content', ($content));
		if ($len == 0 && $page == 0){
			$this->_content = $content;
		}
		return $content;
	}

    /**
     * @return mixed
     */
    public function get_post_type() {
		return get_post_type_object($this->post_type);
	}

    /**
     * @return int
     */
    public function get_comment_count() {
		if (isset($this->ID)) {
			return get_comments_number($this->ID);
		} else {
			return 0;
		}
	}

    /**
     * @param string $field_name
     * @return mixed
     */
    public function get_field($field_name) {
		$value = apply_filters('timber_post_get_meta_field_pre', null, $this->ID, $field_name, $this);
		if ($value === null){
			$value = get_post_meta($this->ID, $field_name);
			if (is_array($value) && count($value) == 1){
				$value = $value[0];
			}
		}
		$value = apply_filters('timber_post_get_meta_field', $value, $this->ID, $field_name, $this);
		return $value;
	}

    /**
     * @param string $field_name
     */
    function import_field($field_name) {
		$this->$field_name = $this->get_field($field_name);
	}

    /**
     * @return mixed
     */
    function get_format(){
		return get_post_format($this->ID);
	}

	// Docs

    /**
     * @return array
     */
    public function get_method_values(){
		$ret = parent::get_method_values();
		$ret['author'] = $this->author();
		$ret['categories'] = $this->categories();
		$ret['category'] = $this->category();
		$ret['children'] = $this->children();
		$ret['comments'] = $this->comments();
		$ret['content'] = $this->content();
		$ret['display_date'] = $this->display_date();
		$ret['edit_link'] = $this->edit_link();
		$ret['format'] = $this->format();
		$ret['link'] = $this->link();
		$ret['next'] = $this->next();
		$ret['pagination'] = $this->pagination();
		$ret['parent'] = $this->parent();
		$ret['path'] = $this->path();
		$ret['prev'] = $this->prev();
		$ret['terms'] = $this->terms();
		$ret['tags'] = $this->tags();
		$ret['thumbnail'] = $this->thumbnail();
		$ret['title'] = $this->title();
		return $ret;
	}

	// Aliases
    /**
     * @return bool|TimberUser
     */
    public function author() {
		return $this->get_author();
	}

    /**
     * @return array
     */
    public function categories() {
		return $this->get_terms('category');
	}

    /**
     * @return mixed
     */
    public function category() {
		return $this->get_category();
	}

    /**
     * @return array
     */
    public function children() {
		return $this->get_children();
	}

    /**
     * @return mixed
     */
    public function comments(){
		return $this->get_comments();
	}

    /**
     * @param int $page
     * @return string
     */
    public function content($page  = 0) {
		return $this->get_content(0, $page);
	}

    /**
     * @return mixed
     */
    public function display_date(){
		return date_i18n(get_option('date_format') , strtotime($this->post_date));
	}

    /**
     * @return bool|string
     */
    public function edit_link(){
		return $this->get_edit_url();
	}

    /**
     * @return mixed
     */
    public function format(){
		return $this->get_format();
	}

    /**
     * @return string     */
    public function link() {
		return $this->get_permalink();
	}

    /**
     * @param string $field_name
     * @return mixed
     */
    public function meta($field_name){
		return $this->get_field($field_name);
	}

    /**
     * @param bool $in_same_cat
     * @return mixed
     */
    public function next($in_same_cat = false) {
		return $this->get_next($in_same_cat);
	}

    /**
     * @return array
     */
    public function pagination(){
		return $this->get_pagination();
	}

    /**
     * @return bool|TimberPost
     */
    public function parent(){
		return $this->get_parent();
	}

    /**
     * @return string
     */
    public function path() {
		return $this->get_path();
	}

    /**
     * @return string
     */
    public function permalink() {
		return $this->get_permalink();
	}

    /**
     * @param bool $in_same_cat
     * @return mixed
     */
    public function prev($in_same_cat = false) {
		return $this->get_prev($in_same_cat);
	}

    /**
     * @param string $tax
     * @return array
     */
    public function terms($tax = '') {
		return $this->get_terms($tax);
	}

    /**
     * @return array
     */
    public function tags() {
		return $this->get_tags();
	}

    /**
     * @return null|TimberImage
     */
    public function thumbnail() {
		return $this->get_thumbnail();
	}

    /**
     * @return string
     */
    public function title() {
		return $this->get_title();
	}

    /**
     * @param string $class
     * @return string
     */
    public function post_class($class='') {
		$pid = $this->ID;
		$class_array = get_post_class($class, $pid);
		return implode(' ', $class_array);
	}


}
