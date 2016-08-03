<?php

namespace Timber;

use Timber\FunctionWrapper;
use Timber\URLHelper;

/**
 * As the name suggests these are helpers for Timber (and you!) when developing. You can find additional (mainly internally-focused helpers) in TimberURLHelper
 */
class Helper {

	/**
	 * A utility for a one-stop shop for Transients
	 * @api
	 * @example
	 * ```php
	 * $favorites = Timber::transient('user-'.$uid.'-favorites', function() use ($uid) {
	 *  	//some expensive query here that's doing something you want to store to a transient
	 *  	return $favorites;
	 * }, 600);
	 * Timber::context['favorites'] = $favorites;
	 * Timber::render('single.twig', $context);
	 * ```
	 *
	 * @param string  	$slug           Unique identifier for transient
	 * @param callable 	$callback      Callback that generates the data that's to be cached
	 * @param integer  	$transient_time (optional) Expiration of transients in seconds
	 * @param integer 	$lock_timeout   (optional) How long (in seconds) to lock the transient to prevent race conditions
	 * @param boolean 	$force          (optional) Force callback to be executed when transient is locked
	 * @return mixed
	 */
	public static function transient( $slug, $callback, $transient_time = 0, $lock_timeout = 5, $force = false ) {
		$slug = apply_filters('timber/transient/slug', $slug);

		$enable_transients = ($transient_time === false || (defined('WP_DISABLE_TRANSIENTS') && WP_DISABLE_TRANSIENTS)) ? false : true;
		$data = $enable_transients ? get_transient($slug) : false;

		if ( false === $data ) {
			$data = self::handle_transient_locking($slug, $callback, $transient_time, $lock_timeout, $force, $enable_transients);
		}
		return $data;
	}

	/**
	 * Does the dirty work of locking the transient, running the callback and unlocking
	 * @param string 	$slug
	 * @param callable 	$callback
	 * @param integer  	$transient_time Expiration of transients in seconds
	 * @param integer 	$lock_timeout   How long (in seconds) to lock the transient to prevent race conditions
	 * @param boolean 	$force          Force callback to be executed when transient is locked
	 * @param boolean 	$enable_transients Force callback to be executed when transient is locked
	 */
	protected static function handle_transient_locking( $slug, $callback, $transient_time, $lock_timeout, $force, $enable_transients ) {
		if ( $enable_transients && self::_is_transient_locked($slug) ) {
			$force = apply_filters('timber_force_transients', $force);
			$force = apply_filters('timber_force_transient_'.$slug, $force);
			if ( !$force ) {
				//the server is currently executing the process.
				//We're just gonna dump these users. Sorry!
				return false;
			}
			$enable_transients = false;
		}
		// lock timeout shouldn't be higher than 5 seconds, unless
		// remote calls with high timeouts are made here
		if ( $enable_transients ) {
			self::_lock_transient($slug, $lock_timeout);
		}
		$data = $callback();
		if ( $enable_transients ) {
			set_transient($slug, $data, $transient_time);
			self::_unlock_transient($slug);
		}
		return $data;
	}

	/**
	 * @internal
	 * @param string $slug
	 * @param integer $lock_timeout
	 */
	public static function _lock_transient( $slug, $lock_timeout ) {
		set_transient($slug.'_lock', true, $lock_timeout);
	}

	/**
	 * @internal
	 * @param string $slug
	 */
	public static function _unlock_transient( $slug ) {
		delete_transient($slug.'_lock', true);
	}

	/**
	 * @internal
	 * @param string $slug
	 */
	public static function _is_transient_locked( $slug ) {
		return (bool) get_transient($slug.'_lock');
	}

	/* These are for measuring page render time */

	/**
	 * For measuring time, this will start a timer
	 * @api
	 * @return float
	 */
	public static function start_timer() {
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		return $time;
	}

	/**
	 * For stopping time and getting the data
	 * @example
	 * ```php
	 * $start = TimberHelper::start_timer();
	 * // do some stuff that takes awhile
	 * echo TimberHelper::stop_timer( $start );
	 * ```
	 * @param int     $start
	 * @return string
	 */
	public static function stop_timer( $start ) {
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish = $time;
		$total_time = round(($finish - $start), 4);
		return $total_time.' seconds.';
	}

	/* Function Utilities
	======================== */

