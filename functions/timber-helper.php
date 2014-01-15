<?php

class TimberHelper {

	public static function transient($slug, $callback, $transient_time = 0){
		$disable_transients = false;
		if (defined('WP_DISABLE_TRANSIENTS')){
			$disable_transients = WP_DISABLE_TRANSIENTS;
		}
		$data = null;
		if ($transient_time === false){
			$data = $callback();
			return $data;
		}
		if (is_callable($callback) && (false === ($data = get_transient($slug)) || $disable_transients) && $transient_time !== false){
			$cache_lock_slug = $slug.'_lock';

			if (get_transient($cache_lock_slug)){
				//the server is currently executing the process.
				//We're just gonna dump these users. Sorry!
				return false;
			}
			set_transient($cache_lock_slug, true, $transient_time);
			$data = $callback();
			set_transient($slug, $data, $transient_time);
			delete_transient($cache_lock_slug);
		}
		return $data;
	}

	public static function start_timer(){
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		return $time;
	}

	public static function stop_timer($start){
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish = $time;
		$total_time = round(($finish - $start), 4);
		return $total_time.' seconds.';
	}

	public static function is_array_assoc($arr) {
		if (!is_array($arr)) {
			return false;
		}
		return (bool)count(array_filter(array_keys($arr), 'is_string'));
	}

	public static function preslashit($path){
		if (strpos($path, '/') != 0) {
			$path = '/' . $path;
		}
		return $path;
	}

	public static function ob_function($function, $args = array(null)) {
		ob_start();
		call_user_func_array($function, $args);
		$data = ob_get_contents();
		ob_end_clean();
		return $data;
	}

    public static function function_wrapper($function_name, $defaults = array(), $return_output_buffer = false) {
        return new TimberFunctionWrapper($function_name, $defaults, $return_output_buffer);
    }

	public static function is_url($url) {
		if (!is_string($url)){
			return false;
		}
		$url = strtolower($url);
		if (strstr('://', $url)) {
			return true;
		}
		return false;
	}

