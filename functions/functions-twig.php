<?php

class TimberTwig
{

  function __construct()
  {
    add_action('twig_apply_filters', array(&$this, 'add_twig_filters'));
  }

  /**
   * @param Twig_Environment $twig
   * @return Twig_Environment
   */
  function add_twig_filters($twig)
  {
    $twig->addFilter('resize', new Twig_Filter_Function('wp_resize'));
    $twig->addFilter('letterbox', new Twig_Filter_Function('wp_resize_letterbox'));
    $twig->addFilter('excerpt', new Twig_Filter_Function('twig_make_excerpt'));
    $twig->addFilter('print_r', new Twig_Filter_Function('twig_print_r'));
    $twig->addFilter('print_a', new Twig_Filter_Function('twig_print_a'));
    $twig->addFilter('docs', new Twig_Filter_function('twig_object_docs'));

    $twig->addFilter('get_src_from_attachment_id', new Twig_Filter_Function('twig_get_src_from_attachment_id'));
    $twig->addFilter('path', new Twig_Filter_Function('twig_get_path'));
    $twig->addFilter('tojpg', new Twig_Filter_Function('twig_img_to_jpg'));
    $twig->addFilter('wpautop', new Twig_Filter_Function('wpautop'));
    $twig->addFilter('twitterify', new Twig_Filter_Function('twig_twitterify'));
    $twig->addFilter('get_class', new Twig_Filter_Function('twig_get_class'));
    $twig->addFilter('function', new Twig_Filter_Function(array(&$this, 'exec_function')));

    $twig->addFilter('get_type', new Twig_Filter_Function('twig_get_type'));
    $twig->addFilter('shortcodes', new Twig_Filter_Function('twig_shortcodes'));
    $twig->addFilter('sanitize', new Twig_Filter_Function('sanitize_title'));
    $twig->addFilter('pretags', new Twig_Filter_Function(array(&$this, 'twig_pretags')));
    $twig->addFilter('wp_body_class', new Twig_Filter_Function('twig_body_class'));
    $twig->addFilter('wp_title', new Twig_Filter_Function('twig_wp_title'));
    $twig->addFilter('wp_sidebar', new Twig_Filter_Function('twig_wp_sidebar'));
    $twig->addFilter('time_ago', new Twig_Filter_Function('twig_time_ago'));

    $twig->addFunction('bloginfo', new Twig_SimpleFunction('bloginfo', function($show = '', $filter = 'raw'){
      return get_bloginfo($show, $filter);
    }));
    $twig->addFunction('__', new Twig_SimpleFunction('__', function($text, $domain = 'default'){
      return __($text, $domain);
    }));

    $twig = apply_filters('get_twig', $twig);

    return $twig;
  }

  function exec_function($function_name){
  	return call_user_func(trim($function_name));
  }

  function twig_pretags( $content ) {
    return preg_replace_callback( '|<pre.*>(.*)</pre|isU' , array(&$this, 'convert_pre_entities'), $content );
  }
function convert_pre_entities( $matches ) {
    return str_replace( $matches[1], htmlentities( $matches[1] ), $matches[0] );
  }


  function add_dir_name_to_locations($locs)
  {
    $locs = array_filter($locs);
    foreach ($locs as &$loc) {
      $loc = trailingslashit($loc) . trailingslashit(self::$dir_name);
    }
    return $locs;
  }

  function template_exists($file, $dirs)
  {
    if (is_string($dirs)) {
      $dirs = array($dirs);
    }
    foreach ($dirs as $dir) {
      $look_for = trailingslashit($dir) . trailingslashit(self::$dir_name) . $file;
      if (file_exists($look_for)) {
        return true;
      }
    }
    return false;
  }

  function twig_choose_template($filenames, $dirs)
  {
    if (is_array($filenames)) {
      /* its an array so we have to figure out which one the dev wants */
      foreach ($filenames as $filename) {
        if (self::template_exists($filename, $dirs)) {
          return $filename;
        }
      }
      return false;
    } else {
      /* its a single, but we still need to figure out if it exists, default to index.html */
      // if (!twig_template_exists($filenames, $dirs)){
      // 	$filenames = 'index.html';
      // }
    }
    return $filenames;
  }


