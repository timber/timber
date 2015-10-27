<?php
if (!class_exists('WP_CLI_Command')) {
	return;
}

class Timber_WP_CLI_Command extends WP_CLI_Command {

    /**
     * Clears Timber and Twig's Cache
     *
     * ## EXAMPLES
     *
     *    wp timber clear_cache
     *
     */
    public function clear_cache($mode = 'all') {
        TimberCommand::clear_cache($mode);
    }

    /**
     * Clears Twig's Cache
     *
     * ## EXAMPLES
     *
     *    wp timber clear_cache_twig
     *
     */
    function clear_cache_twig(){
        $clear = TimberCommand::clear_cache_twig();
        if ($clear){
            WP_CLI::success('Cleared contents of twig cache');
        } else {
            WP_CLI::warning('Failed to clear twig cache');
        }
    }

    /**
     * Clears Timber's Cache
     *
     * ## EXAMPLES
     *
     *    wp timber clear_cache_timber
     *
     */
    function clear_cache_timber() {
        $clear = TimberCommand::clear_cache_timber();
        $message = 'Failed to clear timber cache';
        if ($clear){
            $message = "Cleared contents of Timber's Cache";
            WP_CLI::success($message);
        } else {
            WP_CLI::warning($message);
        }
        return $message;
    }

}
