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

    function testVersionMagnitude() {
        $mag = Timber\Admin::get_upgrade_magnitude('1.1.2', '2.0');
        $this->assertEquals('milestone', $mag);
    }

    function testVersionMagnitudeMajor() {
        $mag = Timber\Admin::get_upgrade_magnitude('1.1.2', '1.2.0');
        $this->assertEquals('major', $mag);
        $mag = Timber\Admin::get_upgrade_magnitude('1.1.2', '1.2');
        $this->assertEquals('major', $mag);
    }

    function testVersionMagnitudeMinor() {
        $mag = Timber\Admin::get_upgrade_magnitude('1.1.2', '1.1.4');
        $this->assertEquals('minor', $mag);
    }

    function testAdminInit() {
    	$admin = Admin::init();
    	$this->assertTrue($admin);
    }

    function testUpgradeMessage() {
        
    }

}
