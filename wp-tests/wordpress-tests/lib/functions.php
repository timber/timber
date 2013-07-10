<?php
/**
 * Generate a hash to be used when comparing installed version against
 * codebase and current configuration
 * @return string $hash sha1 hash
 **/
function test_version_check_hash() {
	$hash = '';
	$db_version = get_option( 'db_version' );
	if ( defined('WP_ALLOW_MULTISITE') && WP_ALLOW_MULTISITE ) {
		$version = $db_version;
		if( defined( 'WP_TESTS_BLOGS' ) ) {
			$version .= WP_TESTS_BLOGS;
		}
		if( defined( 'WP_TESTS_SUBDOMAIN_INSTALL' ) ) {
			$version .= WP_TESTS_SUBDOMAIN_INSTALL;
		}
		if( defined( 'WP_TESTS_DOMAIN' ) ) {
			$version .= WP_TESTS_DOMAIN;
		}

	} else {
		$version = $db_version;
	}

	return sha1($version);
}

