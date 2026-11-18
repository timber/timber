<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Ticket;
use Timber\PostType;
use Timber\Taxonomy;
use Timber\Timber;

#[Group('called-post-constructor')]
class PostTypeTest extends TimberIntegrationTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        \register_post_type('pt_recipe', [
            'public' => true,
            'hierarchical' => true,
            'has_archive' => true,
            'labels' => [
                'name' => 'Recipes',
                'all_items' => 'All Recipes',
            ],
        ]);
        \register_post_type('pt_secret', [
            'public' => false,
        ]);
        \register_taxonomy('pt_cuisine', 'pt_recipe', [
            'public' => true,
        ]);
    }

    public function testPostTypeObject()
    {
        $obj = \get_post_type_object('post');
        $this->assertEquals('Posts', $obj->labels->name);
    }

    public function testPostTypeProperty()
    {
        $post_id = static::factory()->post->create();
        $post = Timber::get_post($post_id);
        $this->assertEquals('post', $post->post_type);
    }

    #[Ticket('#2221')]
    public function testNonExistentPostType()
    {
        $post_type = new PostType('foobar');
        $this->assertEquals('foobar', $post_type);
        $this->assertEquals(PostType::class, $post_type::class);
    }

    public function testPostTypeMethodInTwig()
    {
        $post_id = static::factory()->post->create();
        $post = Timber::get_post($post_id);
        $template = '{{post.post_type}}';
        $str = Timber::compile_string($template, [
            'post' => $post,
        ]);
        $this->assertEquals('post', $str);
    }

    public function testTypeMethodInTwig()
    {
        $post_id = static::factory()->post->create();
        $post = Timber::get_post($post_id);
        $template = '{{post.type}}';
        $str = Timber::compile_string($template, [
            'post' => $post,
        ]);
        $this->assertEquals('post', $str);
    }

    public function testTypeMethodInTwigLabels()
    {
        $post_id = static::factory()->post->create();
        $post = Timber::get_post($post_id);
        $template = '{{post.type.labels.name}}';
        $str = Timber::compile_string($template, [
            'post' => $post,
        ]);
        $this->assertEquals('Posts', $str);
    }

    public function testNonExistentPostTypeHasNoRights()
    {
        \wp_set_current_user(static::factory()->user->create([
            'role' => 'administrator',
        ]));

        $post_type = new PostType('foobar');

        $this->assertEquals('foobar', $post_type->name);
        $this->assertEquals('foobar', $post_type->title());
        $this->assertFalse($post_type->can_edit());
        $this->assertNull($post_type->edit_link());
    }

    public function testConstructorAcceptsWpPostType()
    {
        $wp_post_type = \get_post_type_object('pt_recipe');
        $post_type = new PostType($wp_post_type);

        $this->assertSame($wp_post_type, $post_type->wp_object());
        $this->assertEquals('pt_recipe', (string) $post_type);
    }

    public function testPostTypeImportsRegistrationArguments()
    {
        $post_type = new PostType('pt_recipe');

        $this->assertSame(\get_post_type_object('pt_recipe'), $post_type->wp_object());
        $this->assertEquals('pt_recipe', $post_type->name);
        $this->assertEquals('Recipes', $post_type->labels->name);
        $this->assertTrue($post_type->hierarchical);
        $this->assertEquals('Recipes', $post_type->title());
    }

    public function testGetPostType()
    {
        $post_type = Timber::get_post_type('pt_recipe');

        $this->assertInstanceOf(PostType::class, $post_type);
        $this->assertEquals('pt_recipe', (string) $post_type);
        $this->assertEquals('All Recipes', $post_type->labels->all_items);
    }

    public function testGetPostTypeFromWpPostType()
    {
        $post_type = Timber::get_post_type(\get_post_type_object('pt_recipe'));

        $this->assertInstanceOf(PostType::class, $post_type);
        $this->assertEquals('pt_recipe', $post_type->name);
    }

    public function testGetPostTypeIsNullWhenNotRegistered()
    {
        $this->assertNull(Timber::get_post_type('not-a-post-type'));
    }

    public function testGetPostTypeUsesTheQueriedPost()
    {
        $post_id = static::factory()->post->create([
            'post_type' => 'pt_recipe',
        ]);

        $this->go_to('/?post_type=pt_recipe&p=' . $post_id);

        $this->assertEquals('pt_recipe', (string) Timber::get_post_type());
    }

    public function testGetPostTypeUsesTheQueriedPostTypeArchive()
    {
        $this->go_to('/?post_type=pt_recipe');

        $this->assertEquals('pt_recipe', (string) Timber::get_post_type());
    }

    public function testGetPostTypeIsNullWithoutAQueriedObject()
    {
        $this->go_to('/');

        $this->assertNull(Timber::get_post_type());
    }

    public function testGetPostTypesDefaultsToPublicPostTypes()
    {
        $post_types = Timber::get_post_types();

        $this->assertArrayHasKey('post', $post_types);
        $this->assertArrayHasKey('pt_recipe', $post_types);
        $this->assertArrayNotHasKey('pt_secret', $post_types);
        $this->assertContainsOnlyInstancesOf(PostType::class, $post_types);
    }

    public function testGetPostTypesByName()
    {
        $this->assertEquals(['pt_recipe'], \array_keys(Timber::get_post_types('pt_recipe')));
    }

    public function testGetPostTypesByNames()
    {
        $post_types = Timber::get_post_types(['post', 'pt_recipe', 'not-a-post-type']);

        $this->assertEquals(['post', 'pt_recipe'], \array_keys($post_types));
    }

    public function testGetPostTypesByArguments()
    {
        $post_types = Timber::get_post_types([
            'public' => true,
            '_builtin' => false,
            'hierarchical' => true,
        ]);

        $this->assertArrayHasKey('pt_recipe', $post_types);
        $this->assertArrayNotHasKey('post', $post_types);
        $this->assertArrayNotHasKey('page', $post_types);
    }

    public function testTaxonomies()
    {
        $taxonomies = Timber::get_post_type('pt_recipe')->taxonomies();

        $this->assertEquals(['pt_cuisine'], \array_keys($taxonomies));
        $this->assertContainsOnlyInstancesOf(Taxonomy::class, $taxonomies);
    }

    public function testEditLink()
    {
        \wp_set_current_user(static::factory()->user->create([
            'role' => 'administrator',
        ]));

        $this->assertTrue(Timber::get_post_type('pt_recipe')->can_edit());
        $this->assertEquals(\admin_url('edit.php?post_type=pt_recipe'), Timber::get_post_type('pt_recipe')->edit_link());
        $this->assertEquals(\admin_url('edit.php'), Timber::get_post_type('post')->edit_link());
        $this->assertEquals(\admin_url('upload.php'), Timber::get_post_type('attachment')->edit_link());
    }

    public function testEditLinkIsNullForUsersWithoutRights()
    {
        \wp_set_current_user(static::factory()->user->create([
            'role' => 'subscriber',
        ]));

        $post_type = Timber::get_post_type('pt_recipe');

        $this->assertFalse($post_type->can_edit());
        $this->assertNull($post_type->edit_link());
    }

    public function testPostTypeFunctionsInTwig()
    {
        $template = '{{ get_post_type("pt_recipe").title }}|{{ get_post_types(["post", "pt_recipe"])|keys|join(",") }}|{{ get_post_type("pt_recipe").taxonomies|keys|join(",") }}';

        $this->assertEquals('Recipes|post,pt_recipe|pt_cuisine', Timber::compile_string($template));
    }

    public function testPostTypeOfAPostIsAPostType()
    {
        $post_id = static::factory()->post->create([
            'post_type' => 'pt_recipe',
        ]);

        $type = Timber::get_post($post_id)->type();

        $this->assertInstanceOf(PostType::class, $type);
        $this->assertEquals('Recipes', $type->title());
    }
}
