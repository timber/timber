<?php

use Timber\LocationManager;

/**
 * @group posts-api
 * @group terms-api
 * @group users-api
 * @group called-post-constructor
 */
class TestTimberMainClass extends Timber_UnitTestCase {

	function testSample() {
		// replace this with some actual testing code
		$this->assertTrue( true );
	}

	function testGetPostNumeric(){
		$post_id = $this->factory->post->create();
		$post = Timber::get_post($post_id);
		$this->assertEquals('Timber\Post', get_class($post));
	}

	// @todo are we dropping support for ::get_post("string") ?
	function testGetPostString(){
		$this->markTestSkipped();
		$this->factory->post->create();
		$post = Timber::get_post('post_type=post');
		$this->assertEquals('Timber\Post', get_class($post));
	}

	function testGetPostBySlug(){
		$this->factory->post->create( [ 'post_name' => 'kill-bill' ] );

		$post = Timber\Timber::get_post_by( 'slug', 'kill-bill');

		$this->assertEquals( 'kill-bill', $post->post_name );
	}

	function testGetPostBySlugNewest(){
		$post_id = $this->factory->post->create( [ 'post_type' => 'post',
												   'post_name' => 'privacy',
												   'post_date'  => '2018-01-10 02:58:18' ] );

		$page_id = $this->factory->post->create( [ 'post_type' => 'page',
												   'post_name' => 'privacy',
												   'post_date'  => '2020-01-10 02:58:18' ] );

		$post = Timber\Timber::get_post_by( 'slug', 'privacy', ['order' => 'DESC'] );

		$this->assertEquals( 'privacy', $post->post_name );
		$this->assertEquals( $page_id, $post->ID );
	}

	function testGetPostBySlugAndPostType(){

		register_post_type('movie', array('public' => true));

		$post_id_movie = $this->factory->post->create( [
			'post_name' => 'kill-bill',
			'post_type' => 'movie',
		] );
		$post_id_page  = $this->factory->post->create( [
			'post_name' => 'kill-bill',
			'post_type' => 'page',
		] );

		$post_movie = Timber\Timber::get_post_by( 'slug', 'kill-bill', ['post_type' => 'movie'] );
		$post_page  = Timber\Timber::get_post_by( 'slug', 'kill-bill', ['post_type' => 'page'] );

		$this->assertEquals( $post_id_movie, $post_movie->ID );
		$this->assertEquals( $post_id_page, $post_page->ID );

		$post_any = Timber\Timber::get_post_by( 'slug', 'kill-bill' );
		$this->assertEquals( $post_id_movie, $post_any->ID );
	}

	function testGetPostBySlugForNonexistentPost(){
		$this->factory->post->create( [ 'post_name' => 'kill-bill' ] );

		$post = Timber\Timber::get_post_by( 'slug', 'kill-bill-2');

		$this->assertEquals( null, $post );
	}

	function testGetPostByTitle(){
		$post_title = 'A Post Title containing Special Characters like # or ! or Ä or ç';
		$this->factory->post->create( [ 'post_title' => $post_title ] );

		$post = Timber\Timber::get_post_by( 'title', $post_title );

		$this->assertEquals( $post_title, $post->title() );
	}

	function testGetPostByTitleWithDifferentCasing(){
		$post_title = 'A Post Title containing Special Characters like # or ! or Ä or ç';
		$this->factory->post->create( [ 'post_title' => $post_title ] );

		$lower_case_title = mb_strtolower( $post_title );
		$post             = Timber\Timber::get_post_by( 'title', $lower_case_title );

		$this->assertEquals( $post_title, $post->title() );
	}

	function testGetPostByTitleAndPostType(){
		register_post_type('book', array('public' => true));
		register_post_type('movie', array('public' => true));
		$post_title    = 'A Special Post Title containing Special Characters like # or ! or Ä or ç';

		$post_id_movie = $this->factory->post->create( [
			'post_title' => $post_title,
			'post_type'  => 'movie',
			'post_date'  => '2020-01-10 02:58:18'
		] );

		$post_id_book  = $this->factory->post->create( [
			'post_title' => $post_title,
			'post_type'  => 'book',
			'post_date'  => '2020-01-13 02:58:18'
		] );

		$post_id_page  = $this->factory->post->create( [
			'post_title' => $post_title,
			'post_type'  => 'page',
			'post_date'  => '2020-01-02 02:58:18'
		] );

		$post_movie        = Timber\Timber::get_post_by( 'title', $post_title, ['post_type' => 'movie'] );
		$post_page         = Timber\Timber::get_post_by( 'title', $post_title, ['post_type' => 'page'] );
		$post_multiple     = Timber\Timber::get_post_by( 'title', $post_title, ['post_type' => [ 'page', 'book' ]] );
		$post_multiple_any = Timber\Timber::get_post_by( 'title', $post_title, ['post_type' => 'any'] );

		$this->assertEquals( $post_id_movie, $post_movie->ID );
		$this->assertEquals( $post_id_page, $post_page->ID );

		// Multiple post types should return the post with the oldest post date.
		$this->assertEquals( $post_id_page, $post_multiple->ID );
		$this->assertEquals( $post_id_page, $post_multiple_any->ID );
	}

