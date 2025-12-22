<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use Timber\Integration\AcfIntegration;
use Timber\Timber;

/**
 * Class TestTimberMeta
 */
#[Group('comments-api')]
#[Group('users-api')]
#[Group('terms-api')]
#[Group('called-post-constructor')]
class TimberMetaDeprecatedTest extends TimberIntegrationTestCase
{
    public function set_up()
    {
        parent::set_up();

        \remove_filter('timber/post/pre_meta', AcfIntegration::post_get_meta_field(...));
        \remove_filter('timber/post/meta_object_field', AcfIntegration::post_meta_object(...));
        \remove_filter('timber/term/pre_meta', AcfIntegration::term_get_meta_field(...));
        \remove_filter('timber/user/pre_meta', AcfIntegration::user_get_meta_field(...));
    }

    #[IgnoreDeprecations]
    public function testDeprecatedTimberPostGetMetaFieldPreFilter()
    {
        $this->setExpectedDeprecated('timber_post_get_meta_field_pre');
        $filter = function ($meta, $object_id, $field_name, $object) {
            $this->assertEquals('name', $field_name);
            $this->assertSame(null, $meta);
            $this->assertSame($object->ID, $object_id);

            return $meta;
        };

        \add_filter('timber_post_get_meta_field_pre', $filter, 10, 4);

        $post_id = static::factory()->post->create();
        $post = Timber::get_post($post_id);

        \update_post_meta($post_id, 'name', 'A girl has no name.');

        $this->assertEquals('A girl has no name.', $post->meta('name'));

        \remove_filter('timber_post_get_meta_field_pre', $filter);
    }

    #[IgnoreDeprecations]
    public function testDeprecatedTimberPostGetMetaPreAction()
    {
        $this->setExpectedDeprecated('timber_post_get_meta_pre');
        $action = function ($meta, $object_id, $object) {
            $this->assertSame(null, $meta);
            $this->assertSame($object->ID, $object_id);
        };

        \add_action('timber_post_get_meta_pre', $action, 10, 3);

        $post_id = static::factory()->post->create();
        $post = Timber::get_post($post_id);

        \update_post_meta($post_id, 'name', 'A girl has no name.');

        $this->assertEquals('A girl has no name.', $post->meta('name'));

        \remove_action('timber_post_get_meta_pre', $action);
    }

    #[IgnoreDeprecations]
    public function testDeprecatedTimberPostGetMetaFieldFilter()
    {
        $this->setExpectedDeprecated('timber_post_get_meta_field');
        $filter = function ($meta, $object_id, $field_name, $object) {
            $this->assertEquals('name', $field_name);
            $this->assertEquals('A girl has no name.', $meta);
            $this->assertSame($object->ID, $object_id);

            return $meta;
        };

        \add_filter('timber_post_get_meta_field', $filter, 10, 4);

        $post_id = static::factory()->post->create();
        $post = Timber::get_post($post_id);

        \update_post_meta($post_id, 'name', 'A girl has no name.');

        $this->assertEquals('A girl has no name.', $post->meta('name'));

        \remove_filter('timber_post_get_meta_field', $filter);
    }

    #[IgnoreDeprecations]
    public function testDeprecatedTimberPostGetMetaFilter()
    {
        $this->setExpectedDeprecated('timber_post_get_meta');
        $filter = function ($meta, $object_id, $object) {
            $this->assertEquals('A girl has no name.', $meta);
            $this->assertSame($object->ID, $object_id);

            return $meta;
        };

        \add_filter('timber_post_get_meta', $filter, 10, 3);

        $post_id = static::factory()->post->create();
        $post = Timber::get_post($post_id);

        \update_post_meta($post_id, 'name', 'A girl has no name.');

        $this->assertEquals('A girl has no name.', $post->meta('name'));

        \remove_filter('timber_post_get_meta', $filter);
    }

