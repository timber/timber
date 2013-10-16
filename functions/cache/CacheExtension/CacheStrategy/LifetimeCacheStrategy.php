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
 * Strategy for caching with a pre-defined lifetime.
 *
 * The value passed to the strategy is the lifetime of the cache block in
 * seconds.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class LifetimeCacheStrategy implements CacheStrategyInterface
{
    private $cache;

    /**
     * @param CacheProviderInterface $cache
     */
    public function __construct(CacheProviderInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchBlock($key)
    {
        return $this->cache->fetch($key['key']);
    }

    /**
     * {@inheritDoc}
     */
    public function generateKey($annotation, $value)
    {
        if (! is_numeric($value)) {
            //todo: specialized exception
            throw new \RuntimeException('Value is not a valid lifetime.');
        }

        return array(
            'lifetime' => $value,
            'key' => '__LCS__' . $annotation,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function saveBlock($key, $block)
    {
        return $this->cache->save($key['key'], $block, $key['lifetime']);
    }
}
