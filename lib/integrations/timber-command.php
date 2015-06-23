<?php

/**
 * These are methods that can be executed by WPCLI, other CLI mechanism or other external controllers
 * @package  timber
 */
class TimberCommand
{
    public static function clear_cache($mode = 'all')
    {
        if (is_array($mode)) {
            $mode = reset($mode);
        }
        if ($mode == 'all') {
            $twig_cache = self::clear_cache_twig();
            $timber_cache = self::clear_cache_timber();
            if ($twig_cache && $timber_cache) {
                return true;
            }
        } elseif ($mode == 'twig') {
            return self::clear_cache_twig();
        } elseif ($mode == 'timber') {
            return self::clear_cache_timber();
        }
    }

    public static function clear_cache_timber()
    {
        $loader = new TimberLoader();
        return $loader->clear_cache_timber();
    }

    public static function clear_cache_twig()
    {
        $loader = new TimberLoader();
        return $loader->clear_cache_twig();
    }
}
