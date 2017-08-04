<?php

namespace Timber\Cache\Psr16;

/**
 * Adapter class to make WordPress' site transient caching available wia the PSR-16 interface.
 * 
 * @author Heino H. Gehlsen <heino@gehlsen.dk>
 * @copyright 2017 Heino H. Gehlsen
 * @license MIT
 */
class WordpressSiteTransientAdapter
	extends AbstractWordpressAdapter
{
	/**
     * 
     */
    public function __construct()
	{
		// Verify that Wordpress is loaded.
		parent::__construct();

		// Verify that the cache implementation exists.
		if (! function_exists('get_site_transient')) {
			throw new CacheException("WordPress' site transient cache implementation is not available");
		}
	}

	/**
     * 
     */
    public static function getMaxKeyLength()
	{
		static $maxLength = null;
		if ($maxLength) {
			return $maxLength;
		}
		
		// "In WordPress versions previous to 4.4, the length limitation was 45 in set_transient (now 172) and 64 in the database (now 191)." (https://codex.wordpress.org/Function_Reference/set_transient)
		// NB! Site-transient is shorter: "167 characters or less in length" (https://codex.wordpress.org/Function_Reference/set_site_transient)
		return $maxLength = version_compare(get_bloginfo('version'), '4.4', '>=') ? 167 : 45;
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
		$value = \get_site_transient($key);
		
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
// TODO:
			throw new CacheException('TODO');
		}
		
		//
		$set = \set_site_transient($key, $value, $ttl);

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
		$deleted = \delete_site_transient($key);

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

		$this->deleteTransients();
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
			$value = \get_site_transient($key);

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
			$set = \set_site_transient($key, $value, $ttl);			

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
			$deleted = \delete_site_transient($key);

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
		$value = $this->get($key);
		
		// "False is returned on fail: "If the transient does not exist, does not have a value, or has expired, then get_transient will return false" (https://codex.wordpress.org/Function_Reference/get_transient)
		if ($value === false) {
			return false;
		}

		//
		return true;
	}

    /**
     * 
     */
    protected function validateKey($key)
	{
		//
		if (! is_string($key)) {
			throw new InvalidKeyException('Must be a string');
		}

		//
		if (strlen($key) > self::getMaxKeyLength()) {
			throw new KeyTooLongException('Max length of key is ' . self::getMaxKeyLength() . ' characters (key is ' . strlen($key) . ' characters long)');
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

    /**
     * 
     */
	protected static function deleteTransients()
	{
// Origin: Timber v1.3.4 (Timber\Cache\Cleaner::delete_transients_single_site())
		global $_wp_using_ext_object_cache;
		if ( $_wp_using_ext_object_cache ) {
			return 0;
		}

		global $wpdb;
		$records = 0;

		// Delete transients from options table
		$records .= self::deleteTransientsSingleSite();

		// Delete transients from multisite, if configured as such
		if ( is_multisite() && is_main_network() ) {
			$records .= self::deleteTransientsMultisite();
		}
		
		return $records;
	}
	
    /**
     * 
     */
	protected static function deleteTransientsSingleSite()
	{
// Origin: Timber v1.3.4 (Timber\Cache\Cleaner::delete_transients_single_site())
		global $wpdb;
		$sql = "
				DELETE
					a, b
				FROM
					{$wpdb->options} a, {$wpdb->options} b
				WHERE
					a.option_name LIKE '%_transient_%' AND
					a.option_name NOT LIKE '%_transient_timeout_%' AND
					b.option_name = CONCAT(
						'_transient_timeout_',
						SUBSTRING(
							a.option_name,
							CHAR_LENGTH('_transient_') + 1
						)
					)
				AND b.option_value < UNIX_TIMESTAMP()
			";

		return $wpdb->query($sql);
	}

    /**
     * 
     */
	protected static function deleteTransientsMultisite()
	{
// Origin: Timber v1.3.4 (Timber\Cache\Cleaner::delete_transients_multisite())
		global $wpdb;
		$sql = "
				DELETE
					a, b
				FROM
					{$wpdb->sitemeta} a, {$wpdb->sitemeta} b
				WHERE
					a.meta_key LIKE '_site_transient_%' AND
					a.meta_key NOT LIKE '_site_transient_timeout_%' AND
					b.meta_key = CONCAT(
						'_site_transient_timeout_',
						SUBSTRING(
							a.meta_key,
							CHAR_LENGTH('_site_transient_') + 1
						)
					)
				AND b.meta_value < UNIX_TIMESTAMP()
			";

		$clean = $wpdb->query($sql);

		return $clean;
	}
}
