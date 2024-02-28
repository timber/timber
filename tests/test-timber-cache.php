<?php

use Timber\Cache\TimberKeyGeneratorInterface;

/**
 * @group called-post-constructor
 */
class TestTimberCache extends Timber_UnitTestCase
{
    private function _generate_transient_name()
    {
        static $i = 0;
        $i++;
        return 'timber_test_transient_' . $i;
    }

    public function testTransientLock()
    {
        $transient = $this->_generate_transient_name();
        Timber\Helper::_lock_transient($transient, 5);
        $this->assertTrue(Timber\Helper::_is_transient_locked($transient));
    }

    public function testTransientUnlock()
    {
        $transient = $this->_generate_transient_name();
        Timber\Helper::_lock_transient($transient, 5);
        Timber\Helper::_unlock_transient($transient, 5);
        $this->assertFalse(Timber\Helper::_is_transient_locked($transient));
    }

    public function testTransientExpire()
    {
        $transient = $this->_generate_transient_name();

        Timber\Helper::_lock_transient($transient, 1);
        sleep(2);
        $this->assertFalse(Timber\Helper::_is_transient_locked($transient));
    }

    public function testTransientLocksInternal()
    {
        $transient = $this->_generate_transient_name();

        $is_locked = Timber\Helper::transient($transient, function () use ($transient) {
            return Timber\Helper::_is_transient_locked($transient);
        }, 30);

        $this->assertTrue($is_locked);
    }

    public function testTransientLocksExternal()
    {
        $transient = $this->_generate_transient_name();

        Timber\Helper::_lock_transient($transient, 30);
        $get_transient = Timber\Helper::transient($transient, '__return_true', 30);

        $this->assertFalse($get_transient);
    }

    public function testTransientAsAnonymousFunction()
    {
        $transient = $this->_generate_transient_name();

        $result = Timber\Helper::transient($transient, function () {
            return 'pooptime';
        }, 200);
        $this->assertEquals($result, 'pooptime');
    }

    public function testSetTransient()
    {
        $transient = $this->_generate_transient_name();

        $first_value = Timber\Helper::transient($transient, function () {
            return 'first_value';
        }, 30);

        $second_value = Timber\Helper::transient($transient, function () {
            return 'second_value';
        }, 30);

        $this->assertEquals('first_value', $second_value);
    }

    public function testDisableTransients()
    {
        $transient = $this->_generate_transient_name();

        $first_value = Timber\Helper::transient($transient, function () {
            return 'first_value';
        }, 30);

        $second_value = Timber\Helper::transient($transient, function () {
            return 'second_value';
        }, false);

        $this->assertEquals('second_value', $second_value);
    }

    public function testTransientAsString()
    {
        $transient = $this->_generate_transient_name();

        $result = Timber\Helper::transient($transient, 'my_test_callback', 200);
        $this->assertEquals($result, 'lbj');
    }

    public function testTransientLocked()
    {
        $transient = $this->_generate_transient_name();

        Timber\Helper::_lock_transient($transient, 30);

        // Transient is locked and won't be forced, so it should return false
        $get_transient = Timber\Helper::transient($transient, '__return_true');

        $this->assertFalse($get_transient);
    }

    public function testTransientForce()
    {
        $transient = $this->_generate_transient_name();

        Timber\Helper::_lock_transient($transient, 30);
        $get_transient = Timber\Helper::transient($transient, '__return_true', 0, 5, true);

        $this->assertTrue($get_transient);
    }

    public function testTransientForceAllFilter()
    {
        $transient = $this->_generate_transient_name();

        Timber\Helper::_lock_transient($transient, 30);

        add_filter('timber/transient/force_transients', '__return_true');
        $get_transient = Timber\Helper::transient($transient, '__return_true');
        remove_filter('timber/transient/force_transients', '__return_true');

        $this->assertTrue($get_transient);
    }

