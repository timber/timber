<?php

namespace Timber\Tests\Cache;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use Timber\Cache\KeyGenerator;
use Timber\Cache\TimberKeyGeneratorInterface;
use Timber\Helper;
use Timber\Loader;
use Timber\Tests\TimberIntegrationTestCase;
use Timber\Timber;

class MyFakeThing implements TimberKeyGeneratorInterface
{
    public function _get_cache_key()
    {
        return 'iamakey';
    }
}

function my_test_callback()
{
    return "lbj";
}

#[Group('cache')]
#[Group('called-post-constructor')]
class CacheTest extends TimberIntegrationTestCase
{
    private function _generate_transient_name()
    {
        static $i = 0;
        $i++;
        return 'timber_test_transient_' . $i;
    }

    /**
     * Force a transient to be considered expired by backdating its timeout option.
     *
     * Avoids real `sleep()` waits in tests that exercise TTL behavior.
     */
    private function expireTransient(string $transient): void
    {
        \update_option('_transient_timeout_' . $transient, \time() - 1);
    }

    /**
     * Models a real `sleep($within_seconds)` without waiting: backdates the
     * timeout of every Timber loader transient that is due to expire within
     * the given window. Long-lived transients (set with larger TTL) remain
     * untouched, matching what a real sleep of that duration would have done.
     */
    private function expireShortTimberLoaderTransients(int $within_seconds = 5): void
    {
        global $wpdb;
        $threshold = \time() + $within_seconds;
        $rows = $wpdb->get_results(
            "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_timberloader_%'"
        );
        foreach ($rows as $row) {
            if ((int) $row->option_value <= $threshold) {
                \update_option($row->option_name, \time() - 1);
            }
        }
    }

    public function tear_down()
    {
        global $_wp_using_ext_object_cache;
        $_wp_using_ext_object_cache = false;
        global $wpdb;
        $query = "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timberloader_%'";
        $wpdb->query($query);
        parent::tear_down();
    }

    public function testTransientLock()
    {
        $transient = $this->_generate_transient_name();
        Helper::_lock_transient($transient, 5);
        $this->assertTrue(Helper::_is_transient_locked($transient));
    }

    public function testTransientUnlock()
    {
        $transient = $this->_generate_transient_name();
        Helper::_lock_transient($transient, 5);
        Helper::_unlock_transient($transient);
        $this->assertFalse(Helper::_is_transient_locked($transient));
    }

    public function testTransientExpire()
    {
        $transient = $this->_generate_transient_name();

        Helper::_lock_transient($transient, 1);
        $this->expireTransient($transient . '_lock');
        $this->assertFalse(Helper::_is_transient_locked($transient));
    }

    public function testTransientLocksInternal()
    {
        $transient = $this->_generate_transient_name();

        $is_locked = Helper::transient($transient, fn () => Helper::_is_transient_locked($transient), 30);

        $this->assertTrue($is_locked);
    }

    public function testTransientLocksExternal()
    {
        $transient = $this->_generate_transient_name();

        Helper::_lock_transient($transient, 30);
        $get_transient = Helper::transient($transient, '__return_true', 30);

        $this->assertFalse($get_transient);
    }

    public function testTransientAsAnonymousFunction()
    {
        $transient = $this->_generate_transient_name();

        $result = Helper::transient($transient, fn () => 'pooptime', 200);
        $this->assertEquals($result, 'pooptime');
    }

    public function testSetTransient()
    {
        $transient = $this->_generate_transient_name();

        $first_value = Helper::transient($transient, fn () => 'first_value', 30);

        $second_value = Helper::transient($transient, fn () => 'second_value', 30);

        $this->assertEquals('first_value', $second_value);
    }

    public function testDisableTransients()
    {
        $transient = $this->_generate_transient_name();

        $first_value = Helper::transient($transient, fn () => 'first_value', 30);

        $second_value = Helper::transient($transient, fn () => 'second_value', false);

        $this->assertEquals('second_value', $second_value);
    }

