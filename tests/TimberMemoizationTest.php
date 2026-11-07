<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use Timber\Timber;

#[Group('posts-api')]
#[Group('terms-api')]
class TimberMemoizationTest extends TimberAttachmentTestCase
{
    public function testChildrenAreMemoized()
    {
        $parent_id = static::factory()->post->create();
        static::factory()->post->create_many(3, [
            'post_parent' => $parent_id,
        ]);
        $parent = Timber::get_post($parent_id);

        $this->assertSame($parent->children(), $parent->children());
    }

    public function testChildrenAreMemoizedPerArguments()
    {
        $parent_id = static::factory()->post->create([
            'post_type' => 'foo',
        ]);
        static::factory()->post->create_many(3, [
            'post_parent' => $parent_id,
            'post_type' => 'bar',
        ]);
        static::factory()->post->create_many(2, [
            'post_parent' => $parent_id,
            'post_type' => 'foo',
        ]);
        $parent = Timber::get_post($parent_id);

        $this->assertCount(5, $parent->children(['foo', 'bar']));
        $this->assertCount(2, $parent->children('parent'));
        $this->assertCount(3, $parent->children('bar'));
        $this->assertCount(5, $parent->children(['foo', 'bar']));
    }

    public function testChildrenRefreshWhenPostsChange()
    {
        $parent_id = static::factory()->post->create();
        static::factory()->post->create_many(2, [
            'post_parent' => $parent_id,
        ]);
        $parent = Timber::get_post($parent_id);
        $this->assertCount(2, $parent->children());

        static::factory()->post->create([
            'post_parent' => $parent_id,
        ]);

        $this->assertCount(3, $parent->children());
    }

    public function testChildrenStillApplyArgsFilter()
    {
        $parent_id = static::factory()->post->create();
        static::factory()->post->create_many(2, [
            'post_parent' => $parent_id,
        ]);
        static::factory()->post->create([
            'post_parent' => $parent_id,
            'post_status' => 'draft',
        ]);
        $parent = Timber::get_post($parent_id);
        $this->assertCount(2, $parent->children());

        $this->add_filter_temporarily('timber/post/children_args', function ($args) {
            $args['post_status'] = 'draft';
            return $args;
        });

        $this->assertCount(1, $parent->children());
    }

    public function testChildrenRefreshWhenUserChanges()
    {
        $parent_id = static::factory()->post->create();
        static::factory()->post->create([
            'post_parent' => $parent_id,
        ]);
        static::factory()->post->create([
            'post_parent' => $parent_id,
            'post_status' => 'private',
            'post_author' => static::factory()->user->create(),
        ]);
        $parent = Timber::get_post($parent_id);
        $this->add_filter_temporarily('timber/post/children_args', function ($args) {
            $args['post_status'] = ['publish', 'private'];
            $args['post_type'] = 'post';
            $args['perm'] = 'readable';
            return $args;
        });

        \wp_set_current_user(0);
        $this->assertCount(1, $parent->children());

        \wp_set_current_user(static::factory()->user->create([
            'role' => 'administrator',
        ]));
        $this->assertCount(2, Timber::get_post($parent_id)->children());
        $this->assertCount(2, $parent->children());
    }

    public function testTermsAreMemoized()
    {
        $post_id = static::factory()->post->create();
        $category_id = static::factory()->category->create();
        \wp_set_object_terms($post_id, [$category_id], 'category');
        $post = Timber::get_post($post_id);

        $this->assertSame($post->terms(), $post->terms());
        $this->assertSame($post->categories(), $post->categories());
        $this->assertSame($post->category(), $post->category());
        $this->assertSame($post->tags(), $post->tags());
    }

    public function testTermsAreMemoizedPerArguments()
    {
        $post_id = static::factory()->post->create();
        $category_id = static::factory()->category->create();
        $tag_id = static::factory()->tag->create();
        \wp_set_object_terms($post_id, [$category_id], 'category');
        \wp_set_object_terms($post_id, [$tag_id], 'post_tag');
        $post = Timber::get_post($post_id);

        $this->assertCount(1, $post->categories());
        $this->assertCount(1, $post->tags());
        $this->assertCount(2, $post->terms([
            'taxonomy' => ['category', 'post_tag'],
        ]));
        $this->assertCount(2, $post->terms([
            'taxonomy' => ['category', 'post_tag'],
        ], [
            'merge' => true,
        ]));
        $this->assertCount(2, $post->terms([
            'taxonomy' => ['category', 'post_tag'],
        ], [
            'merge' => false,
        ]));
        $this->assertArrayHasKey('category', $post->terms([
            'taxonomy' => ['category', 'post_tag'],
        ], [
            'merge' => false,
        ]));
        $this->assertArrayNotHasKey('category', $post->terms([
            'taxonomy' => ['category', 'post_tag'],
        ], [
            'merge' => true,
        ]));
    }