    public function testKeyGenerator()
    {
        $post_id = $this->factory->post->create([
            'post_title' => 'My Test Post',
        ]);
        $post = Timber::get_post($post_id);

        $kg = new Timber\Cache\KeyGenerator();
        $key = $kg->generateKey($post);

        $this->assertStringStartsWith('Timber;Post;', $key);
    }

    public function testKeyGeneratorWithTimberKeyGeneratorInterface()
    {
        $kg = new Timber\Cache\KeyGenerator();
        $thing = new MyFakeThing();
        $key = $kg->generateKey($thing);
        $this->assertEquals('iamakey', $key);
    }

    public function testKeyGeneratorWithArray()
    {
        $kg = new Timber\Cache\KeyGenerator();
        $thing = [
            '_cache_key' => 'iAmAKeyButInAnArray',
        ];
        $key = $kg->generateKey($thing);
        $this->assertEquals('iAmAKeyButInAnArray', $key);
    }

    public function testTransientForceFilter()
    {
        $transient = $this->_generate_transient_name();

        Timber\Helper::_lock_transient($transient, 30);

        $this->add_filter_temporarily('timber/transient/force_transient_' . $transient, '__return_true');
        $get_transient = Timber\Helper::transient($transient, '__return_true');

        $this->assertTrue($get_transient);
    }

    public function testExpireTransient()
    {
        $transient = $this->_generate_transient_name();

        $first_value = Timber\Helper::transient($transient, function () {
            return 'first_value';
        }, 1);

        sleep(2);

        $second_value = Timber\Helper::transient($transient, function () {
            return 'second_value';
        }, 1);

        $this->assertEquals('second_value', $second_value);
    }

    /**
     * @expectedDeprecated Timber::$cache and Timber::$twig_cache
     */
    public function testTwigCacheDeprecated()
    {
        $cache_dir = __DIR__ . '/../cache/twig';
        if (is_dir($cache_dir)) {
            Timber\Loader::rrmdir($cache_dir);
        }
        $this->assertFileDoesNotExist($cache_dir);
        Timber::$twig_cache = true;
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        Timber::compile('assets/single-post.twig', [
            'post' => $post,
        ]);
        sleep(1);
        $this->assertFileExists($cache_dir);
        $loader = new Timber\Loader();
        $loader->clear_cache_twig();
        Timber::$twig_cache = false;
        $this->assertFileDoesNotExist($cache_dir);
    }

    /**
     * @expectedDeprecated Timber::$cache and Timber::$twig_cache
     */
    public function testTwigCacheAliasDeprecated()
    {
        $cache_dir = __DIR__ . '/../cache/twig';
        if (is_dir($cache_dir)) {
            Timber\Loader::rrmdir($cache_dir);
        }
        $this->assertFileDoesNotExist($cache_dir);
        Timber::$cache = true;
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        Timber::compile('assets/single-post.twig', [
            'post' => $post,
        ]);
        //sleep(1);
        $this->assertFileExists($cache_dir);
        $loader = new Timber\Loader();
        $loader->clear_cache_twig();
        Timber::$cache = false;
        Timber::$twig_cache = false;
        $this->assertFileDoesNotExist($cache_dir);
    }

    public function testTwigCache()
    {
        $cache_dir = __DIR__ . '/../cache/twig';

        if (is_dir($cache_dir)) {
            Timber\Loader::rrmdir($cache_dir);
        }

        $this->assertFileDoesNotExist($cache_dir);

        $cache_enabler = function ($options) {
            $options['cache'] = true;

            return $options;
        };

        add_filter('timber/twig/environment/options', $cache_enabler);

        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        Timber::compile('assets/single-post.twig', [
            'post' => $post,
        ]);
        sleep(1);

        $this->assertFileExists($cache_dir);

        $loader = new Timber\Loader();
        $loader->clear_cache_twig();
        $this->assertFileDoesNotExist($cache_dir);

        remove_filter('timber/twig/environment/options', $cache_enabler);
    }

