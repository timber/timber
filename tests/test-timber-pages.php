<?php

/**
 * @group called-post-constructor
 */
class TestTimberPages extends Timber_UnitTestCase {

	function testTimberPostOnCategoryPage() {
		$post_id = $this->factory->post->create();
		$category_id = $this->factory->term->create(array('taxonomy' => 'category', 'name' => 'News'));
		$cat = new Timber\Term($category_id);
		$this->go_to($cat->path());
		$term = new Timber\Term();
		$this->assertEquals($category_id, $term->ID);
		// FIXME #1793 factories
		$post = new Timber\Post();
		$this->assertEquals(0, $post->ID);
	}



}
