<?php

class TimberTest extends WP_UnitTestCase {

	function testSample() {
		// replace this with some actual testing code
		$this->assertTrue( true );
	}

	function testGetPostNumeric(){
		$post_id = $this->factory->post->create();
		$post = Timber::get_post($post_id);
		$this->assertEquals('TimberPost', get_class($post));
	}

	function testGetPostString(){
		$this->factory->post->create();
		$post = Timber::get_post('post_type=post');
		$this->assertEquals('TimberPost', get_class($post));
	}

	function testGetPostBySlug(){
		$this->factory->post->create(array('post_name' => 'kill-bill'));
		$post = Timber::get_post('kill-bill');
		$this->assertEquals('kill-bill', $post->post_name);
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
		$this->assertEquals('TimberPost', get_class($posts[0]));
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
		$this->assertEquals('TimberPost', get_class($post));
	}

	function testGetPostsFromArrayOfIds(){
		$pids = array();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$posts = Timber::get_posts($pids);
		$this->assertEquals('TimberPost', get_class($posts[0]));
	}

	function testGetPostsArrayCount(){
		$pids = array();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$posts = Timber::get_posts($pids);
		$this->assertEquals(3, count($posts));
	}

	function testGetPids(){
		$pids = array();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$pids[] = $this->factory->post->create();
		$pidz = Timber::get_pids('post_type=post');
		sort($pidz, SORT_NUMERIC);
		$this->assertTrue(arrays_are_similar($pids, $pidz));
	}

	function testQueryPostsInContext(){
        $context = Timber::get_context();
        $this->assertArrayHasKey( 'posts', $context );
        $this->assertInstanceOf( 'TimberQueryIterator', $context['posts'] );
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
		$this->assertEquals('TimberTerm', get_class($terms[0]));
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

    function testGetPid(){
    	$post_id = $this->factory->post->create(array('post_name' => 'test-get-pid-slug'));
    	$pid = Timber::get_pid('test-get-pid-slug');
    	$this->assertEquals($post_id, $pid);
    	$pid = Timber::get_pid('dfsfsdfdsfs');
    	$this->assertNull($pid);
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