    public function testTransientAsString()
    {
        $transient = $this->_generate_transient_name();

        $result = Helper::transient($transient, 'Timber\Tests\Cache\my_test_callback', 200);
        $this->assertEquals($result, 'lbj');
    }

    public function testTransientLocked()
    {
        $transient = $this->_generate_transient_name();

        Helper::_lock_transient($transient, 30);

        // Transient is locked and won't be forced, so it should return false
        $get_transient = Helper::transient($transient, '__return_true');

        $this->assertFalse($get_transient);
    }

    public function testTransientForce()
    {
        $transient = $this->_generate_transient_name();

        Helper::_lock_transient($transient, 30);
        $get_transient = Helper::transient($transient, '__return_true', 0, 5, true);

        $this->assertTrue($get_transient);
    }

    public function testTransientForceAllFilter()
    {
        $transient = $this->_generate_transient_name();

        Helper::_lock_transient($transient, 30);

        $this->add_filter_temporarily('timber/transient/force_transients', '__return_true');
        $get_transient = Helper::transient($transient, '__return_true');

        $this->assertTrue($get_transient);
    }

    public function testKeyGenerator()
    {
        $post_id = static::factory()->post->create([
            'post_title' => 'My Test Post',
        ]);
        $post = Timber::get_post($post_id);

        $kg = new KeyGenerator();
        $key = $kg->generateKey($post);

        $this->assertStringStartsWith('Timber;Post;', $key);
    }

    public function testKeyGeneratorWithTimberKeyGeneratorInterface()
    {
        $kg = new KeyGenerator();
        $thing = new MyFakeThing();
        $key = $kg->generateKey($thing);
        $this->assertEquals('iamakey', $key);
    }

    public function testKeyGeneratorWithArray()
    {
        $kg = new KeyGenerator();
        $thing = [
            '_cache_key' => 'iAmAKeyButInAnArray',
        ];
        $key = $kg->generateKey($thing);
        $this->assertEquals('iAmAKeyButInAnArray', $key);
    }

    public function testTransientForceFilter()
    {
        $transient = $this->_generate_transient_name();

        Helper::_lock_transient($transient, 30);

        $this->add_filter_temporarily('timber/transient/force_transient_' . $transient, '__return_true');
        $get_transient = Helper::transient($transient, '__return_true');

        $this->assertTrue($get_transient);
    }

    public function testExpireTransient()
    {
        $transient = $this->_generate_transient_name();

        $first_value = Helper::transient($transient, fn () => 'first_value', 1);

        $this->expireTransient($transient);

        $second_value = Helper::transient($transient, fn () => 'second_value', 1);

        $this->assertEquals('second_value', $second_value);
    }

    #[IgnoreDeprecations]
    public function testTwigCacheDeprecated()
    {
        $this->setExpectedDeprecated('Timber::$cache and Timber::$twig_cache');
        $cache_dir = __DIR__ . '/../../cache/twig';
        if (\is_dir($cache_dir)) {
            Loader::rrmdir($cache_dir);
        }
        $this->assertFileDoesNotExist($cache_dir);
        Timber::$twig_cache = true;
        $pid = static::factory()->post->create();
        $post = Timber::get_post($pid);
        Timber::compile('assets/single-post.twig', [
            'post' => $post,
        ]);
        \sleep(1);
        $this->assertFileExists($cache_dir);
        $loader = new Loader();
        $loader->clear_cache_twig();
        Timber::$twig_cache = false;
        $this->assertFileDoesNotExist($cache_dir);
    }

    #[IgnoreDeprecations]
    public function testTwigCacheAliasDeprecated()
    {
        $this->setExpectedDeprecated('Timber::$cache and Timber::$twig_cache');
        $cache_dir = __DIR__ . '/../../cache/twig';
        if (\is_dir($cache_dir)) {
            Loader::rrmdir($cache_dir);
        }
        $this->assertFileDoesNotExist($cache_dir);
        Timber::$cache = true;
        $pid = static::factory()->post->create();
        $post = Timber::get_post($pid);
        Timber::compile('assets/single-post.twig', [
            'post' => $post,
        ]);
        $this->assertFileExists($cache_dir);
        $loader = new Loader();
        $loader->clear_cache_twig();
        Timber::$cache = false;
        Timber::$twig_cache = false;
        $this->assertFileDoesNotExist($cache_dir);
    }

