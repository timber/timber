<?php

namespace Timber;

use Timber\Cache\Cleaner;

class Cache 
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

	/**
	 *
	 */
	public function __construct()
	{	
	}

	public function deleteCache()
	{
		Cleaner::delete_transients();
	}

	public function clearCacheTimber( $cache_mode = self::CACHE_USE_DEFAULT )
	{
		$cache_mode = $this->filterCacheMode($cache_mode);
		switch ($cache_mode) {
				
			case self::CACHE_TRANSIENT:
			case self::CACHE_SITE_TRANSIENT:
				return self::clearCacheTimberDatabase();
			
			case self::CACHE_OBJECT:
				$object_cache = isset($GLOBALS['wp_object_cache']) && is_object($GLOBALS['wp_object_cache']);
				if ($object_cache) {
					return self::clearCacheTimberObject();
				}
				break;
			
			default:
// TODO:
		}

		return false;
	}

	protected static function clearCacheTimberDatabase()
	{
		global $wpdb;
		$query = $wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name LIKE '%s'", '_transient_timberloader_%');
		return $wpdb->query($query);
	}

	protected static function clearCacheTimberObject()
	{
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

	/**
	 * @return \Asm89\Twig\CacheExtension\Extension
	 */
	public static function createCacheExtension() {

		$key_generator   = new \Timber\Cache\KeyGenerator();
		$cache_provider  = new \Timber\Cache\WPObjectCacheAdapter(new self());
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
	public function fetch( $key, $group = self::CACHEGROUP, $cache_mode = self::CACHE_USE_DEFAULT ) {
		$value = false;

		$trans_key = substr($group.'_'.$key, 0, self::TRANS_KEY_LEN);
		
		$cache_mode = $this->filterCacheMode($cache_mode);
		switch ($cache_mode) {
				
			case self::CACHE_TRANSIENT:
				$value = get_transient($trans_key);
				break;
				
			case self::CACHE_SITE_TRANSIENT:
				$value = get_site_transient($trans_key);
				break;

			case self::CACHE_OBJECT:
				$object_cache = isset($GLOBALS['wp_object_cache']) && is_object($GLOBALS['wp_object_cache']);
				if ($object_cache) {
					$value = wp_cache_get($key, $group);
				}
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
	public function save( $key, $value, $group = self::CACHEGROUP, $expires = 0, $cache_mode = self::CACHE_USE_DEFAULT ) {
		if ( (int) $expires < 1 ) {
			$expires = 0;
		}

		$trans_key = substr($group.'_'.$key, 0, self::TRANS_KEY_LEN);

		$cache_mode = self::filterCacheMode($cache_mode);
		switch ($cache_mode) {
		
			case self::CACHE_TRANSIENT:
				set_transient($trans_key, $value, $expires);
				break;
		
			case self::CACHE_SITE_TRANSIENT:
				set_site_transient($trans_key, $value, $expires);
				break;
		
			case self::CACHE_OBJECT:
				$object_cache = isset($GLOBALS['wp_object_cache']) && is_object($GLOBALS['wp_object_cache']);
				if ($object_cache) {
					wp_cache_set($key, $value, $group, $expires);
				}
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
	private function filterCacheMode( $cache_mode )
	{
		if ( empty($cache_mode) || self::CACHE_USE_DEFAULT === $cache_mode ) {
			$cache_mode = self::CACHE_TRANSIENT;
			$cache_mode = apply_filters('timber_cache_mode', $cache_mode);
			$cache_mode = apply_filters('timber/cache/mode', $cache_mode);
		}

		// Fallback if self::$cache_mode did not get a valid value
		if ( !in_array($cache_mode, self::$cache_modes) ) {
			$cache_mode = self::CACHE_OBJECT;
		}

		return $cache_mode;
	}

}
