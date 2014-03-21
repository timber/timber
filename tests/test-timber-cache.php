<?php

	class TestTimberCache extends WP_UnitTestCase {

		function testTransientCacheLock(){
			$transientGood = TimberHelper::transient('jn_test', array($this, 'trans_cachelock_test'));
			sleep(2);
			$transientBad = TimberHelper::transient('jn_test', array($this, 'trans_cachelock_test_two'));
			$this->assertEquals($transientGood, 'poop');
			$this->assertEquals($transientBad, 'poop');
		}

		function testTransientAsAnonymousFunction(){
			$transient = TimberHelper::transient('jn_test_anon', function(){
				return 'pooptime';
			}, 200);
			$this->assertEquals($transient, 'pooptime');
		}

		function testTransientAsString(){
			$transient = TimberHelper::transient('jn_test_string', 'my_test_callback', 200);
			$this->assertEquals($transient, 'lbj');
		}

		function trans_cachelock_test(){
			return 'poop';
		}

		function trans_cachelock_test_two(){
			//should never run since 'jn_test' gets cached;
			return 'poopy pants';
		}

	}

	function my_test_callback(){
		return "lbj";
	}