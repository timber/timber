<?php
	
	require_once('../objects/timber-post.php');

	class TimberPostTest extends PHPUnit_testCase {
		var $post;

		function TimberPostTest($query){
			$this->PHPUnit_TestCase($query);
		}

		function setUp(){
			$this->post = new TimberPost();
		}

		function tearDown(){
			unset($this->post);
		}

		function test_get_thumbnail(){
			$image = $this->post->get_thumbnail();
			$this->assertTrue(is_string($image->get_src));
		}

		
	}