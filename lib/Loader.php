<?php

namespace Timber;

use Timber\Cache\Cleaner;

final class Loader
{
	const CACHEGROUP = Cache::CACHEGROUP;

	const TRANS_KEY_LEN = Cache::TRANS_KEY_LEN;

	const CACHE_NONE = Cache::CACHE_NONE;
	const CACHE_OBJECT = Cache::CACHE_OBJECT;
	const CACHE_TRANSIENT = Cache::CACHE_TRANSIENT;
	const CACHE_SITE_TRANSIENT = Cache::CACHE_SITE_TRANSIENT;
	const CACHE_USE_DEFAULT = Cache::CACHE_USE_DEFAULT;

	public static $cache_modes = array(
		self::CACHE_NONE,
		self::CACHE_OBJECT,
		self::CACHE_TRANSIENT,
		self::CACHE_SITE_TRANSIENT
	);

	private $cacheInstance;
	
	/**
	 * @param bool|string   $caller the calling directory or false
	 */
	public function __construct( $caller = false )
	{
		$this->cacheInstance = new Cache();
	}

	/**
	 * @param string $name
	 * @return bool
	 * @deprecated 1.3.5 No longer used internally
	 * @todo remove in 2.x
	 * @codeCoverageIgnore
	 */
	protected function template_exists( $name ) {
		return $this->twig->getLoader()->exists($name);
	}


	/**
	 * @return \Twig_LoaderInterface
	 * @deprecated No longer relevant due to Twig_Environment::getLoader().
	 * @todo remove
	 */
	public function get_loader() {
// TODO: Remove.
		// This returns a proxy filesystem loader to preserve backward compatibility, by letting users add (but not remove internal) paths.
		if ($this->twig->getLoader() instanceof ChainLoader) {
			return $this->twig->getLoader()->getTemporaryLoader();
		}
		// Just return loader...
		return $this->twig->getLoader();
	}


	/**
	 * @return \Twig_Environment
	 * @deprecated Since class now extends Twig_Environment.
	 * @todo remove
	 */
	public function get_twig() {
		return Timber::getTwigEnvironment();
	}

	public function clear_cache_timber( $cache_mode = self::CACHE_USE_DEFAULT ) {
		return $this->cacheInstance->clear_cache_timber( $cache_mode);
	}

	public function clear_cache_twig() {
		if ( method_exists($this, 'clearCacheFiles') ) {
			$this->clearCacheFiles();
		}
		$cache = Timber::getTwigEnvironment()->getCache();
		if ( $cache ) {
			self::rrmdir(Timber::getTwigEnvironment()->getCache());
			return true;
		}
		return false;
	}

	/**
	 * @param string|false $dirPath
	 */
	public static function rrmdir( $dirPath ) {
		if ( !is_dir($dirPath) ) {
			throw new \InvalidArgumentException("$dirPath must be a directory");
		}
		if ( substr($dirPath, strlen($dirPath) - 1, 1) != '/' ) {
			$dirPath .= '/';
		}
		$files = glob($dirPath.'*', GLOB_MARK);
		foreach ( $files as $file ) {
			if ( is_dir($file) ) {
				self::rrmdir($file);
			} else {
				unlink($file);
			}
		}
		rmdir($dirPath);
	}

	/**
	 * @return \Asm89\Twig\CacheExtension\Extension
	 */
	public static function createCacheExtension() {
		return Cache::createCacheExtension();
	}

	/**
	 * @param string $key
	 * @param string $group
	 * @param string $cache_mode
	 * @return bool
	 */
	public function get_cache( $key, $group = self::CACHEGROUP, $cache_mode = self::CACHE_USE_DEFAULT ) {
		return $this->cacheInstance->fetch( $key, $group, $cache_mode);
	}

	/**
	 * @param string $key
	 * @param string|boolean $value
	 * @param string $group
	 * @param integer $expires
	 * @param string $cache_mode
	 * @return string|boolean
	 */
	public function set_cache( $key, $value, $group = self::CACHEGROUP, $expires = 0, $cache_mode = self::CACHE_USE_DEFAULT ) {
		return $this->cacheInstance->save( $key, $value, $group, $expires, $cache_mode);
	}
}
