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
class TimberObjectCachePool
	extends WordpressObjectCachePool
{
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
