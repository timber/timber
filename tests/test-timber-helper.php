<?php

class TimberHelperTest extends WP_UnitTestCase {

    public function testTransientLock() {
        TimberHelper::_lock_transient( 'timber_test_transient', 5 );
        $this->assertTrue( TimberHelper::_is_transient_locked( 'timber_test_transient' ) );
    }

    public function testTransientUnlock() {
        TimberHelper::_lock_transient( 'timber_test_transient', 5 );
        TimberHelper::_unlock_transient( 'timber_test_transient', 5 );
        $this->assertFalse( TimberHelper::_is_transient_locked( 'timber_test_transient' ) );
    }

    public function testTransientExpire() {
        TimberHelper::_lock_transient( 'timber_test_transient', 1 );
        sleep(2);
        $this->assertFalse( TimberHelper::_is_transient_locked( 'timber_test_transient' ) );
    }

    public function testTransientLocksInternal() {
        $is_locked = TimberHelper::transient( 'timber_test_transient', function() {
            return TimberHelper::_is_transient_locked( 'timber_test_transient' );
        }, 30 );

        $this->assertTrue( $is_locked );
    }

    public function testTransientLocksExternal() {
        TimberHelper::_lock_transient('timber_test_transient', 30);
        $get_transient = TimberHelper::transient( 'timber_test_transient', '__return_true' );

        $this->assertFalse( $get_transient );
    }

    public function testTransientForce() {
        TimberHelper::_lock_transient('timber_test_transient', 30);
        $get_transient = TimberHelper::transient( 'timber_test_transient', '__return_true', 0, 5, true );

        $this->assertTrue( $get_transient );
    }

    public function testSetTransient() {
        $first_value = TimberHelper::transient( 'timber_test_transient', function(){
            return 'first_value';
        }, 30 );

        $second_value = TimberHelper::transient( 'timber_test_transient', function(){
            return 'second_value';
        }, 30 );

        $this->assertEquals( 'first_value', $second_value );
    }

    public function testExpireTransient() {
        $first_value = TimberHelper::transient( 'timber_test_transient', function(){
            return 'first_value';
        }, 2 );

        sleep(3);

        $second_value = TimberHelper::transient( 'timber_test_transient', function(){
            return 'second_value';
        }, 2 );

        $this->assertEquals( 'second_value', $second_value );
    }

}