	/**
	 * Calls a function with an output buffer. This is useful if you have a function that outputs text that you want to capture and use within a twig template.
	 * @example
	 * ```php
	 * function the_form() {
	 *     echo '<form action="form.php"><input type="text" /><input type="submit /></form>';
	 * }
	 *
	 * $context = Timber::get_context();
	 * $context['post'] = new TimberPost();
	 * $context['my_form'] = TimberHelper::ob_function('the_form');
	 * Timber::render('single-form.twig', $context);
	 * ```
	 * ```twig
	 * <h1>{{ post.title }}</h1>
	 * {{ my_form }}
	 * ```
	 * ```html
	 * <h1>Apply to my contest!</h1>
	 * <form action="form.php"><input type="text" /><input type="submit /></form>
	 * ```
	 * @api
	 * @param callback $function
	 * @param array   $args
	 * @return string
	 */
	public static function ob_function( $function, $args = array(null) ) {
		ob_start();
		call_user_func_array($function, $args);
		$data = ob_get_contents();
		ob_end_clean();
		return $data;
	}

	/**
	 * @param mixed $function_name or array( $class( string|object ), $function_name )
	 * @param array (optional) $defaults
	 * @param bool (optional) $return_output_buffer Return function output instead of return value (default: false)
	 * @return Timber\FunctionWrapper|mixed
	 */
	public static function function_wrapper( $function_name, $defaults = array(), $return_output_buffer = false ) {
		return new FunctionWrapper($function_name, $defaults, $return_output_buffer);
	}

	/**
	 *
	 *
	 * @param mixed $arg that you want to error_log
	 * @return void
	 */
	public static function error_log( $error ) {
		global $timber_disable_error_log;
		if ( !WP_DEBUG || $timber_disable_error_log ) {
			return;
		}
		if ( is_object($error) || is_array($error) ) {
			$error = print_r($error, true);
		}
		return error_log($error);
	}

	/**
	 * @param string $message that you want to output
	 * @return boolean
	 */
	public static function warn( $message ) {
		return trigger_error($message, E_USER_WARNING);
	}
	
	/**
	 *
	 *
	 * @param string  $separator
	 * @param string  $seplocation
	 * @return string
	 */
	public static function get_wp_title( $separator = ' ', $seplocation = 'left' ) {
		$separator = apply_filters('timber_wp_title_seperator', $separator);
		return trim(wp_title($separator, false, $seplocation));
	}

	/* Text Utilities
	======================== */

