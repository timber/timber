<?php

	class TestTimberDeprecated extends Timber_UnitTestCase {

		function testGetPostByMeta() {
			$post_id = $this->factory->post->create(array('post_title' => 'Hugh Abbot'));
			$position = 'Secretary of State for Social Affairs';
			update_post_meta($post_id, 'position', $position);
			$pid = TimberHelper::get_post_by_meta('position', $position);
			$this->assertEquals($post_id, $pid);
		}

		function testGetPostsByMeta() {
			$pids = array();
			$lauren = $this->factory->post->create(array('post_title' => 'Lauren Richler'));
			$jared = $this->factory->post->create(array('post_title' => 'Jared Novack'));
			update_post_meta($lauren, 'in', 'love');
			update_post_meta($jared, 'in', 'love');
			$in_love = TimberHelper::get_posts_by_meta('in', 'love');
			$this->assertContains($lauren, $in_love);
			$this->assertContains($jared, $in_love);
			$this->assertEquals(2, count($in_love));
		}

		function testTwitterify() {
			$this->markTestSkipped('It belongs to the ages now');
		}

	}
