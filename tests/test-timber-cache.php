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

	}

	function my_test_callback(){
		return "lbj";
	}