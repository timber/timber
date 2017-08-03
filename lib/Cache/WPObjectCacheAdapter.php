<?php namespace Timber\Cache;

use Asm89\Twig\CacheExtension\CacheProviderInterface;
use Timber\Cache;

class WPObjectCacheAdapter implements CacheProviderInterface {

	private $cache_group;

	public function __construct($cache_group = 'timber' ) {
		$this->cache_group = $cache_group;
	}

	public function fetch( $key ) {
		return Cache::fetch($key, Cache::CACHE_USE_DEFAULT, $this->cache_group);
	}

	public function save( $key, $value, $expire = 0 ) {
		return Cache::save($key, $value, $expire, Cache::CACHE_USE_DEFAULT, $this->cache_group);
	}

}
