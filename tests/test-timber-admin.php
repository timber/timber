<?php

class TestTimberAdmin extends WP_UnitTestCase {

	function testSettingsLinks() {
		global $timber_admin;
		$links = $timber_admin->meta_links( array(), 'timber/timber.php' );
		$links = implode(' ', $links);
		$this->assertContains( 'Documentation', $links);

		$links = $timber_admin->meta_links( array(), '/foo.php' );
		if ( isset( $links ) ) {
			$this->assertNotContains( 'Documentation', $links);
		}
	}

	function testAdminConstruct() {
		global $timber_admin;
		$this->assertInstanceOf( 'TimberAdmin', $timber_admin );
	}

}
