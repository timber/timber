<?php
class TimberCache_Loader
{

    public static function register($prepend = false)
    {
        if (version_compare(phpversion(), '5.3.0', '>=')) {
            spl_autoload_register(array(new self, 'autoload'), true, $prepend);
        } else {
            spl_autoload_register(array(new self, 'autoload'));
        }
    }

    public static function autoload($class)
    {
        if (0 === strpos($class, 'Timber\Cache') || 0 === strpos($class, 'Asm89\Twig\CacheExtension')) {
            $classes = explode( '\\', $class );
            array_splice( $classes, 0, 2 );
            $path = implode( $classes, '/' );
            if ( is_file($file = dirname(__FILE__) . '/' . $path . '.php'))
                require $file;
        }
    }
}
