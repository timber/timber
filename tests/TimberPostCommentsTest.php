<?php

namespace Timber\Tests;

use CustomComment;
use PHPUnit\Framework\Attributes\Group;
use Timber\Timber;

require_once __DIR__ . '/Support/CustomComment.php';

#[Group('posts-api')]
#[Group('comments-api')]
#[Group('called-post-constructor')]
class TimberPostCommentsTest extends TimberIntegrationTestCase
{
    public function testComments()
    {
        $post_id = static::factory()->post->create([
            'post_title' => 'Gobbles',
        ]);
        $comment_id_array = static::factory()->comment->create_many(5, [
            'comment_post_ID' => $post_id,
        ]);
        $post = Timber::get_post($post_id);
        $this->assertSame(5, \count($post->comments()));
        $this->assertSame(5, $post->comment_count());
    }

    public function testCommentCount()
    {
        $post_id = static::factory()->post->create([
            'post_title' => 'Gobbles',
        ]);
        $comment_id_array = static::factory()->comment->create_many(5, [
            'comment_post_ID' => $post_id,
        ]);
        $post = Timber::get_post($post_id);
        $this->assertSame(2, \count($post->comments(2)));
        $this->assertSame(5, \count($post->comments()));
    }

    public function testCommentCountZero()
    {
        $quote = 'Named must your fear be before banish it you can.';
        $post_id = static::factory()->post->create([
            'post_content' => $quote,
        ]);
        $post = Timber::get_post($post_id);
        $this->assertSame(0, $post->comment_count());
    }

    public function testShowUnmoderatedCommentIfByLoggedInUser()
    {
        $post_id = static::factory()->post->create();
        $uid = static::factory()->user->create();
        \wp_set_current_user($uid);
        $quote = "You know, I always wanted to pretend I was an architect";
        $comment_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
            'comment_content' => $quote,
            'user_id' => $uid,
            'comment_approved' => 0,
        ]);
        $post = Timber::get_post($post_id);
        $this->assertSame(1, \count($post->comments()));
        \wp_set_current_user(0);
        $post = Timber::get_post($post_id);
        $this->assertSame(0, \count($post->comments()));
    }

    public function testPostWithCustomCommentClass()
    {
        $post_id = static::factory()->post->create([
            'post_title' => 'Gobbles',
        ]);
        $comment_id_array = static::factory()->comment->create_many(5, [
            'comment_post_ID' => $post_id,
        ]);
        $post = Timber::get_post($post_id);

        $filter = (fn () => [
            'post' => CustomComment::class,
        ]);
        \add_filter('timber/comment/classmap', $filter);

        $comments = $post->comments(null, 'wp', 'comment', 'approve', '\CustomComment');
        $this->assertEquals(CustomComment::class, $comments[0]::class);

        \remove_filter('timber/comment/classmap', $filter);
    }

    public function testShowUnmoderatedCommentIfByCurrentUser()
    {
        $post_id = static::factory()->post->create();
        \add_filter('wp_get_current_commenter', function ($author_data) {
            $author_data['comment_author_email'] = 'jarednova@upstatement.com';
            return $author_data;
        });
        $commenter = \wp_get_current_commenter();
        $quote = "And in that moment, I was a marine biologist";
        $comment_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
            'comment_content' => $quote,
            'comment_approved' => 0,
            'comment_author_email' => 'jarednova@upstatement.com',
        ]);
        $post = Timber::get_post($post_id);
        $this->assertSame(1, \count($post->comments()));
    }

    public function testMultilevelThreadedComments()
    {
        \update_option('comment_order', 'ASC');
        $post_id = static::factory()->post->create([
            'post_title' => 'Gobbles',
        ]);
        $comment_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
        ]);
        $child_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
            'comment_parent' => $comment_id,
        ]);
        $grandchild_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
            'comment_parent' => $child_id,
        ]);
        $grandchild_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
            'comment_parent' => $child_id,
        ]);
        $post = Timber::get_post($post_id);
        $comments = $post->comments();
        $this->assertSame(1, \count($comments));
        $children = $comments[0]->children();
        $this->assertSame(1, \count($children));
        $grand_children = $children[0]->children();
        $this->assertSame(2, \count($grand_children));
    }

    public function testMultilevelThreadedCommentsCorrectParents()
    {
        \update_option('comment_order', 'ASC');
        $post_id = static::factory()->post->create([
            'post_title' => 'Gobbles',
            'post_date' => '2016-11-28 12:00:00',
        ]);
        $uncle_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
            'comment_date' => '2016-11-28 13:00:00',
            'comment_content' => 'i am the UNCLE',
        ]);
        $parent_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
            'comment_date' => '2016-11-28 14:00:00',
            'comment_content' => 'i am the Parent',
        ]);
        $child_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
            'comment_parent' => $parent_id,
            'comment_date' => '2016-11-28 15:00:00',
            'comment_content' => 'I am the child',
        ]);
        $grandchild_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
            'comment_parent' => $child_id,
            'comment_date' => '2016-11-28 16:00:00',
            'comment_content' => 'I am the GRANDchild',
        ]);
        $post = Timber::get_post($post_id);
        $comments = $post->comments();
        $children = $comments[1]->children();
        $this->assertEquals($parent_id, $children[0]->comment_parent);
        $grand_children = $children[0]->children();
        $grandchild = $grand_children[0];
        $this->assertEquals($child_id, $grandchild->comment_parent);
    }

    public function testThreadedCommentsWithTemplate()
    {
        $post_id = static::factory()->post->create([
            'post_title' => 'Gobbles',
        ]);
        $comment_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
            'comment_content' => 'oldest!',
            'comment_date' => '2016-11-28 12:58:18',
        ]);
        $comment2_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
            'comment_content' => 'newest!',
            'comment_date' => '2016-11-28 13:58:18',
        ]);
        $comment2_child_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
            'comment_parent' => $comment2_id,
            'comment_content' => 'a]response',
            'comment_date' => '2016-11-28 14:58:18',
        ]);
        $comment2_grandchild_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
            'comment_parent' => $comment2_child_id,
            'comment_content' => 'Respond2Respond',
            'comment_date' => '2016-11-28 15:58:18',
        ]);
        $post = Timber::get_post($post_id);
        $str = Timber::compile('assets/comments-thread.twig', [
            'post' => $post,
        ]);
        $str = \preg_replace('/\s+/', ' ', $str);
        $this->assertEquals('<article data-depth="0"> <p><p>newest!</p></p> <article data-depth="1"> <p><p>a]response</p></p> <article data-depth="2"> <p><p>Respond2Respond</p></p> </article> </article> </article> <article data-depth="0"> <p><p>oldest!</p></p> </article>', \trim((string) $str));
    }
}
