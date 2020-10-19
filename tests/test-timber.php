<?php

use Timber\LocationManager;

class TestTimber extends Timber_UnitTestCase {

	function testSample() {
		// replace this with some actual testing code
		$this->assertTrue( true );
	}

	function testGetPostNumeric(){
		$post_id = $this->factory->post->create();
		$post = Timber::get_post($post_id);
		$this->assertEquals('Timber\Post', get_class($post));
	}

	function testGetPostString(){
		$this->factory->post->create();
		$post = Timber::get_post('post_type=post');
		$this->assertEquals('Timber\Post', get_class($post));
	}

	function testGetPostBySlug(){
		$this->factory->post->create(array('post_name' => 'kill-bill'));
		$post = Timber::get_post('kill-bill');
		$this->assertEquals('kill-bill', $post->post_name);
	}

	function testGetPostByPostObject() {
		$pid = $this->factory->post->create();
		$wp_post = get_post($pid);
		$post = Timber::get_post($wp_post, 'TimberAlert');
		$this->assertEquals('TimberAlert', get_class($post));
		$this->assertEquals($pid, $post->ID);
	}

	function testGetPostByQueryArray() {
		$pid = $this->factory->post->create();
		$post = Timber::get_post(array('post_type' => 'post'), 'TimberAlert');
		$this->assertEquals('TimberAlert', get_class($post));
		$this->assertEquals($pid, $post->ID);
	}

	function testGetPostWithCustomPostType() {
		register_post_type('event', array('public' => true));
		$pid = $this->factory->post->create(array('post_type' => 'event'));
		$post = Timber::get_post($pid, 'TimberAlert');
		$this->assertEquals('TimberAlert', get_class($post));
		$this->assertEquals($pid, $post->ID);
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
		$posts = Timber::get_posts('post_type=post');
		$this->assertGreaterThan(1, count($posts));
	}

	function testGetPostsQueryArray(){
		$this->factory->post->create();
		$query = array('post_type' => 'post');
		$posts = Timber::get_posts($query);
		$this->assertEquals('Timber\Post', get_class($posts[0]));
	}

	function testGetPostsFromSlugWithHash(){
		$post_id = $this->factory->post->create();
		$post = Timber::get_post($post_id);
		$str = '#'.$post->post_name;
		$post = Timber::get_post($str);
		$this->assertEquals($post_id, $post->ID);
	}

	function testGetPostsFromSlugWithHashAndPostType(){
		$post_id = $this->factory->post->create();
		$post = Timber::get_post($post_id);
		$str = $post->post_type.'#'.$post->post_name;
		$post = Timber::get_post($str);
		$this->assertEquals($post_id, $post->ID);
	}

	function testGetPostsFromSlug(){
		$post_id = $this->factory->post->create();
		$post = Timber::get_post($post_id);
		$str = $post->post_name;
		$post = Timber::get_post($str);
		$this->assertEquals($post_id, $post->ID);
	}

	function testGetPostsQueryStringClassName(){
		$this->factory->post->create();
		$this->factory->post->create();
		$posts = Timber::get_posts('post_type=post');
		$post = $posts[0];
		$this->assertEquals('Timber\Post', get_class($post));
	}

	function testGetPostsFromArrayOfIds(){
		$pids = array();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$posts = Timber::get_posts($pids);
		$this->assertEquals('Timber\Post', get_class($posts[0]));
	}

	function testGetPostsArrayCount(){
		$pids = array();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$posts = Timber::get_posts($pids);
		$this->assertEquals(3, count($posts));
	}

	function testGetPostsCollection() {
		$pids = array();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$posts = new Timber\PostCollection($pids);
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
		$this->assertInstanceOf( 'TimberUser', $context['user'] );
	}

	function testQueryPostsInContext(){
        $context = Timber::context();
        $this->assertArrayHasKey( 'posts', $context );
        $this->assertInstanceOf( 'Timber\PostCollection', $context['posts'] );
	}

	/* Terms */
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
		$this->assertTrue(arrays_are_similar($results, $tags));

		//lets add one more occurance in..

	}

    /* Previews */
    function testGetPostPreview(){
        $editor_user_id = $this->factory->user->create( array( 'role' => 'editor' ) );
        wp_set_current_user( $editor_user_id );

        $post_id = $this->factory->post->create( array( 'post_author' => $editor_user_id ) );
        _wp_put_post_revision( array( 'ID' => $post_id, 'post_content' => 'New Stuff Goes here'), true );

        $_GET['preview'] = true;
        $_GET['preview_id'] = $post_id;

        $the_post = Timber::get_post( $post_id );
        $this->assertEquals( 'New Stuff Goes here', $the_post->post_content );
    }

    function testTimberRenderString() {
    	$pid = $this->factory->post->create(array('post_title' => 'Zoogats'));
        $post = new TimberPost($pid);
        ob_start();
        Timber::render_string('<h2>{{post.title}}</h2>', array('post' => $post));
       	$data = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('<h2>Zoogats</h2>', trim($data));
    }

    function testTimberRender() {
    	$pid = $this->factory->post->create(array('post_title' => 'Foobar'));
        $post = new TimberPost($pid);
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

}

function arrays_are_similar($a, $b) {
  	// if the indexes don't match, return immediately
	if (count(array_diff_assoc($a, $b))) {
		return false;
	}
	// we know that the indexes, but maybe not values, match.
	// compare the values between the two arrays
	foreach($a as $k => $v) {
		if ($v !== $b[$k]) {
			return false;
		}
	}
	// we have identical indexes, and no unequal values
	return true;
}