    public function testTwigCache()
    {
        $cache_dir = __DIR__ . '/../../cache/twig';

        if (\is_dir($cache_dir)) {
            Loader::rrmdir($cache_dir);
        }

        $this->assertFileDoesNotExist($cache_dir);

        $this->add_filter_temporarily('timber/twig/environment/options', function ($options) {
            $options['cache'] = true;
            return $options;
        });

        $pid = static::factory()->post->create();
        $post = Timber::get_post($pid);
        Timber::compile('assets/single-post.twig', [
            'post' => $post,
        ]);
        \sleep(1);

        $this->assertFileExists($cache_dir);

        $loader = new Loader();
        $loader->clear_cache_twig();
        $this->assertFileDoesNotExist($cache_dir);
    }

    public function testTimberLoaderCache()
    {
        $pid = static::factory()->post->create();
        $post = Timber::get_post($pid);
        $str_old = Timber::compile('assets/single-post.twig', [
            'post' => $post,
        ], 600);
        $str_another = Timber::compile('assets/single-parent.twig', [
            'post' => $post,
            'rand' => \random_int(0, 99),
        ], 500);
        $str_new = Timber::compile('assets/single-post.twig', [
            'post' => $post,
        ], 600);
        $this->assertEquals($str_old, $str_new);
        $loader = new Loader();
        $clear = $loader->clear_cache_timber();
        $this->assertGreaterThan(0, $clear);
        global $wpdb;
        $query = "SELECT * FROM $wpdb->options WHERE option_name LIKE '_transient_timberloader_%'";
        $wpdb->query($query);
        $this->assertSame(0, $wpdb->num_rows);
    }

    public function testTimberLoaderCacheObject()
    {
        global $_wp_using_ext_object_cache;
        global $wp_object_cache;
        $_wp_using_ext_object_cache = true;
        $pid = static::factory()->post->create();
        $post = Timber::get_post($pid);
        $str_old = Timber::compile('assets/single-post.twig', [
            'post' => $post,
        ], 600, Loader::CACHE_OBJECT);
        $str_new = Timber::compile('assets/single-post.twig', [
            'post' => $post,
        ], 600, Loader::CACHE_OBJECT);
        $this->assertEquals($str_old, $str_new);
        $loader = new Loader();
        $clear = $loader->clear_cache_timber(Loader::CACHE_OBJECT);
        $this->assertTrue($clear);
        $works = true;

        if (
            isset($wp_object_cache->cache[Loader::CACHEGROUP])
            && !empty($wp_object_cache->cache[Loader::CACHEGROUP])
        ) {
            $works = false;
        }
        $this->assertTrue($works);
    }

    public function testTimberLoaderCacheTransients()
    {
        $time = 1;
        $pid = static::factory()->post->create();
        $post = Timber::get_post($pid);
        $str_old = Timber::compile('assets/single-post.twig', [
            'post' => $post,
            'rand' => \random_int(0, 99999),
        ], $time);
        $this->expireShortTimberLoaderTransients();
        $str_new = Timber::compile('assets/single-post.twig', [
            'post' => $post,
            'rand' => \random_int(0, 99999),
        ], $time);
        $this->assertEquals($str_old, $str_new);
        global $wpdb;
        $query = "SELECT * FROM $wpdb->options WHERE option_name LIKE '_transient_timberloader_%'";
        $wpdb->get_results($query);
        $this->assertSame(1, $wpdb->num_rows);
    }