    #[IgnoreDeprecations]
    public function testDeprecatedTimberTermGetMetaFilter()
    {
        $this->setExpectedDeprecated('timber_term_get_meta');
        $filter = function ($meta, $object_id, $object) {
            $this->assertEquals('A girl has no name.', $meta);
            $this->assertSame($object->ID, $object_id);

            return $meta;
        };

        \add_filter('timber_term_get_meta', $filter, 10, 3);

        $term_id = static::factory()->term->create();
        $term = Timber::get_term($term_id);

        \update_term_meta($term_id, 'name', 'A girl has no name.');

        $this->assertEquals('A girl has no name.', $term->meta('name'));

        \remove_filter('timber_term_get_meta', $filter);
    }

    #[IgnoreDeprecations]
    public function testDeprecatedTimberTermMetaFieldFilter()
    {
        $this->setExpectedDeprecated('timber/term/meta/field');
        $filter = function ($meta, $object_id, $field_name, $object) {
            $this->assertEquals('name', $field_name);
            $this->assertEquals('A girl has no name.', $meta);
            $this->assertSame($object->ID, $object_id);

            return $meta;
        };

        \add_filter('timber/term/meta/field', $filter, 10, 4);

        $term_id = static::factory()->term->create();
        $term = Timber::get_term($term_id);

        \update_term_meta($term_id, 'name', 'A girl has no name.');

        $this->assertEquals('A girl has no name.', $term->meta('name'));

        \remove_filter('timber/term/meta/field', $filter);
    }

    #[IgnoreDeprecations]
    public function testDeprecatedTimberTermGetMetaFieldFilter()
    {
        $this->setExpectedDeprecated('timber_term_get_meta_field');
        $filter = function ($meta, $object_id, $field_name, $object) {
            $this->assertEquals('name', $field_name);
            $this->assertEquals('A girl has no name.', $meta);
            $this->assertSame($object->ID, $object_id);

            return $meta;
        };

        \add_filter('timber_term_get_meta_field', $filter, 10, 4);

        $term_id = static::factory()->term->create();
        $term = Timber::get_term($term_id);

        \update_term_meta($term_id, 'name', 'A girl has no name.');

        $this->assertEquals('A girl has no name.', $term->meta('name'));

        \remove_filter('timber_term_get_meta_field', $filter);
    }

    #[IgnoreDeprecations]
    public function testDeprecatedTimberUserGetMetaPreFilter()
    {
        $this->setExpectedDeprecated('timber_user_get_meta_pre');
        $filter = function ($meta, $object_id, $object) {
            $this->assertSame(null, $meta);
            $this->assertSame($object->ID, $object_id);

            return $meta;
        };

        \add_filter('timber_user_get_meta_pre', $filter, 10, 3);

        $user_id = static::factory()->user->create();
        $user = Timber::get_user($user_id);

        \update_user_meta($user_id, 'name', 'A girl has no name.');

        $this->assertEquals('A girl has no name.', $user->meta('name'));

        \remove_filter('timber_user_get_meta_pre', $filter);
    }

    #[IgnoreDeprecations]
    public function testDeprecatedTimberUserGetMetaFieldPreFilter()
    {
        $this->setExpectedDeprecated('timber_user_get_meta_field_pre');
        $filter = function ($meta, $object_id, $field_name, $object) {
            $this->assertEquals('name', $field_name);
            $this->assertSame(null, $meta);
            $this->assertSame($object->ID, $object_id);

            return $meta;
        };

        \add_filter('timber_user_get_meta_field_pre', $filter, 10, 4);

        $user_id = static::factory()->user->create();
        $user = Timber::get_user($user_id);

        \update_user_meta($user_id, 'name', 'A girl has no name.');

        $this->assertEquals('A girl has no name.', $user->meta('name'));

        \remove_filter('timber_user_get_meta_field_pre', $filter);
    }

    #[IgnoreDeprecations]
    public function testDeprecatedTimberUserGetMetaFilter()
    {
        $this->setExpectedDeprecated('timber_user_get_meta');
        $filter = function ($meta, $object_id, $object) {
            $this->assertEquals('A girl has no name.', $meta);
            $this->assertSame($object->ID, $object_id);

            return $meta;
        };

        \add_filter('timber_user_get_meta', $filter, 10, 3);

        $user_id = static::factory()->user->create();
        $user = Timber::get_user($user_id);

        \update_user_meta($user_id, 'name', 'A girl has no name.');

        $this->assertEquals('A girl has no name.', $user->meta('name'));

        \remove_filter('timber_user_get_meta', $filter);
    }

