<?php

namespace Timber\Tests\Factory;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use Timber\Comment;
use Timber\Factory\CommentFactory;
use Timber\Tests\TimberIntegrationTestCase;
use Timber\Timber;
use WP_comment;
use WP_Comment_Query;

class PostComment extends Comment
{
}
class PageComment extends Comment
{
}
class DummyComment extends Comment
{
}
class SickBurn extends PostComment
{
}
class BadComment
{
}

#[Group('factory')]
#[Group('comments-api')]
class CommentFactoryTest extends TimberIntegrationTestCase
{
    public function testGetComment()
    {
        $comment_id = static::factory()->comment->create([
            'comment_post_ID' => static::factory()->post->create(),
            'comment_content' => 'Hello, Timber!',
        ]);

        $commentFactory = new CommentFactory();
        $comment = $commentFactory->from($comment_id);

        $this->assertInstanceOf(Comment::class, $comment);
    }

    public function testGetCommentFromInvalidId()
    {
        $commentFactory = new CommentFactory();
        $comment = $commentFactory->from(99999);

        $this->assertNull($comment);
    }

    public function testGetCommentFromIdString()
    {
        $comment_id = static::factory()->comment->create([
            'comment_post_ID' => static::factory()->post->create(),
            'comment_content' => 'Hello, Timber!',
        ]);

        $commentFactory = new CommentFactory();
        $comment = $commentFactory->from('' . $comment_id);

        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals($comment_id, $comment->id);
    }

    public function testGetCommentWithClassmapFilter()
    {
        $my_class_map = (fn () => [
            'post' => PostComment::class,
            'page' => PageComment::class,
        ]);
        \add_filter('timber/comment/classmap', $my_class_map);

        $post_comment_id = static::factory()->comment->create([
            'comment_post_ID' => static::factory()->post->create(),
            'comment_content' => "blorg",
        ]);
        $page_comment_id = static::factory()->comment->create([
            'comment_post_ID' => static::factory()->post->create([
                'post_type' => 'page',
            ]),
            'comment_content' =>
"porge",
        ]);

        $commentFactory = new CommentFactory();
        $post_comment = $commentFactory->from($post_comment_id);
        $page_comment = $commentFactory->from($page_comment_id);

        $this->assertTrue(PostComment::class === $post_comment::class);
        $this->assertTrue(PageComment::class === $page_comment::class);

        \remove_filter('timber/comment/classmap', $my_class_map);
    }

    public function testGetCommentWithClassFilter()
    {
        $my_class_filter = (fn ($class, WP_comment $comment) => DummyComment::class);
        \add_filter('timber/comment/class', $my_class_filter, 10, 2);

        $post_comment_id = static::factory()->comment->create([
            'comment_post_ID' => static::factory()->post->create(),
            'comment_content' => "blorg",
        ]);

        $commentFactory = new CommentFactory();
        $post_comment = $commentFactory->from($post_comment_id);

        $this->assertTrue(DummyComment::class === $post_comment::class);

        \remove_filter('timber/comment/class', $my_class_filter, 10);
    }

    public function testInvalidCommentClassThrowsError()
    {
        $this->expectException(InvalidArgumentException::class);

        $post_comment_id = static::factory()->comment->create([
            'comment_post_ID' => static::factory()->post->create(),
            'comment_content' => "blorg",
        ]);

        $bad_comment_obj = new BadComment();

        $commentFactory = new CommentFactory();
        $post_comment = $commentFactory->from($bad_comment_obj);

        // $normie_wp_user = Timber::get_user($bad_user_obj);
        // $this->assertInstanceOf(User::class, $userFactory->from($normie_wp_user));
    }

    public function testGetCommentWithCallables()
    {
        $my_class_map = (fn () => [
            'post' => fn () => PostComment::class,
            'page' => fn (WP_comment $comment) => \strstr($comment->comment_content, 'snowflake')
                ? PageComment::class
                : SickBurn::class,
        ]);
        \add_filter('timber/comment/classmap', $my_class_map);

        $post_comment_id = static::factory()->comment->create([
            'comment_post_ID' => static::factory()->post->create(),
            'comment_content' => "blorg",
        ]);
        $page_comment_id = static::factory()->comment->create([
            'comment_post_ID' => static::factory()->post->create([
                'post_type' => 'page',
            ]),
            'comment_content' =>
"porge",
        ]);
        $page_comment_id = static::factory()->comment->create([
            'comment_post_ID' => static::factory()->post->create([
                'post_type' => 'page',
            ]),
            'comment_content' => "impeachment inquiry DESTROYS snowflake president",
        ]);

        $commentFactory = new CommentFactory();
        $post_comment = $commentFactory->from($post_comment_id);
        $page_comment = $commentFactory->from($page_comment_id);

        $this->assertTrue(PostComment::class === $post_comment::class);
        $this->assertTrue(PageComment::class === $page_comment::class);

        \remove_filter('timber/comment/classmap', $my_class_map);
    }

