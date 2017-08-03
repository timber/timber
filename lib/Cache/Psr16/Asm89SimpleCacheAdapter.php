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
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @param CacheItemPoolInterface $cache
     */
    public function __construct(\Psr\SimpleCache\CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $key
     * @return mixed|false
     */
    public function fetch($key)
    {
		return $this->cache->get($key);
	}

    /**
     * @param string $key
     * @param string $value
     * @param int|\DateInterval $lifetime
     * @return bool
     */
    public function save($key, $value, $lifetime = 0)
    {
		return $this->cache->set($key, $value, $lifetime);
    }
}