  function get_twig($loader)
  {

    $loader_loc = TIMBER_LOC . '/Twig/lib/Twig/Autoloader.php';
    require_once($loader_loc);
    Twig_Autoloader::register();

    $params = array('debug' => WP_DEBUG, 'autoescape' => false);
    if (Timber::$cache) {
      $params['cache'] = TIMBER_LOC . '/twig-cache';
    }
    $twig = new Twig_Environment($loader, $params);
    $twig->addExtension(new Twig_Extension_Debug());
    $twig->addFilter('resize', new Twig_Filter_Function('wp_resize'));
    $twig->addFilter('letterbox', new Twig_Filter_Function('wp_resize_letterbox'));
    $twig->addFilter('excerpt', new Twig_Filter_Function('twig_make_excerpt'));
    $twig->addFilter('print_r', new Twig_Filter_Function('twig_print_r'));
    $twig->addFilter('print_a', new Twig_Filter_Function('twig_print_a'));
    $twig->addFilter('get_src_from_attachment_id', new Twig_Filter_Function('twig_get_src_from_attachment_id'));
    $twig->addFilter('path', new Twig_Filter_Function('twig_get_path'));
    $twig->addFilter('tojpg', new Twig_Filter_Function('twig_img_to_jpg'));
    $twig->addFilter('wpautop', new Twig_Filter_Function('wpautop'));
    $twig->addFilter('twitterify', new Twig_Filter_Function('twig_twitterify'));
    $twig->addFilter('get_class', new Twig_Filter_Function('twig_get_class'));

    $twig->addFilter('get_type', new Twig_Filter_Function('twig_get_type'));
    $twig->addFilter('shortcodes', new Twig_Filter_Function('twig_shortcodes'));
    $twig->addFilter('sanitize', new Twig_Filter_Function('sanitize_title'));

    $twig->addFilter('wp_body_class', new Twig_Filter_Function('twig_body_class'));
    $twig->addFilter('wp_title', new Twig_Filter_Function('twig_wp_title'));
    $twig->addFilter('wp_sidebar', new Twig_Filter_Function('twig_wp_sidebar'));
    $twig->addFilter('time_ago', new Twig_Filter_Function('twig_time_ago'));

    $twig = apply_filters('get_twig', $twig);
    return $twig;
  }
}

function twig_shortcodes($text)
{
  return do_shortcode($text);
  //apply_filters('the_content', ($text));
}

function twig_get_class($this)
{
  return get_class($this);
}

function twig_get_type($this)
{
  return gettype($this);
}

function wp_resize_external($src, $w, $h)
{
  $upload = wp_upload_dir();
  $dir = $upload['path'];
  $file = parse_url($src);
  $path_parts = pathinfo($file['path']);
  $basename = $path_parts['filename'];
  $newbase = $basename . '-r-' . $w . 'x' . $h;
  $ext = $path_parts['extension'];

  $new_root_path = $dir . '/' . $newbase . '.' . $ext;

  $new_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $new_root_path);
  if (strpos($new_path, '/') != 0) {
    $new_path = '/' . $new_path;
  }
  $ret = array('new_root_path' => $new_root_path, 'old_root_path' => $dir . '/' . $basename . '.' . $ext, 'new_path' => $new_path);

  if (file_exists($new_root_path)) {
    return $ret;
  }
  $image = WPHelper::sideload_image($src);
  return $ret;
}

function hexrgb($hexstr)
{
  $int = hexdec($hexstr);

  return array("red" => 0xFF & ($int >> 0x10), "green" => 0xFF & ($int >> 0x8), "blue" => 0xFF & $int);
}

function wp_resize_letterbox($src, $w, $h, $color = '#000000')
{
  $old_file = WPHelper::get_full_path($src);
  $new_file = WPHelper::get_letterbox_file_path($src, $w, $h);
  $new_file_rel = WPHelper::get_letterbox_file_rel($src, $w, $h);
  $new_file_boxed = str_replace('-lb-', '-lbox-', $new_file);
  if (file_exists($new_file_boxed)) {
    $new_file_rel = str_replace('-lb-', '-lbox-', $new_file_rel);
    return $new_file_rel;
  }

  $bg = imagecreatetruecolor($w, $h);
  $c = hexrgb($color);

  $white = imagecolorallocate($bg, $c['red'], $c['green'], $c['blue']);
  imagefill($bg, 0, 0, $white);

  $image = wp_get_image_editor($old_file);
  if (!is_wp_error($image)) {
    $current_size = $image->get_size();
    $ow = $current_size['width'];
    $oh = $current_size['height'];
    $new_aspect = $w / $h;
    $old_aspect = $ow / $oh;
    if ($new_aspect > $old_aspect) {
      //taller than goal
      $h_scale = $h / $oh;
      $owt = $ow * $h_scale;
      $y = 0;
      $x = $w / 2 - $owt / 2;
      $oht = $h;
      $image->crop(0, 0, $ow, $oh, $owt, $oht);
    } else {
      $w_scale = $w / $ow;
      $oht = $oh * $w_scale;
      $x = 0;
      $y = $h / 2 - $oht / 2;
      $owt = $w;
      $image->crop(0, 0, $ow, $oh, $owt, $oht);
    }
    $image->save($new_file);
    $func = 'imagecreatefromjpeg';
    $ext = pathinfo($new_file, PATHINFO_EXTENSION);
    if ($ext == 'gif') {
      $func = 'imagecreatefromgif';
    } else if ($ext == 'png') {
      $func = 'imagecreatefrompng';
    }
    $image = $func($new_file);
    imagecopy($bg, $image, $x, $y, 0, 0, $owt, $oht);
    $new_file = str_replace('-lb-', '-lbox-', $new_file);
    imagejpeg($bg, $new_file);
    return WPHelper::get_rel_path($new_file);
  }
  return null;
}

