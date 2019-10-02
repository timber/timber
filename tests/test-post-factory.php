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

	public function testGetPost() {
		$post_id   = $this->factory->post->create(['post_type' => 'post']);
		$page_id   = $this->factory->post->create(['post_type' => 'page']);
		$custom_id = $this->factory->post->create(['post_type' => 'custom']);

		$postFactory = new PostFactory();
		$post				 = $postFactory->get_post($post_id);
		$page				 = $postFactory->get_post($page_id);
		$custom  		 = $postFactory->get_post($custom_id);

		// Assert that all instances are of Timber\Post
		$this->assertInstanceOf(Post::class, $post);
		$this->assertInstanceOf(Post::class, $post);
		$this->assertInstanceOf(Post::class, $custom);
	}

	public function testGetPostWithOverrides() {
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
		$post        = $postFactory->get_post($post_id);
		$page        = $postFactory->get_post($page_id);
		$custom      = $postFactory->get_post($custom_id);

		$this->assertInstanceOf(MyPost::class, $post);
		$this->assertInstanceOf(MyPage::class, $page);
		$this->assertInstanceOf(MyCustom::class, $custom);

		remove_filter( 'timber/post/classmap', 'my_class_map' );
	}

	public function testGetPostWithCallable() {
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
		$post        = $postFactory->get_post($post_id);
		$page        = $postFactory->get_post($page_id);
		$custom      = $postFactory->get_post($custom_id);
		$special     = $postFactory->get_post($special_id);

		$this->assertInstanceOf(Post::class, $post);
		$this->assertInstanceOf(MyPage::class, $page);
		$this->assertInstanceOf(MyCustom::class, $custom);
		$this->assertInstanceOf(MySpecialCustom::class, $special);

		remove_filter( 'timber/post/classmap', $my_class_map );
	}

	public function testFromArray() {
		$postFactory = new PostFactory();

		$this->factory->post->create(['post_type' => 'page', 'post_title' => 'Title One']);
		$this->factory->post->create(['post_type' => 'page', 'post_title' => 'Title Two']);

		$res = $postFactory->from(get_posts('post_type=page'));

		$this->assertTrue(true, is_array($res));
		$this->assertCount(2, $res);
		$this->assertInstanceOf(Post::class, $res[0]);
		$this->assertInstanceOf(Post::class, $res[1]);
	}

	public function testFromArrayCustom() {
		$my_class_map = function(array $map) {
			return array_merge($map, [
				'page'   => MyPage::class,
				'custom' => MyCustom::class,
			]);
		};
		add_filter( 'timber/post/classmap', $my_class_map );

		$postFactory = new PostFactory();

		$this->factory->post->create(['post_type' => 'post',   'post_title' => 'AAA']);
		$this->factory->post->create(['post_type' => 'page',   'post_title' => 'BBB']);
		$this->factory->post->create(['post_type' => 'custom', 'post_title' => 'CCC']);

		$res = $postFactory->from(get_posts([
			'post_type' => ['custom', 'page', 'post'],
			'orderby'   => 'title',
			'order'     => 'ASC',
		]));

		$this->assertTrue(true, is_array($res));
		$this->assertCount(3, $res);
		$this->assertInstanceOf(Post::class,     $res[0]);
		$this->assertInstanceOf(MyPage::class,   $res[1]);
		$this->assertInstanceOf(MyCustom::class, $res[2]);

		remove_filter( 'timber/post/classmap', $my_class_map );
	}

}
