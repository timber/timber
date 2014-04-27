<?php

	class TestTimberHelper extends WP_UnitTestCase {

        function testCommentForm() {
            $post_id = $this->factory->post->create();
            $form = TimberHelper::get_comment_form($post_id);
            $form = trim($form);
            $this->assertStringStartsWith('<div id="respond"', $form);
        }

        function testWPTitle(){
        	//since we're testing with twentyfourteen -- need to remove its filters on wp_title
        	remove_all_filters('wp_title');
        	$this->assertEquals('', TimberHelper::get_wp_title());
        }

        function testWPTitleSingle(){
        	//since we're testing with twentyfourteen -- need to remove its filters on wp_title
        	remove_all_filters('wp_title');
        	$post_id = $this->factory->post->create(array('post_title' => 'My New Post'));
        	$post = get_post($post_id);
        	global $wp_query;
        	$wp_query = new WP_Query('p='.$post_id);
        	$this->assertEquals('My New Post', TimberHelper::get_wp_title());
        }
    }
