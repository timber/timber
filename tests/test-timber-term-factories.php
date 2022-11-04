<?php

/**
 * @group terms-api
 */
class TestTimberTermFactories extends Timber_UnitTestCase
{
    public function set_up()
    {
        $this->truncate('term_relationships');
        $this->truncate('term_taxonomy');
        $this->truncate('terms');
        $this->truncate('termmeta');
        parent::set_up();
    }

    public function testGetTerm()
    {
        $term_id = $this->factory->term->create([
            'name' => 'Thingo',
            'taxonomy' => 'post_tag',
        ]);
        $term = Timber::get_term($term_id);
        $this->assertEquals('Thingo', $term->name);
    }

    /**
     * @expectedDeprecated Term::from()
     */
    public function testGetMultiTerm()
    {
        register_taxonomy('cars', 'post');
        $term_ids[] = $this->factory->term->create([
            'name' => 'Toyota',
            'taxonomy' => 'cars',
        ]);
        $term_ids[] = $this->factory->term->create([
            'name' => 'Honda',
            'taxonomy' => 'cars',
        ]);
        $term_ids[] = $this->factory->term->create([
            'name' => 'Chevy',
            'taxonomy' => 'cars',
        ]);
        $post_id = $this->factory->post->create();
        wp_set_object_terms($post_id, $term_ids, 'cars');

        $term_get = Timber::get_terms([
            'taxonomy' => 'cars',
        ]);
        $this->assertEquals('Chevy', $term_get[0]->title());

        $terms_from = Timber\Term::from(get_terms([
            'taxonomy' => 'cars',
        ]), 'cars');
        $this->assertEquals('Chevy', $terms_from[0]->title());
    }

    /**
     * @expectedDeprecated Term::from()
     */
    public function testTermFrom()
    {
        register_taxonomy('baseball', ['post']);
        register_taxonomy('hockey', ['post']);
        $term_id = $this->factory->term->create([
            'name' => 'Rangers',
            'taxonomy' => 'baseball',
        ]);
        $term_id = $this->factory->term->create([
            'name' => 'Cardinals',
            'taxonomy' => 'baseball',
        ]);
        $term_id = $this->factory->term->create([
            'name' => 'Rangers',
            'taxonomy' => 'hockey',
        ]);
        $baseball_teams = Timber\Term::from(get_terms([
            'taxonomy' => 'baseball',
            'hide_empty' => false,
        ]), 'baseball');
        $this->assertSame(2, count($baseball_teams));
        $this->assertEquals('Cardinals', $baseball_teams[0]->name);
    }

    /**
     * @expectedDeprecated Term::from()
     */
    public function testGetSingleTermFrom()
    {
        register_taxonomy('cars', 'post');
        $term_id = $this->factory->term->create([
            'name' => 'Toyota',
            'taxonomy' => 'cars',
        ]);
        $post_id = $this->factory->post->create();
        wp_set_object_terms($post_id, $term_id, 'cars');

        $term_from = Timber\Term::from(get_terms([
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
