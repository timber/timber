<?php

class WPHelper
{

  public static function is_array_assoc($arr)
  {
    if (!is_array($arr)) {
      return false;
    }
    return (bool)count(array_filter(array_keys($arr), 'is_string'));
  }

  public static function ob_function($function, $args = array(null))
  {
    ob_start();
    call_user_func_array($function, $args);
    $data = ob_get_contents();
    ob_end_clean();
    return $data;
  }

  public static function is_url($url)
  {
    $url = strtolower($url);
    if (strstr('://', $url)) {
      return true;
    }
    return false;
  }

  public static function resize_letterbox($src, $w, $h)
  {

  }

  public static function get_path_base()
  {
    $struc = get_option('permalink_structure');
    $struc = explode('/', $struc);
    $p = '/';
    foreach ($struc as $s) {
      if (!strstr($s, '%') && strlen($s)) {
        $p .= $s . '/';
      }
    }
    return $p;
  }

  public static function get_full_path($src)
  {
    $root = $_SERVER['DOCUMENT_ROOT'];
    $old_root_path = $root . $src;
    $old_root_path = str_replace('//', '/', $old_root_path);
    return $old_root_path;
  }

  public static function get_rel_path($src)
  {
    return str_replace($_SERVER['DOCUMENT_ROOT'], '', $src);
  }

  public static function get_letterbox_file_rel($src, $w, $h)
  {
    $path_parts = pathinfo($src);
    $basename = $path_parts['filename'];
    $ext = $path_parts['extension'];
    $dir = $path_parts['dirname'];
    $newbase = $basename . '-lb-' . $w . 'x' . $h;
    $new_path = $dir . '/' . $newbase . '.' . $ext;
    return $new_path;
  }

  public static function get_letterbox_file_path($src, $w, $h)
  {
    $root = $_SERVER['DOCUMENT_ROOT'];
    $path_parts = pathinfo($src);
    $basename = $path_parts['filename'];
    $ext = $path_parts['extension'];
    $dir = $path_parts['dirname'];
    $newbase = $basename . '-lb-' . $w . 'x' . $h;
    $new_path = $dir . '/' . $newbase . '.' . $ext;
    $new_root_path = $root . $new_path;
    $new_root_path = str_replace('//', '/', $new_root_path);
    return $new_root_path;
  }

  public static function download_url($url, $timeout = 300)
  {
    //WARNING: The file is not automatically deleted, The script must unlink() the file.
    if (!$url) {
      return new WP_Error('http_no_url', __('Invalid URL Provided.'));
    }

    $tmpfname = wp_tempnam($url);
    if (!$tmpfname) {
      return new WP_Error('http_no_file', __('Could not create Temporary file.'));
    }

    $response = wp_remote_get($url, array('timeout' => $timeout, 'stream' => true, 'filename' => $tmpfname));

    if (is_wp_error($response)) {
      unlink($tmpfname);
      return $response;
    }

    if (200 != wp_remote_retrieve_response_code($response)) {
      unlink($tmpfname);
      return new WP_Error('http_404', trim(wp_remote_retrieve_response_message($response)));
    }

    return $tmpfname;
  }

  public static function sideload_image($file)
  {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-admin/includes/file.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-admin/includes/media.php');
    if (empty($file)) {
      error_log('returnning');
      return null;
    }
    // Download file to temp location
    $tmp = download_url($file);
    preg_match('/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches);
    $file_array['name'] = basename($matches[0]);
    $file_array['tmp_name'] = $tmp;
    // If error storing temporarily, unlink
    if (is_wp_error($tmp)) {
      error_log('theres an error');
      @unlink($file_array['tmp_name']);
      $file_array['tmp_name'] = '';
    }
    error_log('continuing on');
    // do the validation and storage stuff
    $file = wp_upload_bits($file_array['name'], null, file_get_contents($file_array['tmp_name']));

    return $file;
  }

  public static function osort(&$array, $prop)
  {
    usort($array, function ($a, $b) use ($prop) {
      return $a->$prop > $b->$prop ? 1 : -1;
    });
  }

  public static function error_log($arg)
  {
    if (is_object($arg) || is_array($arg)) {
      $arg = print_r($arg, true);
    }
    error_log($arg);
  }

  public static function get_params($i = -1)
  {
    $args = explode('/', trim(strtolower($_SERVER['REQUEST_URI'])));
    $newargs = array();
    foreach ($args as $arg) {
      if (strlen($arg)) {
        $newargs[] = $arg;
      }
    }
    if ($i > -1) {
      if (isset($newargs[$i])) {
        return $newargs[$i];
      }
    }
    return $newargs;
  }

  public static function get_json($url)
  {
    $data = self::get_curl($url);
    return json_decode($data);
  }

  public static function get_curl($url)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $content = curl_exec($ch);
    curl_close($ch);
    return $content;
  }

  public static function get_wp_title()
  {
    return wp_title('|', false, 'right');
  }

  public static function force_update_option($option, $value)
  {
    global $wpdb;
    $wpdb->query("UPDATE $wpdb->options SET option_value = '$value' WHERE option_name = '$option'");
  }

