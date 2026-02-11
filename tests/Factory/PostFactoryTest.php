<?php

namespace Timber\Tests\Factory;

use PHPUnit\Framework\Attributes\Group;
use Timber\Factory\PostFactory;
use Timber\Post;
use Timber\PostArrayObject;
use Timber\PostQuery;
use Timber\Tests\TimberIntegrationTestCase;
use WP_Post;
use WP_Query;

class MyPost extends Post
{
}
class MyPage extends Post
{
}
class MyCustom extends Post
{
}
class MySpecialCustom extends MyCustom
{
}

#[Group('factory')]
#[Group('post-collections')]
class PostFactoryTest extends TimberIntegrationTestCase
{
    public function testFrom()
    {
        $post_id = static::factory()->post->create([
            'post_type' => 'post',
        ]);
        $page_id = static::factory()->post->create([
            'post_type' => 'page',
        ]);
        $custom_id = static::factory()->post->create([
            'post_type' => 'custom',
        ]);

        $postFactory = new PostFactory();
        $post = $postFactory->from($post_id);
        $page = $postFactory->from($page_id);
        $custom = $postFactory->from($custom_id);

        // Assert that all instances are of \Timber\Post
        $this->assertInstanceOf(Post::class, $post);
        $this->assertInstanceOf(Post::class, $post);
        $this->assertInstanceOf(Post::class, $custom);
    }

    public function testFromInvalidId()
    {
        $postFactory = new PostFactory();
        $post = $postFactory->from(99999);

        $this->assertNull($post);
    }

    public function testFromIdString()
    {
        $post_id = static::factory()->post->create();

        $postFactory = new PostFactory();
        $post = $postFactory->from('' . $post_id);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals($post_id, $post->id);
    }

    public function testFromWithClassmapFilter()
    {
        $my_class_map = (fn () => [
            'post' => MyPost::class,
            'page' => MyPage::class,
            'custom' => MyCustom::class,
        ]);

        $this->add_filter_temporarily('timber/post/classmap', $my_class_map);

        $post_id = static::factory()->post->create([
            'post_type' => 'post',
        ]);
        $page_id = static::factory()->post->create([
            'post_type' => 'page',
        ]);
        $custom_id = static::factory()->post->create([
            'post_type' => 'custom',
        ]);

        $postFactory = new PostFactory();
        $post = $postFactory->from($post_id);
        $page = $postFactory->from($page_id);
        $custom = $postFactory->from($custom_id);

        $this->assertTrue(MyPost::class === $post::class);
        $this->assertTrue(MyPage::class === $page::class);
        $this->assertTrue(MyCustom::class === $custom::class);
    }

    public function testFromWithClassFilter()
    {
        $my_class_filter = (fn ($class, WP_Post $post) => MyCustom::class);

        $this->add_filter_temporarily('timber/post/class', $my_class_filter, 10, 2);

        $custom_id = static::factory()->post->create([
            'post_type' => 'custom',
        ]);

        $postFactory = new PostFactory();
        $custom = $postFactory->from($custom_id);

        $this->assertTrue(MyCustom::class === $custom::class);
    }

    public function testFromWithCallable()
    {
        $my_class_map = (fn (array $map) => \array_merge($map, [
            'page' => fn () => MyPage::class,
            'custom' => function (WP_Post $post) {
                if ($post->post_name === 'my-special-post') {
                    return MySpecialCustom::class;
                }
                return MyCustom::class;
            },
        ]));

        $this->add_filter_temporarily('timber/post/classmap', $my_class_map);

        $post_id = static::factory()->post->create([
            'post_type' => 'post',
        ]);
        $page_id = static::factory()->post->create([
            'post_type' => 'page',
        ]);
        $custom_id = static::factory()->post->create([
            'post_type' => 'custom',
        ]);
        $special_id = static::factory()->post->create([
            'post_type' => 'custom',
            'post_name' => 'my-special-post',
        ]);

        $postFactory = new PostFactory();
        $post = $postFactory->from($post_id);
        $page = $postFactory->from($page_id);
        $custom = $postFactory->from($custom_id);
        $special = $postFactory->from($special_id);

        $this->assertTrue(Post::class === $post::class);
        $this->assertTrue(MyPage::class === $page::class);
        $this->assertTrue(MyCustom::class === $custom::class);
        $this->assertTrue(MySpecialCustom::class === $special::class);
    }

    public function testFromWpPost()
    {
        $post_id = static::factory()->post->create([
            'post_type' => 'page',
            'post_title' => 'Title One',
        ]);

        $postFactory = new PostFactory();
        $this->assertInstanceOf(Post::class, $postFactory->from(\get_post($post_id)));
    }

    public function testFromWpQuery()
    {
        $my_class_map = (fn () => [
            'post' => MyPost::class,
            'page' => MyPage::class,
            'custom' => MyCustom::class,
        ]);

        $this->add_filter_temporarily('timber/post/classmap', $my_class_map);

        $post_id = static::factory()->post->create([
            'post_type' => 'post',
            'post_date' => '2020-01-10 19:46:41',
        ]);
        $page_id = static::factory()->post->create([
            'post_type' => 'page',
            'post_date' => '2020-01-09 19:46:41',
        ]);
        $custom_id = static::factory()->post->create([
            'post_type' => 'custom',
            'post_date' => '2020-01-08 19:46:41',
        ]);

        $postFactory = new PostFactory();
        $query = new WP_Query([
            'post_type' => ['post', 'page', 'custom'],
        ]);
        $posts = $postFactory->from($query);

        $this->assertTrue(MyPost::class === $posts[0]::class);
        $this->assertTrue(MyPage::class === $posts[1]::class);
        $this->assertTrue(MyCustom::class === $posts[2]::class);
    }

