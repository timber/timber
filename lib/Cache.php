<?php

namespace Timber;

use Timber\Cache\Cleaner;

final class Cache 
{
	const CACHEGROUP = 'timberloader';

	const TRANS_KEY_LEN = 50;

	const CACHE_NONE = 'none';
	const CACHE_OBJECT = 'cache';
	const CACHE_TRANSIENT = 'transient';
	const CACHE_SITE_TRANSIENT = 'site-transient';
	const CACHE_USE_DEFAULT = 'default';

	/**
	 *
	 */
	protected function __construct()
	{	
	}

	public static function deleteCache()
	{
		Self::deleteTransients();
	}

	public static function clearCacheTimber( $cache_mode = self::CACHE_USE_DEFAULT )
	{
		$cache_mode = self::filterCacheMode($cache_mode);
		switch ($cache_mode) {
				
			case self::CACHE_TRANSIENT:
			case self::CACHE_SITE_TRANSIENT:
//				return self::clearCacheTimberDatabase();
			
			case self::CACHE_OBJECT:
				$object_cache = isset($GLOBALS['wp_object_cache']) && is_object($GLOBALS['wp_object_cache']);
				if ($object_cache) {
//					return self::clearCacheTimberObject();
				}
				break;
			
			default:
// TODO:
		}

		return false;
	}

	/**
	 * @return \Asm89\Twig\CacheExtension\Extension
	 */
	public static function createCacheExtension() {

		$key_generator   = new \Timber\Cache\KeyGenerator();
		$cache_provider  = new \Timber\Cache\WPObjectCacheAdapter();
		$cache_strategy  = new \Asm89\Twig\CacheExtension\CacheStrategy\GenerationalCacheStrategy($cache_provider, $key_generator);
		$cache_extension = new \Asm89\Twig\CacheExtension\Extension($cache_strategy);

		return $cache_extension;
	}

	/**
	 * @param string $key
	 * @param string $cache_mode
	 * @param string $group
	 * @return bool
	 */
	public static function fetch( $key, $cache_mode = self::CACHE_USE_DEFAULT, $group = self::CACHEGROUP ) {
		$value = false;

		$cache_mode = self::filterCacheMode($cache_mode);
		switch ($cache_mode) {
				
			case self::CACHE_TRANSIENT:
				$cachePool = new \Timber\Cache\Psr16\WordpressTransientPool();
				$trans_key = substr($group.'_'.$key, 0, self::TRANS_KEY_LEN);
				$value = $cachePool->get($trans_key);
				break;
				
			case self::CACHE_SITE_TRANSIENT:
				$cachePool = new \Timber\Cache\Psr16\WordpressSiteTransientPool();
				$trans_key = substr($group.'_'.$key, 0, self::TRANS_KEY_LEN);
				$value = $cachePool->get($trans_key);
				break;

			case self::CACHE_OBJECT:
				$object_cache = isset($GLOBALS['wp_object_cache']) && is_object($GLOBALS['wp_object_cache']);
				if ($object_cache) {
					$cachePool = new \Timber\Cache\Psr16\WordpressObjectCachePool($group);
					$value = $cachePool->get($key);
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
	 * @param integer $expires
	 * @param string $cache_mode
	 * @param string $group
	 * @return string|boolean
	 */
	public static function save( $key, $value, $expires = 0, $cache_mode = self::CACHE_USE_DEFAULT, $group = self::CACHEGROUP ) {
		if ( (int) $expires < 1 ) {
			$expires = 0;
		}

		$cache_mode = self::filterCacheMode($cache_mode);
		switch ($cache_mode) {
		
			case self::CACHE_TRANSIENT:
				$cachePool = new \Timber\Cache\Psr16\WordpressTransientPool();
				$trans_key = substr($group.'_'.$key, 0, self::TRANS_KEY_LEN);
				$cachePool->set($trans_key, $value, $expires);
				break;
		
			case self::CACHE_SITE_TRANSIENT:
				$cachePool = new \Timber\Cache\Psr16\WordpressSiteTransientPool();
				$trans_key = substr($group.'_'.$key, 0, self::TRANS_KEY_LEN);
				$cachePool->set($trans_key, $value, $expires);
				break;
		
			case self::CACHE_OBJECT:
				$object_cache = isset($GLOBALS['wp_object_cache']) && is_object($GLOBALS['wp_object_cache']);
				if ($object_cache) {
					$cachePool = new \Timber\Cache\Psr16\WordpressObjectCachePool($group);
					$cachePool->set($key, $value, $expires);
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
	private static function filterCacheMode( $cache_mode )
	{
		if ( empty($cache_mode) || self::CACHE_USE_DEFAULT === $cache_mode ) {
			$cache_mode = self::CACHE_TRANSIENT;
			$cache_mode = apply_filters('timber_cache_mode', $cache_mode);
			$cache_mode = apply_filters('timber/cache/mode', $cache_mode);
		}

		// Fallback if self::$cache_mode did not get a valid value
		switch ($cache_mode) {
			
			case self::CACHE_NONE:
			case self::CACHE_OBJECT:
			case self::CACHE_TRANSIENT:
			case self::CACHE_SITE_TRANSIENT:
				break;

			default:
				$cache_mode = self::CACHE_OBJECT;
		}

		return $cache_mode;
	}
}