  public static function get_current_url()
  {
    $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
    if ($_SERVER["SERVER_PORT"] != "80") {
      $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
      $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
  }

  public static function trim_words($text, $num_words = 55, $more = null, $allowed_tags = 'p a span b i br')
  {
    if (null === $more) {
      $more = __('&hellip;');
    }
    $original_text = $text;
    $allowed_tag_string = '';
    foreach (explode(' ', $allowed_tags) as $tag) {
      $allowed_tag_string .= '<' . $tag . '>';
    }
    $text = strip_tags($text, $allowed_tag_string);
    /* translators: If your word count is based on single characters (East Asian characters),
       enter 'characters'. Otherwise, enter 'words'. Do not translate into your own language. */
    if ('characters' == _x('words', 'word count: words or characters?') && preg_match('/^utf\-?8$/i', get_option('blog_charset'))) {
      $text = trim(preg_replace("/[\n\r\t ]+/", ' ', $text), ' ');
      preg_match_all('/./u', $text, $words_array);
      $words_array = array_slice($words_array[0], 0, $num_words + 1);
      $sep = '';
    } else {
      $words_array = preg_split("/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY);
      $sep = ' ';
    }
    if (count($words_array) > $num_words) {
      array_pop($words_array);
      $text = implode($sep, $words_array);
      $text = $text . $more;
    } else {
      $text = implode($sep, $words_array);
    }
    $text = self::close_tags($text);
    return apply_filters('wp_trim_words', $text, $num_words, $more, $original_text);
  }

  public static function trim_text($input, $length, $strip_html = true, $ellipses = '')
  {
    //strip tags, if desired
    if ($strip_html) {
      $input = strip_tags($input);
    }

    //no need to trim, already shorter than trim length
    if (strlen($input) <= $length) {
      return $input;
    }

    //find last space within length
    $last_space = strrpos(substr($input, 0, $length), ' ');
    $trimmed_text = substr($input, 0, $last_space);

    //add ellipses (...)
    if ($ellipses) {
      $trimmed_text .= $ellipses;
    }
    return $trimmed_text;
  }

  public static function close_tags($html)
  {
    #put all opened tags into an array
    preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
    $openedtags = $result[1];
    #put all closed tags into an array
    preg_match_all('#</([a-z]+)>#iU', $html, $result);
    $closedtags = $result[1];
    $len_opened = count($openedtags);
    # all tags are closed
    if (count($closedtags) == $len_opened) {
      return $html;
    }
    $openedtags = array_reverse($openedtags);
    # close tags
    for ($i = 0; $i < $len_opened; $i++) {
      if (!in_array($openedtags[$i], $closedtags)) {
        $html .= '</' . $openedtags[$i] . '>';
      } else {
        unset($closedtags[array_search($openedtags[$i], $closedtags)]);
      }
    }
    return $html;
  }

  public static function get_posts_by_meta($key, $value)
  {
    global $wpdb;
    $query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '$key' AND meta_value = '$value'";
    $results = $wpdb->get_results($query);
    $pids = array();
    foreach ($results as $result) {
      if (get_post($result->post_id)) {
        $pids[] = $result->post_id;
      }
    }
    if (count($pids)) {
      return $pids;
    }
    return 0;
  }

  public static function get_post_by_meta($key, $value)
  {
    global $wpdb;
    $query = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '$key' AND meta_value = '$value' ORDER BY post_id";
    $result = $wpdb->get_row($query);
    if ($result && get_post($result->post_id)) {
      return $result->post_id;
    }
    return 0;
  }

  /* this $args thing is a fucking mess, fix at some point: 

  http://codex.wordpress.org/Function_Reference/comment_form */

  public static function get_comment_form($post_id = null, $args = array())
  {
    ob_start();
    comment_form($args, $post_id);
    $ret = ob_get_contents();
    ob_end_clean();
    return $ret;
  }

  public static function is_true($property)
  {
    if (isset($property)) {
      if ($property == 'true' || $property == 1 || $property == '1' || $property == true) {
        return true;
      }
    }
    return false;
  }


  public static function array_to_object($array)
  {
    $obj = new stdClass;
    foreach ($array as $k => $v) {
      if (is_array($v)) {
        $obj->{$k} = self::array_to_object($v); //RECURSION
      } else {
        $obj->{$k} = $v;
      }
    }
    return $obj;
  }

  public static function get_object_index_by_property($array, $key, $value)
  {
    if (is_array($array)) {
      $i = 0;
      foreach ($array as $arr) {
        if ($arr->$key == $value || $arr[$key] == $value) {
          return $i;
        }
        $i++;
      }
    }
    return false;
  }

  public static function get_object_by_property($array, $key, $value)
  {
    if (is_array($array)) {
      foreach ($array as $arr) {
        if ($arr->$key == $value) {
          return $arr;
        }
      }
    } else {
      throw new Exception('$array is not an array, given value: ' . $array);
    }
    return null;
  }

  public static function get_image_path($iid)
  {
    $size = 'full';
    $src = wp_get_attachment_image_src($iid, $size);
    $src = $src[0];
    return self::get_path($src);
  }

  public static function array_truncate($array, $len)
  {
    if (sizeof($array) > $len) {
      $array = array_splice($array, 0, $len);
    }
    return $array;
  }

  public static function iseven($i)
  {
    return ($i % 2) == 0;
  }

  public static function isodd($i)
  {
    return ($i % 2) != 0;
  }

}