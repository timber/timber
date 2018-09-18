<?php


class TestTimberDebug extends Timber_UnitTestCase {

	function testCallingPHPFile() {
		$phpunit = $this;
		add_filter('timber/calling_php_file', function($file) use ($phpunit) {
			$phpunit->assertStringEndsWith('/tests/test-timber-debug.php', $file);
		});
		Timber::compile('assets/output.twig');

	}
}