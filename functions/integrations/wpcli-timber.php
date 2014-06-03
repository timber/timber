<?php
if (class_exists('WP_CLI_Command')) {
    class Timber_Command extends WP_CLI_Command
    {

        /**
         * Clears Timber and Twig's Cache
         *
         * ## EXAMPLES
         *
         *    wp timber clear_cache
         *
         */

        public function clear_cache($mode = 'all') {
        	if (is_array($mode)){
            	$mode = reset($mode);
        	}
            if ($mode == 'all') {
                self::clear_cache_twig();
                self::clear_cache_timber();
            } else if ($mode == 'twig') {
            	self::clear_cache_twig();
            } else if ($mode == 'timber') {
            	self::clear_cache_timber();
            }
        }

        function clear_cache_twig(){
        	$loader = new TimberLoader();
        	$clear = $loader->clear_cache_twig();
        	if ($clear){
        		WP_CLI::success('Cleared contents of twig cache');
        	} else {
        		WP_CLI::failure('Failed to clear cache');
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
            WP_CLI::success("Cleared contents of Timber's Cache");
        }

        /**
         * Clears Twig's Cache
         *
         * ## EXAMPLES
         *
         *    wp timber clear_cache_twig
         *
         */
        

    }

    WP_CLI::add_command('timber', 'Timber_Command');
}
