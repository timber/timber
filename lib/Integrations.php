<?php

namespace Timber;

use Timber\ACF;

/**
 * This is for integrating external plugins into timber
 * @package  timber
 */
class Integrations {
    public static function init() {

        add_action( 'init', array( __CLASS__, 'maybe_init_acftimber' ) );

        if ( class_exists( 'WP_CLI_Command' ) ) {
            \WP_CLI::add_command( 'timber', 'Timber_WP_CLI_Command' );
        }
    }

    public static function maybe_init_acftimber() {

        if ( class_exists( 'ACF' ) ) {
            new Timber\ACF();
        }

    }
}
