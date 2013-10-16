<?php namespace Timber\Cache;

use Asm89\Twig\CacheExtension\CacheProviderInterface;
use TimberLoader;

class WPObjectCacheAdapter implements CacheProviderInterface
{

    private $cache_group;

    /**
     * @var TimberLoader
     */
    private $timberloader;

    public function __construct( TimberLoader $timberloader, $cache_group = 'timber' ) {
        $this->cache_group  = $cache_group;
        $this->timberloader = $timberloader;
    }

    public function fetch( $key ) {
        return $this->timberloader->get_cache( $key, $this->cache_group, TimberLoader::CACHE_USE_DEFAULT );
    }

    public function save( $key, $value, $expire = 0 ) {
        return $this->timberloader->set_cache( $key, $value, $this->cache_group, $expire, TimberLoader::CACHE_USE_DEFAULT );
    }

}
