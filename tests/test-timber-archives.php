<?php

	class TimberArchiveTest extends WP_UnitTestCase {

		function testArchiveMonthly(){
			$dates = array('2013-11-08', '2013-12-08', '2013-11-09', '2013-06-08');
			foreach( $dates as $date ) {
				$this->factory->post->create(array('post_date' => $date.' 19:46:41'));
			}
			$this->go_to('/');
			$archives = new TimberArchives();
			$this->assertEquals(3, count($archives->items));
		}

		function testArchiveYearlyMonthly(){
			$dates = array('2013-11-08', '2013-12-08', '2013-11-09', '2013-06-08', '2014-01-08'
				);
			foreach( $dates as $date ) {
				$this->factory->post->create(array('post_date' => $date.' 19:46:41'));
			}
			$this->go_to('/');
			$archives = new TimberArchives();
			$this->assertEquals(4, count($archives->items));
		}

		function testArchivesWithArgs() {
			$dates = array('2013-11-08', '2013-12-08', '2013-11-09', '2013-06-08', '2014-01-08'
				);
			foreach($dates as $date) {
				$this->factory->post->create(array('post_date' => $date. ' 19:46:41'));
			}
			$dates = array('2014-11-08', '2014-12-08', '2014-11-09', '2014-06-08', '2015-01-08', '2015-02-14'
				);
			foreach($dates as $date) {
				$this->factory->post->create(array('post_date' => $date. ' 19:46:41', 'post_type' => 'book'));
			}
			$this->go_to('/');
			$archives = new TimberArchives();
			$this->assertEquals(4, count($archives->items));
			$archives = new TimberArchives(array('post_type' => 'book'));
			$this->assertEquals(5, count($archives->items));
		}

	}
