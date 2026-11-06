<?php

namespace Timber\Tests\Factory;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use Timber\Factory\PostTypeFactory;
use Timber\PostType;
use Timber\Tests\TimberIntegrationTestCase;
use WP_Post;
use WP_Post_Type;

class RecipePostType extends PostType
{
}

#[Group('factory')]
class PostTypeFactoryTest extends TimberIntegrationTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        \register_post_type('ptf_recipe', [
            'public' => true,
            'hierarchical' => true,
        ]);
        \register_post_type('ptf_review', [
            'public' => true,
        ]);
    }

    public function testFromName()
    {
        $this->assertInstanceOf(PostType::class, (new PostTypeFactory())->from('ptf_recipe'));
    }

    public function testFromUnregisteredName()
    {
        $this->assertNull((new PostTypeFactory())->from('not-a-post-type'));
    }

    public function testFromNames()
    {
        $post_types = (new PostTypeFactory())->from(['ptf_recipe', 'ptf_review']);

        $this->assertEquals(['ptf_recipe', 'ptf_review'], \array_keys($post_types));
        $this->assertContainsOnlyInstancesOf(PostType::class, $post_types);
    }

    public function testFromQueryArgs()
    {
        $post_types = (new PostTypeFactory())->from([
            'hierarchical' => true,
            '_builtin' => false,
        ]);

        $this->assertArrayHasKey('ptf_recipe', $post_types);
        $this->assertArrayNotHasKey('ptf_review', $post_types);
    }

    public function testFromWpPostType()
    {
        $post_type = (new PostTypeFactory())->from(\get_post_type_object('ptf_recipe'));

        $this->assertInstanceOf(PostType::class, $post_type);
        $this->assertInstanceOf(WP_Post_Type::class, $post_type->wp_object());
    }

    public function testFromTimberPostTypeIsIdempotent()
    {
        $factory = new PostTypeFactory();
        $post_type = $factory->from('ptf_recipe');

        $this->assertSame($post_type, $factory->from($post_type));
    }

    public function testFromInvalidObject()
    {
        $this->expectException(InvalidArgumentException::class);

        (new PostTypeFactory())->from(new WP_Post((object) []));
    }

    public function testFromInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);

        (new PostTypeFactory())->from(123);
    }

    public function testClassMap()
    {
        $this->add_filter_temporarily('timber/post_type/classmap', fn () => [
            'ptf_recipe' => RecipePostType::class,
        ]);

        $factory = new PostTypeFactory();

        $this->assertInstanceOf(RecipePostType::class, $factory->from('ptf_recipe'));
        $this->assertNotInstanceOf(RecipePostType::class, $factory->from('ptf_review'));
    }

    public function testClassMapCallable()
    {
        $this->add_filter_temporarily('timber/post_type/classmap', fn () => [
            'ptf_recipe' => fn (WP_Post_Type $post_type) => $post_type->hierarchical
                ? RecipePostType::class
                : PostType::class,
            'ptf_review' => fn (WP_Post_Type $post_type) => $post_type->hierarchical
                ? RecipePostType::class
                : PostType::class,
        ]);

        $post_types = (new PostTypeFactory())->from(['ptf_recipe', 'ptf_review']);

        $this->assertInstanceOf(RecipePostType::class, $post_types['ptf_recipe']);
        $this->assertNotInstanceOf(RecipePostType::class, $post_types['ptf_review']);
    }

    public function testClassFilter()
    {
        $this->add_filter_temporarily('timber/post_type/class', fn ($class, WP_Post_Type $post_type) => $post_type->hierarchical
            ? RecipePostType::class
            : $class, 10, 2);

        $post_types = (new PostTypeFactory())->from(['ptf_recipe', 'ptf_review']);

        $this->assertInstanceOf(RecipePostType::class, $post_types['ptf_recipe']);
        $this->assertNotInstanceOf(RecipePostType::class, $post_types['ptf_review']);
    }
}
