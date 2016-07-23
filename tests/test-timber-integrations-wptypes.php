<?php

use Timber\Integrations\WPTypes;
use Timber\Integrations\Command;

class TestTimberIntegrationsWPTypes extends Timber_UnitTestCase {

	function testPostField() {
		$pid = $this->factory->post->create();
		update_post_meta( $pid, 'wpcf-subhead', 'foobar' );
		$str = '{{post.get_field("subhead")}}';
		$post = new TimberPost( $pid );
		$str = Timber::compile_string( $str, array( 'post' => $post ) );
		$this->assertEquals( 'foobar', $str );
	}

	// function testTermField() {

	// }

	// function testUserField() {

	// }
}
