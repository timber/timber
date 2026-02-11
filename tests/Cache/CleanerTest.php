<?php

namespace Timber\Tests\Cache;

use PHPUnit\Framework\Attributes\Group;
use Timber\Cache\Cleaner;
use Timber\Loader;
use Timber\Tests\TimberIntegrationTestCase;
use Timber\Timber;

#[Group('cache')]
class CleanerTest extends TimberIntegrationTestCase
{
    protected function create_timber_database_cache()
    {
        Timber::compile('assets/single-post.twig', [
            'post' => Timber::get_post(static::factory()->post->create()),
        ], 600);
    }

    protected function create_timber_object_cache()
    {
        Timber::compile('assets/single-post.twig', [
            'post' => Timber::get_post(static::factory()->post->create()),
        ], 600, Loader::CACHE_OBJECT);
    }

    protected function enable_twig_cache()
    {
        $this->add_filter_temporarily('timber/twig/environment/options', function ($options) {
            $options['cache'] = true;

            return $options;
        });
    }

    protected function create_twig_cache()
    {
        Timber::compile('assets/single-post.twig', [
            'post' => Timber::get_post(static::factory()->post->create()),
        ]);
    }

    public function test_clear_cache_all()
    {
        $this->create_timber_database_cache();
        $this->enable_twig_cache();
        $this->create_twig_cache();

        $result = Cleaner::clear_cache('all');

        $this->assertTrue($result);
    }

    public function test_clear_cache_all_without_cache()
    {
        $result = Cleaner::clear_cache('all');

        $this->assertTrue($result);
    }

    public function test_clear_cache_default_mode_is_all()
    {
        $this->create_timber_database_cache();
        $this->enable_twig_cache();
        $this->create_twig_cache();

        $result = Cleaner::clear_cache();

        $this->assertTrue($result);
    }

    public function test_clear_cache_timber()
    {
        $this->create_timber_database_cache();

        $result = Cleaner::clear_cache('timber');

        $this->assertTrue($result);
    }

    public function test_clear_cache_timber_without_cache()
    {
        $result = Cleaner::clear_cache('timber');

        // Returns true - clearing empty cache is still success
        $this->assertTrue($result);
    }

    public function test_clear_cache_twig()
    {
        $this->enable_twig_cache();
        $this->create_twig_cache();

        $result = Cleaner::clear_cache('twig');

        $this->assertTrue($result);
    }

    public function test_clear_cache_twig_without_cache()
    {
        $result = Cleaner::clear_cache('twig');

        // Returns true because no cache to clear is still success
        $this->assertTrue($result);
    }

    public function test_clear_cache_invalid_mode()
    {
        $result = Cleaner::clear_cache('invalid');

        $this->assertFalse($result);
    }

    public function test_clear_cache_timber_method()
    {
        $this->create_timber_database_cache();

        $result = Cleaner::clear_cache_timber();

        $this->assertTrue($result);
    }

    public function test_clear_cache_twig_method()
    {
        $this->enable_twig_cache();
        $this->create_twig_cache();

        $result = Cleaner::clear_cache_twig();

        $this->assertTrue($result);
    }

    public function test_clear_cache_twig_method_without_cache()
    {
        $result = Cleaner::clear_cache_twig();

        $this->assertTrue($result);
    }

    public function test_delete_transients()
    {
        $result = Cleaner::delete_transients();

        // Should return number of deleted records (as string due to concatenation in Cleaner)
        $this->assertIsString($result);
    }

    public function test_delete_transients_with_object_cache()
    {
        global $_wp_using_ext_object_cache;

        // Simulate external object cache
        $original = $_wp_using_ext_object_cache;
        $_wp_using_ext_object_cache = true;

        $result = Cleaner::delete_transients();

        // Restore original value
        $_wp_using_ext_object_cache = $original;

        $this->assertEquals(0, $result);
    }

    public function test_clear_cache_all_with_object_cache()
    {
        $this->create_timber_object_cache();
        $this->enable_twig_cache();
        $this->create_twig_cache();

        $result = Cleaner::clear_cache('all');

        $this->assertTrue($result);
    }
}
