<?php

namespace Timber\Tests\Factory;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use Timber\Factory\RegisteredEntityFactory;
use Timber\RegisteredEntity;
use Timber\Tests\TimberIntegrationTestCase;
use WP_Post;
use WP_Taxonomy;

class FixtureEntity extends RegisteredEntity
{
    protected function edit_capability(): string
    {
        return $this->cap->manage_terms;
    }

    protected function edit_path(): string
    {
        return 'edit-tags.php?taxonomy=' . $this->name;
    }
}

class SpecialFixtureEntity extends FixtureEntity
{
}

class FixtureFactory extends RegisteredEntityFactory
{
    protected function entity_class(): string
    {
        return FixtureEntity::class;
    }

    protected function wp_object_class(): string
    {
        return WP_Taxonomy::class;
    }

    protected function get_wp_object(string $name): ?object
    {
        return \get_taxonomy($name) ?: null;
    }

    protected function query_names(array $args): array
    {
        return \get_taxonomies($args);
    }

    protected function apply_classmap_filter(): array
    {
        return \apply_filters('timber/test_fixture/classmap', []);
    }

    protected function apply_class_filter(string $class, object $wp_object): string
    {
        return \apply_filters('timber/test_fixture/class', $class, $wp_object);
    }
}

#[Group('factory')]
#[Group('registered-entities')]
class RegisteredEntityFactoryTest extends TimberIntegrationTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        \register_taxonomy('fixture_genre', 'post', [
            'public' => true,
            'hierarchical' => true,
        ]);
        \register_taxonomy('fixture_mood', 'post', [
            'public' => true,
        ]);
    }

    public function testFromName()
    {
        $this->assertInstanceOf(FixtureEntity::class, (new FixtureFactory())->from('fixture_genre'));
    }

    public function testFromUnregisteredName()
    {
        $this->assertNull((new FixtureFactory())->from('not-registered'));
    }

    public function testFromNames()
    {
        $entities = (new FixtureFactory())->from(['fixture_genre', 'fixture_mood']);

        $this->assertEquals(['fixture_genre', 'fixture_mood'], \array_keys($entities));
        $this->assertContainsOnlyInstancesOf(FixtureEntity::class, $entities);
    }

    public function testFromNamesSkipsUnregisteredNames()
    {
        $entities = (new FixtureFactory())->from(['fixture_genre', 'not-registered']);

        $this->assertEquals(['fixture_genre'], \array_keys($entities));
    }

    public function testFromQueryArgs()
    {
        $entities = (new FixtureFactory())->from([
            'hierarchical' => true,
            'public' => true,
        ]);

        $this->assertArrayHasKey('fixture_genre', $entities);
        $this->assertArrayNotHasKey('fixture_mood', $entities);
    }

    public function testFromWpObject()
    {
        $entity = (new FixtureFactory())->from(\get_taxonomy('fixture_genre'));

        $this->assertInstanceOf(FixtureEntity::class, $entity);
        $this->assertEquals('fixture_genre', $entity->name);
    }

    public function testFromEntityIsIdempotent()
    {
        $factory = new FixtureFactory();
        $entity = $factory->from('fixture_genre');

        $this->assertSame($entity, $factory->from($entity));
    }

    public function testFromOtherTimberObjectThrows()
    {
        $factory = new FixtureFactory();
        $other = new class() extends RegisteredEntity {
            public function __construct()
            {
            }

            protected function edit_capability(): string
            {
                return '';
            }

            protected function edit_path(): string
            {
                return '';
            }
        };

        $this->expectException(InvalidArgumentException::class);

        $factory->from($other);
    }

    public function testFromInvalidObject()
    {
        $this->expectException(InvalidArgumentException::class);

        (new FixtureFactory())->from(new WP_Post((object) []));
    }

    public function testFromInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);

        (new FixtureFactory())->from(123);
    }

    public function testClassMapArray()
    {
        $this->add_filter_temporarily('timber/test_fixture/classmap', fn () => [
            'fixture_genre' => SpecialFixtureEntity::class,
        ]);

        $entities = (new FixtureFactory())->from(['fixture_genre', 'fixture_mood']);

        $this->assertInstanceOf(SpecialFixtureEntity::class, $entities['fixture_genre']);
        $this->assertNotInstanceOf(SpecialFixtureEntity::class, $entities['fixture_mood']);
    }

    public function testClassMapCallable()
    {
        $this->add_filter_temporarily('timber/test_fixture/classmap', fn () => [
            'fixture_genre' => fn (WP_Taxonomy $taxonomy) => $taxonomy->hierarchical
               ? SpecialFixtureEntity::class
               : FixtureEntity::class,
            'fixture_mood' => fn (WP_Taxonomy $taxonomy) => $taxonomy->hierarchical
               ? SpecialFixtureEntity::class
               : FixtureEntity::class,
        ]);

        $entities = (new FixtureFactory())->from(['fixture_genre', 'fixture_mood']);

        $this->assertInstanceOf(SpecialFixtureEntity::class, $entities['fixture_genre']);
        $this->assertNotInstanceOf(SpecialFixtureEntity::class, $entities['fixture_mood']);
    }

    public function testClassFilter()
    {
        $this->add_filter_temporarily('timber/test_fixture/class', fn ($class, WP_Taxonomy $taxonomy) => 'fixture_mood' === $taxonomy->name
           ? SpecialFixtureEntity::class
           : $class, 10, 2);

        $entities = (new FixtureFactory())->from(['fixture_genre', 'fixture_mood']);

        $this->assertNotInstanceOf(SpecialFixtureEntity::class, $entities['fixture_genre']);
        $this->assertInstanceOf(SpecialFixtureEntity::class, $entities['fixture_mood']);
    }
}
