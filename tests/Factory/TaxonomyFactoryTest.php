<?php

namespace Timber\Tests\Factory;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Ticket;
use Timber\Factory\TaxonomyFactory;
use Timber\Taxonomy;
use Timber\Tests\TimberIntegrationTestCase;
use WP_Post;

class GenreTaxonomy extends Taxonomy
{
}
class HierarchicalTaxonomy extends Taxonomy
{
}

#[Group('factory')]
#[Group('terms-api')]
#[Ticket('#3282')]
class TaxonomyFactoryTest extends TimberIntegrationTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        \register_taxonomy('genre', 'post', [
            'public' => true,
            'hierarchical' => true,
        ]);
        \register_taxonomy('mood', 'post', [
            'public' => true,
        ]);
    }

    public function testFromName()
    {
        $factory = new TaxonomyFactory();

        $this->assertInstanceOf(Taxonomy::class, $factory->from('genre'));
    }

    public function testFromUnregisteredName()
    {
        $factory = new TaxonomyFactory();

        $this->assertNull($factory->from('not-a-taxonomy'));
    }

    public function testFromNames()
    {
        $factory = new TaxonomyFactory();
        $taxonomies = $factory->from(['genre', 'mood']);

        $this->assertEquals(['genre', 'mood'], \array_keys($taxonomies));
        $this->assertContainsOnlyInstancesOf(Taxonomy::class, $taxonomies);
    }

    public function testFromQueryArgs()
    {
        $factory = new TaxonomyFactory();
        $taxonomies = $factory->from([
            'hierarchical' => true,
            'public' => true,
        ]);

        $this->assertArrayHasKey('genre', $taxonomies);
        $this->assertArrayNotHasKey('mood', $taxonomies);
    }

    public function testFromWpTaxonomy()
    {
        $factory = new TaxonomyFactory();

        $this->assertInstanceOf(Taxonomy::class, $factory->from(\get_taxonomy('genre')));
    }

    public function testFromTimberTaxonomyIsIdempotent()
    {
        $factory = new TaxonomyFactory();
        $taxonomy = $factory->from('genre');

        $this->assertSame($taxonomy, $factory->from($taxonomy));
    }

    public function testFromInvalidObject()
    {
        $factory = new TaxonomyFactory();

        $this->expectException(InvalidArgumentException::class);

        $factory->from(new WP_Post((object) []));
    }

    public function testFromInvalidType()
    {
        $factory = new TaxonomyFactory();

        $this->expectException(InvalidArgumentException::class);

        $factory->from(123);
    }

    public function testClassMap()
    {
        $this->add_filter_temporarily('timber/taxonomy/classmap', fn () => [
            'genre' => GenreTaxonomy::class,
        ]);

        $factory = new TaxonomyFactory();

        $this->assertInstanceOf(GenreTaxonomy::class, $factory->from('genre'));
        $this->assertInstanceOf(Taxonomy::class, $factory->from('mood'));
        $this->assertNotInstanceOf(GenreTaxonomy::class, $factory->from('mood'));
    }

    public function testClassMapCallable()
    {
        $this->add_filter_temporarily('timber/taxonomy/classmap', fn () => [
            'genre' => fn ($taxonomy) => $taxonomy->hierarchical
               ? HierarchicalTaxonomy::class
               : Taxonomy::class,
        ]);

        $factory = new TaxonomyFactory();

        $this->assertInstanceOf(HierarchicalTaxonomy::class, $factory->from('genre'));
    }

    public function testTaxonomyClassFilter()
    {
        $this->add_filter_temporarily(
            'timber/taxonomy/class',
            fn ($class, $taxonomy) => 'mood' === $taxonomy->name ? GenreTaxonomy::class : $class,
            10,
            2
        );

        $factory = new TaxonomyFactory();

        $this->assertInstanceOf(GenreTaxonomy::class, $factory->from('mood'));
    }
}
