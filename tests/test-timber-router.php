<?php

class TimberTestRouter extends WP_UnitTestCase {

	function testThemeRoute(){
		$template = Timber::load_template('single.php');
		$this->assertTrue($template);
	}

	function testThemeRouteDoesntExist(){
		$template = Timber::load_template('singlefoo.php');
		$this->assertFalse($template);
	}

	function testFullPathRoute(){
		$hello = ABSPATH.'wp-content/plugins/hello.php';
		$template = Timber::load_template($hello);
		$this->assertTrue($template);
	}

	function testFullPathRouteDoesntExist(){
		$hello = ABSPATH.'wp-content/plugins/hello-foo.php';
		$template = Timber::load_template($hello);
		$this->assertFalse($template);
	}

	function testRouterClass(){
		$this->assertFalse(class_exists('PHPRouter\Router'), 'PHP Router shout not be loaded yet');
		Timber::add_route('foo', 'print');
		$this->assertTrue(class_exists('PHPRouter\Router'));
	}
}
