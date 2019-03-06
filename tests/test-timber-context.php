<?php

class TestTimberContext extends Timber_UnitTestCase {

	/**
	 * This throws an infite loop if memorization isn't working
	 */
	function testContextLoop() {
		add_filter('timber_context', function($context) {
			$context = Timber::get_context();
			$context['zebra'] = 'silly horse';
			return $context;
		});
		$context = Timber::get_context();
		$this->assertEquals('http://example.org', $context['http_host']);
	}

	function testContext() {
		$context = Timber::context();
		$this->assertEquals('http://example.org', $context['http_host']);
	}


}
