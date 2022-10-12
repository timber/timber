<?php

class TestTimberPages extends Timber_UnitTestCase {

	function testTimberPostOnCategoryPage() {
		$post_id = self::factory()->post->create();
		$category_id = self::factory()->term->create(array('taxonomy' => 'category', 'name' => 'News'));
		$cat = new TimberTerm($category_id);
		$this->go_to($cat->path());
		$term = new TimberTerm();
		$this->assertEquals($category_id, $term->ID);
		$post = new TimberPost();
		$this->assertEquals(0, $post->ID);
	}



}
