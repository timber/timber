<?php

namespace Timber\Cache\Psr16;

/**
 * Adapter class to make Asm89 Twig caching extension interoperable with every PSR-16 adapter.
 *
 * @see http://www.php-fig.org/psr/psr-16/
 *
 * @author Heino H. Gehlsen <heino@gehlsen.dk>
 * @copyright 2017 Heino H. Gehlsen
 * @license MIT
 */
class Asm89SimpleCacheAdapter
	implements \Asm89\Twig\CacheExtension\CacheProviderInterface
{
    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    private $cache;

    /**
     * @var \Psr\SimpleCache\CacheException
     */
    private $lastException;

    /**
     * @param \Psr\SimpleCache\CacheInterface $cache
     */
    public function __construct(\Psr\SimpleCache\CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $key
     * @return mixed|false False, if there was no value to be fetched. Null or a string otherwise.
     */
    public function fetch($key)
    {
		// Reset last exception
		$this->lastException = null;
		
		try {
			// Get value from implementing library. Return false if there was no value to be fetched
			$value = $this->cache->get($key, false);
			
			// Return cached value
			return $value;

		// "Exception interface for invalid cache arguments."
		} catch (\Psr\SimpleCache\InvalidArgumentException $e) {
			// Save exception for retrieval by caller 
			$this->lastException = $e;
			// Return fail to caller
			return false;

		// "Interface used for all types of exceptions thrown by the implementing library."
		} catch (\Psr\SimpleCache\CacheException $e) {
			// Save exception for retrieval by caller 
			$this->lastException = $e;
			// Return fail to caller
			return false;
		}
	}

    /**
     * @param string  $key
     * @param string  $value
     * @param integer $lifetime
     *
     * @return bool
     */
    public function save($key, $value, $lifetime = 0)
    {
		// Reset last exception
		$this->lastException = null;
		
		try {
			// Send value to implementing library.
			$success = $this->cache->set($key, $value, $lifetime);

			// Return boolean from implementing library.
			return $success;

		// "Exception interface for invalid cache arguments."
		} catch (\Psr\SimpleCache\InvalidArgumentException $e) {
			// Save exception for retrieval by caller 
			$this->lastException = $e;
			// Return fail to caller
			return false;

		// "Interface used for all types of exceptions thrown by the implementing library."
		} catch (\Psr\SimpleCache\CacheException $e) {
			// Save exception for retrieval by caller 
			$this->lastException = $e;
			// Return fail to caller
			return false;
		}
    }
}
