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
class UnsafeWordpressSiteTransientPool
	extends WordpressSiteTransientPool
{
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
			//
			$key = substr($key, 0, self::getMaxKeyLength());
		}
	
		//
		return parent::validateKey($key);
	}
}
