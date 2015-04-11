<?php

class TimberPagesTest extends WP_UnitTestCase {

	function testTimberPostOnCategoryPage() {
		$post_id = $this->factory->post->create();
		$category_id = $this->factory->term->create(array('taxonomy' => 'category', 'name' => 'News'));
		$posts = $this->factory->post->create_many(8, array('post_category' => array($category_id)));
		$cat = new TimberTerm($category_id);
		$this->go_to($cat->path());
		$term = new TimberTerm();
		$this->assertEquals($category_id, $term->ID);
		$post = new TimberPost();
		$this->assertFalse($post);
		print_r($post);

	}



}
