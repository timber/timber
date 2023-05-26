<?php

/**
 * @group comments-api
 * @group called-post-constructor
 */
class TestTimberCommentThread extends Timber_UnitTestCase
{
    public function testCommentThreadWithArgs()
    {
        $post_id = $this->factory->post->create([
            'post_title' => 'Gobbles',
        ]);
        $comment_id_array = $this->factory->comment->create_many(5, [
            'comment_post_ID' => $post_id,
        ]);
        $args = [];
        $ct = new Timber\CommentThread($post_id, $args);
        $this->assertSame(5, count($ct));
    }

    public function testCommentThreadCountMethod()
    {
        $post_id = $this->factory->post->create([
            'post_title' => 'Gobbles',
        ]);
        $comment_id_array = $this->factory->comment->create_many(5, [
            'comment_post_ID' => $post_id,
        ]);
        $args = [];
        $ct = new Timber\CommentThread($post_id, $args);
        $this->assertSame(5, $ct->count());
    }

    public function testShowUnmoderatedCommentIfByAnon()
    {
        global $wp_version;
        $post_id = $this->factory->post->create();

        $quote = "And in that moment, I was a marine biologist";
        $comment_id = $this->factory->comment->create([
            'comment_post_ID' => $post_id,
            'comment_content' => $quote,
            'comment_approved' => 0,
            'comment_author_email' => 'jarednova@upstatement.com',
        ]);

        $comment = get_comment($comment_id);

        $post = Timber::get_post($post_id);
        $this->assertSame(0, count($post->comments()));

        $_GET['unapproved'] = $comment->comment_ID;
        $_GET['moderation-hash'] = wp_hash($comment->comment_date_gmt);
        $post = Timber::get_post($post_id);
        if (!function_exists('wp_get_unapproved_comment_author_email')) {
            $this->assertSame(0, count($post->comments()));
        } else {
            $timber_comment = $post->comments()[0];
            $this->assertEquals($quote, $timber_comment->comment_content);
        }
    }
}
