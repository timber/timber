<?php
/*
Plugin Name: TimberFramework
Description: The WordPress Timber Framework allows you to write themes using the power of MVT and Twig
Author: Jared Novack + Upstatement
Version: 0.2
Author URI: http://timber.upstatement.com/
*/

global $wp_version;
global $plugin_timber;
$exit_msg = 'Timber reqiures WordPress 3.0 or newer';
if (version_compare($wp_version, '3.0', '<')){
	exit ($exit_msg);
}

require_once(__DIR__.'/functions/functions-twig.php');
require_once(__DIR__.'/functions/functions-post-master.php');
require_once(__DIR__.'/functions/functions-php-helper.php');
require_once(__DIR__.'/functions/functions-wp-helper.php');

require_once(__DIR__.'/objects/timber.php');
require_once(__DIR__.'/objects/timber-core.php');
require_once(__DIR__.'/objects/timber-post.php');
require_once(__DIR__.'/objects/timber-comment.php');
require_once(__DIR__.'/objects/timber-user.php');
require_once(__DIR__.'/objects/timber-term.php');
require_once(__DIR__.'/objects/timber-image.php');

$timber = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', realpath(__DIR__));
define("TIMBER", $timber);
define("TIMBER_URL", 'http://'.$_SERVER["HTTP_HOST"].TIMBER);
define("TIMBER_LOC", realpath(__DIR__));




//$plugin_timber = new Timber();