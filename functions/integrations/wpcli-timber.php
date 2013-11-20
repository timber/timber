<?php

	class Timber_Command extends WP_CLI_Command{

		/**
		 * Clears Twig's Cache
		 *
		 * ## EXAMPLES
		 *
		 *	wp clear_cache
		 *
		 * @synopsis <nothing>
		 */
		function clear_cache(){
			$files = glob('../../twig-cache/*');
			foreach($files as $file){
  				if (is_file($file)) {
					unlink($file);
				}
			}
		}

		/**
		 * ## EXAMPLES
		 *		wp timber poop
		 */

		function poop(){
			WP_CLI::success('HEllo!!! poopy pants');
		}

	}

	WP_CLI::add_command( 'timber', 'Timber_Command' );