	public static function get_path_base() {
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

	public static function get_full_path($src) {
		$root = ABSPATH;
		$old_root_path = $root . $src;
		$old_root_path = str_replace('//', '/', $old_root_path);
		return $old_root_path;
	}

	public static function is_local($url){
		if (strstr($url, $_SERVER['HTTP_HOST'])){
			return true;
		}
		return false;
	}

	/* URL Stuff
	======================== */

	public static function get_rel_url($url, $force = false){
		if (!strstr($url, $_SERVER['HTTP_HOST']) && !$force){
			return $url;
		}
		$url_info = parse_url($url);
		$link = $url_info['path'];
		if (isset($url_info['query']) && strlen($url_info['query'])){
			$link .= '?'.$url_info['query'];
		}
		return $link;
	}

	public static function get_rel_path($src) {
		return str_replace(ABSPATH, '', $src);
	}

	public static function remove_double_slashes($url){
		$url = str_replace('//', '/', $url);
		if (strstr($url, 'http:') && !strstr($url, 'http://')){
			$url = str_replace('http:/', 'http://', $url);
		}
		return $url;
	}

	public static function prepend_to_url($url, $path){
		if (strstr(strtolower($url), 'http')){
			$url_parts = parse_url($url);
			$url = $url_parts['scheme'].'://'.$url_parts['host'].$path.$url_parts['path'];
		} else {
			$url = $url.$path;
		}
		return self::remove_double_slashes($url);
	}

	public static function download_url($url, $timeout = 300) {
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

	public static function osort(&$array, $prop) {
		usort($array, function ($a, $b) use ($prop) {
			return $a->$prop > $b->$prop ? 1 : -1;
		});
	}

	public static function error_log($arg) {
		if (!WP_DEBUG){
			return;
		}
		if (is_object($arg) || is_array($arg)) {
			$arg = print_r($arg, true);
		}
		error_log($arg);
	}

	public static function get_params($i = -1) {
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

	public static function get_json($url) {
		$data = self::get_curl($url);
		return json_decode($data);
	}

	public static function get_curl($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		$content = curl_exec($ch);
		curl_close($ch);
		return $content;
	}

	public static function get_wp_title() {
		return wp_title('|', false, 'right');
	}

	public static function force_update_option($option, $value) {
		global $wpdb;
		$wpdb->query("UPDATE $wpdb->options SET option_value = '$value' WHERE option_name = '$option'");
	}

	public static function get_current_url() {
		$pageURL = "http://";
		if (isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on"){
			$pageURL = "https://";;
		}
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}

	public static function trim_words($text, $num_words = 55, $more = null, $allowed_tags = 'p a span b i br') {
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

	public static function close_tags($html) {
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

	public static function get_posts_by_meta($key, $value) {
		global $wpdb;
		$query = $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s", $key, $value);
		$results = $wpdb->get_col($query);
		$pids = array();
		foreach ($results as $result) {
			if (get_post($result)) {
				$pids[] = $result;
			}
		}
		if (count($pids)) {
			return $pids;
		}
		return 0;
	}

	public static function get_post_by_meta($key, $value) {
		global $wpdb;
		$query = $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value = %s ORDER BY post_id", $key, $value);
		$results = $wpdb->get_col($query);
		foreach($results as $result){
			if ($result && get_post($result)) {
				return $result;
			}
		}
		return 0;
	}

	public static function get_term_id_by_term_taxonomy_id($ttid){
		global $wpdb;
		$query = $wpdb->prepare("SELECT term_id FROM $wpdb->term_taxonomy WHERE term_taxonomy_id = %s", $ttid);
		return $wpdb->get_var($query);
	}

	/* this $args thing is a fucking mess, fix at some point:

	http://codex.wordpress.org/Function_Reference/comment_form */

	public static function get_comment_form($post_id = null, $args = array()) {
		ob_start();
		comment_form($args, $post_id);
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}

	public static function is_true($property) {
		if (isset($property)) {
			if ($property == 'true' || $property == 1 || $property == '1' || $property == true) {
				return true;
			}
		}
		return false;
	}

	public static function array_to_object($array) {
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

	public static function get_object_index_by_property($array, $key, $value) {
		if (is_array($array)) {
			$i = 0;
			foreach ($array as $arr) {
				if (is_array($arr)){
					if ($arr[$key] == $value){
						return $i;
					}
				} else {
					if ($arr->$key == $value) {
						return $i;
					}
				}
				$i++;
			}
		}
		return false;
	}

	public static function get_object_by_property($array, $key, $value) {
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

	public static function get_image_path($iid) {
		$size = 'full';
		$src = wp_get_attachment_image_src($iid, $size);
		$src = $src[0];
		return self::get_rel_path($src);
	}

	public static function array_truncate($array, $len) {
		if (sizeof($array) > $len) {
			$array = array_splice($array, 0, $len);
		}
		return $array;
	}

	public static function iseven($i) {
		return ($i % 2) == 0;
	}

	public static function isodd($i) {
		return ($i % 2) != 0;
	}

	public static function is_external($url){
		$has_http = strstr(strtolower($url), 'http');
        $on_domain = strstr($url, $_SERVER['HTTP_HOST']);
        if ($has_http && !$on_domain){
            return true;
        }
        return false;
	}

	public static function twitterify($ret) {
		$ret = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $ret);
		$ret = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $ret);
		$pattern = '#([0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.';
		$pattern .= '[a-wyz][a-z](fo|g|l|m|mes|o|op|pa|ro|seum|t|u|v|z)?)#i';
		$ret = preg_replace($pattern, '<a href="mailto:\\1">\\1</a>', $ret);
		$ret = preg_replace("/\B@(\w+)/", " <a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $ret);
		$ret = preg_replace("/\B#(\w+)/", " <a href=\"http://search.twitter.com/search?q=\\1\" target=\"_blank\">#\\1</a>", $ret);
		return $ret;
	}

	public static function paginate_links( $args = '' ) {
		$defaults = array(
			'base' => '%_%', // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
			'format' => '?page=%#%', // ?page=%#% : %#% is replaced by the page number
			'total' => 1,
			'current' => 0,
			'show_all' => false,
			'prev_next' => true,
			'prev_text' => __('&laquo; Previous'),
			'next_text' => __('Next &raquo;'),
			'end_size' => 1,
			'mid_size' => 2,
			'type' => 'array',
			'add_args' => false, // array of query args to add
			'add_fragment' => ''
		);

		$args = wp_parse_args( $args, $defaults );
		extract($args, EXTR_SKIP);

		// Who knows what else people pass in $args
		$total = (int) $total;
		if ( $total < 2 ){
			return;
		}
		$current  = (int) $current;
		$end_size = 0  < (int) $end_size ? (int) $end_size : 1; // Out of bounds?  Make it the default.
		$mid_size = 0 <= (int) $mid_size ? (int) $mid_size : 2;
		$add_args = is_array($add_args) ? $add_args : false;
		$r = '';
		$page_links = array();
		$n = 0;
		$dots = false;
		if ( $prev_next && $current && 1 < $current ){
			$link = str_replace('%_%', 2 == $current ? '' : $format, $base);
			$link = str_replace('%#%', $current - 1, $link);
			if ( $add_args ){
				$link = add_query_arg( $add_args, $link );
			}
			$link .= $add_fragment;
			$page_links[] = array('class' => 'prev page-numbers', 'link' => esc_url( apply_filters( 'paginate_links', $link )), 'title' => $prev_text);
		}
		for ( $n = 1; $n <= $total; $n++ ) {
			$n_display = number_format_i18n($n);
			if ( $n == $current ) {
				$page_links[] = array('class' => 'page-number page-numbers current', 'title' => $n_display, 'text' => $n_display, 'name' => $n_display);
				$dots = true;
			} else {
				if ( $show_all || ( $n <= $end_size || ( $current && $n >= $current - $mid_size && $n <= $current + $mid_size ) || $n > $total - $end_size ) ) {
					$link = str_replace('%_%', 1 == $n ? '' : $format, $base);
					$link = str_replace('%#%', $n, $link);
					if ( $add_args ) {
						$link = add_query_arg( $add_args, $link );
					}
					$link = trailingslashit($link).ltrim($add_fragment, '/');
					$page_links[] = array('class' => 'page-number page-numbers', 'link' => esc_url( apply_filters( 'paginate_links', $link ) ), 'title' => $n_display);
					$dots = true;
				} elseif ( $dots && !$show_all ) {
					$page_links[] = array('class' => 'dots', 'title' => __( '&hellip;' ));
					$dots = false;
				}
			}
		}
		if ( $prev_next && $current && ( $current < $total || -1 == $total ) ) {
			$link = str_replace('%_%', $format, $base);
			$link = str_replace('%#%', $current + 1, $link);
			if ( $add_args ) {
				$link = add_query_arg( $add_args, $link );
			}
			$link = trailingslashit($link).$add_fragment;
			$page_links[] = array('class' => 'next page-numbers', 'link' => esc_url( apply_filters( 'paginate_links', $link ) ), 'title' => $next_text);
		}
		return $page_links;
	}
}

class WPHelper extends TimberHelper {
	//for backwards compat, will remove eventually
}