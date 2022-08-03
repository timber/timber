<?php

use WP_CLI\Loggers\Regular;

/**
 * Class WpCliLogger
 *
 * Custom WP CLI Logger for using in tests.
 */
class WpCliLogger extends Regular
{
    public function write($handle, $str)
    {
        echo trim($str);
    }
}