	function testGetPostByTitleForNonexistentPost(){
		$this->factory->post->create();

		$post = Timber\Timber::get_post_by( 'title', 'Just a nonexistent post' );

		$this->assertEquals( null, $post );
	}

	function testGetPostByPostObject() {
		$this->markTestSkipped();
		$pid = $this->factory->post->create();
		$wp_post = get_post($pid);
		$post = new TimberAlert($wp_post);
		$this->assertEquals('TimberAlert', get_class($post));
		$this->assertEquals($pid, $post->ID);
		$post = Timber::get_post($wp_post, 'TimberAlert');
		$this->assertEquals('TimberAlert', get_class($post));
		$this->assertEquals($pid, $post->ID);
	}

	function testGetPostByQueryArray() {
		$this->markTestSkipped();
		$pid = $this->factory->post->create();
		$posts = new Timber\PostQuery( array(
			'query'      => array(
				'post_type' => 'post'
			),
			'post_class' => 'TimberAlert',
		) );
		$this->assertEquals('TimberAlert', get_class($posts[0]));
		$this->assertEquals($pid, $posts[0]->ID);
		$post = Timber::get_post(array('post_type' => 'post'), 'TimberAlert');
		$this->assertEquals('TimberAlert', get_class($post));
		$this->assertEquals($pid, $post->ID);
	}

	function testGetPostWithCustomPostType() {
		register_post_type('event', array('public' => true));
		$pid = $this->factory->post->create(array('post_type' => 'event'));
		$post = new TimberAlert($pid);
		$this->assertEquals('TimberAlert', get_class($post));
		$this->assertEquals($pid, $post->ID);
		$this->assertEquals('event', $post->post_type);
		$post = Timber::get_post($pid, 'TimberAlert');
		$this->assertEquals('TimberAlert', get_class($post));
		$this->assertEquals($pid, $post->ID);
		$this->assertEquals('event', $post->post_type);
	}

	function testGetPostWithCustomPostTypeNotPublic() {
		register_post_type('event', array('public' => false));
		$pid = $this->factory->post->create(array('post_type' => 'event'));
		$post = Timber::get_post($pid, 'TimberAlert');
		$this->assertEquals('TimberAlert', get_class($post));
		$this->assertEquals($pid, $post->ID);
	}

	function testGetPostsQueryString(){
		$this->factory->post->create();
		$this->factory->post->create();
		$posts = new Timber\PostQuery( array(
			'query' => 'post_type=post'
		) );
		$this->assertGreaterThan(1, count($posts));
	}

	function testGetPostsQueryArray(){
		$this->factory->post->create();
		$query = array('post_type' => 'post');
		$posts = new Timber\PostQuery( array(
			'query' => $query
		) );
		$this->assertEquals('Timber\Post', get_class($posts[0]));
	}

	function testGetPostsFromSlug(){
		$post_id = $this->factory->post->create(array('post_name' => 'mycoolpost'));
		$post    = Timber::get_post('mycoolpost');
		$this->assertEquals($post_id, $post->ID);

		$post = Timber::get_post('mycoolpost');
		$this->assertEquals($post_id, $post->ID);
	}

	function testGetPostsQueryStringClassName(){
		$this->factory->post->create();
		$this->factory->post->create();
		$posts = new Timber\PostQuery( array(
			'query' => 'post_type=post'
		) );
		$post = $posts[0];
		$this->assertEquals('Timber\Post', get_class($post));
	}

	function testGetPostsFromArrayOfIds(){
		$this->markTestSkipped();
		$pids = array();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$posts  = new Timber\PostQuery( array(
			'query' => $pids,
		) );
		$this->assertEquals('Timber\Post', get_class($posts[0]));
	}

	function testGetPostsArrayCount(){
		$pids = array();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$posts  = new Timber\PostQuery( array(
			'query' => $pids
		) );
		$this->assertEquals(3, count($posts));
	}

	function testGetPostsCollection() {
		$pids   = array();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$posts  = new Timber\PostCollection($pids);
		$this->assertEquals(3, count($posts));
		$this->assertEquals('Timber\PostCollection', get_class($posts));
	}

	function testUserInContextAnon() {
		$context = Timber::context();
		$this->assertArrayHasKey( 'user', $context );
		$this->assertFalse($context['user']);
	}

	function testUserInContextLoggedIn() {
		$uid = $this->factory->user->create(array(
			'user_login' => 'timber',
			'user_pass' => 'timber',
		));
		$user = wp_set_current_user($uid);

		$context = Timber::context();
		$this->assertArrayHasKey( 'user', $context );
		$this->assertInstanceOf( 'Timber\User', $context['user'] );
	}

