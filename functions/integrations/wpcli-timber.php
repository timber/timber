<?php

	class Timber_Command extends WP_CLI_Command{

		/**
		 * Clears Twig's Cache
		 *
		 * ## EXAMPLES
		 *
		 *	wp clear_cache
		 *
		 */
		function clear_cache(){
			$cache_path = plugin_dir_path(__FILE__).'../../twig-cache/';

			$loader = new TimberLoader();
			$twig = $loader->get_twig();
			$twig->clearCacheFiles();
			self::rrmdir($cache_path);
			WP_CLI::success('path='.$cache_path);
		}

		private function rrmdir($dir) {
			if (is_dir($dir)) {
				$objects = scandir($dir);
				foreach ($objects as $object) {
					if ($object != "." && $object != "..") {
						WPCLI::line($object);
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
