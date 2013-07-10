<?php
	
	require_once('../../timber.php');

	class TimberTest extends WP_UnitTestCase {

		private $plugin;

		function setUp(){
			parent::setUp();
			$this->plugin = $GLOBALS['timber'];
		}

		function testPluginInitialization(){
			$this->assertFalse(null == $this->plugin);
		}

		function testGetPost(){
			$this->assertEquals('1', $this->plugin->get_post(1)->ID, 'get_post() is getting the "Hello World" post');
		}

		function testGetPostTitle(){
			$this->assertEquals('Hello world!', $this->plugin->get_post(1)->post_title, 'get_post() is getting the "Hello World" post');
		}

		function testGetPage(){
			$this->assertEquals('page', $this->plugin->get_posts('post_type=page')[0]->post_type);
		}

		function testGetPosts(){
			$query = array('post_type'=>array('page', 'post'));
			$this->assertGreaterThanOrEqual(2, count($this->plugin->get_posts($query)));
		}

		function testGetPids(){
			$query = array('post_type'=>array('page', 'post'));
			$pids = $this->plugin->get_pids($query);
			$this->assertContainsOnly('integer', $pids);
		}
	}