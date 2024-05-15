<?php

namespace Timber;

/**
 * Class Admin
 */
class Admin
{
    public static function init()
    {
        global $wp_version;

        $minimum_version = '6.0.0';

        // Show notice if user is running something older than the required
        // WordPress version.
        if (\version_compare($minimum_version, $wp_version) === 1) {
            $upgrade_url = \admin_url('update-core.php');

            self::show_notice("<a href='https://github.com/timber/timber'>Timber 2.0</a> requires <strong>WordPress $minimum_version</strong> or greater, but you are running <strong>WordPress $wp_version</strong>. Please <a href='$upgrade_url'>update WordPress</a> in order to use Timber 2.0.");
        }
    }

    /**
     * Display a message in the admin.
     *
     * @date    01/07/2020
     *
     * @param string  $text to display
     * @param string  $class of the notice 'error' (red) or 'warning' (yellow)
     */
    protected static function show_notice($text, $class = 'error')
    {
        \add_action('admin_notices', function () use ($text, $class) {
            echo '<div class="' . $class . '"><p>' . $text . '</p></div>';
        }, 1);
    }
}
