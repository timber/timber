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
 * Cache strategy interface.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
interface CacheStrategyInterface
{
    /**
     * Fetch the block for a given key.
     *
     * @param mixed $key
     *
     * @return string
     */
    public function fetchBlock($key);

    /**
     * Generate a key for the value.
     *
     * @param string $annotation
     * @param mixed  $value
     *
     * @return mixed
     */
    public function generateKey($annotation, $value);

    /**
     * Save the contents of a rendered block.
     *
     * @param mixed  $key
     * @param string $block
     */
    public function saveBlock($key, $block);
}
