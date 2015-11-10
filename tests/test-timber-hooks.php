<?php

	class TestTimberHooks extends Timber_UnitTestCase {

		function testTimberContext() {
			add_filter('timber/context', function($context) {
				$context['person'] = "Nathan Hass";
				return $context;
			});
			$context = Timber::get_context();
			$this->assertEquals('Nathan Hass', $context['person']);
		}
	}
