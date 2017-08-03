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
}