    public function testTermsRefreshWhenTermsChange()
    {
        $post_id = static::factory()->post->create();
        $first = static::factory()->category->create();
        $second = static::factory()->category->create();
        \wp_set_object_terms($post_id, [$first], 'category');
        $post = Timber::get_post($post_id);
        $this->assertCount(1, $post->categories());

        \wp_set_object_terms($post_id, [$first, $second], 'category');

        $this->assertCount(2, $post->categories());
    }

    public function testTermsRefreshWhenTermIsRenamed()
    {
        $post_id = static::factory()->post->create();
        $category_id = static::factory()->category->create([
            'name' => 'Before',
        ]);
        \wp_set_object_terms($post_id, [$category_id], 'category');
        $post = Timber::get_post($post_id);
        $this->assertSame('Before', $post->category()->name);

        \wp_update_term($category_id, 'category', [
            'name' => 'After',
        ]);

        $this->assertSame('After', $post->category()->name);
    }

    public function testThumbnailIsMemoized()
    {
        $post_id = static::factory()->post->create();
        \update_post_meta($post_id, '_thumbnail_id', static::factory()->attachment->create());
        $post = Timber::get_post($post_id);

        $this->assertSame($post->thumbnail(), $post->thumbnail());
    }

    public function testThumbnailRefreshesWhenThumbnailChanges()
    {
        $post_id = static::factory()->post->create();
        $first = static::factory()->attachment->create();
        $second = static::factory()->attachment->create();
        $post = Timber::get_post($post_id);
        $this->assertNull($post->thumbnail());

        \update_post_meta($post_id, '_thumbnail_id', $first);
        $this->assertSame($first, $post->thumbnail()->ID);

        \update_post_meta($post_id, '_thumbnail_id', $second);
        $this->assertSame($second, $post->thumbnail()->ID);

        \delete_post_meta($post_id, '_thumbnail_id');
        $this->assertNull($post->thumbnail());
    }

    public function testGalleryIsMemoizedPerHtmlFlag()
    {
        $attachments = [$this->createAttachmentWithImage(), $this->createAttachmentWithImage()];
        $post_id = static::factory()->post->create([
            'post_content' => '[gallery ids="' . \implode(',', $attachments) . '"]',
        ]);
        $post = Timber::get_post($post_id);

        $html = $post->gallery();
        $this->assertSame($html, $post->gallery());
        $this->assertIsString($html);

        $data = $post->gallery(false);
        $this->assertIsArray($data);
        $this->assertSame($data, $post->gallery(false));
    }

    public function testGalleryRefreshesWhenContentChanges()
    {
        $attachments = [
            $this->createAttachmentWithImage(),
            $this->createAttachmentWithImage(),
            $this->createAttachmentWithImage(),
        ];
        $post_id = static::factory()->post->create([
            'post_content' => '[gallery ids="' . $attachments[0] . '"]',
        ]);
        $post = Timber::get_post($post_id);
        $this->assertCount(1, $post->gallery(false)['src']);

        \wp_update_post([
            'ID' => $post_id,
            'post_content' => '[gallery ids="' . \implode(',', $attachments) . '"]',
        ]);

        $this->assertCount(3, $post->gallery(false)['src']);
    }

    public function testGalleryStillAppliesFilter()
    {
        $post_id = static::factory()->post->create([
            'post_content' => 'No gallery',
        ]);
        $post = Timber::get_post($post_id);
        $this->assertFalse($post->gallery());

        $this->add_filter_temporarily('get_post_gallery', fn () => 'filtered');

        $this->assertSame('filtered', $post->gallery());
    }

    public function testTermPostsAreMemoizedPerQuery()
    {
        $term_id = static::factory()->category->create();
        static::factory()->post->create_many(4, [
            'post_category' => [$term_id],
        ]);
        $term = Timber::get_term($term_id);

        $this->assertSame($term->posts(), $term->posts());
        $this->assertCount(4, $term->posts());
        $this->assertCount(2, $term->posts(2));
        $this->assertCount(3, $term->posts([
            'posts_per_page' => 3,
        ]));
        $this->assertCount(4, $term->posts());
    }

    public function testTermPostsRefreshWhenPostsChange()
    {
        $term_id = static::factory()->category->create();
        static::factory()->post->create_many(2, [
            'post_category' => [$term_id],
        ]);
        $term = Timber::get_term($term_id);
        $this->assertCount(2, $term->posts());

        static::factory()->post->create([
            'post_category' => [$term_id],
        ]);

        $this->assertCount(3, $term->posts());
    }

    public function testMemoizedValuesAreNotSharedBetweenObjects()
    {
        $parent_id = static::factory()->post->create();
        static::factory()->post->create([
            'post_parent' => $parent_id,
        ]);

        $this->assertNotSame(
            Timber::get_post($parent_id)->children(),
            Timber::get_post($parent_id)->children()
        );
    }
}
