<?php

use Timber\Comment;
use Timber\Factory\CommentFactory;

class PostComment extends Comment {}
class PageComment extends Comment {}
class SickBurn extends PostComment {}

/**
 * @group factory
 */
class TestCommentFactory extends Timber_UnitTestCase {

	public function testGetComment() {
		$comment_id = $this->factory->comment->create([
			'comment_post_ID' => $this->factory->post->create(),
			'comment_content' => 'Hello, Timber!',
		]);

		$commentFactory = new CommentFactory();
		$comment				= $commentFactory->get_comment($comment_id);

		$this->assertInstanceOf(Comment::class, $comment);
	}

	public function testGetCommentWithOverrides() {
		$my_class_map = function() {
			return [
				'post'  => PostComment::class,
				'page'  => PageComment::class,
			];
		};
		add_filter( 'timber/comment/classmap', $my_class_map );

		$post_comment_id = $this->factory->comment->create([
			'comment_post_ID' => $this->factory->post->create(),
			'comment_content' => "blorg"
		]);
		$page_comment_id = $this->factory->comment->create([
			'comment_post_ID' => $this->factory->post->create(['post_type' => 'page']),
			'comment_content' => "porge"
		]);

		$commentFactory = new CommentFactory();
		$post_comment   = $commentFactory->get_comment($post_comment_id);
		$page_comment   = $commentFactory->get_comment($page_comment_id);

		$this->assertInstanceOf(PostComment::class, $post_comment);
		$this->assertInstanceOf(PageComment::class, $page_comment);

		remove_filter( 'timber/comment/classmap', $my_class_map );
	}

	public function testGetCommentWithCallables() {
		$my_class_map = function() {
			return [
				'post' => function() {
					return PostComment::class;
				},
				'page' => function(WP_Comment $comment) {
					return strstr($comment->comment_content, 'snowflake')
						? PageComment::class
						: SickBurn::class;
				},
			];
		};
		add_filter( 'timber/comment/classmap', $my_class_map );

		$post_comment_id = $this->factory->comment->create([
			'comment_post_ID' => $this->factory->post->create(),
			'comment_content' => "blorg"
		]);
		$page_comment_id = $this->factory->comment->create([
			'comment_post_ID' => $this->factory->post->create(['post_type' => 'page']),
			'comment_content' => "porge"
		]);
		$page_comment_id = $this->factory->comment->create([
			'comment_post_ID' => $this->factory->post->create(['post_type' => 'page']),
			'comment_content' => "impeachment inquiry DESTROYS snowflake president",
		]);

		$commentFactory = new CommentFactory();
		$post_comment   = $commentFactory->get_comment($post_comment_id);
		$page_comment   = $commentFactory->get_comment($page_comment_id);

		$this->assertInstanceOf(PostComment::class, $post_comment);
		$this->assertInstanceOf(PageComment::class, $page_comment);

		remove_filter( 'timber/comment/classmap', $my_class_map );
	}

	public function testFromArray() {
		$pid = $this->factory->post->create();
		$a = $this->factory->comment->create([
			'comment_post_ID' => $pid,
			'comment_content' => 'eyyyy',
		]);
		$b = $this->factory->comment->create([
			'comment_post_ID' => $pid,
			'comment_content' => 'beeee',
		]);

		$commentFactory = new CommentFactory();
		$res = $commentFactory->from(get_comments(['post_id' => $pid]));

		$this->assertTrue(true, is_array($res));
		$this->assertCount(2, $res);
		$this->assertInstanceOf(Comment::class, $res[0]);
		$this->assertInstanceOf(Comment::class, $res[1]);
		$this->assertEquals('eyyyy', $res[0]->comment_content);
		$this->assertEquals('beeee', $res[1]->comment_content);
	}
}
