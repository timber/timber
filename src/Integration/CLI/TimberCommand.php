<?php

namespace Timber\Integration\CLI;

use Timber\Cache\Cleaner;
use WP_CLI;
use WP_CLI_Command;

if (!\class_exists('WP_CLI_Command')) {
    return;
}

/**
 * Class TimberCommand
 *
 * Handles WP-CLI commands.
 */
class TimberCommand extends WP_CLI_Command
{
    /**
     * Clears caches in Timber.
     *
     * ## OPTIONS
     *
     * [<mode>]
     * : Optional. The type of cache to clear. Accepts 'timber' or 'twig'. If not provided, the command will clear all caches.
     *
     * ## EXAMPLES
     *
     *    # Clear all caches.
     *    wp timber clear-cache
     *
     *    # Clear Timber caches.
     *    wp timber clear-cache timber
     *
     *    # Clear Twig caches.
     *    wp timber clear-cache twig
     *
     * @subcommand clear-cache
     * @alias clear_cache
     */
    public function clear_cache($args = []): void
    {
        $mode = $args[0] ?? 'all';
        $mode_string = 'all' !== $mode ? \ucfirst((string) $mode) : $mode;

        WP_CLI::log("Clearing {$mode_string} caches …");

        if (Cleaner::clear_cache($mode)) {
            WP_CLI::success("Cleared {$mode_string} caches.");
        } else {
            WP_CLI::warning("Failed to clear {$mode_string} cached contents.");
        }
    }
}
