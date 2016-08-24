<?php

namespace Timber;

/**
 * This is for integrating external plugins into timber
 * @package  timber
 */
class Integrations {
    
	public static function init() {
		add_action('init', array(__CLASS__, 'maybe_init_integrations'));

		if ( class_exists('WP_CLI_Command') ) {
			\WP_CLI::add_command('timber', 'Timber\Integrations\Timber_WP_CLI_Command');
		}
	}

	public static function maybe_init_integrations() {
		if ( class_exists('ACF') ) {
			new Integrations\ACF();
		}
		if ( class_exists('CoAuthors_Plus') ) {
			new Integrations\CoAuthorsPlus();
		}
	}
}
