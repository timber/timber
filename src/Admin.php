<?php

namespace Timber;

/**
 * Class Admin
 */
class Admin
{
    public static function init(?string $wp_version = null): void
    {
        // Resolve the version ourselves when none was passed. WordPress fires `admin_init`
        // with no args, which reaches this callback as an empty string (not null), so we
        // can't rely on the parameter default alone.
        // wp_get_wp_version() exists since WP 6.7; fall back to the global for older versions
        // (which is exactly the range this notice targets).
        if ($wp_version === null || $wp_version === '') {
            $wp_version = \function_exists('wp_get_wp_version') ? \wp_get_wp_version() : (string) ($GLOBALS['wp_version'] ?? '');
        }

        // Bail if the running WordPress version meets Timber's tested minimum.
        if (\version_compare(Timber::MINIMUM_WP_VERSION, $wp_version) !== 1) {
            return;
        }

        \add_action('admin_notices', static function () use ($wp_version): void {
            \printf(
                '<div class="error"><p>Your installed version of <a href="https://github.com/timber/timber" target="_blank" rel="noopener noreferrer">Timber</a> is only tested against <strong>WordPress %1$s</strong> or greater, but you are running <strong>WordPress %2$s</strong>. Please <a href="%3$s">update WordPress</a> to make sure Timber runs fine.</p></div>',
                Timber::MINIMUM_WP_VERSION,
                $wp_version,
                \admin_url('update-core.php'),
            );
        }, 1);
    }
}
