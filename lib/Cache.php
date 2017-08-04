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

	private static $registeredAdapters = array();
	private static $loadedAdapters = array();

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
	 * @param string $classname
	 * @param bool   $supportGroup
	 * @return bool
	 */
	public static function registerAdapter($cache_mode, $classname, $supportGroup = false)
	{
		switch (true) {

			// Accept PSR-16 interfaces
			case is_a($classname, '\Psr\SimpleCache\CacheInterface', true):
				break;

			// Accept PSR-6 interfaces
			case is_a($classname, '\Psr\Cache\CacheItemPoolInterface', true):
				throw new \Exception('PSR-6 is not implemented yet');
				
			// Handle non-supported classes
			case class_exists($classname):
				throw new \Exception('Class exists, but does not implement a supported (PSR-16 or PSR-6) interface');

			// Handle garbage...
			default:
				throw new \Exception('Unknown input');
		}

		//
		$registerName = $cache_mode;

		//
		self::$registeredAdapters[$registerName] = array(
			'name' => $cache_mode,
			'classname' => $classname,
			'supports_group' => $supportGroup,
		);
	}

	/**
	 * @param string $cache_mode
	 * @param string $group
	 */
	protected static function loadAdapter($cache_mode, $group = null)
	{
		// Get registration
		$register = self::$registeredAdapters[$cache_mode];

		// Create name to be used in $loadedAdapters
		$loadedName = $cache_mode;

		// Test if $group was used or not
		if ($group === null) {
			
			// Create adapter object
			$adapter = new $register['classname']();

		} else {

			// Adapter must be registered with support for $group parameter for this to work
			if ($register['supports_group'] !== true) {
				throw new \Exception('Adapter does not support usage of the $group parameter');				
			}
			
			// Append ':$group' to $loadedName
			$loadedName .= ':'.$group;

			// Create adapter object - with $group parameter
			$adapter = new $register['classname']($group);
		}
		
		// Put the created adapter into the array
		self::$loadedAdapters[$loadedName] = $adapter;
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
		
		// Create name to be used in $loadedAdapters
		$loadedName = $cache_mode;
		
		// Test if $group was used or not
		if ($group !== null) {

			// Verify that $group is allowed
			if (! isset(self::$registeredAdapters[$cache_mode])) {
				throw new \Exception('Only autoloading adapters support the $group parameter');
			}

			// Get registration
			$register = self::$registeredAdapters[$cache_mode];

			// Adapter must be registered with support for $group parameter for this to work
			if ($register['supports_group'] !== true) {
				throw new \Exception('Adapter does not support usage of the $group parameter');				
			}
			
			// Append ':$group' to $loadedName
			$loadedName .= ':'.$group;
		}

		// Test if adapter is not loaded
		if (! isset(self::$loadedAdapters[$loadedName])) {
		
			// Try to load adaptor
			self::autoloadAdapter($cache_mode, $group);

			// Test if adapter is still not loaded
			if (! isset(self::$loadedAdapters[$loadedName])) {
				// This is unexpected
				throw new \Exception("Cache '$cache_mode' is not registered registered.");
			}
		}
		
		// Return adapter
		return self::$loadedAdapters[$loadedName];
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

Cache::registerAdapter(
	Cache::CACHE_TRANSIENT,
	'\Timber\Cache\Psr16\TimberTransientPool',
	true
);

Cache::registerAdapter(
	Cache::CACHE_SITE_TRANSIENT,
	'\Timber\Cache\Psr16\TimberSiteTransientPool',
	true
);

if (isset($GLOBALS['wp_object_cache']) && is_object($GLOBALS['wp_object_cache'])) {
	Cache::registerAdapter(
		Cache::CACHE_OBJECT,
		'\Timber\Cache\Psr16\TimberObjectCachePool',
		true
	);
} else {
	throw new \Exception('Ehh ?!?');
}
