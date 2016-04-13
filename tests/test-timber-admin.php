<?php

use Timber\Admin;

class TestTimberAdmin extends Timber_UnitTestCase {

	function testSettingsLinks() {
        $links = apply_filters( 'plugin_row_meta', array(), 'timber/timber.php' );

        $links = implode( ' ', $links );
        $this->assertContains( 'Documentation', $links );

        $links = apply_filters( 'plugin_row_meta', array(), 'foo/bar.php' );
        if ( isset( $links ) ) {
            $this->assertNotContains( 'Documentation', $links );
        }
    }

    function testAdminInit() {
    	$admin = Admin::init();
    	$this->assertTrue($admin);
    }

}
