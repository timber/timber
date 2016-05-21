<?php

	class TestTimberPostPreviewObject extends Timber_UnitTestCase {

		function testPostPreviewObjectWithLength() {
			$pid = $this->factory->post->create( array('post_content' => 'Lauren is a duck she a big ole duck!', 'post_excerpt' => '') );
			$template = '{{ post.preview.length(4) }}';
			$post = new TimberPost($pid);
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('Lauren is a duck&hellip; <a href="http://example.org/?p='.$pid.'" class="read-more">Read More</a>', $str);
		}

		function testPostPreviewObjectWithForcedLength() {
			$pid = $this->factory->post->create( array('post_content' => 'Great Gatsby', 'post_excerpt' => 'In my younger and more vulnerable years my father gave me some advice that I’ve been turning over in my mind ever since.') );
			$template = '{{ post.preview.force.length(3) }}';
			$post = new TimberPost($pid);
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('In my younger&hellip; <a href="http://example.org/?p='.$pid.'" class="read-more">Read More</a>', $str);
		}

		function testPostPreviewObject() {
			$pid = $this->factory->post->create( array('post_content' => 'Great Gatsby', 'post_excerpt' => 'In my younger and more vulnerable years my father gave me some advice that I’ve been turning over in my mind ever since.') );
			$template = '{{ post.preview }}';
			$post = new TimberPost($pid);
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('In my younger and more vulnerable years my father gave me some advice that I’ve been turning over in my mind ever since. <a href="http://example.org/?p='.$pid.'" class="read-more">Read More</a>', $str);
		}

		function testPostPreviewObjectStrip() {
			$pid = $this->factory->post->create( array('post_content' => 'Great Gatsby', 'post_excerpt' => 'In my younger and more vulnerable years my father gave me some advice that I’ve been <a href="http://google.com">turning over</a> in my mind ever since.') );
			$template = '{{ post.preview.strip(false) }}';
			$post = new TimberPost($pid);
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('In my younger and more vulnerable years my father gave me some advice that I’ve been <a href="http://google.com">turning over</a> in my mind ever since. <a href="http://example.org/?p='.$pid.'" class="read-more">Read More</a>', $str);
		}

		function testPostPreviewObjectWithReadMore() {
			$pid = $this->factory->post->create( array('post_content' => 'Great Gatsby', 'post_excerpt' => 'In my younger and more vulnerable years my father gave me some advice that I’ve been turning over in my mind ever since.') );
			$template = '{{ post.preview.read_more("Keep Reading") }}';
			$post = new TimberPost($pid);
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('In my younger and more vulnerable years my father gave me some advice that I’ve been turning over in my mind ever since. <a href="http://example.org/?p='.$pid.'" class="read-more">Keep Reading</a>', $str);
		}

		function testPostPreviewObjectWithEverything() {
			$pid = $this->factory->post->create( array('post_content' => 'Great Gatsby', 'post_excerpt' => 'In my younger and more vulnerable years my father gave me some advice that I’ve been turning over in my mind ever since.') );
			$template = '{{ post.preview.length(6).force.end("-->").read_more("Keep Reading") }}';
			$post = new TimberPost($pid);
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('In my younger and more vulnerable--> <a href="http://example.org/?p='.$pid.'" class="read-more">Keep Reading</a>', $str);
		}


	}