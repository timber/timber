<?php

//function _manually_load_plugin() {
require_once(__DIR__.'/../lib/Core.php');
require_once(__DIR__.'/../lib/CoreInterface.php');
require_once(__DIR__.'/../lib/Post.php');
foreach (glob(__DIR__."/../lib/*.php") as $filename)
{
    require_once $filename;
}
	global $timber;

	require dirname( __FILE__ ) . '/../vendor/autoload.php';
	//require_once(__DIR__.'/../init.php');
	$timber = new \Timber\Timber();

	//require dirname( __FILE__ ) . '/../wp-content/plugins/advanced-custom-fields/acf.php';
//}

//add_filter( 'muplugins_loaded', '_manually_load_plugin' );

//require $_tests_dir . '/includes/bootstrap.php';

//require_once __DIR__.'/Timber_UnitTestCase.php';
//require_once __DIR__.'/TimberImage_UnitTestCase.php';

if ( !function_exists('is_post_type_viewable') ) {
	function is_post_type_viewable( $post_type_object ) {
 		return $post_type_object->publicly_queryable || ( $post_type_object->_builtin && $post_type_object->public );
 	}
}
