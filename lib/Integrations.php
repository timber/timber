<?php

namespace Timber;

/**
 * This is for integrating external plugins into Timber
 * @package Timber
 */
class Integrations {

  var $acf;
  var $carbon_fields;
  var $coauthors_plus;

  public function __construct() {
    $this->init();
  }

  public function init() {
    add_action('init', array($this, 'maybe_init_integrations'));

    if ( class_exists('WP_CLI_Command') ) {
      \WP_CLI::add_command('timber', 'Timber\Integrations\Timber_WP_CLI_Command');
    }
  }

  public function maybe_init_integrations() {
    if ( class_exists('ACF') ) {
      $this->acf = new Integrations\ACF();
    }
    if ( class_exists('Carbon_Fields\Carbon_Fields') ) {
      $this->carbon_fields = new Integrations\CarbonFields();
    }
    if ( class_exists('CoAuthors_Plus') ) {
      $this->coauthors_plus = new Integrations\CoAuthorsPlus();
    }
    $this->wpml = new Integrations\WPML();
  }
}
