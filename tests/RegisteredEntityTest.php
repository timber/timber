<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use stdClass;
use Timber\RegisteredEntity;
use Timber\Timber;

class RegisteredEntityFixture extends RegisteredEntity
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

#[Group('registered-entities')]
class RegisteredEntityTest extends TimberIntegrationTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        \register_taxonomy('fixture_genre', 'post', [
            'public' => true,
            'labels' => [
                'name' => 'Genres',
            ],
        ]);
    }

    public function testBuildImportsTheWordPressObject()
    {
        $wp_taxonomy = \get_taxonomy('fixture_genre');
        $entity = RegisteredEntityFixture::build($wp_taxonomy);

        $this->assertSame($wp_taxonomy, $entity->wp_object());
        $this->assertEquals('fixture_genre', $entity->name);
        $this->assertEquals('Genres', $entity->labels->name);
        $this->assertSame($wp_taxonomy->cap, $entity->cap);
        $this->assertTrue($entity->public);
    }

    public function testConstructorIsNotPublic()
    {
        $constructor = (new ReflectionClass(RegisteredEntity::class))->getConstructor();

        $this->assertTrue($constructor->isProtected());
    }

    public function testToStringIsTheRegisteredName()
    {
        $entity = RegisteredEntityFixture::build(\get_taxonomy('fixture_genre'));

        $this->assertEquals('fixture_genre', (string) $entity);
    }

    public function testTitleIsTheLabel()
    {
        $entity = RegisteredEntityFixture::build(\get_taxonomy('fixture_genre'));

        $this->assertEquals('Genres', $entity->title());
    }

    public function testTitleFallsBackToTheName()
    {
        $wp_taxonomy = clone \get_taxonomy('fixture_genre');
        $wp_taxonomy->labels = new stdClass();

        $entity = RegisteredEntityFixture::build($wp_taxonomy);

        $this->assertEquals('fixture_genre', $entity->title());
    }

    public function testEditLinkForUsersWithRights()
    {
        \wp_set_current_user(static::factory()->user->create([
            'role' => 'administrator',
        ]));

        $entity = RegisteredEntityFixture::build(\get_taxonomy('fixture_genre'));

        $this->assertTrue($entity->can_edit());
        $this->assertEquals(
            \admin_url('edit-tags.php?taxonomy=fixture_genre'),
            $entity->edit_link()
        );
    }

    public function testEditLinkIsNullForUsersWithoutRights()
    {
        \wp_set_current_user(static::factory()->user->create([
            'role' => 'subscriber',
        ]));

        $entity = RegisteredEntityFixture::build(\get_taxonomy('fixture_genre'));

        $this->assertFalse($entity->can_edit());
        $this->assertNull($entity->edit_link());
    }

    public function testTwigRendering()
    {
        $entity = RegisteredEntityFixture::build(\get_taxonomy('fixture_genre'));

        $this->assertEquals(
            'Genres (fixture_genre)',
            Timber::compile_string('{{ entity.title }} ({{ entity }})', [
                'entity' => $entity,
            ])
        );
    }
}