    public function testFromAcfArray()
    {
        $id = static::factory()->post->create([
            'post_type' => 'page',
            'post_title' => 'Title One',
        ]);

        $postFactory = new PostFactory();
        $post = $postFactory->from([
            'ID' => $id,
        ]);

        $this->assertEquals($id, $post->id);
    }

    public function testFromArray()
    {
        static::factory()->post->create([
            'post_type' => 'page',
            'post_title' => 'Title One',
        ]);
        static::factory()->post->create([
            'post_type' => 'page',
            'post_title' => 'Title Two',
        ]);

        $postFactory = new PostFactory();
        $res = $postFactory->from(\get_posts('post_type=page'));

        $this->assertTrue(true, \is_array($res));
        $this->assertCount(2, $res);
        $this->assertInstanceOf(Post::class, $res[0]);
        $this->assertInstanceOf(Post::class, $res[1]);
    }

    public function testFromArrayCustom()
    {
        $my_class_map = (fn (array $map) => \array_merge($map, [
            'page' => MyPage::class,
            'custom' => MyCustom::class,
        ]));

        $this->add_filter_temporarily('timber/post/classmap', $my_class_map);

        $postFactory = new PostFactory();

        static::factory()->post->create([
            'post_type' => 'post',
            'post_title' => 'AAA',
        ]);
        static::factory()->post->create([
            'post_type' => 'page',
            'post_title' => 'BBB',
        ]);
        static::factory()->post->create([
            'post_type' => 'custom',
            'post_title' => 'CCC',
        ]);

        $res = $postFactory->from(\get_posts([
            'post_type' => ['custom', 'page', 'post'],
            'orderby' => 'title',
            'order' => 'ASC',
        ]));

        $this->assertTrue(true, \is_array($res));
        $this->assertCount(3, $res);
        $this->assertTrue(Post::class === $res[0]::class);
        $this->assertTrue(MyPage::class === $res[1]::class);
        $this->assertTrue(MyCustom::class === $res[2]::class);
    }

    public function testFromAssortedArray()
    {
        $a_id = static::factory()->post->create([
            'post_type' => 'post',
            'post_title' => 'AAA',
        ]);
        $b_id = static::factory()->post->create([
            'post_type' => 'post',
            'post_title' => 'BBB',
        ]);
        $c_id = static::factory()->post->create([
            'post_type' => 'post',
            'post_title' => 'CCC',
        ]);

        $postFactory = new PostFactory();

        // pass in an ID, a WP_Post instance, and a \Timber\Post instance
        $res = $postFactory->from([
            $a_id,
            \get_post($b_id),
            $postFactory->from($c_id),
        ]);

        // Here we're operating on a PostArrayObject, which implements ArrayObject/ArrayAccess.
        $this->assertInstanceOf(PostArrayObject::class, $res);

        $this->assertInstanceOf(Post::class, $res[0]);
        $this->assertInstanceOf(Post::class, $res[1]);
        $this->assertInstanceOf(Post::class, $res[2]);
    }

    public function testFromQueryArray()
    {
        $my_class_map = (fn (array $map) => \array_merge($map, [
            'page' => MyPage::class,
            'custom' => MyCustom::class,
        ]));

        $this->add_filter_temporarily('timber/post/classmap', $my_class_map);

        static::factory()->post->create([
            'post_type' => 'post',
            'post_title' => 'AAA',
        ]);
        static::factory()->post->create([
            'post_type' => 'page',
            'post_title' => 'BBB',
        ]);
        static::factory()->post->create([
            'post_type' => 'custom',
            'post_title' => 'CCC',
        ]);
        static::factory()->post->create([
            'post_type' => 'other_thing',
            'post_title' => 'ZZZ',
        ]);

        $postFactory = new PostFactory();

        $res = $postFactory->from([
            'post_type' => ['post', 'page', 'custom'],
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        // Here we're operating on a PostQuery, which implements ArrayAccess.
        $this->assertTrue(PostQuery::class === $res::class);

        $this->assertTrue(Post::class === $res[0]::class);
        $this->assertTrue(MyPage::class === $res[1]::class);
        $this->assertTrue(MyCustom::class === $res[2]::class);
    }

    public function testDeprecatedPostClassMapFilter()
    {
        $this->setExpectedIncorrectUsage('The `Timber\PostClassMap` filter');

        \add_filter('Timber\PostClassMap', fn () => [
            'custom' => MyCustom::class,
        ]);

        static::factory()->post->create([
            'post_type' => 'custom',
            'post_title' => 'CCC',
        ]);

        $post_factory = new PostFactory();
        $res = $post_factory->from([
            'post_type' => ['custom'],
            'orderby' => 'title',
            'order' => 'ASC',
        ]);

        $this->assertTrue(Post::class === $res[0]::class);
    }
}