function wp_resize($src, $w, $h = 0)
{
  $root = $_SERVER['DOCUMENT_ROOT'];
  if (strstr($src, 'http')) {
    //Its a URL so we need to fetch it
    $external = wp_resize_external($src, $w, $h);
    $old_root_path = $external['old_root_path'];
    $new_root_path = $external['new_root_path'];
    $new_path = $external['new_path'];
  } else {
    //oh good, its in the uploads folder!
    $path_parts = pathinfo($src);
    $basename = $path_parts['filename'];
    $ext = $path_parts['extension'];
    $dir = $path_parts['dirname'];
    $newbase = $basename . '-r-' . $w . 'x' . $h;
    $new_path = $dir . '/' . $newbase . '.' . $ext;
    $new_root_path = $root . $new_path;
    $old_root_path = $root . $src;

    $old_root_path = str_replace('//', '/', $old_root_path);
    $new_root_path = str_replace('//', '/', $new_root_path);

    if (file_exists($new_root_path)) {
      return $new_path;
    }
  }
  $image = wp_get_image_editor($old_root_path);
  if (!is_wp_error($image)) {
    $current_size = $image->get_size();
    $ow = $current_size['width'];
    $oh = $current_size['height'];
    if ($h) {
      $new_aspect = $w / $h;
      $old_aspect = $ow / $oh;

      if ($new_aspect > $old_aspect) {
        //cropping a vertical photo horitzonally
        $oht = $ow / $new_aspect;
        $oy = ($oh - $oht) / 6;
        $image->crop(0, $oy, $ow, $oht, $w, $h);
      } else {
        $owt = $oh * $new_aspect;
        $ox = $ow / 2 - $owt / 2;
        $image->crop($ox, 0, $owt, $oh, $w, $h);
      }
    } else {
      $image->resize($w, $w);
    }
    // $image->
    $image->save($new_root_path);
    return $new_path;
  } else {
    return $src;
  }
}

function twig_twitterify($ret)
{
  $ret = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $ret);
  $ret = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $ret);
  $pattern = '#([0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.';
  $pattern .= '[a-wyz][a-z](fo|g|l|m|mes|o|op|pa|ro|seum|t|u|v|z)?)#i';
  $ret = preg_replace($pattern, '<a href="mailto:\\1">\\1</a>', $ret);
  $ret = preg_replace("/\B@(\w+)/", " <a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $ret);
  $ret = preg_replace("/\B#(\w+)/", " <a href=\"http://search.twitter.com/search?q=\\1\" target=\"_blank\">#\\1</a>", $ret);
  return $ret;
}

function twig_time_ago($from, $to = null)
{
  $to = (($to === null) ? (time()) : ($to));
  $to = ((is_int($to)) ? ($to) : (strtotime($to)));
  $from = ((is_int($from)) ? ($from) : (strtotime($from)));

  $units = array(
    "year" => 29030400, // seconds in a year   (12 months)
    "month" => 2419200, // seconds in a month  (4 weeks)
    "week" => 604800, // seconds in a week   (7 days)
    "day" => 86400, // seconds in a day    (24 hours)
    "hour" => 3600, // seconds in an hour  (60 minutes)
    "minute" => 60, // seconds in a minute (60 seconds)
    "second" => 1 // 1 second
  );

  $diff = abs($from - $to);
  $suffix = (($from > $to) ? ("from now") : ("ago"));
  $output = '';
  foreach ($units as $unit => $mult) {
    if ($diff >= $mult) {
      $and = (($mult != 1) ? ("") : ("and "));
      $output .= ", " . $and . intval($diff / $mult) . " " . $unit . ((intval($diff / $mult) == 1) ? ("") : ("s"));
      $diff -= intval($diff / $mult) * $mult;
      break;
    }
  }
  $output .= " " . $suffix;
  $output = substr($output, strlen(", "));
  return $output;
}

