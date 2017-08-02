<?php

namespace Timber;

use Timber\Cache\Cleaner;

class Loader 
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

	/**
     * @param Twig_LoaderInterface $loader
     * @param array                $options An array of options
	 */
	public function __construct(\Twig_LoaderInterface $loader, $options = array()) {
		
		$this->cache_mode = apply_filters('timber_cache_mode', $this->cache_mode);
		$this->cache_mode = apply_filters('timber/cache/mode', $this->cache_mode);

		$this->addExtension($this->_get_cache_extension());
	}

	protected function delete_cache() {
		Cleaner::delete_transients();
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
	 * @deprecated No longer relevant due to Twig_Environment::getLoader().
	 * @todo remove
	 */
	public function get_loader() {
// TODO: Remove.
		// This returns a proxy filesystem loader to preserve backward compatibility, by letting users add (but not remove internal) paths.
		if ($this->getLoader() instanceof ChainLoader) {
			return $this->getLoader()->getTemporaryLoader();
		}
		// Just return loader...
		return $this->getLoader();
	}


	/**
	 * @return \Twig_Environment
	 * @deprecated Since class now extends Twig_Environment.
	 * @todo remove
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
		
		switch (true) {
				
			case self::CACHE_TRANSIENT === $cache_mode:
			case self::CACHE_SITE_TRANSIENT === $cache_mode:
				return self::clear_cache_timber_database();
			
			case self::CACHE_OBJECT === $cache_mode && $object_cache:
				return self::clear_cache_timber_object();
			
			default:
// TODO:
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
		
		switch (true) {
			case ( self::CACHE_TRANSIENT === $cache_mode ):
				$value = get_transient($trans_key);
				break;
				
			case ( self::CACHE_SITE_TRANSIENT === $cache_mode ):
				$value = get_site_transient($trans_key);
				break;

			case ( self::CACHE_OBJECT === $cache_mode && $object_cache ):
				$value = wp_cache_get($key, $group);
				break;
				
			default:
// TODO:
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

		switch (true) {
		
			case self::CACHE_TRANSIENT === $cache_mode:
				set_transient($trans_key, $value, $expires);
				break;
		
			case self::CACHE_SITE_TRANSIENT === $cache_mode:
				set_site_transient($trans_key, $value, $expires);
				break;
		
			case self::CACHE_OBJECT === $cache_mode && $object_cache:
				wp_cache_set($key, $value, $group, $expires);
				break;

			default:
// TODO: 
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
