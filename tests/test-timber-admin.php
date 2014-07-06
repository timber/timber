<?php

class TestTimberAdmin extends WP_UnitTestCase {

	function testSettingsLinks() {
		global $timber_admin;
		$links = $timber_admin->settings_link( array(), '/timber.php' );
		$this->assertContains( 'Documentation', $links['settings'] );

		$links = $timber_admin->settings_link( array(), '/foo.php' );
		if ( isset( $links['settings'] ) ) {
			$this->assertNotContains( 'Documentation', $links['settings'] );
		}
	}

	function testAdminConstruct() {
		global $timber_admin;
		$this->assertInstanceOf( 'TimberAdmin', $timber_admin );
	}

}
