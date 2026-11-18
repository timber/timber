<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Ticket;
use Timber\PostType;
use Timber\Taxonomy;
use Timber\Term;
use Timber\Timber;
use WP_Taxonomy;

class Genre extends Taxonomy
{
    public function top_level_terms(): iterable
    {
        return $this->terms([
            'parent' => 0,
            'hide_empty' => false,
            'orderby' => 'name',
        ]);
    }

    public function term_count(): int
    {
        return (int) \wp_count_terms([
            'taxonomy' => $this->name,
            'hide_empty' => false,
        ]);
    }
}

#[Group('terms-api')]
#[Ticket('#3282')]
class TaxonomyTest extends TimberIntegrationTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        \register_post_type('recipe', [
            'public' => true,
        ]);

        \register_taxonomy('genre', ['post', 'recipe'], [
            'public' => true,
            'hierarchical' => true,
            'labels' => [
                'name' => 'Genres',
                'all_items' => 'All Genres',
            ],
        ]);

        \register_taxonomy('mood', 'post', [
            'public' => false,
        ]);
    }

    public function testGetTaxonomy()
    {
        $taxonomy = Timber::get_taxonomy('genre');

        $this->assertInstanceOf(Taxonomy::class, $taxonomy);
        $this->assertEquals('genre', $taxonomy->name);
        $this->assertEquals('All Genres', $taxonomy->labels->all_items);
        $this->assertTrue($taxonomy->hierarchical);
        $this->assertInstanceOf(WP_Taxonomy::class, $taxonomy->wp_object());
    }

    public function testGetTaxonomyFromWpTaxonomy()
    {
        $taxonomy = Timber::get_taxonomy(\get_taxonomy('genre'));

        $this->assertInstanceOf(Taxonomy::class, $taxonomy);
        $this->assertEquals('genre', $taxonomy->name);
    }

    public function testGetUnregisteredTaxonomy()
    {
        $this->assertNull(Timber::get_taxonomy('not-a-taxonomy'));
    }

    public function testToString()
    {
        $this->assertEquals('genre', (string) Timber::get_taxonomy('genre'));
    }

    public function testTitle()
    {
        $this->assertEquals('Genres', Timber::get_taxonomy('genre')->title());
    }

    public function testTitleInTwig()
    {
        $this->assertEquals('Genres', Timber::compile_string("{{ get_taxonomy('genre').title }}"));
    }

    public function testDefaultTerm()
    {
        \register_taxonomy('flavour', 'post', [
            'default_term' => [
                'name' => 'Neutral',
            ],
        ]);

        $default_term = Timber::get_taxonomy('flavour')->default_term();

        $this->assertInstanceOf(Term::class, $default_term);
        $this->assertEquals('Neutral', $default_term->title());
    }

    public function testDefaultTermIsNullWhenNoneIsRegistered()
    {
        $this->assertNull(Timber::get_taxonomy('genre')->default_term());
    }

    public function testEditLink()
    {
        \wp_set_current_user(static::factory()->user->create([
            'role' => 'administrator',
        ]));

        $taxonomy = Timber::get_taxonomy('genre');

        $this->assertTrue($taxonomy->can_edit());
        $this->assertStringContainsString('edit-tags.php?taxonomy=genre', $taxonomy->edit_link());
    }

    public function testEditLinkIsNullForUsersWithoutRights()
    {
        \wp_set_current_user(static::factory()->user->create([
            'role' => 'subscriber',
        ]));

        $taxonomy = Timber::get_taxonomy('genre');

        $this->assertFalse($taxonomy->can_edit());
        $this->assertNull($taxonomy->edit_link());
    }

    public function testObjectTypeHoldsPostTypes()
    {
        $taxonomy = Timber::get_taxonomy('genre');

        $this->assertEquals(['post', 'recipe'], $taxonomy->object_type);

        $post_types = $taxonomy->post_types();

        $this->assertContainsOnlyInstancesOf(PostType::class, $post_types);
        $this->assertEquals(['post', 'recipe'], \array_map(strval(...), $post_types));
    }

    public function testPostTypesExcludesUnregisteredPostTypes()
    {
        $wp_taxonomy = clone \get_taxonomy('genre');
        $wp_taxonomy->object_type[] = 'not-a-post-type';

        $post_types = Timber::get_taxonomy($wp_taxonomy)->post_types();

        $this->assertEquals(['post', 'recipe'], \array_map(strval(...), $post_types));
    }

    public function testTerms()
    {
        static::factory()->term->create_many(3, [
            'taxonomy' => 'genre',
        ]);
        static::factory()->term->create([
            'taxonomy' => 'mood',
        ]);

        $terms = Timber::get_taxonomy('genre')->terms([
            'hide_empty' => false,
        ]);

        $this->assertCount(3, $terms);
        $this->assertContainsOnlyInstancesOf(Term::class, $terms);

        foreach ($terms as $term) {
            $this->assertEquals('genre', $term->taxonomy);
        }
    }

    public function testTermsWithQueryArgs()
    {
        static::factory()->term->create([
            'taxonomy' => 'genre',
            'name' => 'Ambient',
        ]);
        static::factory()->term->create([
            'taxonomy' => 'genre',
            'name' => 'Blues',
        ]);

        $terms = Timber::get_taxonomy('genre')->terms([
            'orderby' => 'name',
            'order' => 'DESC',
            'hide_empty' => false,
        ]);

        $this->assertEquals(['Blues', 'Ambient'], \array_map(strval(...), $terms));
    }

    public function testTermsAreLazy()
    {
        $term_query_count = 0;

        $this->add_filter_temporarily('pre_get_terms', function () use (&$term_query_count) {
            $term_query_count++;
        });

        $taxonomy = Timber::get_taxonomy('genre');

        $this->assertSame(0, $term_query_count, 'Getting a taxonomy should not construct a WP_Term_Query');

        $taxonomy->terms();

        $this->assertGreaterThan(0, $term_query_count, 'Getting terms should construct a WP_Term_Query');
    }

    public function testGetTaxonomies()
    {
        $taxonomies = Timber::get_taxonomies();

        $this->assertArrayHasKey('genre', $taxonomies);
        $this->assertArrayNotHasKey('mood', $taxonomies);
        $this->assertContainsOnlyInstancesOf(Taxonomy::class, $taxonomies);
    }

    public function testGetTaxonomiesByName()
    {
        $taxonomies = Timber::get_taxonomies(['genre', 'mood', 'not-a-taxonomy']);

        $this->assertEquals(['genre', 'mood'], \array_keys($taxonomies));
        $this->assertContainsOnlyInstancesOf(Taxonomy::class, $taxonomies);
    }

    public function testGetTaxonomiesByString()
    {
        $taxonomies = Timber::get_taxonomies('genre');

        $this->assertEquals(['genre'], \array_keys($taxonomies));
    }

    public function testGetTaxonomiesForPostType()
    {
        $taxonomies = Timber::get_taxonomies([
            'post_type' => 'recipe',
        ]);

        $this->assertArrayHasKey('genre', $taxonomies);
        $this->assertArrayNotHasKey('category', $taxonomies);
    }

    public function testGetTaxonomiesForPostTypeCombinedWithOtherArgs()
    {
        $taxonomies = Timber::get_taxonomies([
            'post_type' => 'recipe',
            'hierarchical' => true,
        ]);

        $this->assertEquals(['genre'], \array_keys($taxonomies));

        $taxonomies = Timber::get_taxonomies([
            'post_type' => 'recipe',
            'hierarchical' => false,
        ]);

        $this->assertArrayNotHasKey('genre', $taxonomies);
    }

    public function testGetTaxonomiesInTwig()
    {
        $result = Timber::compile_string("{{ get_taxonomy('genre').labels.all_items }}");

        $this->assertEquals('All Genres', $result);
    }

    public function testGetTaxonomyTermsInTwig()
    {
        $post_id = static::factory()->post->create();
        static::factory()->term->create([
            'taxonomy' => 'genre',
            'name' => 'Ambient',
        ]);
        \wp_set_object_terms($post_id, 'Ambient', 'genre');

        $result = Timber::compile_string(
            "{% for term in get_taxonomy('genre').terms %}{{ term.title }}{% endfor %}"
        );

        $this->assertEquals('Ambient', $result);
    }

    public function testClassMapIsHonoured()
    {
        $this->register_genre_class();

        $this->assertInstanceOf(Genre::class, Timber::get_taxonomy('genre'));
        $this->assertInstanceOf(Genre::class, Timber::get_taxonomies()['genre']);

        $mood = Timber::get_taxonomy('mood');

        $this->assertInstanceOf(Taxonomy::class, $mood);
        $this->assertNotInstanceOf(Genre::class, $mood);
    }

    public function testClassMapMethods()
    {
        $this->register_genre_class();
        $this->create_genre_terms();

        $genre = Timber::get_taxonomy('genre');

        $this->assertEquals('Genres', $genre->title());
        $this->assertEquals(3, $genre->term_count());
        $this->assertEquals(['Ambient', 'Jazz'], \array_map(strval(...), $genre->top_level_terms()));
    }

    public function testClassMapMethodsInTwig()
    {
        $this->register_genre_class();
        $this->create_genre_terms();

        $result = Timber::compile_string(
            "{% set genre = get_taxonomy('genre') %}"
                . '{{ genre.title }} ({{ genre.term_count }}): '
                . "{{ genre.top_level_terms|join(', ') }}"
        );

        $this->assertEquals('Genres (3): Ambient, Jazz', $result);
    }

    private function register_genre_class(): void
    {
        $this->add_filter_temporarily('timber/taxonomy/classmap', fn ($classmap) => \array_merge($classmap, [
            'genre' => Genre::class,
        ]));
    }

    private function create_genre_terms(): void
    {
        $jazz = static::factory()->term->create([
            'taxonomy' => 'genre',
            'name' => 'Jazz',
        ]);
        static::factory()->term->create([
            'taxonomy' => 'genre',
            'name' => 'Bebop',
            'parent' => $jazz,
        ]);
        static::factory()->term->create([
            'taxonomy' => 'genre',
            'name' => 'Ambient',
        ]);
    }
}
