<?php

class TimberTerm extends TimberCore {

    var $taxonomy;
    var $PostClass;

    function __construct($tid = null) {
        if ($tid === null) {
            $tid = $this->get_term_from_query();
        }
        $this->init($tid);
    }

    function __toString(){
        return $this->name;
    }

  function get_term_from_query()
  {
    global $wp_query;
    $qo = $wp_query->queried_object;
    return $qo->term_id;
  }

  function get_page($i)
  {
    return $this->get_path() . '/page/' . $i;
  }

  function init($tid)
  {
    global $wpdb;
    $term = $this->get_term($tid);
    if (isset($term->id)) {
      $term->ID = $term->id;
    } else if (isset($term->term_id)) {
      $term->ID = $term->term_id;
    } else {
      //echo 'bad call';
      WPHelper::error_log(debug_backtrace());
    }
    if (function_exists('get_fields')) {
      //lets get whatever we can from advanced custom fields;
      //IF you have the wonderful ACF installed
      $searcher = $term->taxonomy . "_" . $term->ID; // save to a specific category
      $fields = array();
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
      $this->import($fields);

    }
    $this->import($term);
  }

  function get_term($tid)
  {
    if (is_object($tid) || is_array($tid)) {
      return $tid;
    }
    $tid = self::get_tid($tid);
    global $wpdb;
    $query = "SELECT * FROM $wpdb->term_taxonomy WHERE term_id = '$tid'";
    $tax = $wpdb->get_row($query);
    if (isset($tax) && isset($tax->taxonomy)) {
      if ($tax->taxonomy) {
        $term = get_term($tid, $tax->taxonomy);
        return $term;
      }
    }
    return null;
  }

  function get_tid($tid)
  {
    global $wpdb;
    if (is_numeric($tid)) {
      return $tid;
    }
    if (gettype($tid) == 'object') {
      $tid = $tid->term_id;
    }
    if (is_numeric($tid)) {
      $query = "SELECT * FROM $wpdb->terms WHERE term_id = '$tid'";
    } else {
      $query = "SELECT * FROM $wpdb->terms WHERE slug = '$tid'";
    }

    $result = $wpdb->get_row($query);
    if (isset($result->term_id)) {
      $result->ID = $result->term_id;
      return $result->ID;
    }
    return 0;
  }

  function get_path()
  {
    $p = WPHelper::get_path_base();
    return $p . $this->get_url();
  }

  function get_link(){
    return $this->get_path();
  }

  function get_url()
  {
    $base = $this->taxonomy;
    if ($base == 'post_tag') {
      $base = 'tag';
    }
    return $base . '/' . $this->slug;
  }

  function get_posts($numberposts = 10, $post_type = 'any', $PostClass = '') {
    if (!strlen($PostClass)) {
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

  /* Alias 
  ====================== */

  public function url(){
    return $this->get_url();
  }

  public function link(){
    return $this->get_link();
  }

}