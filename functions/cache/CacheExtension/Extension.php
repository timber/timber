<?php

/*
 * This file is part of twig-cache-extension.
 *
 * (c) Alexander <iam.asm89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm89\Twig\CacheExtension;

/**
 * Extension for caching template blocks with twig.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class Extension extends \Twig_Extension
{
    private $cacheStrategy;

    /**
     * @param CacheStrategyInterface $cacheStrategy
     */
    public function __construct(CacheStrategyInterface $cacheStrategy)
    {
        $this->cacheStrategy = $cacheStrategy;
    }

    /**
     * @return CacheStrategyInterface
     */
    public function getCacheStrategy()
    {
        return $this->cacheStrategy;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'asm89_cache';
    }

    /**
     * {@inheritDoc}
     */
    public function getTokenParsers()
    {
        return array(
            new TokenParser\Cache(),
        );
    }
}
