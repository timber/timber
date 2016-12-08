<?php

namespace Timber;

use Timber\URLHelper;
use Timber\Helper;

use Timber\Post;
use Timber\Term;
use Timber\Image;
use Timber\User;

class Twig {

	public static $dir_name;

	/**
	 * @codeCoverageIgnore
	 */
	public static function init() {
		new self();
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function __construct() {
		add_action('timber/twig/filters', array($this, 'add_timber_filters'));
		add_action('timber/twig/escapers', array($this, 'add_timber_escapers'));
	}

	/**
	 *
	 *
	 * @param Twig_Environment $twig
	 * @return Twig_Environment
	 */
	public function add_timber_filters( $twig ) {
		/* image filters */
		$twig->addFilter(new \Twig_SimpleFilter('resize', array('Timber\ImageHelper', 'resize')));
		$twig->addFilter(new \Twig_SimpleFilter('retina', array('Timber\ImageHelper', 'retina_resize')));
		$twig->addFilter(new \Twig_SimpleFilter('letterbox', array('Timber\ImageHelper', 'letterbox')));
		$twig->addFilter(new \Twig_SimpleFilter('tojpg', array('Timber\ImageHelper', 'img_to_jpg')));

		/* debugging filters */
		$twig->addFilter(new \Twig_SimpleFilter('get_class', 'get_class'));
		$twig->addFilter(new \Twig_SimpleFilter('get_type', 'get_type'));
		$twig->addFilter(new \Twig_SimpleFilter('print_r', function( $arr ) {
					return print_r($arr, true);
				} ));

		/* other filters */
		$twig->addFilter(new \Twig_SimpleFilter('stripshortcodes', 'strip_shortcodes'));
		$twig->addFilter(new \Twig_SimpleFilter('array', array($this, 'to_array')));
		$twig->addFilter(new \Twig_SimpleFilter('excerpt', 'wp_trim_words'));
		$twig->addFilter(new \Twig_SimpleFilter('excerpt_chars', array('Timber\TextHelper','trim_characters')));
		$twig->addFilter(new \Twig_SimpleFilter('function', array($this, 'exec_function')));
		$twig->addFilter(new \Twig_SimpleFilter('pretags', array($this, 'twig_pretags')));
		$twig->addFilter(new \Twig_SimpleFilter('sanitize', 'sanitize_title'));
		$twig->addFilter(new \Twig_SimpleFilter('shortcodes', 'do_shortcode'));
		$twig->addFilter(new \Twig_SimpleFilter('time_ago', array($this, 'time_ago')));
		$twig->addFilter(new \Twig_SimpleFilter('wpautop', 'wpautop'));
		$twig->addFilter(new \Twig_SimpleFilter('list', array($this, 'add_list_separators')));

		$twig->addFilter(new \Twig_SimpleFilter('pluck', array('Timber\Helper', 'pluck')));

		$twig->addFilter(new \Twig_SimpleFilter('relative', function( $link ) {
					return URLHelper::get_rel_url($link, true);
				} ));

		$twig->addFilter(new \Twig_SimpleFilter('date', array($this, 'intl_date')));

		$twig->addFilter(new \Twig_SimpleFilter('truncate', function( $text, $len ) {
					return TextHelper::trim_words($text, $len);
				} ));

		/* actions and filters */
		$twig->addFunction(new \Twig_SimpleFunction('action', function( $context ) {
					$args = func_get_args();
					array_shift($args);
					$args[] = $context;
					call_user_func_array('do_action', $args);
				}, array('needs_context' => true)));

		$twig->addFilter(new \Twig_SimpleFilter('apply_filters', function() {
					$args = func_get_args();
					$tag = current(array_splice($args, 1, 1));

					return apply_filters_ref_array($tag, $args);
				} ));
		$twig->addFunction(new \Twig_SimpleFunction('function', array(&$this, 'exec_function')));
		$twig->addFunction(new \Twig_SimpleFunction('fn', array(&$this, 'exec_function')));

		$twig->addFunction(new \Twig_SimpleFunction('shortcode', 'do_shortcode'));

		/* TimberObjects */
		$twig->addFunction(new \Twig_SimpleFunction('TimberPost', function( $pid, $PostClass = 'Timber\Post' ) {
					if ( is_array($pid) && !Helper::is_array_assoc($pid) ) {
						foreach ( $pid as &$p ) {
							$p = new $PostClass($p);
						}
						return $pid;
					}
					return new $PostClass($pid);
				} ));
		$twig->addFunction(new \Twig_SimpleFunction('TimberImage', function( $pid = false, $ImageClass = 'Timber\Image' ) {
					if ( is_array($pid) && !Helper::is_array_assoc($pid) ) {
						foreach ( $pid as &$p ) {
							$p = new $ImageClass($p);
						}
						return $pid;
					}
					return new $ImageClass($pid);
				} ));

		$twig->addFunction(new \Twig_SimpleFunction('TimberTerm', function( $pid, $TermClass = 'Timber\Term' ) {
					if ( is_array($pid) && !Helper::is_array_assoc($pid) ) {
						foreach ( $pid as &$p ) {
							$p = new $TermClass($p);
						}
						return $pid;
					}
					return new $TermClass($pid);
				} ));
		$twig->addFunction(new \Twig_SimpleFunction('TimberUser', function( $pid, $UserClass = 'Timber\User' ) {
					if ( is_array($pid) && !Helper::is_array_assoc($pid) ) {
						foreach ( $pid as &$p ) {
							$p = new $UserClass($p);
						}
						return $pid;
					}
					return new $UserClass($pid);
				} ));

		/* TimberObjects Alias */
		$twig->addFunction(new \Twig_SimpleFunction('Post', function( $pid, $PostClass = 'Timber\Post' ) {
					if ( is_array($pid) && !Helper::is_array_assoc($pid) ) {
						foreach ( $pid as &$p ) {
							$p = new $PostClass($p);
						}
						return $pid;
					}
					return new $PostClass($pid);
				} ));
		$twig->addFunction(new \Twig_SimpleFunction('Image', function( $pid, $ImageClass = 'Timber\Image' ) {
					if ( is_array($pid) && !Helper::is_array_assoc($pid) ) {
						foreach ( $pid as &$p ) {
							$p = new $ImageClass($p);
						}
						return $pid;
					}
					return new $ImageClass($pid);
				} ));
		$twig->addFunction(new \Twig_SimpleFunction('Term', function( $pid, $TermClass = 'Timber\Term' ) {
					if ( is_array($pid) && !Helper::is_array_assoc($pid) ) {
						foreach ( $pid as &$p ) {
							$p = new $TermClass($p);
						}
						return $pid;
					}
					return new $TermClass($pid);
				} ));
		$twig->addFunction(new \Twig_SimpleFunction('User', function( $pid, $UserClass = 'Timber\User' ) {
					if ( is_array($pid) && !Helper::is_array_assoc($pid) ) {
						foreach ( $pid as &$p ) {
							$p = new $UserClass($p);
						}
						return $pid;
					}
					return new $UserClass($pid);
				} ));

		/* bloginfo and translate */
		$twig->addFunction(new \Twig_SimpleFunction('bloginfo', function( $show = '', $filter = 'raw' ) {
					return get_bloginfo($show, $filter);
				} ));
		$twig->addFunction(new \Twig_SimpleFunction('__', function( $text, $domain = 'default' ) {
					return __($text, $domain);
				} ));
		$twig->addFunction(new \Twig_SimpleFunction('translate', function( $text, $domain = 'default' ) {
					return translate($text, $domain);
				} ));
		$twig->addFunction(new \Twig_SimpleFunction('_e', function( $text, $domain = 'default' ) {
					return _e($text, $domain);
				} ));
		$twig->addFunction(new \Twig_SimpleFunction('_n', function( $single, $plural, $number, $domain = 'default' ) {
					return _n($single, $plural, $number, $domain);
				} ));
		$twig->addFunction(new \Twig_SimpleFunction('_x', function( $text, $context, $domain = 'default' ) {
					return _x($text, $context, $domain);
				} ));
		$twig->addFunction(new \Twig_SimpleFunction('_ex', function( $text, $context, $domain = 'default' ) {
					return _ex($text, $context, $domain);
				} ));
		$twig->addFunction(new \Twig_SimpleFunction('_nx', function( $single, $plural, $number, $context, $domain = 'default' ) {
					return _nx($single, $plural, $number, $context, $domain);
				} ));
		$twig->addFunction(new \Twig_SimpleFunction('_n_noop', function( $singular, $plural, $domain = 'default' ) {
					return _n_noop($singular, $plural, $domain);
				} ));
		$twig->addFunction(new \Twig_SimpleFunction('_nx_noop', function( $singular, $plural, $context, $domain = 'default' ) {
					return _nx_noop($singular, $plural, $context, $domain);
				} ));
		$twig->addFunction(new \Twig_SimpleFunction('translate_nooped_plural', function( $nooped_plural, $count, $domain = 'default' ) {
					return translate_nooped_plural($nooped_plural, $count, $domain);
				} ));
		$twig = apply_filters('timber/twig', $twig);
		/**
		 * get_twig is deprecated, use timber/twig
		 */
		$twig = apply_filters('get_twig', $twig);
		return $twig;
	}

	/**
	 *
	 *
	 * @param Twig_Environment $twig
	 * @return Twig_Environment
	 */
	public function add_timber_escapers( $twig ) {

		$twig->getExtension('Twig_Extension_Core')->setEscaper('esc_url', function( \Twig_Environment $env, $string ) {
			return esc_url( $string );
		});
		$twig->getExtension('Twig_Extension_Core')->setEscaper('wp_kses_post', function( \Twig_Environment $env, $string ) {
			return wp_kses_post( $string );
		});

		$twig->getExtension('Twig_Extension_Core')->setEscaper('esc_html', function( \Twig_Environment $env, $string ) {
			return esc_html( $string );
		});

		$twig->getExtension('Twig_Extension_Core')->setEscaper('esc_js', function( \Twig_Environment $env, $string ) {
			return esc_js( $string );
		});

		return $twig;

	}

	/**
	 *
	 *
	 * @param mixed   $arr
	 * @return array
	 */
	public function to_array( $arr ) {
		if ( is_array($arr) ) {
			return $arr;
		}
		$arr = array($arr);
		return $arr;
	}

	/**
	 *
	 *
	 * @param string  $function_name
	 * @return mixed
	 */
	public function exec_function( $function_name ) {
		$args = func_get_args();
		array_shift($args);
		if ( is_string($function_name) ) {
			$function_name = trim($function_name);
		}
		return call_user_func_array($function_name, ($args));
	}

	/**
	 *
	 *
	 * @param string  $content
	 * @return string
	 */
	public function twig_pretags( $content ) {
		return preg_replace_callback('|<pre.*>(.*)</pre|isU', array(&$this, 'convert_pre_entities'), $content);
	}

	/**
	 *
	 *
	 * @param array   $matches
	 * @return string
	 */
	public function convert_pre_entities( $matches ) {
		return str_replace($matches[1], htmlentities($matches[1]), $matches[0]);
	}

	/**
	 *
	 *
	 * @param string  $date
	 * @param string  $format (optional)
	 * @return string
	 */
	public function intl_date( $date, $format = null ) {
		if ( $format === null ) {
			$format = get_option('date_format');
		}

		if ( $date instanceof \DateTime ) {
			$timestamp = $date->getTimestamp() + $date->getOffset();
		} else if ( is_numeric($date) && (strtotime($date) === false || strlen($date) !== 8) ) {
			$timestamp = intval($date);
		} else {
			$timestamp = strtotime($date);
		}

		return date_i18n($format, $timestamp);
	}

	/**
	 * @param int|string $from
	 * @param int|string $to
	 * @param string $format_past
	 * @param string $format_future
	 * @return string
	 */
	public static function time_ago( $from, $to = null, $format_past = '%s ago', $format_future = '%s from now' ) {
		$to = $to === null ? time() : $to;
		$to = is_int($to) ? $to : strtotime($to);
		$from = is_int($from) ? $from : strtotime($from);

		if ( $from < $to ) {
			return sprintf($format_past, human_time_diff($from, $to));
		} else {
			return sprintf($format_future, human_time_diff($to, $from));
		}
	}

	/**
	 * @param array $arr
	 * @param string $first_delimiter
	 * @param string $second_delimiter
	 * @return string
	 */
	public function add_list_separators( $arr, $first_delimiter = ',', $second_delimiter = 'and' ) {
		$length = count($arr);
		$list = '';
		foreach ( $arr as $index => $item ) {
			if ( $index < $length - 2 ) {
				$delimiter = $first_delimiter.' ';
			} elseif ( $index == $length - 2 ) {
				$delimiter = ' '.$second_delimiter.' ';
			} else {
				$delimiter = '';
			}
			$list = $list.$item.$delimiter;
		}
		return $list;
	}

}
