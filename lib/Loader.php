<?php

namespace Timber;

use Timber\Cache\Cleaner;

class Loader 
	extends \Twig_Environment
{

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

	private $legacy = true;

	private $loader;
	private $temporaryLoader;
	private $locationsLoader;
	private $themeLoader;
	private $basedirLoader;
	private $callerLoader;
	private $caller2Loader;

	/**
	 * @param array $locations
	 */
	public function __construct(\Twig_LoaderInterface $loader, $options = array()) {
		
		$this->cache_mode = apply_filters('timber_cache_mode', $this->cache_mode);
		$this->cache_mode = apply_filters('timber/cache/mode', $this->cache_mode);

		$loader = apply_filters('timber/loader/loader', $loader);
// TODO: Consider this new filter as a future replacement for 'timber/loader/loader'
//		$loader = apply_filters('timber/twig/loader', $loader, $this);
		if ( !$loader instanceof \Twig_LoaderInterface ) {
			throw new \UnexpectedValueException('Loader must implement \Twig_LoaderInterface');
		}

		if ($loader instanceof LegacyLoader) {
			// It's a legacy loader...
		}

		$this->create_twig_environment($loader, $options);

		do_action('timber/twig', $this);
		/**
		 * get_twig is deprecated, use timber/twig
		 */
		do_action('get_twig', $this);
	}

	/**
	 * @param \Twig_LoaderInterface $loader
	 * @return \Twig_Environment
	 */
	protected function create_twig_environment(\Twig_LoaderInterface $loader, $options) {
		$options = array('debug' => WP_DEBUG, 'autoescape' => false);
		if ( isset(Timber::$autoescape) ) {
			$options['autoescape'] = Timber::$autoescape;
		}

// TODO: Consider this new (experimental) filter!
//		$options = apply_filters('timber/twig/options', $options, $this);

		if ( Timber::$cache === true ) {
			Timber::$twig_cache = true;
		}
		if ( Timber::$twig_cache ) {
			$twig_cache_loc = apply_filters('timber/cache/location', TIMBER_LOC.'/cache/twig');
			if ( !file_exists($twig_cache_loc) ) {
				mkdir($twig_cache_loc, 0777, true);
			}
			$options['cache'] = $twig_cache_loc;
		}

		parent::__construct($loader, $options);
		if ( WP_DEBUG ) {
			$this->addExtension(new \Twig_Extension_Debug());
		}
		$this->addExtension($this->_get_cache_extension());
	}

	/**
	 * @param string        	$file
	 * @param array         	$data
	 * @param array|boolean    	$expires (array for options, false for none, integer for # of seconds)
	 * @param string        	$cache_mode
	 * @return bool|string
	 */
	public function render( $file, array $data = null, $expires = false, $cache_mode = self::CACHE_USE_DEFAULT ) {
		// Different $expires if user is anonymous or logged in
		if ( is_array($expires) ) {
			/** @var array $expires */
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
			if ( strlen($file) ) {
				$loader = $this->getLoader();
				$result = $loader->getCacheKey($file);
				do_action('timber_loader_render_file', $result);
			}
			$data = apply_filters('timber_loader_render_data', $data);
			$data = apply_filters('timber/loader/render_data', $data, $file);
			$output = parent::render($file, $data);
		}

		if ( false !== $output && false !== $expires && null !== $key ) {
			$this->delete_cache();
			$this->set_cache($key, $output, self::CACHEGROUP, $expires, $cache_mode);
		}
		$output = apply_filters('timber_output', $output);
		return apply_filters('timber/output', $output, $data, $file);
	}

	protected function delete_cache() {
		Cleaner::delete_transients();
	}

	/**
	 * Get first existing template.
	 *
	 * @param array|string $templates  Name(s) of the Twig template(s) to choose from.
	 * @return string|bool             Name of chosen template, otherwise false.
	 */
	public function choose_template( $templates ) {
		// Change $templates into array, if needed 
		if ( !is_array($templates) ) {
			$templates = (array) $templates;
		}
		
		// Get Twig loader
		$loader = $this->getLoader();

		// Run through template array
		foreach ( $templates as $template ) {
			// Use the Twig loader to test for existance
			if ( $loader->exists($template) ) {
				// Return name of existing template
				return $template;
			}
		}

		// No existing template was found
		return false;
	}

	/**
	 * @param string $name
	 * @return bool
	 * @deprecated 1.3.5 No longer used internally
	 * @todo remove in 2.x
	 * @codeCoverageIgnore
	 */
	protected function template_exists( $name ) {
		return $this->getLoader()->exists($name);
	}


	/**
	 * @return \Twig_LoaderInterface
	 */
	public function get_loader() {
// TODO: Change this to return the loaderchin ($this->loader), but for now return the filesystem loader to preserve backward compatibility, by letting users add (but not remove internal) paths.
		return $this->temporaryLoader;
	}


	/**
	 * @return \Twig_Environment
	 */
	public function get_twig() {
		return $this;
	}

	public function clear_cache_timber( $cache_mode = self::CACHE_USE_DEFAULT ) {
		//_transient_timberloader
		$object_cache = false;
		if ( isset($GLOBALS['wp_object_cache']) && is_object($GLOBALS['wp_object_cache']) ) {
			$object_cache = true;
		}
		$cache_mode = $this->_get_cache_mode($cache_mode);
		if ( self::CACHE_TRANSIENT === $cache_mode || self::CACHE_SITE_TRANSIENT === $cache_mode ) {
			return self::clear_cache_timber_database();
		} else if ( self::CACHE_OBJECT === $cache_mode && $object_cache ) {
			return self::clear_cache_timber_object();
		}
		return false;
	}

	protected static function clear_cache_timber_database() {
		global $wpdb;
		$query = $wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name LIKE '%s'", '_transient_timberloader_%');
		return $wpdb->query($query);
	}

	protected static function clear_cache_timber_object() {
		global $wp_object_cache;
		if ( isset($wp_object_cache->cache[self::CACHEGROUP]) ) {
			$items = $wp_object_cache->cache[self::CACHEGROUP];
			foreach ( $items as $key => $value ) {
				if ( is_multisite() ) {
					$key = preg_replace('/^(.*?):/', '', $key);
				}
				wp_cache_delete($key, self::CACHEGROUP);
			}
			return true;
		}
	}

	public function clear_cache_twig() {
		if ( method_exists($this, 'clearCacheFiles') ) {
			$this->clearCacheFiles();
		}
		$cache = $this->getCache();
		if ( $cache ) {
			self::rrmdir($this->getCache());
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
	 * @param integer $expires
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


/**
 * @param \Twig_Environment $twig
 * @return \Twig_Environment
 * @internal
 */
function do_legacy_twig_environment_filters_pre_timber_twig(\Twig_Environment $twig) {
	do_action('twig_apply_filters', $twig);
	do_action('timber/twig/filters', $twig);
}
// Attach action with lower than default priority to simulate the filters prior location before 'timber/twig' was fired at the bottom of Twig::add_timber_filters()
add_action('timber/twig', __NAMESPACE__.'\do_legacy_twig_environment_filters_pre_timber_twig', 5);

/**
 * @param \Twig_Environment $twig
 * @return \Twig_Environment
 * @internal
 */
function do_legacy_twig_environment_filters_post_timber_twig(\Twig_Environment $twig) {
	do_action('timber/twig/functions', $twig);
	do_action('timber/twig/escapers', $twig);
	do_action('timber/loader/twig', $twig);
}
// Attach action with higher than default priority to simulate the filters prior location after 'timber/twig' was fired at the bottom of Twig::add_timber_filters()
add_action('timber/twig', __NAMESPACE__.'\do_legacy_twig_environment_filters_post_timber_twig', 15);
