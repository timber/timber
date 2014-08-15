<?php

class TestTimberPostGetter extends WP_UnitTestCase {

	function testGetAttachment() {
		$upload_dir = wp_upload_dir();
		$post_id = $this->factory->post->create();
		$filename = TimberImageTest::copyTestImage( 'flag.png' );
		$destination_url = str_replace( ABSPATH, 'http://'.$_SERVER['HTTP_HOST'].'/', $filename );
		$wp_filetype = wp_check_filetype( basename( $filename ), null );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content' => '',
			'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
		add_post_meta( $post_id, '_thumbnail_id', $attach_id, true );
		$data = array();
		$data['post'] = new TimberPost( $post_id );
		$data['size'] = array( 'width' => 100, 'height' => 50 );
		$data['crop'] = 'default';
		Timber::compile( 'assets/thumb-test.twig', $data );
		$exists = file_exists( $filename );
		$this->assertTrue( $exists );
		$resized_path = $upload_dir['path'].'/flag-'.$data['size']['width'].'x'.$data['size']['height'].'-c-'.$data['crop'].'.png';
		$exists = file_exists( $resized_path );
		$this->assertTrue( $exists );
		$attachments = Timber::get_posts('post_type=attachment&post_status=inherit');
		$this->assertGreaterThan(0, count($attachments));
	}
	
	function testQueryPost() {
		$posts = $this->factory->post->create_many( 6 );
		$post = Timber::get_post( $posts[3] );
		$this->go_to( home_url( '/?p='.$posts[2] ) );
		$this->assertNotEquals( get_the_ID(), $post->ID );
		$post = Timber::query_post( $posts[3] );
		$this->assertEquals( get_the_ID(), $post->ID );
	}

	function testBlankQueryPost() {
		$pid = $this->factory->post->create( );
		$this->go_to( home_url( '/?p='.$pid ) );
		$post = Timber::query_post();
		$this->assertEquals( $pid, $post->ID );
	}

	function testGetPostsInLoop() {
		$posts = $this->factory->post->create_many( 55 );
		$this->go_to( '/' );
		$start = microtime( true );
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				$posts = Timber::get_posts();
			}
		}
		$end = microtime( true );
		$diff = $end - $start;
		//if this takes more than 10 seconds, we're in trouble
		$this->assertLessThan( 10, $diff );
	}

	function testGetPostsFromLoop() {
		require_once 'php/timber-post-subclass.php';
		$posts = $this->factory->post->create_many( 15 );
		$this->go_to( '/' );
		$posts = Timber::get_posts();
		$this->assertEquals( 10, count( $posts ) );
		$posts = Timber::get_posts_from_loop( 'TimberPostSubclass' );
		$this->assertEquals( 10, count( $posts ) );
		$this->assertEquals( 'TimberPostSubclass', get_class( $posts[0] ) );
	}

	function testGetPostsFromArray() {
		$pids = $this->factory->post->create_many( 15 );
		$posts = Timber::get_posts( $pids );
		$this->assertEquals( 15, count( $posts ) );
		$this->assertEquals( $pids[3], $posts[3]->ID );
	}

	function testGetPostsFromSlug() {
		$post = $this->factory->post->create( array( 'post_name' => 'silly-post' ) );
		$posts = Timber::get_posts_from_slug( 'silly-post' );
		$this->assertEquals( 1, count( $posts ) );
		$this->assertEquals( 'silly-post', $posts[0]->slug );
	}

}
