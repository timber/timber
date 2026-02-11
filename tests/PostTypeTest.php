<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Ticket;
use Timber\PostType;
use Timber\Timber;

#[Group('called-post-constructor')]
class PostTypeTest extends TimberIntegrationTestCase
{
    public function testPostTypeObject()
    {
        $obj = \get_post_type_object('post');
        $this->assertEquals('Posts', $obj->labels->name);
    }

    public function testPostTypeProperty()
    {
        $post_id = static::factory()->post->create();
        $post = Timber::get_post($post_id);
        $this->assertEquals('post', $post->post_type);
    }

    #[Ticket('#2111')]
    public function testNonExistentPostType()
    {
        $post_type = new PostType('foobar');
        $this->assertEquals('foobar', $post_type);
        $this->assertEquals(PostType::class, $post_type::class);
    }

    public function testPostTypeMethodInTwig()
    {
        $post_id = static::factory()->post->create();
        $post = Timber::get_post($post_id);
        $template = '{{post.post_type}}';
        $str = Timber::compile_string($template, [
            'post' => $post,
        ]);
        $this->assertEquals('post', $str);
    }

    public function testTypeMethodInTwig()
    {
        $post_id = static::factory()->post->create();
        $post = Timber::get_post($post_id);
        $template = '{{post.type}}';
        $str = Timber::compile_string($template, [
            'post' => $post,
        ]);
        $this->assertEquals('post', $str);
    }

    public function testTypeMethodInTwigLabels()
    {
        $post_id = static::factory()->post->create();
        $post = Timber::get_post($post_id);
        $template = '{{post.type.labels.name}}';
        $str = Timber::compile_string($template, [
            'post' => $post,
        ]);
        $this->assertEquals('Posts', $str);
    }
}
