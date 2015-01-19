<?php

class TestTimberPostGetter extends WP_UnitTestCase {

	function testGettingArrayWithSticky(){
		$pids = $this->factory->post->create_many(6);
		$sticky_id = $this->factory->post->create();
		$sticky = array($sticky_id, $pids[0]);
		update_option('sticky_posts', $sticky);
		$posts = Timber::get_posts($pids);
		$post_ids_gotten = array();
		foreach($posts as $post) {
			$post_ids_gotten[] = $post->ID;
		}
		$this->assertNotContains($sticky_id, $post_ids_gotten);
		$this->assertContains($pids[0], $post_ids_gotten);
	}

	function testGettingEmptyArray(){
		$pids = $this->factory->post->create_many( 15 );
		$posts = Timber::get_posts(array());
		$this->assertEquals(0, count($posts));
	}

	function testGettingWithFalse(){
		$pids = $this->factory->post->create_many( 15 );
		$posts = Timber::get_posts(false);
		$this->assertEquals(0, count($posts));
	}

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

	function testNumberPosts() {
		$pids = $this->factory->post->create_many( 15 );
		$query = 'post_type=post&numberposts=7';
		$posts = Timber::get_posts($query);
		$this->assertEquals(7, count($posts));

	}

	function testNumberPostsBig() {
		$pids = $this->factory->post->create_many( 15 );
		$query = 'post_type=post&numberposts=15';
		$posts = Timber::get_posts($query);
		$this->assertEquals(15, count($posts));

	}

	function testNumberPostsAll() {
		$pids = $this->factory->post->create_many( 17 );
		$query = 'post_type=post&numberposts=-1';
		$posts = Timber::get_posts($query);
		$this->assertEquals(17, count($posts));

	}

	function testPostsPerPage() {
		$pids = $this->factory->post->create_many( 15 );
		$query = 'post_type=post&posts_per_page=7';
		$posts = Timber::get_posts($query);
		$this->assertEquals(7, count($posts));
	}

	function testPostsPerPageAll() {
		$pids = $this->factory->post->create_many( 23 );
		$query = 'post_type=post&posts_per_page=-1';
		$posts = Timber::get_posts($query);
		$this->assertEquals(23, count($posts));
	}

	function testPostsPerPageBig() {
		$pids = $this->factory->post->create_many( 15 );
		$query = 'post_type=post&posts_per_page=15';
		$posts = Timber::get_posts($query);
		$this->assertEquals(15, count($posts));
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
