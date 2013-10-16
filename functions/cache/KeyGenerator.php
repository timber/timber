<?php

namespace Timber\Cache;

use Asm89\Twig\CacheExtension\CacheStrategy\KeyGeneratorInterface;

class KeyGenerator implements KeyGeneratorInterface
{

    public function generateKey( $value ) {
        $key = md5( json_encode( $value ) );
        if ( is_object( $value ) )
            $key = get_class( $key ) . '|' . $key;

        return $key;
    }

}
