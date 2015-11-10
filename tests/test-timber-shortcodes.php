<?php

	class TestTimberShortcodes extends Timber_UnitTestCase {

		function testShortcodes(){
			add_shortcode('timber_shortcode', function($text){
				return 'timber '.$text[0];
			});
			$return = Timber::compile('assets/test-shortcodes.twig');
			$this->assertEquals('hello timber foo', trim($return));
		}
	}
