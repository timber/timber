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

	static function clear_cache_timber() {
		$loader = new Loader();
		return $loader->clear_cache_timber();
	}

	static function clear_cache_twig() {
		$loader = new Loader();
		return $loader->clear_cache_twig();
	}

}
