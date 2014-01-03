<?php

class TimberTestWidgets extends WP_UnitTestCase {

	function testHTML() {
		// replace this with some actual testing code
		$widgets = wp_get_sidebars_widgets();
		$data = array();
		$content = Timber::get_widgets('sidebar-1');
		$content = trim($content);
		$this->assertEquals('<', substr($content, 0, 1));
	}

}