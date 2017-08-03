<?php

namespace Timber;

final class Cache 
{
	const CACHEGROUP = 'timberloader';

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
		\Timber\Cache\Psr16\WordpressTransientPool::deleteTransients();
	}

	public static function clearCacheTimber( $cache_mode = self::CACHE_USE_DEFAULT )
	{
		//
		$cachePool = self::getSimplePool($cache_mode);

		//
		switch (true) {

			//
			case $cachePool instanceof \Timber\Cache\Psr16\TimberTransientPool:
			case $cachePool instanceof \Timber\Cache\Psr16\TimberSiteTransientPool:
				return $cachePool->clearTimber();

			//
			case $cachePool instanceof \Timber\Cache\Psr16\TimberObjectCachePool:
				return $cachePool->clearTimber();
				
			default:
				// Unknown cache pool :-)

// TODO: call $cachePool->clear() ???
				throw new \Exception('Currently unimplemented');
		}

		return false;
	}

	/**
	 * @return \Asm89\Twig\CacheExtension\Extension
	 */
	public static function createCacheExtension($cache_mode = Cache::CACHE_USE_DEFAULT, $group = 'timber')
	{
		$key_generator   = new \Timber\Cache\KeyGenerator();
		$cache_provider  = new \Timber\Cache\Psr16\Asm89SimpleCacheAdapter(
			self::getSimplePool($cache_mode, $group)
		);
		$cache_strategy  = new \Asm89\Twig\CacheExtension\CacheStrategy\GenerationalCacheStrategy($cache_provider, $key_generator);
		$cache_extension = new \Asm89\Twig\CacheExtension\Extension($cache_strategy);

		return $cache_extension;
	}

	/**
	 * @param string $cache_mode
	 * @param string $group
	 * @return bool
	 */
	protected static function getSimplePool( $cache_mode = self::CACHE_USE_DEFAULT, $group = self::CACHEGROUP )
	{
		$cache_mode = self::filterCacheMode($cache_mode);
		switch ($cache_mode) {
				
			case self::CACHE_TRANSIENT:
				return new \Timber\Cache\Psr16\TimberTransientPool();
				
			case self::CACHE_SITE_TRANSIENT:
				return new \Timber\Cache\Psr16\TimberSiteTransientPool();

			case self::CACHE_OBJECT:
// TODO: ???
				$object_cache = isset($GLOBALS['wp_object_cache']) && is_object($GLOBALS['wp_object_cache']);
				if ( ! $object_cache) {
					throw new \Exception('Ehh ?!?');
				}
				return new \Timber\Cache\Psr16\TimberObjectCachePool($group);
				
			case self::CACHE_NONE:
				throw new \Exception('This makes no sense!');
				
			default:
				throw new \Exception('Invalid pool');
		}		
	}

	/**
	 * @param string $key
	 * @param string $cache_mode
	 * @param string $group
	 * @return bool
	 */
	public static function fetch( $key, $cache_mode = self::CACHE_USE_DEFAULT, $group = self::CACHEGROUP ) {

		if ($cache_mode == self::CACHE_NONE) {
			return false;
		}

		//
		$cachePool = self::getSimplePool($cache_mode, $group);
			
		//
		switch (true) {
				
			//
			case $cachePool instanceof \Timber\Cache\Psr16\TimberWordpressTransientPool:
			case $cachePool instanceof \Timber\Cache\Psr16\TimberWordpressSiteTransientPool:
				$key = $group.'_'.$key;
				break;

			//
			case $cachePool instanceof \Timber\Cache\Psr16\TimberObjectCachePool:
				// No thing to do here
				break;
				
			//
			default:
				// Unknown cache pool :-)
		}

		//
		$value = $cachePool->get($key);

		//
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

		//
		$cachePool = self::getSimplePool($cache_mode, $group);

		//
		switch (true) {

			//
			case $cachePool instanceof \Timber\Cache\Psr16\TimberTransientPool:
			case $cachePool instanceof \Timber\Cache\Psr16\TimberTransientPool:
				$key = $group.'_'.$key;
				break;

			//
			case $cachePool instanceof \Timber\Cache\Psr16\TimberObjectCachePool:
				// No thing to do here
				break;
				
			default:
				// Unknown cache pool :-)
		}

		//
		$cachePool->set($key, $value, $expires);

		//
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
