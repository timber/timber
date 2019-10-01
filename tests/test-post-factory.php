<?php

use Timber\Post;
use Timber\Factory\PostFactory;

class MyPost extends Post {}
class MyPage extends Post {}

/**
 * @group factory
 */
class TestPostFactory extends Timber_UnitTestCase {

	public function testGet() {
		$post_id = $this->factory->post->create(['post_type' => 'post']);
		$page_id = $this->factory->post->create(['post_type' => 'page']);

		$postFactory = new PostFactory();
		$post				 = $postFactory->get($post_id);
		$page				 = $postFactory->get($page_id);

		$this->assertInstanceOf(Post::class, $post);
		$this->assertInstanceOf(Post::class, $post);
	}

	public function testGetWithOverriddenDefault() {
		$filter = function () {
			return MyPost::class;
		};
		add_filter( 'timber/post/classmap/default', $filter );

		$post_id   = $this->factory->post->create(['post_type' => 'post']);
		$page_id   = $this->factory->post->create(['post_type' => 'page']);
		$custom_id = $this->factory->post->create(['post_type' => 'custom']);

		$postFactory = new PostFactory();
		$post        = $postFactory->get($post_id);
		$page        = $postFactory->get($page_id);
		$custom      = $postFactory->get($custom_id);

		$this->assertInstanceOf(Post::class, $post);
		$this->assertInstanceOf(Post::class, $page);
		$this->assertInstanceOf(MyPost::class, $custom);

		remove_filter( 'timber/post/classmap/default', $filter );
	}

}
