<?php

namespace Timber;

/**
 * Interface CoreEntityInterface
 */
interface CoreEntityInterface
{
    /**
     * Gets the underlying WordPress Core object.
     *
     * @since 2.0.0
     *
     * @return object|null
     */
    public function wp_object();
}
