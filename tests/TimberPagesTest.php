<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use Timber\Timber;

#[Group('terms-api')]
#[Group('posts-api')]
class TimberPagesTest extends TimberIntegrationTestCase
{
    public function testTimberPostOnCategoryPage()
    {
        $post_id = static::factory()->post->create();
        $category_id = static::factory()->term->create([
            'taxonomy' => 'category',
            'name' => 'News',
        ]);
        $cat = Timber::get_term($category_id);
        $this->get($cat->path());
        $term = Timber::get_term();
        $this->assertEquals($category_id, $term->ID);
        $this->assertNull(Timber::get_post());
    }
}
