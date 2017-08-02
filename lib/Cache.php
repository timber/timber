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
	public static function fetch( $key, $group = self::CACHEGROUP, $cache_mode = self::CACHE_USE_DEFAULT ) {
		$value = false;

		$trans_key = substr($group.'_'.$key, 0, self::TRANS_KEY_LEN);
		
		$cache_mode = self::filterCacheMode($cache_mode);
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
	public static function save( $key, $value, $group = self::CACHEGROUP, $expires = 0, $cache_mode = self::CACHE_USE_DEFAULT ) {
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

	public static function deleteTransients()
	{
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
	protected static function deleteTransientsSingleSite()
	{
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

	protected static function deleteTransientsMultisite()
	{
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
