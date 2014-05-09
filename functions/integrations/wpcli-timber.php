<?php

	class Timber_Command extends WP_CLI_Command{

		/**
		 * Clears Timber and Twig's Cache
		 *
		 * ## EXAMPLES
		 *
		 *	wp timber clear_cache
		 *
		 */

		function clear_cache($mode = 'all'){
			WP_CLI::line('#mode = '.print_r($mode, true));
			if ($mode == 'all'){
				self::clear_cache_twig();
				self::clear_cache_timber();
			}
			if ($mode == 'twig'){
			}
			if ($mode == 'timber'){

			}
		}

		/**
		 * Clears Timber's Cache
		 *
		 * ## EXAMPLES
		 *
		 *	wp timber clear_cache_timber
		 *
		 */

		function clear_cache_timber(){
			WP_CLI::success('Cleared contents of Timbers Cache');
		}

		/**
		 * Clears Twig's Cache
		 *
		 * ## EXAMPLES
		 *
		 *	wp timber clear_cache_twig
		 *
		 */

		function clear_cache_twig(){
			$loader = new TimberLoader();
			$twig = $loader->get_twig();
			$twig->clearCacheFiles();
			self::rrmdir($twig->getCache());
			WP_CLI::success('Cleared contents of '.$twig->getCache());
		}

		private function rrmdir($dir) {
			if (is_dir($dir)) {
				$objects = scandir($dir);
				foreach ($objects as $object) {
					if ($object != "." && $object != "..") {
						if (filetype($dir."/".$object) == "dir"){
							self::rrmdir($dir."/".$object);
							rmdir($dir."/".$object);
						} else {
							unlink($dir."/".$object);
						}
    				}
    			}
    			reset($objects);
  			}
 		}

	}

	WP_CLI::add_command( 'timber', 'Timber_Command' );
