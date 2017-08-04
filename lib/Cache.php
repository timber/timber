<?php

namespace Timber;

/**
 * 
 * @author Heino H. Gehlsen <heino@gehlsen.dk>
 * @copyright 2017 Heino H. Gehlsen
 * @license MIT
 */
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

	public static function clearTimber( $cache_mode = self::CACHE_USE_DEFAULT )
	{
		//
		$adapter = self::getAdapter($cache_mode);

		//
		switch (true) {

			//
			case $adapter instanceof \Timber\Cache\Psr16\TimberTransientPool:
			case $adapter instanceof \Timber\Cache\Psr16\TimberSiteTransientPool:
				return $adapter->clearTimber();

			//
			case $adapter instanceof \Timber\Cache\Psr16\TimberObjectCachePool:
				return $adapter->clearTimber();
				
			default:
				// Unknown cache pool :-)

// TODO: call $adapter->clear() ???
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
			self::getAdapter($cache_mode, $group)
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
	protected static function getAdapter( $cache_mode = self::CACHE_USE_DEFAULT, $group = self::CACHEGROUP )
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
		$adapter = self::getAdapter($cache_mode, $group);
			
		//
		$value = $adapter->get($key);

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
		$adapter = self::getAdapter($cache_mode, $group);

		//
		$adapter->set($key, $value, $expires);

		//
		return $value;
	}
}
