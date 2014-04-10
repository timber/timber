<?php

	class TestTimberHelper extends WP_UnitTestCase {

        function testCommentForm() {
            $post_id = $this->factory->post->create();
            $form = TimberHelper::get_comment_form($post_id);
            $form = trim($form);
            $this->assertStringStartsWith('<div id="respond" class="comment-respond">', $form);
        }	
    }