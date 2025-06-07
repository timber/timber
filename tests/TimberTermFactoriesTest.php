<?php

namespace Timber\Tests;

use Mantle\Testing\Concerns\Refresh_Database;
use PHPUnit\Framework\Attributes\Group;
use Timber\Timber;

#[Group('terms-api')]
class TimberTermFactoriesTest extends TimberIntegrationTestCase
{
    use Refresh_Database;

    public function testGetTerm()
    {
        $term_id = static::factory()->term->create([
            'name' => 'Thingo',
            'taxonomy' => 'post_tag',
        ]);
        $term = Timber::get_term($term_id);
        $this->assertEquals('Thingo', $term->name);
    }
}
