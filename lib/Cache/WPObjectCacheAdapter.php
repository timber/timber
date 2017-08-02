<?php namespace Timber\Cache;

use Asm89\Twig\CacheExtension\CacheProviderInterface;
use Timber\Cache;

class WPObjectCacheAdapter implements CacheProviderInterface {

	private $cache_group;

	/**
	 * @var TimberCache
	 */
	private $timberCache;

	public function __construct( Cache $timberCache, $cache_group = 'timber' ) {
		$this->cache_group = $cache_group;
		$this->timberCache = $timberCache;
	}

	public function fetch( $key ) {
		return $this->timberCache->fetch($key, $this->cache_group, Cache::CACHE_USE_DEFAULT);
	}

	public function save( $key, $value, $expire = 0 ) {
		return $this->timberCache->save($key, $value, $this->cache_group, $expire, Cache::CACHE_USE_DEFAULT);
	}

}
