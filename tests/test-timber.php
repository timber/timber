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
