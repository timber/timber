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

	private static $defaultAdapter = self::CACHE_TRANSIENT;

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

	public static function clearTimber( $adapterName = self::CACHE_USE_DEFAULT )
	{
		//
		$adapter = self::getAdapter($adapterName);

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
	public static function createCacheExtension($adapterName = Cache::CACHE_USE_DEFAULT, $group = 'timber')
	{
		$key_generator   = new \Timber\Cache\KeyGenerator();
		$cache_provider  = new \Timber\Cache\Psr16\Asm89SimpleCacheAdapter(
			self::getAdapter($adapterName, $group)
		);
		$cache_strategy  = new \Asm89\Twig\CacheExtension\CacheStrategy\GenerationalCacheStrategy($cache_provider, $key_generator);
		$cache_extension = new \Asm89\Twig\CacheExtension\Extension($cache_strategy);

		return $cache_extension;
	}

	/**
	 * @param string $adapterName
	 * @param string $classname
	 * @param bool   $supportGroup
	 * @return bool
	 */
	public static function registerAdapter($adapterName, $classname, $supportGroup = false)
	{
		switch (true) {

			// Accept PSR-16 interfaces
			case is_a($classname, '\Psr\SimpleCache\CacheInterface', true):
				break;

			// Accept PSR-6 interfaces
			case is_a($classname, '\Psr\Cache\CacheItemPoolInterface', true):
				break;
				
			// Handle non-supported classes
			case class_exists($classname):
				throw new \Exception('Class exists, but does not implement a supported (PSR-16 or PSR-6) interface');

			// Handle garbage...
			default:
				throw new \Exception('Unknown input');
		}

		//
		$registerName = $adapterName;

		//
		self::$registeredAdapters[$registerName] = array(
			'name' => $adapterName,
			'classname' => $classname,
			'supports_group' => $supportGroup,
		);
	}

	/**
	 * @param string $adapterName
	 * @param string $group
	 */
	protected static function autoloadAdapter($adapterName, $group = null)
	{
		//
		if (! isset(self::$registeredAdapters[$adapterName])) {
			throw new \Exception("No loader '$adapterName' registeret for autoloading");
		}
		
		// Get registration
		$register = self::$registeredAdapters[$adapterName];

		// Create name to be used in $loadedAdapters
		$loadedName = $adapterName;

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
		
		// Load the adapter
		self::loadAdapter($loadedName, $adapter);
	}

	/**
	 * @param string $adapterName
	 * @param string $adapter
	 */
	public static function loadAdapter($adapterName, $adapter)
	{
		// 
		if (isset(self::$loadedAdapters[$adapterName])) {
			throw new \Exception("Another adapter has already been loaded as $adapterName");
		}

		switch (true) {

			// Accept PSR-16 interfaces
			case $adapter instanceof \Psr\SimpleCache\CacheInterface:
				break;

			// Accept PSR-6 interfaces
			case $adapter instanceof \Psr\Cache\CacheItemPoolInterface:
				// Use Symfony's PSR-6 to PSR-16 adapter 
				$adapter = new \Symfony\Component\Cache\Simple\Psr6Cache($adapter);
				break;
				
			// Handle garbage...
			default:
				throw new \Exception('Unknown adapter');
		}
		
		// Put the created adapter into the array
		self::$loadedAdapters[$adapterName] = $adapter;
	}

	/**
	 * @param string $adapterName
	 * @param string $group
	 * @return bool
	 */
	protected static function getAdapter( $adapterName = self::CACHE_USE_DEFAULT, $group = self::CACHEGROUP )
	{
// TODO: What to make of this?
		if ( empty($adapterName) || self::CACHE_USE_DEFAULT === $adapterName ) {
			$adapterName = self::$defaultAdapter;
			$adapterName = apply_filters('timber_cache_mode', $adapterName);
			$adapterName = apply_filters('timber/cache/mode', $adapterName);
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
		$loadedName = $adapterName;
		
		// Test if $group was used or not
		if ($group !== null) {

			// Verify that $group is allowed
			if (! isset(self::$registeredAdapters[$adapterName])) {
				throw new \Exception('Only autoloading adapters support the $group parameter');
			}

			// Get registration
			$register = self::$registeredAdapters[$adapterName];

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
			self::autoloadAdapter($adapterName, $group);

			// Test if adapter is still not loaded
			if (! isset(self::$loadedAdapters[$loadedName])) {
				// This is unexpected
				throw new \Exception("Cache '$adapterName' is not registered registered.");
			}
		}
		
		// Return adapter
		return self::$loadedAdapters[$loadedName];
	}

	/**
	 * @param string $key
	 * @param string $adapterName
	 * @param string $group
	 * @return bool
	 */
	public static function fetch( $key, $adapterName = self::CACHE_USE_DEFAULT, $group = self::CACHEGROUP ) {

		if ($adapterName == self::CACHE_NONE) {
			return false;
		}

		//
		$adapter = self::getAdapter($adapterName, $group);
			
		//
		$value = $adapter->get($key);

		//
		return $value;
	}

	/**
	 * @param string $key
	 * @param string|boolean $value
	 * @param integer $expires
	 * @param string $adapterName
	 * @param string $group
	 * @return string|boolean
	 */
	public static function save( $key, $value, $expires = 0, $adapterName = self::CACHE_USE_DEFAULT, $group = self::CACHEGROUP ) {
		if ( (int) $expires < 1 ) {
			$expires = 0;
		}

		//
		$adapter = self::getAdapter($adapterName, $group);

		//
		$adapter->set($key, $value, $expires);

		//
		return $value;
	}
}

/* Register original 'none', 'trancient', 'site-trancient', 'object' cache modes as autoload adapters */

// Register null adapter to imitate legacy cache mode 'none'
Cache::registerAdapter(
	Cache::CACHE_NONE,
	'Symfony\Component\Cache\Adapter\NullAdapter'
);

// Register WordPress's Trancient caching as 'trancient' (with support for $group)
Cache::registerAdapter(
	Cache::CACHE_TRANSIENT,
	'\Timber\Cache\Psr16\TimberTransientPool',
	true // Support group
);

// Register WordPress's Site Trancient caching as 'site-trancient' (with support for $group)
Cache::registerAdapter(
	Cache::CACHE_SITE_TRANSIENT,
	'\Timber\Cache\Psr16\TimberSiteTransientPool',
	true // Support group
);

// Register WordPress's Object caching as 'object' (with support for $group)
if (isset($GLOBALS['wp_object_cache']) && is_object($GLOBALS['wp_object_cache'])) {
	Cache::registerAdapter(
		Cache::CACHE_OBJECT,
		'\Timber\Cache\Psr16\TimberObjectCachePool',
		true // Support group
	);
} else {
	throw new \Exception('Ehh ?!?');
}
