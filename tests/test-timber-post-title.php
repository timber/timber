<?php

/**
 * @group called-post-constructor
 */
class TestTimberPostTitle extends Timber_UnitTestCase
{
    public function testAmpersandInTitle()
    {
        $post_id = $this->factory->post->create([
            'post_title' => 'Jared & Lauren',
        ]);
        $post = Timber::get_post($post_id);
        $this->assertEquals(get_the_title($post_id), $post->title());
        $this->assertEquals(get_the_title($post_id), $post->post_title);
    }
}
