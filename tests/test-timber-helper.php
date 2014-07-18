<?php

	class TestTimberHelper extends WP_UnitTestCase {

        function testCloseTagsWithSelfClosingTags(){
            $p = '<p>My thing is this <hr>Whatever';
            $html = TimberHelper::close_tags($p);
            $this->assertEquals('<p>My thing is this <hr />Whatever</p>', $html);
        }

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
            $this->go_to( site_url( '?p='.$post_id ) );
        	$this->assertEquals('My New Post', TimberHelper::get_wp_title());
        }

        function testCloseTags(){
            $str = '<a href="http://wordpress.org">Hi!';
            $closed = TimberHelper::close_tags($str);
            $this->assertEquals($str.'</a>', $closed);
        }

        function testArrayToObject(){
            $arr = array('jared' => 'super cool');
            $obj = TimberHelper::array_to_object($arr);
            $this->assertEquals('super cool', $obj->jared);
        }

        function testGetObjectIndexByProperty(){
            $obj1 = new stdClass();
            $obj1->name = 'mark';
            $obj1->skill = 'acro yoga';
            $obj2 = new stdClass();
            $obj2->name = 'austin';
            $obj2->skill = 'cooking';
            $arr = array($obj1, $obj2);
            $index = TimberHelper::get_object_index_by_property($arr, 'skill', 'cooking');
            $this->assertEquals(1, $index);
            $obj = TimberHelper::get_object_by_property($arr, 'skill', 'cooking');
            $this->assertEquals('austin', $obj->name);
        }
    }
