<?php

namespace Timber\Integrations;

use Timber\Loader;

/**
 * These are methods that can be executed by WPCLI, other CLI mechanism or other external controllers
 * @package  timber
 */
class Command {

	public static function clear_cache( $mode = 'all' ) {
		if ( is_array($mode) ) {
			$mode = reset($mode);
		}
		if ( $mode == 'all' ) {
			$twig_cache = self::clear_cache_twig();
			$timber_cache = self::clear_cache_timber();
			if ( $twig_cache && $timber_cache ) {
				return true;
			}
		} else if ( $mode == 'twig' ) {
			return self::clear_cache_twig();
		} else if ( $mode == 'timber' ) {
			return self::clear_cache_timber();
		}
	}

	public static function clear_cache_timber() {
		$cache = new Cache();
		return $cache->clearCacheTimber();
	}

	public static function clear_cache_twig() {
		$twig = new Loader();
		return $twig->clear_cache_twig();
	}

}
