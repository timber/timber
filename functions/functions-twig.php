<?php 

	function get_twig($uri){
		$loader_loc = TIMBER_LOC.'/Twig/lib/Twig/Autoloader.php';
		require_once($loader_loc);
		$reg = Twig_Autoloader::register();
		if (is_array($uri)){
			$loaders = array();
			foreach($uri as $u){
				$loaders[] = new Twig_Loader_Filesystem($u.'/views/');
			}
			$loader = new Twig_Loader_Chain($loaders);
		} else {
			$loader = new Twig_Loader_Filesystem($uri.'/views/');
		}
		$twig = new Twig_Environment($loader, array(
    		/*'cache' => TIMBER_LOC.'/twig-cache',*/
			'debug' => false,
			'autoescape' => false
		));

		
		$twig->addExtension(new Twig_Extension_Debug());
		$twig->addFilter('resize', new Twig_Filter_Function('twig_resize_image'));
		$twig->addFilter('excerpt', new Twig_Filter_Function('twig_make_excerpt'));
		$twig->addFilter('print_r', new Twig_Filter_Function('twig_print_r'));
		$twig->addFilter('print_a', new Twig_Filter_Function('twig_print_a'));
		$twig->addFilter('get_src_from_attachment_id', new Twig_Filter_Function('twig_get_src_from_attachment_id'));
		$twig->addFilter('path', new Twig_Filter_Function('twig_get_path'));
		$twig->addFilter('tojpg', new Twig_Filter_Function('twig_img_to_jpg'));
		$twig->addFilter('wpautop', new Twig_Filter_Function('wpautop'));
		$twig->addFilter('twitterify', new Twig_Filter_Function('twitterify'));

		$twig->addFilter('get_class', new Twig_Filter_Function('twig_get_class'));

		$twig->addFilter('get_type', new Twig_Filter_Function('twig_get_type'));

		$twig->addFilter('sanitize', new Twig_Filter_Function('sanitize_title'));

		$twig->addFilter('editable', new Twig_Filter_Function('twig_editable'));
		$twig->addFilter('cdn', new Twig_Filter_Function('twig_cdn'));

		$twig->addFilter('wp_body_class', new Twig_Filter_Function('twig_body_class'));
		$twig->addFilter('wp_title', new Twig_Filter_Function('twig_wp_title'));
		$twig->addFilter('wp_sidebar', new Twig_Filter_Function('twig_wp_sidebar'));
		$twig->addFilter('time_ago', new Twig_Filter_Function('twig_time_ago'));
		$twig->addFilter('get_post_info', new Twig_Filter_Function('twig_get_post_info'));

		$twig = apply_filters('get_twig', $twig);
		return $twig;
	}

	function twig_get_class($this){
		return get_class($this);
	}

	function twig_get_type($this){
		return gettype($this);
	}

	function twig_time_ago($from, $to = null) {
        $to = (($to === null) ? (time()) : ($to));
  		$to = ((is_int($to)) ? ($to) : (strtotime($to)));
  		$from = ((is_int($from)) ? ($from) : (strtotime($from)));

  		$units = array(
			"year"   => 29030400, // seconds in a year   (12 months)
			"month"  => 2419200,  // seconds in a month  (4 weeks)
			"week"   => 604800,   // seconds in a week   (7 days)
			"day"    => 86400,    // seconds in a day    (24 hours)
			"hour"   => 3600,     // seconds in an hour  (60 minutes)
			"minute" => 60,       // seconds in a minute (60 seconds)
			"second" => 1         // 1 second
		);

  		$diff = abs($from - $to);
  		$suffix = (($from > $to) ? ("from now") : ("ago"));
  		$output = '';
  		foreach($units as $unit => $mult) {
   			if ($diff >= $mult) {
    			$and = (($mult != 1) ? ("") : ("and "));
    			$output .= ", ".$and.intval($diff / $mult)." ".$unit.((intval($diff / $mult) == 1) ? ("") : ("s"));
    			$diff -= intval($diff / $mult) * $mult;
    			break;
   			}
		}
  		$output .= " ".$suffix;
  		$output = substr($output, strlen(", "));
  		return $output;
    }

	function twig_get_post_info($id, $field = 'path'){
		$pi = PostMaster::get_post_info($id);
		return $pi->$field;
	}

	function twig_wp_sidebar($arg){
		get_sidebar($arg);
	}

	function twig_wp_title(){
		return wp_title('|', false, 'right'); 
	}

	function twig_body_class($body_classes){
		ob_start();
		if (is_array($body_classes)){
			$body_classes = explode(' ', $body_classes);
		}
		body_class($body_classes);
		$return = ob_get_contents();
		ob_end_clean();
		return $return;
	}

	function twig_cdn($path){
		return 'http://yourcdn.com'.$path;
	}

	function twig_template_exists($file, $dirs){
		if (is_string($dirs)){
			$dirs = array($dirs);
		}
		foreach($dirs as $dir){
			$look_for = $dir.'/views/'.$file;
			if (file_exists($look_for)){
				return true;
			}
		}
		return false;
	}

	function twig_choose_template($filenames, $dirs){
		if(is_array($filenames)){
			/* its an array so we have to figure out which one the dev wants */
			foreach($filenames as $filename){
				if (twig_template_exists($filename, $dirs)){
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

	function render_twig_string($string, $data = array()){
		$loader = new Twig_Loader_String();
		$twig = new Twig_Environment($loader);
		return $twig->render($string, $data);
	}

	function get_calling_script_dir($backtrace){
		$caller = $backtrace[0]['file'];
		$pathinfo = pathinfo($caller);
		$dir = $pathinfo['dirname'];
		return $dir.'/';
	}

	function render_twig($filenames, $data = array(), $render = true){
		$backtrace = debug_backtrace();

		$dir = get_calling_script_dir($backtrace);
		
		if(!$data){
			$data = array();
		}
		$uri = array();
		$uri[] = get_stylesheet_directory();
		$uri_parent = get_template_directory();

		if ($uri[0] != $uri_parent){
			$uri[] = $uri_parent;
		}
		$uri[] = $dir;
		$twig = get_twig($uri);
		
		$filename = twig_choose_template($filenames, $uri);
		$output = '';
		if (strlen($filename)){
			$output = $twig->render($filename, $data);
		}
		if ($render){
			echo $output;
		}
		return $output;
	}

	function twig_editable($content, $ID, $field){
		if (!function_exists('ce_wrap_content')){
			return $content;
		}		
		return ce_wrap_content_field($content, $ID, $field);
	}

	function twig_get_src_from_attachment_id($aid){
		$src = PostMaster::get_image_path($aid);
		return $src;
	}

	function twig_print_r($arr){
		return print_r($arr, true);
	}

	function twig_print_a($arr){
		return '<pre>'.print_r($arr, true).'</pre>';
	}

	function twig_get_path($url){
		$url = parse_url($url);
		return $url['path'];
	}

	function twig_make_excerpt($text, $length = 55){
		return wp_trim_words($text, $length);
	}

	function twig_img_to_jpg($src){
		$output = str_replace('.png', '.jpg', $src);
		if (file_exists($_SERVER['DOCUMENT_ROOT'].$output)){
			return $output;
		}
		$image = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'].$src);
		$w = imagesx($image);
		$h = imagesy($image);
		$bg = imagecreatetruecolor($w, $h);
		imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
		imagealphablending($bg, TRUE);
		imagecopy($bg, $image, 0, 0, 0, 0, $w, $h);
    	imagejpeg($bg, '/'.$_SERVER['DOCUMENT_ROOT'].$output, 90);
    	imagedestroy($image);
    	return $output;
	}
	
	function twig_resize_image($src, $w, $h = 0, $ratio = 0, $append = ''){
		return InkwellImage::get_photon($src, $w, $h, $ratio, $append);
	}

	
