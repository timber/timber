<?php

namespace Timber\Cache;

use Asm89\Twig\CacheExtension\CacheStrategy\KeyGeneratorInterface;

class KeyGenerator implements KeyGeneratorInterface {

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function generateKey( $value ) {
		if ( is_a($value, 'Timber\Cache\TimberKeyGeneratorInterface') ) {
			return $value->_get_cache_key();
		}

		if ( is_array($value) && isset($value['_cache_key']) ) {
			return $value['_cache_key'];
		}

		$key = md5(json_encode($value));
		if ( is_object($value) ) {
			$key = get_class($value).'|'.$key;
		}

		return $key;
	}

}