    #[IgnoreDeprecations]
    public function testDeprecatedTimberUserGetMetaFieldFilter()
    {
        $this->setExpectedDeprecated('timber_user_get_meta_field');
        $filter = function ($meta, $object_id, $field_name, $object) {
            $this->assertEquals('name', $field_name);
            $this->assertEquals('A girl has no name.', $meta);
            $this->assertSame($object->ID, $object_id);

            return $meta;
        };

        \add_filter('timber_user_get_meta_field', $filter, 10, 4);

        $user_id = static::factory()->user->create();
        $user = Timber::get_user($user_id);

        \update_user_meta($user_id, 'name', 'A girl has no name.');

        $this->assertEquals('A girl has no name.', $user->meta('name'));

        \remove_filter('timber_user_get_meta_field', $filter);
    }

    #[IgnoreDeprecations]
    public function testDeprecatedTimberCommentGetMetaFieldPreFilter()
    {
        $this->setExpectedDeprecated('timber_comment_get_meta_field_pre');
        $filter = function ($meta, $object_id, $field_name, $object) {
            $this->assertEquals('name', $field_name);
            $this->assertSame(null, $meta);
            $this->assertSame($object->ID, $object_id);

            return $meta;
        };

        \add_filter('timber_comment_get_meta_field_pre', $filter, 10, 4);

        $comment_id = static::factory()->comment->create();
        $comment = Timber::get_comment($comment_id);

        \update_comment_meta($comment_id, 'name', 'A girl has no name.');

        $this->assertEquals('A girl has no name.', $comment->meta('name'));

        \remove_filter('timber_comment_get_meta_field_pre', $filter);
    }

    #[IgnoreDeprecations]
    public function testDeprecatedTimberCommentGetMetaPreAction()
    {
        $this->setExpectedDeprecated('timber_comment_get_meta_pre');
        $action = function ($meta, $object_id) {
            $this->assertSame(null, $meta);
        };

        \add_action('timber_comment_get_meta_pre', $action, 10, 2);

        $comment_id = static::factory()->comment->create();
        $comment = Timber::get_comment($comment_id);

        \update_comment_meta($comment_id, 'name', 'A girl has no name.');

        $this->assertEquals('A girl has no name.', $comment->meta('name'));

        \remove_action('timber_comment_get_meta_pre', $action);
    }

    #[IgnoreDeprecations]
    public function testDeprecatedTimberCommentGetMetaFilter()
    {
        $this->setExpectedDeprecated('timber_comment_get_meta');
        $filter = function ($meta, $object_id) {
            $this->assertEquals('A girl has no name.', $meta);

            return $meta;
        };

        \add_filter('timber_comment_get_meta', $filter, 10, 2);

        $comment_id = static::factory()->comment->create();
        $comment = Timber::get_comment($comment_id);

        \update_comment_meta($comment_id, 'name', 'A girl has no name.');

        $this->assertEquals('A girl has no name.', $comment->meta('name'));

        \remove_filter('timber_comment_get_meta', $filter);
    }

    #[IgnoreDeprecations]
    public function testDeprecatedTimberCommentGetMetaFieldFilter()
    {
        $this->setExpectedDeprecated('timber_comment_get_meta_field');
        $filter = function ($meta, $object_id, $field_name, $object) {
            $this->assertEquals('name', $field_name);
            $this->assertEquals('A girl has no name.', $meta);
            $this->assertSame($object->ID, $object_id);

            return $meta;
        };

        \add_filter('timber_comment_get_meta_field', $filter, 10, 4);

        $comment_id = static::factory()->comment->create();
        $comment = Timber::get_comment($comment_id);

        \update_comment_meta($comment_id, 'name', 'A girl has no name.');

        $this->assertEquals('A girl has no name.', $comment->meta('name'));

        \remove_filter('timber_comment_get_meta_field', $filter);
    }
}
