<?php

class TestTimberPostGetter extends Timber_UnitTestCase {


	function setUp() {
		delete_option('sticky_posts');
		parent::setUp();
	}
	/**
	 * @group wp_query_hacks
	 */
	function testGettingWithCatAndOtherStuff() {
		$pids = $this->factory->post->create_many(6);
		$cat = $this->factory->term->create(array('name' => 'Something', 'taxonomy' => 'category'));
		$cat_post = $this->factory->post->create(array('post_title' => 'Germany', 'post_category' => array($cat)) );
		$cat_post = $this->factory->post->create(array('post_title' => 'France', 'post_category' => array($cat)) );
		$cat_post = $this->factory->post->create(array('post_title' => 'England', 'post_category' => array($cat)) );
		$args = array(
            'post_type' => 'post',
            'posts_per_page' => 2,
            'post_status' => 'publish',
            'cat' => $cat
        );
		$posts = Timber::get_posts($args);
		$this->assertEquals(2, count($posts));
	}

	/**
	 * @group wp_query_hacks
	 */
	function testGettingWithCategoryAndOtherStuff() {
		$pids = $this->factory->post->create_many(6);
		$cat = $this->factory->term->create(array('name' => 'Something', 'taxonomy' => 'category'));
		$cat_post = $this->factory->post->create(array('post_title' => 'Germany', 'post_category' => array($cat)) );
		$cat_post = $this->factory->post->create(array('post_title' => 'France', 'post_category' => array($cat)) );
		$cat_post = $this->factory->post->create(array('post_title' => 'England', 'post_category' => array($cat)) );
		$args = array(
            'post_type' => 'post',
            'posts_per_page' => 2,
            'post_status' => 'publish',
            'category' => $cat
        );
		$posts = Timber::get_posts($args);
		$this->assertEquals(2, count($posts));
	}

	/**
	 * @group wp_query_hacks
	 */
	function testGettingWithCat() {
		$cat = $this->factory->term->create(array('name' => 'News', 'taxonomy' => 'category'));

		$pids = $this->factory->post->create_many(6);
		$cats = $this->factory->post->create_many(3, array('post_category' => array($cat)) );
		$cat_post = $this->factory->post->create(array('post_category' => array($cat)) );

		$cat_post = new TimberPost($cat_post);
		$this->assertEquals('News', $cat_post->category()->name());

		$posts = Timber::get_posts(array('cat' => $cat));
		$this->assertEquals(4, count($posts));
	}

	/**
	 * @group wp_query_hacks
	 */
	function testGettingWithCatList() {
		$cat = array();
		$cat[] = $this->factory->term->create(array('name' => 'News', 'taxonomy' => 'category'));
		$cat[] = $this->factory->term->create(array('name' => 'Local', 'taxonomy' => 'category'));
		$pids = $this->factory->post->create_many(6);
		$cat_post = $this->factory->post->create(array('post_category' => array($cat[0])) );
		$cat_post = $this->factory->post->create(array('post_category' => array($cat[1])) );
		$cat_post = $this->factory->post->create(array('post_category' => $cat) );

		$posts = Timber::get_posts( array('cat' => implode(',', $cat)));
		$this->assertEquals(3, count($posts));
	}

	/**
	 * @group wp_query_hacks
	 */
	function testGettingWithCategory() {
		$cat = $this->factory->term->create(array('name' => 'News', 'taxonomy' => 'category'));
		$pids = $this->factory->post->create_many(6);
		$cats = $this->factory->post->create_many(3, array('post_category' => array($cat)) );
		$cat_post = $this->factory->post->create(array('post_category' => array($cat)) );

		$cat_post = new TimberPost($cat_post);
		$this->assertEquals('News', $cat_post->category()->name());

		$posts = Timber::get_posts(array('category' => $cat));
		$this->assertEquals(4, count($posts));
	}

	/**
	 * @group wp_query_hacks
	 */
	function testGettingWithCategoryList() {
		$cat = array();
		$cat[] = $this->factory->term->create(array('name' => 'News', 'taxonomy' => 'category'));
		$cat[] = $this->factory->term->create(array('name' => 'Local', 'taxonomy' => 'category'));
		$pids = $this->factory->post->create_many(6);
		$cat_post = $this->factory->post->create(array('post_category' => array($cat[0])) );
		$cat_post = $this->factory->post->create(array('post_category' => array($cat[1])) );
		$cat_post = $this->factory->post->create(array('post_category' => $cat) );

		$posts = Timber::get_posts( array('category' => implode(',', $cat)));
		$this->assertEquals(3, count($posts));
	}

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

	function testStickyAgainstGetPosts() {
		$first = $this->factory->post->create(array('post_date' => '2015-04-23 15:13:52'));
		$sticky_id = $this->factory->post->create(array('post_date' => '2015-04-21 15:13:52'));
		$last = $this->factory->post->create(array('post_date' => '2015-04-24 15:13:52'));
		update_option('sticky_posts', array($sticky_id));
		$posts = Timber::get_posts('post_type=post');
		$this->assertEquals($last, $posts[0]->ID);
		$posts = get_posts('post_type=post');
		$this->assertEquals($last, $posts[0]->ID);
	}

	function testStickyAgainstTwoSuccessiveLookups() {
		$first = $this->factory->post->create(array('post_date' => '2015-04-23 15:13:52'));
		$sticky_id = $this->factory->post->create(array('post_date' => '2015-04-21 15:13:52'));
		$last = $this->factory->post->create(array('post_date' => '2015-04-24 15:13:52'));
		update_option('sticky_posts', array($sticky_id));
		$posts = Timber::get_posts('post_type=post');
		$this->assertEquals($last, $posts[0]->ID);
		$posts = new Timber\PostQuery('post_type=post');
		$this->assertEquals($sticky_id, $posts[0]->ID);
	}

