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
	 * @param string $group
	 * @return bool
	 */
	public static function getAdapter( $adapterName, $group = null )
	{
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
		
			try {
				// Try to load adaptor
				self::autoloadAdapter($adapterName, $group);
				
			} catch (\Exception $e) {
				
// TODO: Currently bypasses compatibility with old bad practive, to allow loading of new PSR-6/16 cache adapters
//				throw $e;

				// Backward compatibility: On failed autoload, fallback to WordPress' object cache. 
				switch ($adapterName) {

					case self::CACHE_NONE:
					case self::CACHE_OBJECT:
					case self::CACHE_TRANSIENT:
					case self::CACHE_SITE_TRANSIENT:
						// This is unexpected
						throw new \Exception("Cache '$adapterName' is not registered registered.");
						
					default:
						// Overload $loadedName (the bad old Timber way)
						$loadedName = self::CACHE_OBJECT;
						// Load the fallback adaptor
						self::autoloadAdapter($loadedName, $group);
				}
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
	public static function filterAdapterName( $adapterName, $group = null)
	{
		if ( empty($adapterName) || self::CACHE_USE_DEFAULT === $adapterName ) {
			// Use default adapter as set in class property
			$adapterName = self::$defaultAdapter;
			// Apply Wordpress filters
			$adapterName = apply_filters('timber_cache_mode', $adapterName);
			$adapterName = apply_filters('timber/cache/mode', $adapterName);
		}
		
		// Return adapter name
		return $adapterName;
	}

	/**
	 * @param string $key
	 * @param string $adapterName
	 * @param string $group
	 * @return bool
	 */
	public static function get( $key, $adapterName = self::CACHE_USE_DEFAULT, $group = self::CACHEGROUP )
	{
		// Filter $adapterName through Timber's Wordpress filters
		$adapterName = self::filterAdapterName($adapterName, $group);

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
	public static function set( $key, $value, $expires = 0, $adapterName = self::CACHE_USE_DEFAULT, $group = self::CACHEGROUP )
	{
		if ( (int) $expires < 1 ) {
			$expires = 0;
		}

		// Filter $adapterName through Timber's Wordpress filters
		$adapterName = self::filterAdapterName($adapterName, $group);

		//
		$adapter = self::getAdapter($adapterName, $group);

		//
		return $adapter->set($key, $value, $expires);
	}
	
	/**
	 * @param string $adapterName
	 * @return boolean
	 */
	public static function clear( $adapterName = self::CACHE_USE_DEFAULT, $group = self::CACHEGROUP )
	{
		// Filter $adapterName through Timber's Wordpress filters
		$adapterName = self::filterAdapterName($adapterName, $group);

		//
		$adapter = self::getAdapter($adapterName, $group);

// TODO: Remove temp calls to clearTimber() methods in own adapters. These are to be rewritten into PSR-16's naming: clean()
		switch (true) {
			case $adapter instanceof \Timber\Cache\Psr16\TimberTransientAdapter:
			case $adapter instanceof \Timber\Cache\Psr16\TimberSiteTransientAdapter:
			case $adapter instanceof \Timber\Cache\Psr16\TimberObjectCacheAdapter:
				return $adapter->clearTimber();
		}
				
// TODO: Currently diabled, until further tested...
		throw new \Exception('Currently unimplemented');
		// Return boolean from adapter
		return $adapter->clear();
	}

// TODO: Move this avay from this class, or integrate with clear()
	public static function deleteCache()
	{
		\Timber\Cache\Psr16\WordpressTransientAdapter::deleteTransients();
	}
}

/* Register original 'none', 'trancient', 'site-trancient', 'object' cache modes as autoload adapters */

// Register null adapter to imitate legacy cache mode 'none' (with support for $group)
Cache::registerAdapter(
	Cache::CACHE_NONE,
	'Symfony\Component\Cache\Adapter\NullAdapter',
	true // Support group
);

// Register WordPress's Trancient caching as 'trancient' (with support for $group)
Cache::registerAdapter(
	Cache::CACHE_TRANSIENT,
	'\Timber\Cache\Psr16\TimberTransientAdapter',
	true // Support group
);

// Register WordPress's Site Trancient caching as 'site-trancient' (with support for $group)
Cache::registerAdapter(
	Cache::CACHE_SITE_TRANSIENT,
	'\Timber\Cache\Psr16\TimberSiteTransientAdapter',
	true // Support group
);

// Register WordPress's Object caching as 'cache' (with support for $group)
if (isset($GLOBALS['wp_object_cache']) && is_object($GLOBALS['wp_object_cache'])) {
	Cache::registerAdapter(
		Cache::CACHE_OBJECT,
		'\Timber\Cache\Psr16\TimberObjectCacheAdapter',
		true // Support group
	);
} else {
	throw new \Exception('Ehh ?!?');
}
