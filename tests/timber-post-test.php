<?php

	class TimberTest extends WP_UnitTestCase {

		function testPost(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$this->assertEquals('TimberPost', get_class($post));
			$this->assertEquals($post_id, $post->ID);
		}
	}