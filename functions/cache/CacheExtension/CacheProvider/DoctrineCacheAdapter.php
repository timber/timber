<?php

/*
 * This file is part of twig-cache-extension.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\CacheExtension\CacheProvider;

use Asm89\Twig\CacheExtension\CacheProviderInterface;
use Doctrine\Common\Cache\Cache;

/**
 * Adapter class to use the cache classes provider by Doctrine.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class DoctrineCacheAdapter implements CacheProviderInterface
{
    private $cache;

    /**
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($key)
    {
        return $this->cache->fetch($key);
    }

    /**
     * {@inheritDoc}
     */
    public function save($key, $value, $lifetime = 0)
    {
        return $this->cache->save($key, $value, $lifetime);
    }
}
