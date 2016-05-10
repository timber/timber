<?php

class TestTimberContext extends Timber_UnitTestCase {

	function testContextLoop() {
		add_filter('timber_context', function($context) {
			$context = Timber::get_context();
			$context['zebra'] = 'silly horse';
			return $context;
		});
		$context = Timber::get_context();

	}


}
