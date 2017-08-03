<?php

namespace Timber\Cache\Psr16;

/**
 * Adapter class to make WordPress' site transient caching available wia the PSR-16 interface.
 * 
 * @author Heino H. Gehlsen <heino@gehlsen.dk>
 * @copyright 2017 Heino H. Gehlsen
 * @license MIT
 * 
 * @todo Consider implementing a something with wp_cache_switch_to_blog()
 */
class WordpressObjectCachePool
	extends AbstractWordpressPool
{
	/**
     * @param string @group
     */
    public function __construct($group = '')
	{
		// Verify that Wordpress is loaded.
		parent::__construct();

		// Verify that the cache implementation exists.
		if (! function_exists('wp_cache_get')) {
			throw new CacheException("WordPress' object cache implementation is not available");
		}
		
		//
		$this->group = $this->validateGroup($group);
	}

	/**
     * Fetches a value from the cache.
     *
     * @param string $key     The unique key of this item in the cache.
     * @param mixed  $default Default value to return if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function get($key, $default = null)
	{
		//
		$this->validateKey($key);
		
		//
		$value = \wp_cache_get($key, $this->group, $force = false, $found);
		
		// False is returned on fail: "If the transient does not exist, does not have a value, or has expired, then get_transient will return false" (https://codex.wordpress.org/Function_Reference/get_transient)
		if ($value === false) {
			return $default;
		}
		
		//
		return $value;
	}

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string                $key   The key of the item to store.
     * @param mixed                 $value The value of the item to store, must be serializable.
     * @param null|int|DateInterval $ttl   Optional. The TTL value of this item. If no value is sent and
     *                                     the driver supports TTL then the library may set a default value
     *                                     for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function set($key, $value, $ttl = null)
	{
		//
		$this->validateKey($key);

		//
		if ($value === false) {
			// "Because of this "false" value, transients should not be used to hold plain boolean values. Put them into an array or convert them to integers instead." (https://codex.wordpress.org/Function_Reference/get_transient)
			throw new CacheException('');
		}
		
		//
		$set = \wp_cache_set($key, $value, $this->group, $ttl);

		//
		if ($set === false) {
			//
			return $set;
		}
		
		//
		return $set;
	}

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function delete($key)
	{
		//
		$this->validateKey($key);

		//
		$deleted = \wp_cache_delete($key, $this->group);

		// True if successful, false otherwise.
		if ($deleted === false) {
			//
			return $default;
		}

		//
		return $deleted;
	}

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear()
	{
// TODO:

		throw new CacheException('Not implemented yet');

		return \wp_cache_flush();
	}

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys    A list of keys that can obtained in a single operation.
     * @param mixed    $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple($keys, $default = null)
	{
		// 
		if (!is_array($keys) && $keys instanceof \Traversable) {
			throw \Psr\SimpleCache\InvalidArgumentException('');
		}

		foreach ($keys as $key) {
			//
			$this->validateKey($key);
		}

		//
		$array = array();
		
		//
		foreach ($keys as $key) {

			//
			$value = \wp_cache_get($key, $this->group, $force = false, $found);

			// False is returned on fail: "If the transient does not exist, does not have a value, or has expired, then get_transient will return false" (https://codex.wordpress.org/Function_Reference/get_transient)
			if ($value !== false) {
				// 
				$array[$key] = $value;
			} else {
				// 
				$array[$key] = $default;
			}
		}
		
		//
		return $success;
	}

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable              $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple($values, $ttl = null)
	{
		// 
		if (!is_array($values) && $values instanceof \Traversable) {
			throw \Psr\SimpleCache\InvalidArgumentException('');
		}

		// 
		foreach ($values as $key => $value) {
			//
			$this->validateKey($key);

			//
			if ($value === false) {
				// "Because of this "false" value, transients should not be used to hold plain boolean values. Put them into an array or convert them to integers instead." (https://codex.wordpress.org/Function_Reference/get_transient)
				throw new CacheException('');
			}
		}

		//
		$success = false;
		
		//
		foreach ($values as $key => $value) {

			//
			$set = \wp_cache_set($key, $value, $this->group, $ttl);

			//
			if ($set === false) {
				$success = false;
			}
		}
		
		//
		return $success;
	}

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple($keys)
	{
		// 
		if (!is_array($keys) && $keys instanceof \Traversable) {
			throw \Psr\SimpleCache\InvalidArgumentException('');
		}

		foreach ($keys as $key) {
			//
			$this->validateKey($key);
		}

		//
		$success = false;
		
		//
		foreach ($keys as $key) {

			//
			$deleted = \wp_cache_delete($key, $this->group);

			//
			if ($deleted === false) {
				$success = false;
			}
		}
		
		//
		return $success;
	}

	/**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function has($key)
	{
		//
//		throw new CacheException('Not implemented yet');

		//
		$value = $this->get($key);
		
		// False is returned on fail: "If the transient does not exist, does not have a value, or has expired, then get_transient will return false" (https://codex.wordpress.org/Function_Reference/get_transient)
		if ($value === false) {
			return false;
		}

		//
		return true;
	}

    /**
     * 
     */
    protected function validateGroup($group)
	{
		//
		if (! is_string($group)) {
			throw new InvalidArgumentException('Must be a string');
		}
	}

    /**
     * 
     */
    protected function validateKey($key)
	{
		//
		if (! is_string($key) && ! is_int($key)) {
			throw new InvalidKeyException('Must be a string or integer');
		}

		//
		if (strlen($key) == 0) {
			throw new InvalidKeyException('Empty key not allowed');
		}
	}

    /**
     * 
     */
    protected function isKeyValid($key)
	{
		try {
			//
			$this->validateKey($key);

		} catch (InvalidArgumentException $e) {
			//
			return false;
		}

		//
		return true;
	}

	public static function clearTimber()
	{
// Origin: Timber v1.3.4 (Timber\Loader::clear_cache_timber_object())
		global $wp_object_cache;
		if ( isset($wp_object_cache->cache[$this->group]) ) {
			$items = $wp_object_cache->cache[$this->group];
			foreach ( $items as $key => $value ) {
				if ( is_multisite() ) {
					$key = preg_replace('/^(.*?):/', '', $key);
				}
				wp_cache_delete($key, $this->group);
			}
			return true;
		}
}
