<?php

namespace Timber\Tests;

use Mantle\Testing\Concerns\Refresh_Database;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Ticket;
use Timber\Term;
use Timber\Timber;

#[Group('posts-api')]
#[Group('terms-api')]
#[Group('post-terms')]
class TimberPostTermsTest extends TimberIntegrationTestCase
{
    use Refresh_Database;

    public function testPostTerms()
    {
        $pid = static::factory()->post->create();
        $post = Timber::get_post($pid);

        // create a new tag and associate it with the post
        $dummy_tag = \wp_insert_term('whatever', 'post_tag');
        \wp_set_object_terms($pid, $dummy_tag['term_id'], 'post_tag', true);

        $this->add_filter_temporarily('timber/term/classmap', fn () => [
            'post_tag' => MyTimberTerm::class,
        ]);

        $terms = $post->terms([
            'query' => [
                'taxonomy' => 'post_tag',
            ],
        ]);
        $this->assertInstanceOf(MyTimberTerm::class, $terms[0]);

        $post = Timber::get_post($pid);
        $terms = $post->terms([
            'query' => [
                'taxonomy' => 'post_tag',
            ],
            'merge' => true,
        ]);
        $this->assertInstanceOf(MyTimberTerm::class, $terms[0]);
    }

    #[Ticket('#2203')]
    public function testPostTermsUsingUsingFactories()
    {
        $pid = static::factory()->post->create();
        $post = Timber::get_post($pid);

        // create a new tag and associate it with the post
        $dummy_tag_id = self::factory()->term->create([
            'name' => 'whatever',
            'taxonomy' => 'post_tag',
        ]);
        $dummy_cat_id = self::factory()->term->create([
            'name' => 'news',
            'taxonomy' => 'category',
        ]);
        \wp_set_object_terms($pid, $dummy_tag_id, 'post_tag', true);
        // Replace categories (don't append) to avoid including default "Uncategorized"
        \wp_set_object_terms($pid, $dummy_cat_id, 'category', false);

        $this->add_filter_temporarily('timber/term/classmap', fn () => [
            'post_tag' => MyTimberTerm::class,
        ]);

        $terms = $post->terms([
            'taxonomy' => 'post_tag',
        ]);
        $this->assertInstanceOf(MyTimberTerm::class, $terms[0]);

        $post = Timber::get_post($pid);
        $terms = $post->terms([], [
            'merge' => false,
        ]);
        $this->assertEquals('whatever', $terms['post_tag'][0]->name);

        $terms = $post->terms([], [
            'merge' => true,
        ]);
        $this->assertSame(2, \count($terms));
    }

    #[Ticket('#2163
This test confirms that term ordering works when sent through the query parameter of
arguments.')]
    public function testPostTermOrder()
    {
        $pid = static::factory()->post->create();
        \register_taxonomy('cars', 'post');
        $cars[] = static::factory()->term->create([
            'name' => 'Honda Civic',
            'taxonomy' => 'cars',
        ]);
        $cars[] = static::factory()->term->create([
            'name' => 'Toyota Corolla',
            'taxonomy' => 'cars',
        ]);
        $cars[] = static::factory()->term->create([
            'name' => 'Toyota Camry',
            'taxonomy' => 'cars',
        ]);
        $cars[] = static::factory()->term->create([
            'name' => 'Dodge Intrepid',
            'taxonomy' => 'cars',
        ]);
        foreach ($cars as $tid) {
            $car = Timber::get_term($tid);
        }
        \wp_set_object_terms($pid, $cars, 'cars', false);
        $post = Timber::get_post($pid);
        $template = "{% for term_item in post.terms({query : {taxonomy: 'cars', orderby: 'term_id', order: 'ASC'}}) %}{{ term_item.name }} {% endfor %}";
        $str = Timber::compile_string($template, [
            'post' => $post,
        ]);
        $this->assertEquals('Honda Civic Toyota Corolla Toyota Camry Dodge Intrepid ', $str);
    }

    /**
     * This should return an error because the "dfasdf" taxonomy doesn't exist
     * NOTE: In Timber 1.x this returned a WP_Error.
     */
    public function testTermExceptions()
    {
        self::enable_error_log(false);
        $pid = static::factory()->post->create();
        $post = Timber::get_post($pid);
        $terms = $post->terms('dfasdf');
        $this->assertEmpty($terms);
        self::enable_error_log(true);
    }

    /**
     * This shouldn't return an error because the "foobar" taxonomy DOES exist
     */
    public function testTermFromNonExistentTaxonomy()
    {
        self::enable_error_log(false);
        \register_taxonomy('foobar', 'post');
        $pid = static::factory()->post->create();
        $post = Timber::get_post($pid);
        $terms = $post->terms('foobar');
        $this->assertEmpty($terms);
        self::enable_error_log(true);
    }

    public function testTermNotMerged()
    {
        $pid = static::factory()->post->create();

        // create a new tag and category and associate each with the post
        $tag_id = static::factory()->term->create([
            'name' => 'whatever',
            'taxonomy' => 'post_tag',
        ]);
        $cat_id = static::factory()->term->create([
            'name' => 'thingy',
            'taxonomy' => 'category',
        ]);
        \wp_set_object_terms($pid, $tag_id, 'post_tag', true);
        \wp_set_object_terms($pid, $cat_id, 'category', true);

        $post = Timber::get_post($pid);
        $terms = $post->terms([
            'query' => [
                'taxonomy' => 'all',
            ],
            'merge' => false,
        ]);

        $this->assertEquals($terms['post_tag'][0]->name, 'whatever');
        $this->assertEquals($terms['category'][0]->name, 'thingy');
    }
}

class MyTimberTerm extends Term
{
}