    public function testTimberLoaderCacheTransientsAdminLoggedIn()
    {
        \wp_set_current_user(1);
        $time = 1;
        $pid = static::factory()->post->create();
        $post = Timber::get_post($pid);
        $r1 = \random_int(0, 999999);
        $r2 = \random_int(0, 999999);
        $str_old = Timber::compile('assets/single-post-rand.twig', [
            'post' => $post,
            'rand' => $r1,
        ], [600, false]);
        $this->_swapFiles();
        $str_new = Timber::compile('assets/single-post-rand.twig', [
            'post' => $post,
            'rand' => $r2,
        ], [600, false]);
        $this->assertNotEquals($str_old, $str_new);
        $this->_unswapFiles();
    }

    public function testTimberTransientCacheWithMultiplePosts()
    {
        $post_ids = static::factory()->post->create_many(3);

        // Cache the first post.
        $this->go_to(\get_permalink($post_ids[0]));
        $context = Timber::context();

        \ob_start();
        Timber::render('assets/single-post-cached.twig', $context, 60);
        $result = \trim(\ob_get_clean());

        $this->assertEquals($post_ids[0], $result);

        // Get second post.
        $this->go_to(\get_permalink($post_ids[1]));
        $context = Timber::context();

        \ob_start();
        Timber::render('assets/single-post-cached.twig', $context, 60);
        $result = \trim(\ob_get_clean());

        $this->assertEquals($post_ids[1], $result);

        // Check if two transients exists.
        global $wpdb;
        $query = "SELECT * FROM {$wpdb->options} WHERE option_name LIKE '_transient_timberloader_%'";
        $wpdb->get_results($query);
        $this->assertSame(2, $wpdb->num_rows);
    }

    protected function _swapFiles()
    {
        $base = __DIR__ . '/../Fixtures/assets';
        \rename($base . '/single-post-rand.twig', $base . '/single-post-rand.twig.bak');
        \copy($base . '/relative.twig', $base . '/single-post-rand.twig');
    }

    protected function _unswapFiles()
    {
        $base = __DIR__ . '/../Fixtures/assets';
        @\unlink($base . '/single-post-rand.twig');
        \rename($base . '/single-post-rand.twig.bak', $base . '/single-post-rand.twig');
    }

    public function testTimberLoaderCacheTransientsAdminLoggedOut()
    {
        $time = 1;
        $pid = static::factory()->post->create();
        $post = Timber::get_post($pid);
        $r1 = \random_int(0, 999999);
        $str_old = Timber::compile('assets/single-post-rand.twig', [
            'post' => $post,
            'rand' => $r1,
        ], [600, false]);
        $this->_swapFiles();
        $str_new = Timber::compile('assets/single-post-rand.twig', [
            'post' => $post,
            'rand' => $r1,
        ], [600, false]);
        $this->assertEquals($str_old, $str_new);
        $this->_unswapFiles();
    }

    public function testTimberLoaderCacheTransientsAdminLoggedOutWithSiteCache()
    {
        $time = 1;
        $pid = static::factory()->post->create();
        $post = Timber::get_post($pid);
        $r1 = \random_int(0, 999999);
        $str_old = Timber::compile('assets/single-post-rand.twig', [
            'post' => $post,
            'rand' => $r1,
        ], [600, false], Loader::CACHE_SITE_TRANSIENT);
        $this->_swapFiles();
        $str_new = Timber::compile('assets/single-post-rand.twig', [
            'post' => $post,
            'rand' => $r1,
        ], [600, false], Loader::CACHE_SITE_TRANSIENT);
        $this->assertEquals($str_old, $str_new);
        $this->_unswapFiles();
    }

    public function testTimberLoaderCacheTransientsAdminLoggedOutWithObjectCache()
    {
        global $_wp_using_ext_object_cache;
        $_wp_using_ext_object_cache = true;
        $time = 1;
        $pid = static::factory()->post->create();
        $post = Timber::get_post($pid);
        $r1 = \random_int(0, 999999);
        $str_old = Timber::compile('assets/single-post-rand.twig', [
            'post' => $post,
            'rand' => $r1,
        ], [600, false], Loader::CACHE_OBJECT);
        $this->_swapFiles();
        $str_new = Timber::compile('assets/single-post-rand.twig', [
            'post' => $post,
            'rand' => $r1,
        ], [600, false], Loader::CACHE_OBJECT);
        $this->assertEquals($str_old, $str_new);
        $this->_unswapFiles();
        $_wp_using_ext_object_cache = false;
    }

