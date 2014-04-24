<?php

class TimberTwig {

	function __construct() {
		add_action('twig_apply_filters', array(&$this, 'add_twig_filters'));
	}

	/**
	* @param Twig_Environment $twig
	* @return Twig_Environment
	*/
	function add_twig_filters($twig) {
		/* image filters */
		$twig->addFilter('resize', new Twig_Filter_Function(array('TimberImageHelper', 'resize')));
		$twig->addFilter('letterbox', new Twig_Filter_Function(array('TimberImageHelper', 'letterbox')));
		$twig->addFilter('tojpg', new Twig_Filter_Function(array('TimberImageHelper', 'img_to_jpg')));
		$twig->addFilter('get_src_from_attachment_id', new Twig_Filter_Function('twig_get_src_from_attachment_id'));

		/* debugging filters */
		$twig->addFilter('docs', new Twig_Filter_function('twig_object_docs'));
		$twig->addFilter('get_class', new Twig_Filter_Function('twig_get_class'));
		$twig->addFilter('get_type', new Twig_Filter_Function('twig_get_type'));
		$twig->addFilter('print_r', new Twig_Filter_Function('twig_print_r'));
		$twig->addFilter('print_a', new Twig_Filter_Function('twig_print_a'));

		/* other filters */
		$twig->addFilter('stripshortcodes', new Twig_Filter_Function('strip_shortcodes'));
		$twig->addFilter('array', new Twig_Filter_Function(array($this, 'to_array')));
		$twig->addFilter('excerpt', new Twig_Filter_Function('twig_make_excerpt'));
		$twig->addFilter('function', new Twig_Filter_Function(array($this, 'exec_function')));
		$twig->addFilter('path', new Twig_Filter_Function('twig_get_path'));
		$twig->addFilter('pretags', new Twig_Filter_Function(array($this, 'twig_pretags')));
		$twig->addFilter('sanitize', new Twig_Filter_Function('sanitize_title'));
		$twig->addFilter('shortcodes', new Twig_Filter_Function('twig_shortcodes'));
		$twig->addFilter('time_ago', new Twig_Filter_Function('twig_time_ago'));
		$twig->addFilter('twitterify', new Twig_Filter_Function(array('TimberHelper', 'twitterify')));
		$twig->addFilter('twitterfy', new Twig_Filter_Function(array('TimberHelper', 'twitterify')));
		$twig->addFilter('wp_body_class', new Twig_Filter_Function('twig_body_class'));
		$twig->addFilter('wpautop', new Twig_Filter_Function('wpautop'));
		$twig->addFilter('relative', new Twig_Filter_Function(function($link){
			return TimberURLHelper::get_rel_url($link, true);
		}));

		$twig->addFilter('truncate', new Twig_Filter_Function(function($text, $len){
			return TimberHelper::trim_words($text, $len);
		}));

        /* actions and filters */
        $twig->addFunction(new Twig_SimpleFunction('action', function(){
            call_user_func_array('do_action', func_get_args());
        }));
        $twig->addFilter( new Twig_SimpleFilter('apply_filters', function(){
            $args = func_get_args();
            $tag = current(array_splice($args, 1, 1));

            return apply_filters_ref_array($tag, $args);
        }));
        $twig->addFunction(new Twig_SimpleFunction('function', array(&$this, 'exec_function')));
        $twig->addFunction(new Twig_SimpleFunction('fn', array(&$this, 'exec_function')));

        /* TimberObjects */
        $twig->addFunction(new Twig_SimpleFunction('TimberPost', function($pid, $PostClass = 'TimberPost'){
        	if (is_array($pid) && !TimberHelper::is_array_assoc($pid)){
        		foreach($pid as &$p){
        			$p = new $PostClass($p);
        		}
        		return $pid;
        	}
        	return new $PostClass($pid);
        }));
        $twig->addFunction(new Twig_SimpleFunction('TimberImage', function($pid, $ImageClass = 'TimberImage'){
        	if (is_array($pid) && !TimberHelper::is_array_assoc($pid)){
        		foreach($pid as &$p){
        			$p = new $ImageClass($p);
        		}
        		return $pid;
        	}
        	return new $ImageClass($pid);
        }));
        $twig->addFunction(new Twig_SimpleFunction('TimberTerm', function($pid, $TermClass = 'TimberTerm'){
        	if (is_array($pid) && !TimberHelper::is_array_assoc($pid)){
        		foreach($pid as &$p){
        			$p = new $TermClass($p);
        		}
        		return $pid;
        	}
        	return new $TermClass($pid);
        }));

        /* bloginfo and translate */
		$twig->addFunction('bloginfo', new Twig_SimpleFunction('bloginfo', function($show = '', $filter = 'raw'){
			return get_bloginfo($show, $filter);
		}));
		$twig->addFunction('__', new Twig_SimpleFunction('__', function($text, $domain = 'default'){
			return __($text, $domain);
		}));

		$twig = apply_filters('get_twig', $twig);

		return $twig;
	}

