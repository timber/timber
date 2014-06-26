<?php

class TestTimberIntegrations extends WP_UnitTestCase {

	function testACFGetFieldPost() {
		$pid = $this->factory->post->create();
		update_field( 'subhead', 'foobar', $pid );
		$str = '{{post.get_field("subhead")}}';
		$post = new TimberPost( $pid );
		$str = Timber::compile_string( $str, array( 'post' => $post ) );
		$this->assertEquals( 'foobar', $str );
	}

	function testACFGetFieldTermCategory() {
		update_field( 'color', 'blue', 'category_1' );
		$cat = new TimberTerm( 1 );
		$this->assertEquals( 'blue', $cat->color );
		$str = '{{term.color}}';
		$this->assertEquals( 'blue', Timber::compile_string( $str, array( 'term' => $cat ) ) );
	}

	function testACFGetFieldTermTag() {
		$tid = $this->factory->term->create();
		update_field( 'color', 'green', 'post_tag_'.$tid );
		$term = new TimberTerm( $tid );
		$str = '{{term.color}}';
		$this->assertEquals( 'green', Timber::compile_string( $str, array( 'term' => $term ) ) );
	}

	function testACFInit() {
		$acf = new ACFTimber();
		$this->assertInstanceOf( 'ACFTimber', $acf );
	}

	function testWPCLI(){
		$str = Timber::compile_string('whatever {{rand}}', array('rand' => 4004), 600);
		$wp_path = '/tmp/wordpress';
		if (file_exists('/srv/www/wordpress-develop/src')){
			$wp_path = '/srv/www/wordpress-develop/src';
		}
		if (class_exists('WP_CLI_Command')){
			error_log('class exists');
		} else {
			error_log('NOT THERE');
		}
		exec('wp timber clear_cache_timber --path='.$wp_path);
		$success = Timber_Command::clear_cache_timber();
		require_once '../functions/integrations/wpcli-timber';
		
		$this->assertEquals("Success: Cleared contents of Timber's Cache", $success);
	}

}
