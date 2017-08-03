<?php

namespace Timber\Cache\Psr16;

/**
 * Adapter class to make WordPress' site transient caching available wia the PSR-16 interface.
 * This verison truncates keys longer than what Wordpress supports
 * 
 * @author Heino H. Gehlsen <heino@gehlsen.dk>
 * @copyright 2017 Heino H. Gehlsen
 * @license MIT
 */
class TimberWordpressSiteTransientPool
	extends WordpressSiteTransientPool
{
	public static function clearTimber($group = \Timber\Cache::CACHEGROUP)
	{
// Origin: Timber v1.3.4 (Timber\Loader::clearCacheTimberDatabase())
		global $wpdb;
		$query = $wpdb->prepare(
			"DELETE
				FROM
					$wpdb->options
				WHERE
					option_name
				LIKE '%s'",
			'_transient_'.$group.'_%'
		);
		return $wpdb->query($query);
	}
}
