<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use Timber\Post;
use Timber\Timber;

#[Group('called-post-constructor')]
class TimberPostConvertTest extends TimberIntegrationTestCase
{
    public function testConvertWP_Post()
    {
        $post_id = static::factory()->post->create();
        $post = Timber::get_post($post_id);
        $post_id = static::factory()->post->create([
            'post_title' => 'Maybe Child Post',
        ]);
        $posts = \get_posts([
            'post__in' => [$post_id],
        ]);
        $converted = $post->convert($posts[0]);
        $this->assertEquals($post_id, $converted->id);
        $this->assertEquals(Post::class, $converted::class);
    }

    public function testConvertSingleItemArray()
    {
        $post_id = static::factory()->post->create();
        $post = Timber::get_post($post_id);
        $post_id = static::factory()->post->create([
            'post_title' => 'Maybe Child Post',
        ]);
        $posts = \get_posts([
            'post__in' => [$post_id],
        ]);
        $converted = $post->convert($posts);
        $this->assertEquals($post_id, $converted[0]->id);
        $this->assertEquals(Post::class, $converted[0]::class);
    }

    public function testConvertArray()
    {
        $post_ids = static::factory()->post->create_many(8, [
            'post_title' => 'Sample Post ' . \random_int(1, 999),
        ]);

        $post_id = static::factory()->post->create();
        $post = Timber::get_post($post_id);
        $posts = \get_posts([
            'post__in' => $post_ids,
            'orderby' => 'post__in',
        ]);
        $converted = $post->convert($posts);
        $this->assertEquals($post_ids[2], $converted[2]->id);
        $this->assertEquals(Post::class, $converted[3]::class);
    }

    public function testNestedArray()
    {
        $post_ids = static::factory()->post->create_many(8, [
            'post_title' => 'Sample Post ' . \random_int(1, 999),
        ]);

        $post_id = static::factory()->post->create();
        $post = Timber::get_post($post_id);
        $posts = \get_posts([
            'post__in' => $post_ids,
            'orderby' => 'post__in',
        ]);
        $arr = [$post, $posts];

        $converted = $post->convert($arr);
        $this->assertEquals($post_ids[2], $converted[1][2]->id);
        $this->assertEquals(Post::class, $converted[1][3]::class);
    }
}
