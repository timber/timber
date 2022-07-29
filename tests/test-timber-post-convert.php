<?php

/**
 * @group called-post-constructor
 */
class TestTimberPostConvert extends Timber_UnitTestCase
{
    public function testConvertWP_Post()
    {
        $post_id = $this->factory->post->create();
        $post = Timber::get_post($post_id);
        $post_id = $this->factory->post->create([
            'post_title' => 'Maybe Child Post',
        ]);
        $posts = get_posts([
            'post__in' => [$post_id],
        ]);
        $converted = $post->convert($posts[0]);
        $this->assertEquals($post_id, $converted->id);
        $this->assertEquals('Timber\Post', get_class($converted));
    }

    public function testConvertSingleItemArray()
    {
        $post_id = $this->factory->post->create();
        $post = Timber::get_post($post_id);
        $post_id = $this->factory->post->create([
            'post_title' => 'Maybe Child Post',
        ]);
        $posts = get_posts([
            'post__in' => [$post_id],
        ]);
        $converted = $post->convert($posts);
        $this->assertEquals($post_id, $converted[0]->id);
        $this->assertEquals('Timber\Post', get_class($converted[0]));
    }

    public function testConvertArray()
    {
        $post_ids = $this->factory->post->create_many(8, [
            'post_title' => 'Sample Post ' . rand(1, 999),
        ]);

        $post_id = $this->factory->post->create();
        $post = Timber::get_post($post_id);
        $posts = get_posts([
            'post__in' => $post_ids,
            'orderby' => 'post__in',
        ]);
        $converted = $post->convert($posts);
        $this->assertEquals($post_ids[2], $converted[2]->id);
        $this->assertEquals('Timber\Post', get_class($converted[3]));
    }

    public function testNestedArray()
    {
        $post_ids = $this->factory->post->create_many(8, [
            'post_title' => 'Sample Post ' . rand(1, 999),
        ]);

        $post_id = $this->factory->post->create();
        $post = Timber::get_post($post_id);
        $posts = get_posts([
            'post__in' => $post_ids,
            'orderby' => 'post__in',
        ]);
        $arr = [$post, $posts];

        $converted = $post->convert($arr);
        $this->assertEquals($post_ids[2], $converted[1][2]->id);
        $this->assertEquals('Timber\Post', get_class($converted[1][3]));
    }
}
