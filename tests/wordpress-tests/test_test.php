<?php

class WP_Is_Email_Test extends WP_UnitTestCase {

	function test_is_email_only_letters_with_dot_com_domain() {
		$this->assertEquals( 'nb@nikolay.com', is_email( 'nb@nikolay.com' ) );
	}
	
	function test_is_email_should_not_allow_missing_tld() {
		$this->assertFalse( is_email( 'nb@nikolay' ) );
	}
	
	function test_is_email_should_allow_bg_domain() {
		$this->assertEquals( 'nb@nikolay.bg', is_email( 'nb@nikolay.bg' ) );
	}

	function test_is_email_should_not_allow_blah_domain() {
		$this->assertFalse( is_email( 'nb@nikolay.blah' ) );
	}
}
