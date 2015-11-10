<?php

class TestTimberCustomFields extends Timber_UnitTestCase {

	function testPostCustomField(){
		$post_id = $this->factory->post->create();
		update_post_meta($post_id, 'gameshow', 'numberwang');
		$post = new TimberPost($post_id);
		$this->assertEquals('numberwang', $post->gameshow);
	}

	function testPostCustomFieldMethodConflict(){
		$post_id = $this->factory->post->create(array('post_title' => 'foo'));
		update_post_meta($post_id, 'title', 'bar');
		$post = new TimberPost($post_id);
		$str = '{{post.title}}';
		$result = Timber::compile_string($str, array('post' => $post));
		$this->assertEquals('foo', $result);
		//
		$str = '{{post.post_title}}';
		update_post_meta($post_id, 'post_title', 'jiggypoof');
		$post = new TimberPost($post_id);
		$result = Timber::compile_string($str, array('post' => $post));
		$this->assertEquals('foo', $result);
		$str = '{{post.custom.post_title}}';
		$result = Timber::compile_string($str, array('post' => $post));
		$this->assertEquals('jiggypoof', $result);
	}

	function testPostCustomFieldPropertyConflict(){
		$post_id = $this->factory->post->create(array('post_title' => 'foo'));
		update_post_meta($post_id, 'post_title', 'bar');
		$post = new TimberPost($post_id);
		$str = '{{post.title}}';
		$result = Timber::compile_string($str, array('post' => $post));
		$this->assertEquals('foo', $result);
	}


}
