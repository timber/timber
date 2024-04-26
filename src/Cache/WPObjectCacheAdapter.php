<?php

namespace Timber\Cache;

use Timber\Loader;

class WPObjectCacheAdapter
{
    public function __construct(
        private readonly Loader $timberloader,
        private $cache_group = 'timber'
    ) {
    }

    public function fetch($key)
    {
        return $this->timberloader->get_cache($key, $this->cache_group, Loader::CACHE_USE_DEFAULT);
    }

    public function save($key, $value, $expire = 0)
    {
        return $this->timberloader->set_cache($key, $value, $this->cache_group, $expire, Loader::CACHE_USE_DEFAULT);
    }
}
