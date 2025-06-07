<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use Timber\Site;
use Timber\Timber;

#[Group('users-api')]
#[Group('comments-api')]
#[Group('called-post-constructor')]
#[Group('called-term-constructor')]
class TimberPropertyTest extends TimberIntegrationTestCase
{
    public function testPropertyID()
    {
        $post_id = static::factory()->post->create();
        $user_id = static::factory()->user->create();
        $comment_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
        ]);
        $term_id = \wp_insert_term('baseball', 'post_tag');
        $term_id = $term_id['term_id'];
        $post = Timber::get_post($post_id);
        $user = Timber::get_user($user_id);
        $term = Timber::get_term($term_id);
        $comment = Timber::get_comment($comment_id);
        $this->assertEquals($post_id, $post->ID);
        $this->assertEquals($post_id, $post->id);
        $this->assertEquals($user_id, $user->ID);
        $this->assertEquals($user_id, $user->id);
        $this->assertEquals($term_id, $term->ID);
        $this->assertEquals($term_id, $term->id);
        $this->assertEquals($comment_id, $comment->ID);
        $this->assertEquals($comment_id, $comment->id);
    }

    protected function _initObjects()
    {
        $post_id = static::factory()->post->create();
        $user_id = static::factory()->user->create();
        $comment_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
        ]);
        $term_id = \wp_insert_term('baseball', 'post_tag');
        $term_id = $term_id['term_id'];
        $post = Timber::get_post($post_id);
        $user = Timber::get_user($user_id);
        $term = Timber::get_term($term_id);
        $comment = Timber::get_comment($comment_id);
        $site = new Site();
        return [
            'post' => $post,
            'user' => $user,
            'term' => $term,
            'comment' => $comment,
            'site' => $site,
        ];
    }

    public function testMetaForTerm()
    {
        $vars = $this->_initObjects();
        \extract($vars);
        \update_term_meta($term->ID, 'abraham', 'lincoln');
        $this->assertEquals('lincoln', $term->abraham);
        $this->assertEquals('lincoln', Timber::compile_string('{{term.abraham}}', [
            'term' => $term,
        ]));
    }
}