function twig_wp_sidebar($arg)
{
  get_sidebar($arg);
}

function twig_wp_title()
{
  return wp_title('|', false, 'right');
}

function twig_body_class($body_classes)
{
  ob_start();
  if (is_array($body_classes)) {
    $body_classes = explode(' ', $body_classes);
  }
  body_class($body_classes);
  $return = ob_get_contents();
  ob_end_clean();
  return $return;
}

function render_twig_string($string, $data = array())
{
  $loader = new Twig_Loader_String();
  $twig = new Twig_Environment($loader);
  return $twig->render($string, $data);
}

function get_calling_script_dir($backtrace)
{
  $caller = $backtrace[0]['file'];
  $pathinfo = pathinfo($caller);
  $dir = $pathinfo['dirname'];
  return $dir . '/';
}

//deprecated
function render_twig($filenames, $data = array(), $echo = true)
{
  $caller = Timber::get_calling_script_dir();
  $loader = new TimberLoader($caller);
  $file = $loader->choose_template($filenames);
  $output = '';
  if (strlen($file)) {
    $output = $loader->render($file, $data);
  }
  if ($echo) {
    echo $output;
  }
  return $output;
}

function twig_get_src_from_attachment_id($aid)
{
  return WPHelper::get_image_path($aid);
}



function twig_get_path($url)
{
  $url = parse_url($url);
  return $url['path'];
}

function twig_make_excerpt($text, $length = 55)
{
  return wp_trim_words($text, $length);
}

function twig_invoke($method, $obj)
{
  $product = '';
  $totalParams = $method->getNumberOfParameters();
  $reqParams = $method->getNumberOfRequiredParameters();
  if (!$method->getNumberOfParameters()) {
    //zero parameters, easy street
    $product = $method->invoke($obj);
    //$product = $method->getName();
  } else if ($method->getNumberOfRequiredParameters()) {
    //there are required parametres
    //$product = $method->getName();
  } else if ($totalParams && !$reqParams) {
    //all params are optional
    $pass = array();
    $product = $pass;
    if ($method->getName() == 'get_preview') {
      $function = $method->getName();
      // try {
      // 	$product = $obj->$function();
      // } catch($e){
      // 	$product = 'error with '.$method->getName();
      // }
    }

    //$product = $method->invokeArgs($obj, $pass);
    //$product = $args;
  } else {
    $product = '?????';
  }
  return $product;
}

function twig_print_r($arr)
{
  return print_r($arr, true);
}

function twig_print_a($arr)
{
  return '<pre>' . twig_object_docs($arr, true) . '</pre>';
}

function twig_object_docs($obj)
{
  if (!class_exists(get_class($obj))){
    return false;
  }
  $reflector = new ReflectionClass($obj);
  $methods = $reflector->getMethods();
  $rets = array();
  $rep = $reflector->getProperty('representation')->getValue();
  foreach ($methods as $method) {
    if ($method->isPublic()) {
      $comments = $method->getDocComment();
      $comments = str_replace('/**', '', $comments);
      //$comments = preg_replace('(\/)(\*)(\*)\r', '', $comments);
      $info = new stdClass();
      $info->comments = $comments;
      $info->returns = twig_invoke($method, $obj);
      $info->params = $method->getParameters();
      //if (strlen($comments) && !strstr($comments, '@nodoc')){
      //$rets[$rep.'.'.$method->name] = $comments;
      //$rets[$rep.'.'.$method->name] = $info->returns;
      $rets[$method->name] = $info->returns;
      //}
    }
  }
  foreach ($obj as $key => $value) {
    $rets[$key] = $value;
  }
  ksort($rets);

  return '<pre>' . (print_r($rets, true)) . '</pre>';
}

function twig_img_to_jpg($src)
{
  $output = str_replace('.png', '.jpg', $src);
  $oldpath = $_SERVER['DOCUMENT_ROOT'] . $src;
  $newpath = $_SERVER['DOCUMENT_ROOT'] . $output;
  if (file_exists($newpath)) {
    return $output;
  }
  //make it!
  $image = wp_get_image_editor($oldpath);
  if (!is_wp_error($image)){
    $image->save($newpath);
    return $output;
  }
  return $src;
}

new TimberTwig();