	/**
	 *
	 *
	 * @param string  $text
	 * @param int     $num_words
	 * @param string|null|false  $more text to appear in "Read more...". Null to use default, false to hide
	 * @param string  $allowed_tags
	 * @return string
	 */
	public static function trim_words( $text, $num_words = 55, $more = null, $allowed_tags = 'p a span b i br blockquote' ) {
		if ( null === $more ) {
			$more = __('&hellip;');
		}
		$original_text = $text;
		$allowed_tag_string = '';
		foreach ( explode(' ', apply_filters('timber/trim_words/allowed_tags', $allowed_tags)) as $tag ) {
			$allowed_tag_string .= '<'.$tag.'>';
		}
		$text = strip_tags($text, $allowed_tag_string);
		/* translators: If your word count is based on single characters (East Asian characters), enter 'characters'. Otherwise, enter 'words'. Do not translate into your own language. */
		if ( 'characters' == _x('words', 'word count: words or characters?') && preg_match('/^utf\-?8$/i', get_option('blog_charset')) ) {
			$text = trim(preg_replace("/[\n\r\t ]+/", ' ', $text), ' ');
			preg_match_all('/./u', $text, $words_array);
			$words_array = array_slice($words_array[0], 0, $num_words + 1);
			$sep = '';
		} else {
			$words_array = preg_split("/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY);
			$sep = ' ';
		}
		if ( count($words_array) > $num_words ) {
			array_pop($words_array);
			$text = implode($sep, $words_array);
			$text = $text.$more;
		} else {
			$text = implode($sep, $words_array);
		}
		$text = self::close_tags($text);
		return apply_filters('wp_trim_words', $text, $num_words, $more, $original_text);
	}

	/**
	 *
	 *
	 * @param string  $html
	 * @return string
	 */
	public static function close_tags( $html ) {
		//put all opened tags into an array
		preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
		$openedtags = $result[1];
		//put all closed tags into an array
		preg_match_all('#</([a-z]+)>#iU', $html, $result);
		$closedtags = $result[1];
		$len_opened = count($openedtags);
		// all tags are closed
		if ( count($closedtags) == $len_opened ) {
			return $html;
		}
		$openedtags = array_reverse($openedtags);
		// close tags
		for ( $i = 0; $i < $len_opened; $i++ ) {
			if ( !in_array($openedtags[$i], $closedtags) ) {
				$html .= '</'.$openedtags[$i].'>';
			} else {
				unset($closedtags[array_search($openedtags[$i], $closedtags)]);
			}
		}
		$html = str_replace(array('</br>', '</hr>', '</wbr>'), '', $html);
		$html = str_replace(array('<br>', '<hr>', '<wbr>'), array('<br />', '<hr />', '<wbr />'), $html);
		return $html;
	}

	/* Object Utilities
	======================== */

	/**
	 *
	 *
	 * @param array   $array
	 * @param string  $prop
	 * @return void
	 */
	public static function osort( &$array, $prop ) {
		usort($array, function( $a, $b ) use ($prop) {
				return $a->$prop > $b->$prop ? 1 : -1;
			} );
	}

	/**
	 *
	 *
	 * @param array   $arr
	 * @return bool
	 */
	public static function is_array_assoc( $arr ) {
		if ( !is_array($arr) ) {
			return false;
		}
		return (bool) count(array_filter(array_keys($arr), 'is_string'));
	}

	/**
	 *
	 *
	 * @param array   $array
	 * @return \stdClass
	 */
	public static function array_to_object( $array ) {
		$obj = new \stdClass;
		foreach ( $array as $k => $v ) {
			if ( is_array($v) ) {
				$obj->{$k} = self::array_to_object($v); //RECURSION
			} else {
				$obj->{$k} = $v;
			}
		}
		return $obj;
	}

	/**
	 *
	 *
	 * @param array   $array
	 * @param string  $key
	 * @param mixed   $value
	 * @return bool|int
	 */
	public static function get_object_index_by_property( $array, $key, $value ) {
		if ( is_array($array) ) {
			$i = 0;
			foreach ( $array as $arr ) {
				if ( is_array($arr) ) {
					if ( $arr[$key] == $value ) {
						return $i;
					}
				} else {
					if ( $arr->$key == $value ) {
						return $i;
					}
				}
				$i++;
			}
		}
		return false;
	}

	/**
	 *
	 *
	 * @param array   $array
	 * @param string  $key
	 * @param mixed   $value
	 * @return array|null
	 * @throws Exception
	 */
	public static function get_object_by_property( $array, $key, $value ) {
		if ( is_array($array) ) {
			foreach ( $array as $arr ) {
				if ( $arr->$key == $value ) {
					return $arr;
				}
			}
		} else {
			throw new \InvalidArgumentException('$array is not an array, got:');
			Helper::error_log($array);
		}
	}

	/**
	 *
	 *
	 * @param array   $array
	 * @param int     $len
	 * @return array
	 */
	public static function array_truncate( $array, $len ) {
		if ( sizeof($array) > $len ) {
			$array = array_splice($array, 0, $len);
		}
		return $array;
	}

	/* Bool Utilities
	======================== */

	/**
	 *
	 *
	 * @param mixed   $value
	 * @return bool
	 */
	public static function is_true( $value ) {
		if ( isset($value) ) {
			if ( is_string($value) ) {
				$value = strtolower($value);
			}
			if ( ($value == 'true' || $value === 1 || $value === '1' || $value == true) && $value !== false && $value !== 'false' ) {
				return true;
			}
		}
		return false;
	}

	/**
	 *
	 *
	 * @param int     $i
	 * @return bool
	 */
	public static function iseven( $i ) {
		return ($i % 2) == 0;
	}

	/**
	 *
	 *
	 * @param int     $i
	 * @return bool
	 */
	public static function isodd( $i ) {
		return ($i % 2) != 0;
	}

	/* Links, Forms, Etc. Utilities
	======================== */

	/**
	 *
	 * Gets the comment form for use on a single article page
	 * @deprecated 0.21.8 use `{{ function('comment_form') }}` instead
	 * @param int     $post_id which post_id should the form be tied to?
	 * @param array   $args this $args thing is a fucking mess, [fix at some point](http://codex.wordpress.org/Function_Reference/comment_form)
	 * @return string
	 */
	public static function get_comment_form( $post_id = null, $args = array() ) {
		return self::ob_function('comment_form', array($args, $post_id));
	}

	/**
	 *
	 * @deprecated since 1.1.2
	 * @param array  $args
	 * @return array
	 */
	public static function paginate_links( $args = array() ) {
		Helper::warn('Helper/paginate_links has been moved to Pagination/paginate_links');
		return Pagination::paginate_links($args);
	}

	/**
	 * @return string
	 */
	public function get_current_url() {
		Helper::warn('TimberHelper::get_current_url() is deprecated and will be removed in future versions, use Timber\URLHelper::get_current_url()');
		return URLHelper::get_current_url();
	}
}