    public function testTimberLoaderCache()
    {
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $str_old = Timber::compile('assets/single-post.twig', [
            'post' => $post,
        ], 600);
        $str_another = Timber::compile('assets/single-parent.twig', [
            'post' => $post,
            'rand' => rand(0, 99),
        ], 500);
        //sleep(1);
        $str_new = Timber::compile('assets/single-post.twig', [
            'post' => $post,
        ], 600);
        $this->assertEquals($str_old, $str_new);
        $loader = new Timber\Loader();
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
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $str_old = Timber::compile('assets/single-post.twig', [
            'post' => $post,
        ], 600, Timber\Loader::CACHE_OBJECT);
        //sleep(1);
        $str_new = Timber::compile('assets/single-post.twig', [
            'post' => $post,
        ], 600, Timber\Loader::CACHE_OBJECT);
        $this->assertEquals($str_old, $str_new);
        $loader = new Timber\Loader();
        $clear = $loader->clear_cache_timber(Timber\Loader::CACHE_OBJECT);
        $this->assertTrue($clear);
        $works = true;

        if (isset($wp_object_cache->cache[Timber\Loader::CACHEGROUP])
            && !empty($wp_object_cache->cache[Timber\Loader::CACHEGROUP])) {
            $works = false;
        }
        $this->assertTrue($works);
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

    public function testTimberLoaderCacheTransients()
    {
        $time = 1;
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $str_old = Timber::compile('assets/single-post.twig', [
            'post' => $post,
            'rand' => rand(0, 99999),
        ], $time);
        sleep(2);
        $str_new = Timber::compile('assets/single-post.twig', [
            'post' => $post,
            'rand' => rand(0, 99999),
        ], $time);
        $this->assertEquals($str_old, $str_new);
        global $wpdb;
        $query = "SELECT * FROM $wpdb->options WHERE option_name LIKE '_transient_timberloader_%'";
        $data = $wpdb->get_results($query);
        $this->assertSame(1, $wpdb->num_rows);
    }

    public function testTimberLoaderCacheTransientsAdminLoggedIn()
    {
        wp_set_current_user(1);
        $time = 1;
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $r1 = rand(0, 999999);
        $r2 = rand(0, 999999);
        $str_old = Timber::compile('assets/single-post-rand.twig', [
            'post' => $post,
            'rand' => $r1,
        ], [600, false]);
        self::_swapFiles();
        $str_new = Timber::compile('assets/single-post-rand.twig', [
            'post' => $post,
            'rand' => $r2,
        ], [600, false]);
        $this->assertNotEquals($str_old, $str_new);
        self::_unswapFiles();
    }

    public function testTimberTransientCacheWithMultiplePosts()
    {
        $post_ids = $this->factory->post->create_many(3);

        // Cache the first post.
        $this->go_to(get_permalink($post_ids[0]));
        $context = Timber::context();

        ob_start();
        Timber::render('assets/single-post-cached.twig', $context, 60);
        $result = trim(ob_get_clean());

        $this->assertEquals($post_ids[0], $result);

        // Get second post.
        $this->go_to(get_permalink($post_ids[1]));
        $context = Timber::context();

        ob_start();
        Timber::render('assets/single-post-cached.twig', $context, 60);
        $result = trim(ob_get_clean());

        $this->assertEquals($post_ids[1], $result);

        // Check if two transients exists.
        global $wpdb;
        $query = "SELECT * FROM {$wpdb->options} WHERE option_name LIKE '_transient_timberloader_%'";
        $wpdb->get_results($query);
        $this->assertSame(2, $wpdb->num_rows);
    }

    public function _swapFiles()
    {
        rename(__DIR__ . '/assets/single-post-rand.twig', __DIR__ . '/assets/single-post-rand.twig.tmp');
        rename(__DIR__ . '/assets/relative.twig', __DIR__ . '/assets/single-post-rand.twig');
    }

    public function _unswapFiles()
    {
        rename(__DIR__ . '/assets/single-post-rand.twig', __DIR__ . '/assets/relative.twig');
        rename(__DIR__ . '/assets/single-post-rand.twig.tmp', __DIR__ . '/assets/single-post-rand.twig');
    }

    public function testTimberLoaderCacheTransientsAdminLoggedOut()
    {
        $time = 1;
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $r1 = rand(0, 999999);
        $str_old = Timber::compile('assets/single-post-rand.twig', [
            'post' => $post,
            'rand' => $r1,
        ], [600, false]);
        self::_swapFiles();
        $str_new = Timber::compile('assets/single-post-rand.twig', [
            'post' => $post,
            'rand' => $r1,
        ], [600, false]);
        $this->assertEquals($str_old, $str_new);
        self::_unswapFiles();
    }

    public function testTimberLoaderCacheTransientsAdminLoggedOutWithSiteCache()
    {
        $time = 1;
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $r1 = rand(0, 999999);
        $str_old = Timber::compile('assets/single-post-rand.twig', [
            'post' => $post,
            'rand' => $r1,
        ], [600, false], Timber\Loader::CACHE_SITE_TRANSIENT);
        self::_swapFiles();
        $str_new = Timber::compile('assets/single-post-rand.twig', [
            'post' => $post,
            'rand' => $r1,
        ], [600, false], Timber\Loader::CACHE_SITE_TRANSIENT);
        $this->assertEquals($str_old, $str_new);
        self::_unswapFiles();
    }

    public function testTimberLoaderCacheTransientsAdminLoggedOutWithObjectCache()
    {
        global $_wp_using_ext_object_cache;
        $_wp_using_ext_object_cache = true;
        $time = 1;
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $r1 = rand(0, 999999);
        $str_old = Timber::compile('assets/single-post-rand.twig', [
            'post' => $post,
            'rand' => $r1,
        ], [600, false], Timber\Loader::CACHE_OBJECT);
        self::_swapFiles();
        $str_new = Timber::compile('assets/single-post-rand.twig', [
            'post' => $post,
            'rand' => $r1,
        ], [600, false], Timber\Loader::CACHE_OBJECT);
        $this->assertEquals($str_old, $str_new);
        self::_unswapFiles();
        $_wp_using_ext_object_cache = false;
    }

    public function testTimberLoaderCacheTransientsWithExtObjectCache()
    {
        global $_wp_using_ext_object_cache;
        $_wp_using_ext_object_cache = true;
        $time = 1;
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $r1 = rand(0, 999999);
        $r2 = rand(0, 999999);
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
        $data = $wpdb->get_results($query);
        $this->assertSame(0, $wpdb->num_rows);
        $_wp_using_ext_object_cache = false;
    }

    public function testTimberLoaderCacheTransientsButKeepOtherTransients()
    {
        $time = 1;
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        set_transient('random_600', 'foo', 600);
        $random_post = Timber::compile('assets/single-post.twig', [
            'post' => $post,
            'rand' => rand(0, 99999),
        ], 600);
        $str_old = Timber::compile('assets/single-post.twig', [
            'post' => $post,
            'rand' => rand(0, 99999),
        ], $time);
        sleep(2);
        $str_new = Timber::compile('assets/single-post.twig', [
            'post' => $post,
            'rand' => rand(0, 99999),
        ], $time);
        $this->assertEquals($str_old, $str_new);
        global $wpdb;
        $query = "SELECT * FROM $wpdb->options WHERE option_name LIKE '_transient_timberloader_%'";
        $data = $wpdb->get_results($query);
        $this->assertSame(2, $wpdb->num_rows);
        $this->assertEquals('foo', get_transient('random_600'));
    }

    public function testCacheTransientKeyFilter()
    {
        $filter = function ($key) {
            return 'my_custom_key';
        };
        add_filter('timber/cache/transient_key', $filter);

        $loader = new Timber\Loader();
        $loader->set_cache('test', 'foobar', Timber\Loader::CACHE_TRANSIENT);

        remove_filter('timber/cache/transient_key', $filter);

        $this->assertEquals('foobar', get_transient('my_custom_key'));
    }
}

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
