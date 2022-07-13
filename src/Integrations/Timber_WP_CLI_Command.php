<?php

namespace Timber\Integrations;

use Timber\Integrations\Command;

if ( !class_exists('WP_CLI_Command') ) {
	return;
}

class Timber_WP_CLI_Command extends \WP_CLI_Command {

	/**
	 * Clears Timber and Twig's Cache
	 *
	 * ## EXAMPLES
	 *
	 *    wp timber clear_cache
	 *
	 */
	public function clear_cache( $mode = 'all' ) {
		$mode = $mode ? : 'all';
		$cleared = Command::clear_cache( $mode );
		if ( $cleared ) {
			\WP_CLI::success("Cleared {$mode} cached contents");
		} else {
			\WP_CLI::warning("Failed to clear {$mode} cached contents");
		}
	}

	/**
	 * Clears Twig's Cache
	 *
	 * ## EXAMPLES
	 *
	 *    wp timber clear_cache_twig
	 *
	 */
	public function clear_cache_twig() {
		$this->clear_cache( 'twig' );
	}

	/**
	 * Clears Timber's Cache
	 *
	 * ## EXAMPLES
	 *
	 *    wp timber clear_cache_timber
	 *
	 */
	public function clear_cache_timber() {
		$this->clear_cache( 'timber' );
	}
}
