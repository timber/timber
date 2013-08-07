<?php

class TimberPost extends TimberCore
{

  var $ImageClass = 'TimberImage';
  var $PostClass = 'TimberPost';
  var $_can_edit;

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
    return $this;
  }

  function init($pid = false)
  {
    if ($pid === false) {
      $pid = get_the_ID();
    }
    $this->import_info($pid);
  }

  /**
   *  Get the URL that will edit the current post/object
   */
  function get_edit_url()
  {
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
  function update($field, $value)
  {
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
  private function prepare_post_info($pid = 0)
  {
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
    return $pid;
  }


  /**
   *  helps you find the post id regardless of whetehr you send a string or whatever
   *
   * @param mixed $pid;
   * @return integer ID number of a post
   */
  private function check_post_id($pid)
  {
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

  function get_post_id_by_name($post_name)
  {
    global $wpdb;
    $query = "SELECT ID FROM $wpdb->posts WHERE post_name = '$post_name'";
    $result = $wpdb->get_row($query);
    return $result->ID;
  }


  /**
   *  ## get a preview of your post, if you have an excerpt it will use that,
   *  ## otherwise it will pull from the post_content
   *  <p>{{post.get_preview(50)}}</p>
   */

  function get_preview($len = 50, $force = false, $readmore = 'Read More', $strip = true)
  {
    $text = '';
    $trimmed = false;
    if (isset($this->post_excerpt) && strlen($this->post_excerpt)) {
      if ($force) {
        $text = WPHelper::trim_words($this->post_excerpt, $len);
        $trimmed = true;
      } else {
        $text = $this->post_excerpt;
      }
    }
    if (!strlen($text)) {
      $text = WPHelper::trim_words($this->get_content(), $len, false);
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
      $text .= ' <a href="' . $this->get_path() . '" class="read-more">' . $readmore . '</a>';
    }
    return $text;
  }

  /**
   *  gets the post custom and attaches it to the current object
   * @param integer $pid a post ID number
   * @nodoc
   */
  function import_custom($pid)
  {
    $customs = get_post_custom($pid);
    foreach ($customs as $key => $value) {
      $v = $value[0];
      $this->$key = $v;
      if (is_serialized($v)) {
        if (gettype(unserialize($v)) == 'array') {
          $this->$key = unserialize($v);
        }
      }
    }
  }

  /**
   *  ## get the featured image as a TimberImage
   *  <img src="{{post.get_thumbnail.get_src}}" />
   */

  function get_thumbnail()
  {
    if (function_exists('get_post_thumbnail_id')) {
      $tid = get_post_thumbnail_id($this->ID);
      if ($tid) {
        return new $this->ImageClass($tid);
      }
    }
    return null;
  }

  function get_permalink()
  {
    return get_permalink($this->ID);
  }

  function get_link()
  {
    if (isset($this->path)) {
      return $this->path;
    }
    return null;
  }

  function import_info($pid)
  {
    $post_info = $this->get_info($pid);
    $this->import($post_info);
  }

  function get_parent()
  {
    if (!$this->post_parent) {
      return false;
    }
    return new $this->PostClass($this->post_parent);
  }

  /**
   *  ## Gets a User object from the author of the post
   *  <p class="byline">{{post.get_author.name}}</p>
   */

  function get_author()
  {
    if (isset($this->post_author)) {
      return new TimberUser($this->post_author);
    }
    return false;
  }

  function get_info($pid)
  {
    global $wp_rewrite;
    $post = $this->prepare_post_info($pid);
    if (!isset($post->post_title)) {
      return null;
    }
    $post->slug = $post->post_name;
    $post->display_date = date(get_option('date_format'), strtotime($post->post_date));
    $this->import_custom($post->ID);
    $post->status = $post->post_status;
    if (!isset($wp_rewrite)) {
      return $post;
    } else {
      $post->permalink = get_permalink($post->ID);
      $post->path = $this->url_to_path($post->permalink);
    }
    return $post;
  }

  function get_display_date($use = 'post_date')
  {
    return date(get_option('date_format'), strtotime($this->$use));
  }

  function get_children($post_type = 'any', $childPostClass = false)
  {
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

  function get_comments($ct = 0, $type = 'comment', $status = 'approve', $CommentClass = 'TimberComment')
  {
    $args = array('post_id' => $this->ID, 'status' => $status);
    if ($ct > 0) {
      $args['number'] = $ct;
    }
    $comments = get_comments($args);
    foreach ($comments as &$comment) {
      $comment = new $CommentClass($comment);
    }
    return $comments;
  }

  /**
   *  <ul class="categories">
   *  {% for cateogry in post.get_categories %}
   *    <li>{{category.name}}</li>
   *  {% endfor %}
   *  </ul>
   */


  function get_categories()
  {
    return $this->get_terms('category');
  }

  function get_category()
  {
    $cats = $this->get_categories();
    if (count($cats) && isset($cats[0])) {
      return $cats[0];
    }
    return null;
  }

  /** # get terms is good
   *
   */

  function get_terms($tax = '', $merge = true)
  {
    if (!strlen($tax) || $tax == 'all' || $tax == 'any') {
      $taxs = get_object_taxonomies($this->post_type);
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
        $term = new TimberTerm($term->term_id);
      }
      if ($merge) {
        $ret = array_merge($ret, $terms);
      } else if (count($terms)) {
        $ret[$tax] = $terms;
      }
    }
    return $ret;
  }

  function get_image($field)
  {
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

  function get_tags()
  {
    $tags = get_the_tags($this->ID);
    if (is_array($tags)) {
      $tags = array_values($tags);
    } else {
      $tags = array();
    }
    return $tags;
  }

  /**
   *  ## Outputs the title with filters applied
   *  <h1>{{post.get_title}}</h1>
   */

  function get_title()
  {
    $title = $this->post_title;
    return apply_filters('the_title', $title);
  }

  /**
   *  ## Displays the content of the post with filters, shortcodes and wpautop applied
   *  <div class="article-text">{{post.get_content}}</div>
   */

  function get_content($len = 0, $page = 0)
  {
    $content = $this->post_content;
    if ($len) {
      wp_trim_words($content, $len);
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

  function get_post_type()
  {
    return get_post_type_object($this->post_type);
  }

  function get_comment_count()
  {
    if (isset($this->ID)) {
      return get_comments_number($this->ID);
    } else {
      return 0;
    }
  }

  //This is for integration with Elliot Condon's wonderful ACF
  function get_field($field_name)
  {
    return $this->get_field($field_name, $this->ID);
  }

  function import_field($field_name)
  {
    $this->$field_name = $this->get_field($field_name);
  }

  //Aliases
  function author()
  {
    return $this->get_author();
  }

  function categories()
  {
    return $this->get_terms('category');
  }

  function category()
  {
    return $this->get_category();
  }

  function children()
  {
    return $this->get_children();
  }

  function comments(){
    return $this->get_comments();
  }

  function content()
  {
    return $this->get_content();
  }

  function link()
  {
    return $this->get_link();
  }

  function permalink()
  {
    return $this->get_permalink();
  }

  function terms($tax = '')
  {
    return $this->get_terms($tax);
  }

  function tags()
  {
    return $this->get_tags();
  }

  function thumbnail()
  {
    return $this->get_thumbnail();
  }

  function title()
  {
    return $this->get_title();
  }

  //Deprecated
  function get_path()
  {
    return $this->get_link();
  }

}
