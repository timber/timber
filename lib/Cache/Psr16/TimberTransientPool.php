<?php

namespace Timber\Cache\Psr16;

/**
 * Adapter class to make WordPress' transient caching available wia the PSR-16 interface.
 * This verison truncates keys longer than what Wordpress supports
 * 
 * @author Heino H. Gehlsen <heino@gehlsen.dk>
 * @copyright 2017 Heino H. Gehlsen
 * @license MIT
 */
class TimberTransientPool
	extends WordpressTransientPool
{
}