    /**
     * @param mixed $arr
     * @return array
     */
    function to_array($arr){
		if (is_array($arr)){
			return $arr;
		}
		$arr = array($arr);
		return $arr;
	}

    /**
     * @param string $function_name
     * @return mixed
     */
    function exec_function($function_name){
		$args = func_get_args();
		array_shift($args);
		return call_user_func_array(trim($function_name), ($args));
	}

    /**
     * @param string $content
     * @return string
     */
    function twig_pretags( $content ) {
		return preg_replace_callback( '|<pre.*>(.*)</pre|isU' , array(&$this, 'convert_pre_entities'), $content );
	}

    /**
     * @param array $matches
     * @return string
     */
    function convert_pre_entities( $matches ) {
		return str_replace( $matches[1], htmlentities( $matches[1] ), $matches[0] );
	}

    /**
     * @param array $locs
     * @return array
     */
    function add_dir_name_to_locations($locs) {
		$locs = array_filter($locs);
		foreach ($locs as &$loc) {
			$loc = trailingslashit($loc) . trailingslashit(self::$dir_name);
		}
		return $locs;
	}

    /**
     * @param string $file
     * @param array|string $dirs
     * @return bool
     */
    function template_exists($file, $dirs) {
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

}

/**
 * @param string $text
 * @return string
 */
function twig_shortcodes($text) {
	return do_shortcode($text);
	//apply_filters('the_content', ($text));
}

/**
 * @param mixed $this
 * @return string
 */
function twig_get_class($this) {
	return get_class($this);
}

/**
 * @param mixed $this
 * @return string
 */
function twig_get_type($this) {
	return gettype($this);
}


/**
 * @param int|string $from
 * @param int|string $to
 * @param string $format_past
 * @param string $format_future
 * @return string
 */
function twig_time_ago($from, $to = null, $format_past='%s ago', $format_future='%s from now') {
	$to = (($to === null) ? (time()) : ($to));
	$to = ((is_int($to)) ? ($to) : (strtotime($to)));
	$from = ((is_int($from)) ? ($from) : (strtotime($from)));

	if ($from < $to) {
		return sprintf($format_past, human_time_diff($from, $to));
	} else {
		return sprintf($format_future, human_time_diff($to, $from));
	}
}

/**
 * @param mixed $body_classes
 * @return string
 */
function twig_body_class($body_classes) {
	ob_start();
	if (is_array($body_classes)) {
		$body_classes = explode(' ', $body_classes);
	}
	body_class($body_classes);
	$return = ob_get_contents();
	ob_end_clean();
	return $return;
}

/**
 * @param string $string
 * @param array $data
 * @return string
 */
function render_twig_string($string, $data = array()) {
	$timber_loader = new TimberLoader();
	$timber_loader->get_twig();
	$loader = new Twig_Loader_String();
	$twig = new Twig_Environment($loader);
	return $twig->render($string, $data);
}

/**
 * @param array $backtrace
 * @return string
 */
function get_calling_script_dir($backtrace) {
	$caller = $backtrace[0]['file'];
	$pathinfo = pathinfo($caller);
	$dir = $pathinfo['dirname'];
	return $dir . '/';
}

//deprecated

/**
 * @param int $aid
 * @return string
 */
function twig_get_src_from_attachment_id($aid) {
  return TimberImageHelper::get_image_path($aid);
}

/**
 * @param string $url
 * @return mixed
 */
function twig_get_path($url) {
	$url = parse_url($url);
	return $url['path'];
}

/**
 * @param string $text
 * @param int $length
 * @return string
 */
function twig_make_excerpt($text, $length = 55){
	return wp_trim_words($text, $length);
}

/**
 * @param array $arr
 * @return mixed
 */
function twig_print_r($arr) {
	//$rets = twig_object_docs($obj, false);
	return print_r($arr, true);
	return $rets;
}

/**
 * @param array $arr
 * @return string
 */
function twig_print_a($arr) {
	return '<pre>' . twig_object_docs($arr, true) . '</pre>';
}

/**
 * @param mixed $obj
 * @param bool $methods
 * @return string
 */
function twig_object_docs($obj, $methods = true) {
	$class = get_class($obj);
	$properties = (array) $obj;
	if ($methods){
		$methods = $obj->get_method_values();
	}
	$rets = array_merge($properties, $methods);
	ksort($rets);
	$str = print_r($rets, true);
	$str = str_replace('Array', $class . ' Object', $str);
	return $str;
}

new TimberTwig();