	function testQueryPostsInContext(){
		$pids = $this->factory->post->create_many(20);
		$this->go_to('/');
        $context = Timber::context();
        $this->assertArrayHasKey( 'posts', $context );
        $this->assertInstanceOf( 'Timber\PostCollection', $context['posts'] );
	}

	/* Terms */
	function testGetTerm(){
		// @todo #2087
		$this->markTestSkipped();
	}

	function testGetTermWithTaxonomyParam(){
		// @todo #2087
		$this->markTestSkipped();
	}

	function testGetTermWithObject(){
		// @todo #2087
		$this->markTestSkipped();
	}

	function testGetTermWithSlug(){
		// @todo #2087
		$this->markTestSkipped();
		$term_id = $this->factory->term->create(array('name' => 'New England Patriots'));
		$term = Timber::get_term('new-england-patriots');
		$this->assertEquals($term->ID, $term_id);
	}

	function testGetTerms(){
		$posts = $this->factory->post->create_many(15, array( 'post_type' => 'post' ) );
		$tags = array();
		foreach($posts as $post){
			$tag = rand_str();
			wp_set_object_terms($post, $tag, 'post_tag');
			$tags[] = $tag;
		}
		sort($tags);
		$terms = Timber::get_terms('tag');
		$this->assertEquals('Timber\Term', get_class($terms[0]));
		$results = array();
		foreach($terms as $term){
			$results[] = $term->name;
		}
		sort($results);
		$this->assertEquals($results, $tags);

	}

    /* Previews */
    function testGetPostPreview(){
        $editor_user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
        wp_set_current_user( $editor_user_id );

        $post_id = $this->factory->post->create( array( 'post_author' => $editor_user_id, 'post_content' => "OLD CONTENT HERE" ) );
        _wp_put_post_revision( array( 'ID' => $post_id, 'post_content' => 'New Stuff Goes here'), true );

        $_GET['preview']    = true;
        $_GET['preview_id'] = $post_id;

        $the_post = Timber::get_post( $post_id );
        $this->assertEquals( 'New Stuff Goes here', $the_post->post_content );
    }

    function testTimberRenderString() {
    	$pid = $this->factory->post->create(array('post_title' => 'Zoogats'));
        $post = Timber::get_post($pid);
        ob_start();
        Timber::render_string('<h2>{{post.title}}</h2>', array('post' => $post));
       	$data = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('<h2>Zoogats</h2>', trim($data));
    }

    function testTimberRender() {
    	$pid = $this->factory->post->create(array('post_title' => 'Foobar'));
        $post = Timber::get_post($pid);
        ob_start();
        Timber::render('assets/single-post.twig', array('post' => $post));
       	$data = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('<h1>Foobar</h1>', trim($data));
    }

    function testTimberGetCallingScriptFile() {
    	$calling_file = LocationManager::get_calling_script_file();
    	$file = getcwd().'/tests/test-timber.php';
    	$this->assertEquals($calling_file, $file);
    }

    function testCompileNull() {
    	$str = Timber::compile('assets/single-course.twig', null);
    	$this->assertEquals('I am single course', $str);
    }

  /**
	 * @ticket 1660
	 */
	function testDoubleInstantiationOfSubclass() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'person' ) );
		$post = Timber::get_post($post_id, 'Person');
		$this->assertEquals('Person', get_class($post));
	}

	/**
	 * @ticket 1660
	 */
	function testDoubleInstantiationOfTimberPostClass() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'post' ) );
		$post = Timber::get_post($post_id);
		$this->assertEquals('Timber\Post', get_class($post));
	}

	/**
	 * @group wp_query_hacks
	 */
	function testGettingWithCategory() {
		// Create several irrelevant posts that should NOT show up in our query.
		$this->factory->post->create_many(6);

		$cat = $this->factory->term->create(array('name' => 'News', 'taxonomy' => 'category'));
		$cats = $this->factory->post->create_many(3, array('post_category' => array($cat)) );
		$cat_post = $this->factory->post->create(array('post_category' => array($cat)) );

		$cat_post = Timber::get_post($cat_post);
		$this->assertEquals('News', $cat_post->category()->title());

		$this->assertCount(4, Timber\Timber::get_posts( array(
			'category' => $cat,
		) ));
	}

	/**
	 * @group wp_query_hacks
	 */
	function testGettingWithCategoryList() {
		// Create several irrelevant posts that should NOT show up in our query.
		$this->factory->post->create_many(6);

		// Create a list of categories and get their IDs.
		$cats = [
			$this->factory->term->create(array('name' => 'News', 'taxonomy' => 'category')),
			$this->factory->term->create(array('name' => 'Local', 'taxonomy' => 'category')),
		];

		// Create three posts with a combination of relevant categories.
		$this->factory->post->create(array('post_category' => array($cats[0])) );
		$this->factory->post->create(array('post_category' => array($cats[1])) );
		$this->factory->post->create(array('post_category' => $cats) );

		$this->assertCount(3, Timber\Timber::get_posts( array(
			'category' => $cats,
		) ));
	}

}
