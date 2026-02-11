<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use Timber\Timber;

#[Group('called-post-constructor')]
class TimberParentChildTest extends TimberIntegrationTestCase
{
    public function testParentChildGeneral()
    {
        \switch_theme('timber-test-theme-child');
        \register_post_type('course');

        $pid = static::factory()->post->create();
        $post = Timber::get_post($pid);
        $str = Timber::compile(['single-course.twig', 'single.twig'], [
            'post' => $post,
        ]);
        $this->assertEquals('I am single course', $str);

        \switch_theme('default');
    }
}
