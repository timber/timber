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
		$hello = WP_CONTENT_DIR.'/plugins/hello.php';
		$template = Timber::load_template($hello);
		$this->assertTrue($template);
	}

	function testFullPathRouteDoesntExist(){
		$hello = WP_CONTENT_DIR.'/plugins/hello-foo.php';
		$template = Timber::load_template($hello);
		$this->assertFalse($template);
	}

	function testRouterClass(){
		$this->assertTrue(class_exists('PHPRouter\Router'));
	}
}
