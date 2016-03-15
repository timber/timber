<?php

$_tests_dir = getenv('WP_TESTS_DIR');
if ( !$_tests_dir ) $_tests_dir = '/tmp/wordpress-tests-lib';


require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	global $timber;

	require dirname( __FILE__ ) . '/../vendor/autoload.php';
	$timber = new Timber();
	require dirname( __FILE__ ) . '/../wp-content/plugins/advanced-custom-fields/acf.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';

require_once __DIR__.'/Timber_UnitTestCase.php';
require_once __DIR__.'/TimberImage_UnitTestCase.php';

if ( !function_exists('is_post_type_viewable') ) {
	function is_post_type_viewable( $post_type_object ) {
 		return $post_type_object->publicly_queryable || ( $post_type_object->_builtin && $post_type_object->public );
 	}
}
