<?php
/*
Plugin Name: Timber
Description: The WordPress Timber Library allows you to write themes using the power Twig templates.
Plugin URI: http://timber.upstatement.com
Author: Jared Novack + Upstatement
Version: 1.1.8
Author URI: http://upstatement.com/
*/
// we look for Composer files first in the plugins dir.
// then in the wp-content dir (site install).
// and finally in the current themes directories.
if ( file_exists( $composer_autoload = __DIR__ . '/vendor/autoload.php' ) /* check in self */
	|| file_exists( $composer_autoload = WP_CONTENT_DIR.'/vendor/autoload.php') /* check in wp-content */
	|| file_exists( $composer_autoload = plugin_dir_path( __FILE__ ).'vendor/autoload.php') /* check in plugin directory */
	|| file_exists( $composer_autoload = get_stylesheet_directory().'/vendor/autoload.php') /* check in child theme */
	|| file_exists( $composer_autoload = get_template_directory().'/vendor/autoload.php') /* check in parent theme */
) {
	require_once $composer_autoload;
}
new \Timber\Timber;
