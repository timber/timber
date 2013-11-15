<?php

	class TestTimberCache extends WP_UnitTestCase {

		function testTransientCacheLock(){
			$transientGood = TimberHelper::transient('jn_test', array($this, 'trans_cachelock_test'));
			sleep(2);
			$transientBad = TimberHelper::transient('jn_test', array($this, 'trans_cachelock_test_two'));
			$this->assertEquals($transientGood, 'poop');
			$this->assertEquals($transientBad, 'poop');
		}

		function trans_cachelock_test(){
			return 'poop';
		}

		function trans_cachelock_test_two(){
			//should never run since 'jn_test' gets cached;
			return 'poopy pants';
		}

	}