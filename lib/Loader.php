<?php

namespace Timber;

class Loader {

	const CACHEGROUP = 'timberloader';

	const TRANS_KEY_LEN = 50;

	const CACHE_NONE = 'none';
	const CACHE_OBJECT = 'cache';
	const CACHE_TRANSIENT = 'transient';
	const CACHE_SITE_TRANSIENT = 'site-transient';
	const CACHE_USE_DEFAULT = 'default';

	public static $cache_modes = array(
		self::CACHE_NONE,
		self::CACHE_OBJECT,
		self::CACHE_TRANSIENT,
		self::CACHE_SITE_TRANSIENT
	);

	protected $cache_mode = self::CACHE_TRANSIENT;

	public $locations;

	/**
	 * @param bool|string   $caller the calling directory or false
	 */
	function __construct( $caller = false ) {
		$this->locations = $this->get_locations($caller);
		$this->cache_mode = apply_filters('timber_cache_mode', $this->cache_mode);
		$this->cache_mode = apply_filters('timber/cache/mode', $this->cache_mode);
	}

	/**
	 * @param string        $file
	 * @param array         $data
	 * @param bool          $expires
	 * @param string        $cache_mode
	 * @return bool|string
	 */
	function render( $file, $data = null, $expires = false, $cache_mode = self::CACHE_USE_DEFAULT ) {
		// Different $expires if user is anonymous or logged in
		if ( is_array($expires) ) {
			if ( is_user_logged_in() && isset($expires[1]) ) {
				$expires = $expires[1];
			} else {
				$expires = $expires[0];
			}
		}

		$key = null;
		$output = false;
		if ( false !== $expires ) {
			ksort($data);
			$key = md5($file.json_encode($data));
			$output = $this->get_cache($key, self::CACHEGROUP, $cache_mode);
		}

		if ( false === $output || null === $output ) {
			$twig = $this->get_twig();
			if ( strlen($file) ) {
				$loader = $this->get_loader();
				$result = $loader->getCacheKey($file);
				do_action('timber_loader_render_file', $result);
			}
			$data = apply_filters('timber_loader_render_data', $data);
			$data = apply_filters('timber/loader/render_data', $data);
			$output = $twig->render($file, $data);
		}

		if ( false !== $output && false !== $expires && null !== $key ) {
			$this->set_cache($key, $output, self::CACHEGROUP, $expires, $cache_mode);
		}
		$output = apply_filters('timber_output', $output);
		return apply_filters('timber/output', $output);
	}

	/**
	 * @param array $filenames
	 * @return bool
	 */
	public function choose_template( $filenames ) {
		if ( is_array($filenames) ) {
			/* its an array so we have to figure out which one the dev wants */
			foreach ( $filenames as $filename ) {
				if ( self::template_exists($filename) ) {
					return $filename;
				}
			}
			return $filenames[0];
		}
		return $filenames;
	}