    public function testFromArray()
    {
        $pid = static::factory()->post->create();
        $a = static::factory()->comment->create([
            'comment_post_ID' => $pid,
            'comment_content' => 'eyyyy',
        ]);
        $b = static::factory()->comment->create([
            'comment_post_ID' => $pid,
            'comment_content' => 'beeee',
        ]);

        $commentFactory = new CommentFactory();
        $res = $commentFactory->from(\get_comments([
            'post_id' => $pid,
            'orderby' => 'comment_ID',
            'order' => 'ASC',
        ]));

        $this->assertTrue(true, \is_array($res));
        $this->assertCount(2, $res);
        $this->assertInstanceOf(Comment::class, $res[0]);
        $this->assertInstanceOf(Comment::class, $res[1]);
        $this->assertEquals('eyyyy', $res[0]->comment_content);
        $this->assertEquals('beeee', $res[1]->comment_content);
    }

    public function testFromWpCommentObject()
    {
        $id = static::factory()->comment->create([
            'comment_post_ID' => static::factory()->post->create(),
            'comment_content' => 'eyyyy',
        ]);

        // docs: You must pass a variable containing an integer
        // me: lol
        $comment = \get_comment($id);

        $commentFactory = new CommentFactory();

        $this->assertInstanceOf(Comment::class, $commentFactory->from($comment));
    }

    public function testFromWpCommentQuery()
    {
        $pid = static::factory()->post->create();
        $a = static::factory()->comment->create([
            'comment_post_ID' => $pid,
            'comment_content' => 'eyyyy',
        ]);
        $b = static::factory()->comment->create([
            'comment_post_ID' => $pid,
            'comment_content' => 'beeee',
        ]);

        // Create another comment on another post
        $c = static::factory()->comment->create([
            'comment_post_ID' => static::factory()->post->create(),
            'comment_content' => 'beeee',
        ]);

        $commentFactory = new CommentFactory();

        $res = $commentFactory->from(new WP_Comment_Query([
            'post_id' => $pid,
        ]));

        $this->assertCount(2, $res);
        $this->assertInstanceOf(Comment::class, $res[0]);
        $this->assertInstanceOf(Comment::class, $res[1]);
    }

    public function testFromAssortedArray()
    {
        $pid = static::factory()->post->create();
        $a = static::factory()->comment->create([
            'comment_post_ID' => $pid,
            'comment_content' => 'eyyyy',
        ]);
        $b = static::factory()->comment->create([
            'comment_post_ID' => $pid,
            'comment_content' => 'beeee',
        ]);
        $c = static::factory()->comment->create([
            'comment_post_ID' => $pid,
            'comment_content' => 'seeee',
        ]);

        $commentFactory = new CommentFactory();

        // Pass an ID, a WP_Comment instance, and a \Timber\Comment instance
        $res = $commentFactory->from([
            $a,
            \get_comment($b),
            $commentFactory->from($c),
        ]);

        $this->assertCount(3, $res);
        $this->assertInstanceOf(Comment::class, $res[0]);
        $this->assertInstanceOf(Comment::class, $res[1]);
        $this->assertInstanceOf(Comment::class, $res[2]);
    }

    public function testFromQueryArray()
    {
        $pid = static::factory()->post->create();
        $a = static::factory()->comment->create([
            'comment_post_ID' => $pid,
            'comment_content' => 'eyyyy',
        ]);
        $b = static::factory()->comment->create([
            'comment_post_ID' => $pid,
            'comment_content' => 'beeee',
        ]);
        $c = static::factory()->comment->create([
            'comment_post_ID' => $pid,
            'comment_content' => 'seeee',
        ]);

        $commentFactory = new CommentFactory();

        $res = $commentFactory->from([
            'post_id' => $pid,
        ]);

        $this->assertCount(3, $res);
        $this->assertInstanceOf(Comment::class, $res[0]);
        $this->assertInstanceOf(Comment::class, $res[1]);
        $this->assertInstanceOf(Comment::class, $res[2]);
    }
}
