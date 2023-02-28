<?php

/**
 * @group called-post-constructor
 */
class TestTimberParentChild extends Timber_UnitTestCase
{
    public function testParentChildGeneral()
    {
        switch_theme('timber-test-theme-child');
        register_post_type('course');

        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $str = Timber::compile(['single-course.twig', 'single.twig'], [
            'post' => $post,
        ]);
        $this->assertEquals('I am single course', $str);

        switch_theme('default');
    }
}
