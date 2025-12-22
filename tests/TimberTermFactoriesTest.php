<?php

namespace Timber\Tests;

use Mantle\Testing\Concerns\Refresh_Database;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use Timber\Term;
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

    #[IgnoreDeprecations]
    public function testGetMultiTerm()
    {
        $this->setExpectedDeprecated('Term::from()');
        \register_taxonomy('cars', 'post');
        $term_ids[] = static::factory()->term->create([
            'name' => 'Toyota',
            'taxonomy' => 'cars',
        ]);
        $term_ids[] = static::factory()->term->create([
            'name' => 'Honda',
            'taxonomy' => 'cars',
        ]);
        $term_ids[] = static::factory()->term->create([
            'name' => 'Chevy',
            'taxonomy' => 'cars',
        ]);
        $post_id = static::factory()->post->create();
        \wp_set_object_terms($post_id, $term_ids, 'cars');

        $term_get = Timber::get_terms([
            'taxonomy' => 'cars',
        ]);
        $this->assertEquals('Chevy', $term_get[0]->title());

        $terms_from = Term::from(\get_terms([
            'taxonomy' => 'cars',
        ]), 'cars');
        $this->assertEquals('Chevy', $terms_from[0]->title());
    }

    #[IgnoreDeprecations]
    public function testTermFrom()
    {
        $this->setExpectedDeprecated('Term::from()');
        \register_taxonomy('baseball', ['post']);
        \register_taxonomy('hockey', ['post']);
        $term_id = static::factory()->term->create([
            'name' => 'Rangers',
            'taxonomy' => 'baseball',
        ]);
        $term_id = static::factory()->term->create([
            'name' => 'Cardinals',
            'taxonomy' => 'baseball',
        ]);
        $term_id = static::factory()->term->create([
            'name' => 'Rangers',
            'taxonomy' => 'hockey',
        ]);
        $baseball_teams = Term::from(\get_terms([
            'taxonomy' => 'baseball',
            'hide_empty' => false,
        ]), 'baseball');
        $this->assertSame(2, \count($baseball_teams));
        $this->assertEquals('Cardinals', $baseball_teams[0]->name);
    }

    #[IgnoreDeprecations]
    public function testGetSingleTermFrom()
    {
        $this->setExpectedDeprecated('Term::from()');
        \register_taxonomy('cars', 'post');
        $term_id = static::factory()->term->create([
            'name' => 'Toyota',
            'taxonomy' => 'cars',
        ]);
        $post_id = static::factory()->post->create();
        \wp_set_object_terms($post_id, $term_id, 'cars');

        $term_from = Term::from(\get_terms([
            'taxonomy' => 'cars',
            'hide_empty' => false,
        ]), 'cars');
        $this->assertEquals($term_id, $term_from[0]->ID);

        $term_get = Timber::get_term([
            'taxonomy' => 'cars',
        ]);
        $this->assertEquals($term_id, $term_get->ID);
    }
}
