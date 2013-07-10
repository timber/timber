<?php

if(isset($GLOBALS['wp_tests_options'])) {
	function wp_tests_options( $value ) {
		$key = substr( current_filter(), strlen( 'pre_option_' ) );
		return $GLOBALS['wp_tests_options'][$key];
	}

	foreach ( array_keys( $GLOBALS['wp_tests_options'] ) as $key ) {
		add_filter( 'pre_option_'.$key, 'wp_tests_options' );
	}
}

if( !defined( 'TEST_WPMU_PLUGIN_DIR' ) ){
	define( 'TEST_WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins' );
}

function tests_wp_get_mu_plugins() {
	$mu_plugins = array();
	if ( !is_dir( TEST_WPMU_PLUGIN_DIR ) )
		return $mu_plugins;
	if ( ! $dh = opendir( TEST_WPMU_PLUGIN_DIR ) )
		return $mu_plugins;
	while ( ( $plugin = readdir( $dh ) ) !== false ) {
		if ( substr( $plugin, -4 ) == '.php' )
			$mu_plugins[] = TEST_WPMU_PLUGIN_DIR . '/' . $plugin;
	}
	closedir( $dh );
	sort( $mu_plugins );

	return $mu_plugins;
}

foreach( tests_wp_get_mu_plugins() as $mu_plugin ){
	include_once( $mu_plugin );
}
unset( $mu_plugin );