    public function testTimberLoaderCacheTransientsWithExtObjectCache()
    {
        global $_wp_using_ext_object_cache;
        $_wp_using_ext_object_cache = true;
        $time = 1;
        $pid = static::factory()->post->create();
        $post = Timber::get_post($pid);
        $r1 = \random_int(0, 999999);
        $r2 = \random_int(0, 999999);
        $str_old = Timber::compile('assets/single-post.twig', [
            'post' => $post,
            'rand' => $r1,
        ], $time);
        $str_new = Timber::compile('assets/single-post.twig', [
            'post' => $post,
            'rand' => $r2,
        ], $time);
        $this->assertEquals($str_old, $str_new);
        global $wpdb;
        $query = "SELECT * FROM $wpdb->options WHERE option_name LIKE '_transient_timberloader_%'";
        $wpdb->get_results($query);
        $this->assertSame(0, $wpdb->num_rows);
        $_wp_using_ext_object_cache = false;
    }

    public function testTimberLoaderCacheTransientsButKeepOtherTransients()
    {
        $time = 1;
        $pid = static::factory()->post->create();
        $post = Timber::get_post($pid);
        \set_transient('random_600', 'foo', 600);
        $random_post = Timber::compile('assets/single-post.twig', [
            'post' => $post,
            'rand' => \random_int(0, 99999),
        ], 600);
        $str_old = Timber::compile('assets/single-post.twig', [
            'post' => $post,
            'rand' => \random_int(0, 99999),
        ], $time);
        $this->expireShortTimberLoaderTransients();
        $str_new = Timber::compile('assets/single-post.twig', [
            'post' => $post,
            'rand' => \random_int(0, 99999),
        ], $time);
        $this->assertEquals($str_old, $str_new);
        global $wpdb;
        $query = "SELECT * FROM $wpdb->options WHERE option_name LIKE '_transient_timberloader_%'";
        $wpdb->get_results($query);
        $this->assertSame(2, $wpdb->num_rows);
        $this->assertEquals('foo', \get_transient('random_600'));
    }

    public function testCacheTransientKeyFilter()
    {
        $this->add_filter_temporarily('timber/cache/transient_key', fn ($key) => 'my_custom_key');

        $loader = new Loader();
        $loader->set_cache('test', 'foobar', Loader::CACHE_TRANSIENT);

        $this->assertEquals('foobar', \get_transient('my_custom_key'));
    }

    /**
     * @see https://github.com/timber/timber/issues/3219
     */
    public function testTransientExpirationNotResetOnCacheHit()
    {
        $pid = static::factory()->post->create();
        $post = Timber::get_post($pid);
        $data = [
            'post' => $post,
        ];

        // First render: cache miss — stores transient with 600 s expiry.
        Timber::compile('assets/single-post.twig', $data, 600);

        global $wpdb;
        $name_query = "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_timberloader_%' LIMIT 1";
        $timeout_option = $wpdb->get_var($name_query);

        // Manually rewrite the timeout to a known FUTURE value distinct from the
        // default `time() + 600`. The cache must remain considered valid (so the
        // next call is a cache HIT), while a buggy reset to `time() + 600` would
        // produce a different value and fail the assertion. Avoids real sleep().
        $expected_timeout = \time() + 60;
        \update_option($timeout_option, $expected_timeout);

        // Second render: cache hit — must NOT reset the transient expiration.
        Timber::compile('assets/single-post.twig', $data, 600);

        $after_timeout = (int) \get_option($timeout_option);

        $this->assertSame(
            $expected_timeout,
            $after_timeout,
            'Transient expiration must not be reset when the cache is already populated (issue #3219).'
        );
    }
}
