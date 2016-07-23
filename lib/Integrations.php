<?php

namespace Timber;

use Timber\Integrations\ACF;
use Timber\Integrations\Types;

/**
 * This is for integrating external plugins into timber
 * @package  timber
 */
class Integrations {
    
	public static function init() {

		add_action( 'init', array( __CLASS__, 'maybe_init_acf' ) );

		add_action( 'init', array( __CLASS__, 'maybe_init_types' ) );

		if ( class_exists( 'WP_CLI_Command' ) ) {
			\WP_CLI::add_command( 'timber', 'Timber\Integrations\Timber_WP_CLI_Command' );
		}
	}

	public static function maybe_init_acf() {
		if ( class_exists( 'ACF' ) ) {
			new ACF();
		}
	}

	public static function maybe_init_types() {
		if ( defined( 'WPCF_META_PREFIX' ) ) {
			new Types();
		}
	}
}
