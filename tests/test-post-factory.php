<?php

use Timber\Post;
use Timber\Factory\PostFactory;

class MyPost extends Post {}
class MyPage extends Post {}
class MyCustom extends Post {}
class MySpecialCustom extends MyCustom {}

/**
 * @group factory
 */
class TestPostFactory extends Timber_UnitTestCase {

	public function testGet() {
		$post_id   = $this->factory->post->create(['post_type' => 'post']);
		$page_id   = $this->factory->post->create(['post_type' => 'page']);
		$custom_id = $this->factory->post->create(['post_type' => 'custom']);

		$postFactory = new PostFactory();
		$post				 = $postFactory->get($post_id);
		$page				 = $postFactory->get($page_id);
		$custom  		 = $postFactory->get($custom_id);

		// Assert that all instances are of Timber\Post
		$this->assertInstanceOf(Post::class, $post);
		$this->assertInstanceOf(Post::class, $post);
		$this->assertInstanceOf(Post::class, $custom);
	}

	public function testGetWithOverrides() {
		$my_class_map = function() {
			return [
				'post'   => MyPost::class,
				'page'   => MyPage::class,
				'custom' => MyCustom::class,
			];
		};
		add_filter( 'timber/post/classmap', $my_class_map );

		$post_id   = $this->factory->post->create(['post_type' => 'post']);
		$page_id   = $this->factory->post->create(['post_type' => 'page']);
		$custom_id = $this->factory->post->create(['post_type' => 'custom']);

		$postFactory = new PostFactory();
		$post        = $postFactory->get($post_id);
		$page        = $postFactory->get($page_id);
		$custom      = $postFactory->get($custom_id);

		$this->assertInstanceOf(MyPost::class, $post);
		$this->assertInstanceOf(MyPage::class, $page);
		$this->assertInstanceOf(MyCustom::class, $custom);

		remove_filter( 'timber/post/classmap', 'my_class_map' );
	}

	public function testGetWithCallable() {
		$my_class_map = function(array $map) {
			return array_merge($map, [
				'page'   => function() {
					return MyPage::class;
				},
				'custom' => function(WP_Post $post) {
					if ($post->post_name === 'my-special-post') return MySpecialCustom::class;
					return MyCustom::class;
				},
			]);
		};
		add_filter( 'timber/post/classmap', $my_class_map );

		$post_id   = $this->factory->post->create(['post_type' => 'post']);
		$page_id   = $this->factory->post->create(['post_type' => 'page']);
		$custom_id = $this->factory->post->create(['post_type' => 'custom']);
		$special_id = $this->factory->post->create(['post_type' => 'custom', 'post_name' => 'my-special-post']);

		$postFactory = new PostFactory();
		$post        = $postFactory->get($post_id);
		$page        = $postFactory->get($page_id);
		$custom      = $postFactory->get($custom_id);
		$special     = $postFactory->get($special_id);

		$this->assertInstanceOf(Post::class, $post);
		$this->assertInstanceOf(MyPage::class, $page);
		$this->assertInstanceOf(MyCustom::class, $custom);
		$this->assertInstanceOf(MySpecialCustom::class, $special);

		remove_filter( 'timber/post/classmap', $my_class_map );
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
