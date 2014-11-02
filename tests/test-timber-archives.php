<?php

	class TimberArchiveTest extends WP_UnitTestCase {

		function testArchiveMonthly(){
			$pids = array();
			$pids[] = $this->factory->post->create(array('post_date' => '2013-11-08 19:46:41'));
			$pids[] = $this->factory->post->create(array('post_date' => '2013-12-08 19:46:41'));
			$pids[] = $this->factory->post->create(array('post_date' => '2013-11-09 19:46:41'));
			$pids[] = $this->factory->post->create(array('post_date' => '2013-06-08 19:46:41'));
			$this->go_to('/');
			$archives = new TimberArchives();
			$this->assertEquals(3, count($archives->items));
		}

		function testArchiveYearlyMonthly(){
			$pids = array();
			$pids[] = $this->factory->post->create(array('post_date' => '2013-11-08 19:46:41'));
			$pids[] = $this->factory->post->create(array('post_date' => '2013-12-08 19:46:41'));
			$pids[] = $this->factory->post->create(array('post_date' => '2013-11-09 19:46:41'));
			$pids[] = $this->factory->post->create(array('post_date' => '2013-06-08 19:46:41'));
			$pids[] = $this->factory->post->create(array('post_date' => '2014-01-08 19:46:41'));
			$this->go_to('/');
			$archives = new TimberArchives();
			$this->assertEquals(4, count($archives->items));
		}

	}