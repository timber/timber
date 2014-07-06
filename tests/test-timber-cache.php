<?php

	class TestTimberCache extends WP_UnitTestCase {

        private function _generate_transient_name() {
            static $i = 0;
            $i++;
            return 'timber_test_transient_' . $i;
        }

        function testTransientLock() {
            $transient = $this->_generate_transient_name();
            TimberHelper::_lock_transient( $transient, 5 );
            $this->assertTrue( TimberHelper::_is_transient_locked( $transient ) );
        }

        function testTransientUnlock() {
            $transient = $this->_generate_transient_name();
            TimberHelper::_lock_transient( $transient, 5 );
            TimberHelper::_unlock_transient( $transient, 5 );
            $this->assertFalse( TimberHelper::_is_transient_locked( $transient ) );
        }

        function testTransientExpire() {
            $transient = $this->_generate_transient_name();

            TimberHelper::_lock_transient( $transient, 1 );
            sleep(2);
            $this->assertFalse( TimberHelper::_is_transient_locked( $transient ) );
        }

        function testTransientLocksInternal() {
            $transient = $this->_generate_transient_name();

            $is_locked = TimberHelper::transient( $transient, function() use ( $transient ) {
                return TimberHelper::_is_transient_locked( $transient );
            }, 30 );

            $this->assertTrue( $is_locked );
        }

        function testTransientLocksExternal() {
            $transient = $this->_generate_transient_name();

            TimberHelper::_lock_transient($transient, 30);
            $get_transient = TimberHelper::transient( $transient, '__return_true', 30 );

            $this->assertFalse( $get_transient );
        }

		function testTransientAsAnonymousFunction(){
            $transient = $this->_generate_transient_name();

			$result = TimberHelper::transient( $transient, function(){
				return 'pooptime';
			}, 200);
			$this->assertEquals( $result, 'pooptime');
		}

        function testSetTransient() {
            $transient = $this->_generate_transient_name();

            $first_value = TimberHelper::transient( $transient, function(){
                return 'first_value';
            }, 30 );

            $second_value = TimberHelper::transient( $transient, function(){
                return 'second_value';
            }, 30 );

            $this->assertEquals( 'first_value', $second_value );
        }

        function testDisableTransients() {
            $transient = $this->_generate_transient_name();

            $first_value = TimberHelper::transient( $transient, function(){
                return 'first_value';
            }, 30 );

            $second_value = TimberHelper::transient( $transient, function(){
                return 'second_value';
            }, false );

            $this->assertEquals( 'second_value', $second_value );
        }

		function testTransientAsString(){
            $transient = $this->_generate_transient_name();

			$result = TimberHelper::transient( $transient, 'my_test_callback', 200);
			$this->assertEquals($result, 'lbj');
		}

        function testTransientLocked() {
            $transient = $this->_generate_transient_name();

            TimberHelper::_lock_transient($transient, 30);

            // Transient is locked and won't be forced, so it should return false
            $get_transient = TimberHelper::transient( $transient, '__return_true' );

            $this->assertFalse( $get_transient );
        }

        function testTransientForce() {
            $transient = $this->_generate_transient_name();

            TimberHelper::_lock_transient($transient, 30);
            $get_transient = TimberHelper::transient( $transient, '__return_true', 0, 5, true );

            $this->assertTrue( $get_transient );
        }

        function testTransientForceAllFilter() {
            $transient = $this->_generate_transient_name();

            TimberHelper::_lock_transient($transient, 30);

            add_filter( 'timber_force_transients', '__return_true' );
            $get_transient = TimberHelper::transient( $transient, '__return_true' );
            remove_filter( 'timber_force_transients', '__return_true' );

            $this->assertTrue( $get_transient );
        }

        function testKeyGenerator(){
        	$loader = new TimberLoader();
        	$twig = $loader->get_twig();
        	$kg = new Timber\Cache\KeyGenerator();
        	$post_id = $this->factory->post->create(array('post_title' => 'My Test Post'));
        	$post = new TimberPost($post_id);
        	$key = $kg->generateKey($post);
        	$this->assertStringStartsWith('TimberPost|', $key);
        }

        function testTransientForceFilter() {
            $transient = $this->_generate_transient_name();

            TimberHelper::_lock_transient($transient, 30);

            add_filter( 'timber_force_transient_' . $transient, '__return_true' );
            $get_transient = TimberHelper::transient( $transient, '__return_true' );
            remove_filter( 'timber_force_transient_' . $transient, '__return_true' );

            $this->assertTrue( $get_transient );
        }

        function testExpireTransient() {
            $transient = $this->_generate_transient_name();

            $first_value = TimberHelper::transient( $transient, function(){
                return 'first_value';
            }, 2 );

            sleep(3);

            $second_value = TimberHelper::transient( $transient, function(){
                return 'second_value';
            }, 2 );

            $this->assertEquals( 'second_value', $second_value );
        }

        function testTwigCache(){
        	$cache_dir = __DIR__.'/../cache/twig';
        	if (is_dir($cache_dir)){
        		TimberLoader::rrmdir($cache_dir);
        	}
        	$this->assertFileNotExists($cache_dir);
        	Timber::$cache = true;
        	$pid = $this->factory->post->create();
        	$post = new TimberPost($pid);
        	Timber::compile('assets/single-post.twig', array('post' => $post));
        	sleep(1);
        	$this->assertFileExists($cache_dir);
        	Timber::$cache = false;
        	$loader = new TimberLoader();
        	$loader->clear_cache_twig();
        	$this->assertFileNotExists($cache_dir);
        }

        function testTimberLoaderCache(){
            global $wp_object_cache;
            $pid = $this->factory->post->create();
            $post = new TimberPost($pid);
            $str_old = Timber::compile('assets/single-post.twig', array('post' => $post), 600);
            sleep(1);
            $str_new = Timber::compile('assets/single-post.twig', array('post' => $post), 600);
            $this->assertEquals($str_old, $str_new);
            $loader = new TimberLoader();
            $clear = $loader->clear_cache_timber();
            $this->assertTrue($clear);
            global $wpdb;
            $query = "SELECT * FROM $wpdb->options WHERE option_name LIKE '_transient_timberloader_%'";
            $wpdb->query( $query );
            $this->assertEquals(0, $wpdb->num_rows);

        }

	}

	function my_test_callback(){
		return "lbj";
	}