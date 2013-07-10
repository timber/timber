<?php
/**
 * Installs additional MU sites for the purpose of the unit-tests
 *
 * @todo Reuse the init/load code in init.php
 */
error_reporting( E_ALL & ~E_DEPRECATED & ~E_STRICT );

$config_file_path = $argv[1];

require_once $config_file_path;

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['HTTP_HOST'] = WP_TESTS_DOMAIN;
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

require_once ABSPATH . '/wp-settings.php';

require_once ABSPATH . '/wp-admin/includes/upgrade.php';
require_once ABSPATH . '/wp-includes/wp-db.php';

echo "Installing sites…" . PHP_EOL;
wp_install( WP_TESTS_TITLE, 'admin', WP_TESTS_EMAIL, true, '', 'a' );

if ( defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE ) {
	$blogs = explode(',', WP_TESTS_BLOGS);
	foreach ( $blogs as $blog ) {
		if ( WP_TESTS_SUBDOMAIN_INSTALL ) {
			$newdomain = $blog.'.'.preg_replace( '|^www\.|', '', WP_TESTS_DOMAIN );
			$path = $base;
		} else {
			$newdomain = WP_TESTS_DOMAIN;
			$path = $base.$blog.'/';
		}
		wpmu_create_blog( $newdomain, $path, $blog, email_exists(WP_TESTS_EMAIL) , array( 'public' => 1 ), 1 );

	}
}