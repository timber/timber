<?php

class TestTimberWidgets extends Timber_UnitTestCase {

	function testHTML() {
		// replace this with some actual testing code
		$widgets = wp_get_sidebars_widgets();
		$data = array();
		$content = Timber::get_widgets('sidebar-1');
		$content = trim($content);
		$this->assertEquals('<', substr($content, 0, 1));
	}

	function testManySidebars() {
		$widgets = wp_get_sidebars_widgets();
		$sidebar1 = Timber::get_widgets('sidebar-1');
		$sidebar2 = Timber::get_widgets('sidebar-2');
		$this->assertGreaterThan(0, strlen($sidebar1));
	}

}
