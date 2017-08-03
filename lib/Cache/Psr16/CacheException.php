<?php

namespace Timber\Cache\Psr16;

/**
 * Interface used for all types of exceptions thrown by the implementing library.
 * 
 * @author Heino H. Gehlsen <heino@gehlsen.dk>
 * @copyright 2017 Heino H. Gehlsen
 * @license MIT
 */
class CacheException
	extends \Exception
	implements \Psr\SimpleCache\CacheException
{
}
