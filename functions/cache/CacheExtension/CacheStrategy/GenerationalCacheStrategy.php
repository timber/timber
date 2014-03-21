<?php

/*
 * This file is part of twig-cache-extension.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\CacheExtension\CacheStrategy;

use Asm89\Twig\CacheExtension\CacheProviderInterface;
use Asm89\Twig\CacheExtension\CacheStrategyInterface;

/**
 * Strategy for generational caching.
 *
 * In theory the strategy only saves fragments to the cache with infinite
 * lifetime. The key of the strategy lies in the fact that the keys for blocks
 * will change as the value for which the key is generated changes.
 *
 * For example: entities containing a last update time, would include a
 * timestamp in the key.
 *
 * @see http://37signals.com/svn/posts/3113-how-key-based-cache-expiration-works
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class GenerationalCacheStrategy implements CacheStrategyInterface
{
    private $keyGenerator;
    private $cache;
    private $lifetime;

    /**
     * @param CacheProviderInterface $cache
     * @param KeyGeneratorInterface  $keyGenerator
     * @param integer                $lifetime
     */
    public function __construct(CacheProviderInterface $cache, KeyGeneratorInterface $keyGenerator, $lifetime = 0)
    {
        $this->keyGenerator = $keyGenerator;
        $this->cache        = $cache;
        $this->lifetime     = $lifetime;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchBlock($key)
    {
        return $this->cache->fetch($key);
    }

    /**
     * {@inheritDoc}
     */
    public function generateKey($annotation, $value)
    {
        $key = $this->keyGenerator->generateKey($value);

        if (null === $key) {
            // todo: more specific exception
            throw new \RuntimeException('Key generator did not return a key.');
        }

        return $annotation . '__GCS__' . $key;
    }

    /**
     * {@inheritDoc}
     */
    public function saveBlock($key, $block)
    {
        return $this->cache->save($key, $block, $this->lifetime);
    }
}