	/**
	 * @param string $file
	 * @return bool
	 */
	protected function template_exists( $file ) {
		foreach ( $this->locations as $dir ) {
			$look_for = trailingslashit($dir).$file;
			if ( file_exists($look_for) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @return array
	 */
	function get_locations_theme() {
		$theme_locs = array();
		$child_loc = get_stylesheet_directory();
		$parent_loc = get_template_directory();
		if ( DIRECTORY_SEPARATOR == '\\' ) {
			$child_loc = str_replace('/', '\\', $child_loc);
			$parent_loc = str_replace('/', '\\', $parent_loc);
		}
		$theme_locs[] = $child_loc;
		foreach ( $this->get_locations_theme_dir() as $dirname ) {
			$theme_locs[] = trailingslashit($child_loc).trailingslashit($dirname);
		}
		if ( $child_loc != $parent_loc ) {
			$theme_locs[] = $parent_loc;
			foreach ( $this->get_locations_theme_dir() as $dirname ) {
				$theme_locs[] = trailingslashit($parent_loc).trailingslashit($dirname);
			}
		}
		//now make sure theres a trailing slash on everything
		$theme_locs = array_map('trailingslashit', $theme_locs);
		return $theme_locs;
	}

	/**
	 * returns an array of the directory inside themes that holds twig files
	 * @return string[] the names of directores, ie: array('templats', 'views');
	 */
	private function get_locations_theme_dir() {
		if ( is_string(Timber::$dirname) ) {
			return array(Timber::$dirname);
		}
		return Timber::$dirname;
	}

	/**
	 *
	 * @return array
	 */
	function get_locations_user() {
		$locs = array();
		if ( isset(Timber::$locations) ) {
			if ( is_string(Timber::$locations) ) {
				Timber::$locations = array(Timber::$locations);
			}
			foreach ( Timber::$locations as $tloc ) {
				$tloc = realpath($tloc);
				if ( is_dir($tloc) ) {
					$locs[] = $tloc;
				}
			}
		}
		return $locs;
	}

	/**
	 * @param bool|string   $caller the calling directory
	 * @return array
	 */
	function get_locations_caller( $caller = false ) {
		$locs = array();
		if ( $caller && is_string($caller) ) {
			$caller = trailingslashit($caller);
			if ( is_dir($caller) ) {
				$locs[] = $caller;
			}
			foreach ( $this->get_locations_theme_dir() as $dirname ) {
				$caller_sub = $caller.trailingslashit($dirname);
				if ( is_dir($caller_sub) ) {
					$locs[] = $caller_sub;
				}
			}
		}
		return $locs;
	}

	/**
	 * @param bool|string   $caller the calling directory (or false)
	 * @return array
	 */
	function get_locations( $caller = false ) {
		//prioirty: user locations, caller (but not theme), child theme, parent theme, caller
		$locs = array();
		$locs = array_merge($locs, $this->get_locations_user());
		$locs = array_merge($locs, $this->get_locations_caller($caller));
		//remove themes from caller
		$locs = array_diff($locs, $this->get_locations_theme());
		$locs = array_merge($locs, $this->get_locations_theme());
		$locs = array_merge($locs, $this->get_locations_caller($caller));
		$locs = array_unique($locs);
		$locs = apply_filters('timber_locations', $locs);
		$locs = apply_filters('timber/locations', $locs);
		return $locs;
	}

	/**
	 * @return \Twig_Loader_Filesystem
	 */
	function get_loader() {
		$paths = array();
		foreach ( $this->locations as $loc ) {
			$loc = realpath($loc);
			if ( is_dir($loc) ) {
				$loc = realpath($loc);
				$paths[] = $loc;
			} else {
				//error_log($loc.' is not a directory');
			}
		}
		if ( !ini_get('open_basedir') ) {
			$paths[] = '/';
		} else {
			$paths[] = ABSPATH;
		}
		$paths = apply_filters('timber/loader/paths', $paths);
		$loader = new \Twig_Loader_Filesystem($paths);
		return $loader;
	}

	/**
	 * @return Twig_Environment
	 */
	function get_twig() {
		$loader = $this->get_loader();
		$params = array('debug' => WP_DEBUG, 'autoescape' => false);
		if ( isset(Timber::$autoescape) ) {
			$params['autoescape'] = Timber::$autoescape;
		}
		if ( Timber::$cache === true ) {
			Timber::$twig_cache = true;
		}
		if ( Timber::$twig_cache ) {
			$twig_cache_loc = apply_filters('timber/cache/location', TIMBER_LOC.'/cache/twig');
			if ( !file_exists($twig_cache_loc) ) {
				mkdir($twig_cache_loc, 0777, true);
			}
			$params['cache'] = $twig_cache_loc;
		}
		$twig = new \Twig_Environment($loader, $params);
		if ( WP_DEBUG ) {
			$twig->addExtension(new \Twig_Extension_Debug());
		}
		$twig->addExtension($this->_get_cache_extension());

		$twig = apply_filters('twig_apply_filters', $twig);
		$twig = apply_filters('timber/twig/filters', $twig);
		$twig = apply_filters('timber/loader/twig', $twig);
		return $twig;
	}

	public function clear_cache_timber( $cache_mode = self::CACHE_USE_DEFAULT ) {
		//_transient_timberloader
		$object_cache = false;
		if ( isset($GLOBALS['wp_object_cache']) && is_object($GLOBALS['wp_object_cache']) ) {
			$object_cache = true;
		}
		$cache_mode = $this->_get_cache_mode($cache_mode);
		if ( self::CACHE_TRANSIENT === $cache_mode ) {
			global $wpdb;
			$query = $wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name LIKE '%s'", '_transient_timberloader_%');
			$wpdb->query($query);
			return true;
		} else if ( self::CACHE_SITE_TRANSIENT === $cache_mode ) {
			global $wpdb;
			$query = $wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name LIKE '%s'", '_transient_timberloader_%');
			$wpdb->query($query);
			return true;
		} else if ( self::CACHE_OBJECT === $cache_mode && $object_cache ) {
			global $wp_object_cache;
			if ( isset($wp_object_cache->cache[self::CACHEGROUP]) ) {
				unset($wp_object_cache->cache[self::CACHEGROUP]);
				return true;
			}
		}
		return false;
	}

	public function clear_cache_twig() {
		$twig = $this->get_twig();
		$twig->clearCacheFiles();
		$cache = $twig->getCache();
		if ( $cache ) {
			self::rrmdir($twig->getCache());
			return true;
		}
		return false;
	}

	/**
	 * @param string|false $dirPath
	 */
	public static function rrmdir( $dirPath ) {
		if ( !is_dir($dirPath) ) {
			throw new \InvalidArgumentException("$dirPath must be a directory");
		}
		if ( substr($dirPath, strlen($dirPath) - 1, 1) != '/' ) {
			$dirPath .= '/';
		}
		$files = glob($dirPath.'*', GLOB_MARK);
		foreach ( $files as $file ) {
			if ( is_dir($file) ) {
				self::rrmdir($file);
			} else {
				unlink($file);
			}
		}
		rmdir($dirPath);
	}

	/**
	 * @return \Asm89\Twig\CacheExtension\Extension
	 */
	private function _get_cache_extension() {

		$key_generator   = new \Timber\Cache\KeyGenerator();
		$cache_provider  = new \Timber\Cache\WPObjectCacheAdapter($this);
		$cache_strategy  = new \Asm89\Twig\CacheExtension\CacheStrategy\GenerationalCacheStrategy($cache_provider, $key_generator);
		$cache_extension = new \Asm89\Twig\CacheExtension\Extension($cache_strategy);

		return $cache_extension;
	}

	/**
	 * @param string $key
	 * @param string $group
	 * @param string $cache_mode
	 * @return bool
	 */
	public function get_cache( $key, $group = self::CACHEGROUP, $cache_mode = self::CACHE_USE_DEFAULT ) {
		$object_cache = false;

		if ( isset($GLOBALS['wp_object_cache']) && is_object($GLOBALS['wp_object_cache']) ) {
			$object_cache = true;
		}

		$cache_mode = $this->_get_cache_mode($cache_mode);

		$value = false;

		$trans_key = substr($group.'_'.$key, 0, self::TRANS_KEY_LEN);
		if ( self::CACHE_TRANSIENT === $cache_mode ) {
					$value = get_transient($trans_key);
		} elseif ( self::CACHE_SITE_TRANSIENT === $cache_mode ) {
					$value = get_site_transient($trans_key);
		} elseif ( self::CACHE_OBJECT === $cache_mode && $object_cache ) {
					$value = wp_cache_get($key, $group);
		}

		return $value;
	}

	/**
	 * @param string $key
	 * @param string|boolean $value
	 * @param string $group
	 * @param int $expires
	 * @param string $cache_mode
	 * @return string|boolean
	 */
	public function set_cache( $key, $value, $group = self::CACHEGROUP, $expires = 0, $cache_mode = self::CACHE_USE_DEFAULT ) {
		$object_cache = false;

		if ( isset($GLOBALS['wp_object_cache']) && is_object($GLOBALS['wp_object_cache']) ) {
			$object_cache = true;
		}

		if ( (int) $expires < 1 ) {
					$expires = 0;
		}

		$cache_mode = self::_get_cache_mode($cache_mode);
		$trans_key = substr($group.'_'.$key, 0, self::TRANS_KEY_LEN);

		if ( self::CACHE_TRANSIENT === $cache_mode ) {
					set_transient($trans_key, $value, $expires);
		} elseif ( self::CACHE_SITE_TRANSIENT === $cache_mode ) {
					set_site_transient($trans_key, $value, $expires);
		} elseif ( self::CACHE_OBJECT === $cache_mode && $object_cache ) {
					wp_cache_set($key, $value, $group, $expires);
		}

		return $value;
	}

	/**
	 * @param string $cache_mode
	 * @return string
	 */
	private function _get_cache_mode( $cache_mode ) {
		if ( empty($cache_mode) || self::CACHE_USE_DEFAULT === $cache_mode ) {
			$cache_mode = $this->cache_mode;
		}

		// Fallback if self::$cache_mode did not get a valid value
		if ( !in_array($cache_mode, self::$cache_modes) ) {
			$cache_mode = self::CACHE_OBJECT;
		}

		return $cache_mode;
	}

}
