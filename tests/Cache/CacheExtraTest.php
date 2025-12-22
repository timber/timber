<?php

namespace Timber\Tests\Cache;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Timber\Cache\KeyGenerator;
use Timber\Tests\TimberIntegrationTestCase;
use Timber\Timber;
use Twig\Extra\Cache\CacheExtension;
use Twig\Extra\Cache\CacheRuntime;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

class RunTimeLoader implements RuntimeLoaderInterface
{
    public function load($class)
    {
        if (CacheRuntime::class === $class) {
            return new CacheRuntime(new TagAwareAdapter(new FilesystemAdapter()));
        }
    }
}

class CacheExtraTest extends TimberIntegrationTestCase
{
    public function set_up()
    {
        parent::set_up();

        $this->add_filter_temporarily('timber/twig', function ($twig) {
            $twig->addExtension(new CacheExtension());
            $twig->addRuntimeLoader(new RunTimeLoader());

            return $twig;
        });
    }

    public function test_cache_tag()
    {
        $post_id = static::factory()->post->create([
            'post_title' => 'My Test Post',
        ]);
        $post = Timber::get_post($post_id);

        $twig = Timber::compile_string("{% cache 'post' %}{{ post.title }}{% endcache %}", [
            'post' => $post,
        ]);

        $this->assertEquals('My Test Post', $twig);
    }

    public function test_cache_tags()
    {
        $post_id = static::factory()->post->create([
            'post_title' => 'My Test Post',
        ]);
        $post = Timber::get_post($post_id);

        $twig = Timber::compile_string("{% cache 'post' tags('blog') %}{{ post.title }}{% endcache %}", [
            'post' => $post,
        ]);

        $this->assertEquals('My Test Post', $twig);
    }

    public function test_cache_tag_with_key_generator()
    {
        $post_id = static::factory()->post->create([
            'post_title' => 'My Test Post',
        ]);
        $post = Timber::get_post($post_id);

        $kg = new KeyGenerator();
        $key = $kg->generateKey($post);

        $twig = Timber::compile_string("{% cache key %}{{ post.title }}{% endcache %}", [
            'key' => $key,
            'post' => $post,
        ]);

        $this->assertEquals('My Test Post', $twig);
    }

    public function test_cache_tag_with_key_generator_posts()
    {
        $post_ids = static::factory()->post->create_many(3, [
            'post_title' => 'My Test Post',
        ]);
        $posts = Timber::get_posts($post_ids);

        $kg = new KeyGenerator();
        $key = $kg->generateKey($posts);

        $twig = Timber::compile_string("{% cache key %}{% for post in posts %}{{ post.title }}, {% endfor %}{% endcache %}", [
            'key' => $key,
            'posts' => $posts,
        ]);

        $this->assertEquals('My Test Post, My Test Post, My Test Post, ', $twig);
    }
}
