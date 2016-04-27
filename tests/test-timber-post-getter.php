<?php

class TestTimberPostGetter extends Timber_UnitTestCase {

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

	function testGetPostsWithClassMap() {
		register_post_type('portfolio', array('public' => true));
		register_post_type('alert', array('public' => true));
		$this->factory->post->create(array('post_type' => 'portfolio', 'post_title' => 'A portfolio item', 'post_date' => '2015-04-23 15:13:52'));
		$this->factory->post->create(array('post_type' => 'alert', 'post_title' => 'An alert', 'post_date' => '2015-06-23 15:13:52'));
		$posts = Timber::get_posts('post_type=any', array('portfolio' => 'TimberPortfolio', 'alert' => 'TimberAlert'));
		$this->assertEquals( 'TimberAlert', get_class($posts[0]) );
		$this->assertEquals( 'TimberPortfolio', get_class($posts[1]) );
	}

	function test587() {
		register_post_type('product');
		$pids = $this->factory->post->create_many(6, array('post_type' => 'product'));
		$args = array(
        	'post_type' => 'project'
    	);
		$context['projects'] = Timber::get_posts($args);
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
		$filename = TestTimberImage::copyTestImage( 'flag.png' );
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
	}

	function testGetPostsFromArray() {
		$pids = $this->factory->post->create_many( 15 );
		$posts = Timber::get_posts( $pids );
		$this->assertEquals( 15, count( $posts ) );
		$this->assertEquals( $pids[3], $posts[3]->ID );
	}

	function testGetPostWithSlug() {
		$post = $this->factory->post->create( array( 'post_name' => 'silly-post' ) );
		$posts = Timber::get_posts( 'silly-post' );
		$this->assertEquals( 1, count( $posts ) );
		$this->assertEquals( 'silly-post', $posts[0]->slug );
	}

	function testCustomPostTypeAndClass() {
		register_post_type('job');
		$jobs = $this->factory->post->create_many( 10, array('post_type' => 'job'));
		$jobPosts = Timber::get_posts(array('post_type' => 'job'));
		$this->assertEquals(10, count($jobPosts));
	}

	function testCustomPostTypeAndClassOnSinglePage() {
		register_post_type('job');
		$post_id = $this->factory->post->create( array( 'post_type' => 'job' ) );
		$post = new TimberPost($post_id);
		$this->go_to('?p='.$post->ID);
		$jobs = $this->factory->post->create_many( 10, array('post_type' => 'job'));
		$jobPosts = Timber::get_posts(array('post_type' => 'job'));
		$this->assertEquals(10, count($jobPosts));
	}

	function testPostTypeReturnAgainstArgType() {
		register_post_type('person');
		$jobs = $this->factory->post->create_many( 4, array('post_type' => 'person'));
		$personPostsArray = Timber::get_posts(array('post_type' => 'person'), 'Person');
		$personPostsString = Timber::get_posts('post_type=person', 'Person');
		$this->assertEquals(4, count($personPostsArray));
		$this->assertEquals(4, count($personPostsString));
	}

}

class job extends TimberPost {

}

class Person extends TimberPost {

}

class TimberAlert extends TimberPost {

}

class TimberPortfolio extends TimberPost {

}