	function testStickyAgainstQuery() {
		$pids = $this->factory->post->create(array('post_date' => '2015-04-23 15:13:52'));
		$sticky_id = $this->factory->post->create(array('post_date' => '2015-04-21 15:13:52'));
		$pids = $this->factory->post->create(array('post_date' => '2015-04-24 15:13:52'));
		update_option('sticky_posts', array($sticky_id));
		$posts = new Timber\PostQuery('post_type=post');
		$this->assertEquals($sticky_id, $posts[0]->ID);
		$posts = new WP_Query('post_type=post');
		$this->assertEquals($sticky_id, $posts->posts[0]->ID);
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

	function testGetPostWithClassMap() {
		register_post_type('portfolio', array('public' => true));
		$post_id_portfolio = $this->factory->post->create(array('post_type' => 'portfolio', 'post_title' => 'A portfolio item', 'post_date' => '2015-04-23 15:13:52'));
		$post_id_alert = $this->factory->post->create(array('post_type' => 'alert', 'post_title' => 'An alert', 'post_date' => '2015-06-23 15:13:52'));
		$post_portfolio = Timber::get_post($post_id_portfolio, array('portfolio' => 'TimberPortfolio', 'alert' => 'TimberAlert'));
		$post_alert = Timber::get_post($post_id_alert, array('portfolio' => 'TimberPortfolio', 'alert' => 'TimberAlert'));
		$this->assertEquals( 'TimberPortfolio', get_class($post_portfolio) );
		$this->assertEquals( $post_id_portfolio, $post_portfolio->ID );
		$this->assertEquals( 'TimberAlert', get_class($post_alert) );
		$this->assertEquals( $post_id_alert, $post_alert->ID );
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
		$this->assertEmpty($posts);
	}

	function testGettingWithFalse(){
		$pids = $this->factory->post->create_many( 15 );
		$posts = Timber::get_posts(false);
		$this->assertEmpty($posts);
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

	/**
	 * @group wp_query_hacks
	 */
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
		$posts = $this->factory->post->create_many( 15 );
		$this->go_to( '/' );
		$posts = Timber::get_posts();
		$this->assertEquals( 10, count( $posts ) );
		$pc = new Timber\PostQuery();
		$this->assertEquals( 10, count( $pc ) );
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

	function testStringWithPostClass() {
		$yes = \Timber\PostGetter::is_post_class_or_class_map('job');
		$this->assertTrue($yes);
	}

	function testStringWithPostClassBogus() {
		$no = \Timber\PostGetter::is_post_class_or_class_map('pants');
		$this->assertFalse($no);
	}

	function testNotATimberPost() {
		self::enable_error_log(false);
		$post_id = $this->factory->post->create( array( 'post_type' => 'state' ) );
		$use = \Timber\PostGetter::get_post_class('state', 'MyState');
		$this->assertEquals('\Timber\Post', $use);
		$post = new $use($post_id);
		$this->assertEquals('Timber\Post', get_class($post));
		self::enable_error_log(true);
	}

	function testPostTypeReturnAgainstArgType() {
		register_post_type('person');
		$jobs = $this->factory->post->create_many( 4, array('post_type' => 'person'));
		$personPostsArray = Timber::get_posts(array('post_type' => 'person'), 'Person');
		$personPostsString = Timber::get_posts('post_type=person', 'Person');
		$this->assertEquals(4, count($personPostsArray));
		$this->assertEquals(4, count($personPostsString));
	}

	function testGettingPostsWithStickiesReturnsCorrectAmountOfPosts(){
		$post_ids = $this->factory->post->create_many(20);

		//Set some posts as sticky, outside of the first ten posts
        $sticky_ids = array_slice($post_ids, 11, 3);
        foreach($sticky_ids as $sticky_id){
            stick_post($sticky_id);
        }

        //Query the first ten posts
        $numberPosts = 10;
        $queryArgs = array(
            'post_type' => 'post',
            'numberposts' => $numberPosts,
            'orderby' => 'ID',
            'order' => 'ASC'
        );

        $posts = Timber::get_posts($queryArgs);
        $this->assertEquals($numberPosts, count($posts));

	}

	function testOrderOfPostsIn() {
		$pids = $this->factory->post->create_many(30);
		shuffle($pids);
		$first_pids = array_slice($pids, 0, 5);
		$query = array('post__in' => $first_pids, 'orderby' => 'post__in');
		$timber_posts = Timber::get_posts($query);
		$timber_ids = array_map(function($post) {
			return $post->ID;
		}, $timber_posts);

		$this->assertEquals($first_pids, $timber_ids);

		$wp_posts = get_posts($query);
		$wp_ids = array_map(function($post) {
			return $post->ID;
		}, $wp_posts);

		$this->assertEquals($first_pids, $wp_ids);

		$other_query = Timber::get_posts(array('post__in' => $first_pids));
		$timber_ids = array_map(function($post) {
			return $post->ID;
		}, $other_query);
		$this->assertNotEquals($first_pids, $timber_ids);
	}


}

class MyState {

}

class job extends \Timber\Post {

}

class Person extends \Timber\Post {

}

class TimberAlert extends \Timber\Post {

}

class TimberPortfolio extends \Timber\Post {

}
