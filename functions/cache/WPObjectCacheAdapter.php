<?php namespace Timber\Cache;

use Asm89\Twig\CacheExtension\CacheProviderInterface;

class WPObjectCacheAdapter implements CacheProviderInterface
{

    private $_cachegroup = 'timber';

    public function __construct( $cachegroup = '' ) {
        if ( !empty( $cachegroup ) ) {
            $this->_cachegroup = $cachegroup;
        }
    }

    public function fetch( $key ) {
        return wp_cache_get( $key, $this->_cachegroup );
    }

    public function save( $key, $data, $expire = 0 ) {
        return wp_cache_set( $key, $data, $this->_cachegroup, $expire );
    }

}
