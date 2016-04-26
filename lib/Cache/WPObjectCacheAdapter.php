<?php namespace Timber\Cache;

use Asm89\Twig\CacheExtension\CacheProviderInterface;
use Timber\Loader;

class WPObjectCacheAdapter implements CacheProviderInterface {

	private $cache_group;

	/**
	 * @var TimberLoader
	 */
	private $timberloader;

	public function __construct( Loader $timberloader, $cache_group = 'timber' ) {
		$this->cache_group = $cache_group;
		$this->timberloader = $timberloader;
	}

	public function fetch( $key ) {
		return $this->timberloader->get_cache($key, $this->cache_group, Loader::CACHE_USE_DEFAULT);
	}

	public function save( $key, $value, $expire = 0 ) {
		return $this->timberloader->set_cache($key, $value, $this->cache_group, $expire, Loader::CACHE_USE_DEFAULT);
	}

}
