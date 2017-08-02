<?php namespace Timber\Cache;

use Asm89\Twig\CacheExtension\CacheProviderInterface;
use Timber\Cache;

class WPObjectCacheAdapter implements CacheProviderInterface {

	private $cache_group;

	public function __construct($cache_group = 'timber' ) {
		$this->cache_group = $cache_group;
	}

	public function fetch( $key ) {
		return Cache::fetch($key, $this->cache_group, Cache::CACHE_USE_DEFAULT);
	}

	public function save( $key, $value, $expire = 0 ) {
		return Cache::save($key, $value, $this->cache_group, $expire, Cache::CACHE_USE_DEFAULT);
	}

}
