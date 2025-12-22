<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use Timber\Timber;

#[Group('called-post-constructor')]
class TimberPostTitleTest extends TimberIntegrationTestCase
{
    public function testAmpersandInTitle()
    {
        $post_id = static::factory()->post->create([
            'post_title' => 'Jared & Lauren',
        ]);
        $post = Timber::get_post($post_id);
        $this->assertEquals(\get_the_title($post_id), $post->title());
        $this->assertEquals(\get_the_title($post_id), $post->post_title);
    }
}
