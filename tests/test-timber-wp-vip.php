<?php

	class TestTimberWPVIP extends Timber_UnitTestCase {

		function testDisableCache() {
			add_filter('timber/cache/mode', function() {
				return 'none';
			});

			$loader = new Timber\Loader();
			$cache = $loader->set_cache('test', 'foobar');
			$cache = $loader->get_cache('test');
			$this->assertFalse($cache);
		}



	}