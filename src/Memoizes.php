<?php

namespace Timber;

/**
 * Trait for caching the result of a costly method for the lifetime of an object.
 *
 * Results are stored together with a stamp made of the `last_changed` value of the given WordPress
 * cache groups and the current user ID. When content changes in WordPress or another user is set,
 * the stamp changes and the result is computed again.
 *
 * @internal
 */
trait Memoizes
{
    /**
     * Memoized results, indexed by key. Each entry is a `[stamp, value]` pair.
     *
     * @var array<string, array{0: string, 1: mixed}>
     */
    private array $memoized = [];

    /**
     * Gets a memoized value or computes and stores it.
     *
     * @param string   $key          Identifies the method and its arguments, e.g. `'children:' . md5(serialize($args))`.
     * @param callable $callback     Computes the value. Called when there is no up-to-date value.
     * @param string[] $cache_groups WordPress cache groups (e.g. `posts`, `terms`) that invalidate the value when
     *                               their `last_changed` value changes.
     * @return mixed
     */
    protected function memoize(string $key, callable $callback, array $cache_groups = [])
    {
        $stamp = (string) \get_current_user_id();

        foreach ($cache_groups as $group) {
            $stamp .= '|' . \wp_cache_get_last_changed($group);
        }

        if (!isset($this->memoized[$key]) || $this->memoized[$key][0] !== $stamp) {
            $this->memoized[$key] = [$stamp, $callback()];
        }

        return $this->memoized[$key][1];
    }
}
