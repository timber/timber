<?php

	class TestTimberPostPreviewObject extends Timber_UnitTestCase {

		function testPostPreviewObjectWtihLength() {
			$pid = $this->factory->post->create( array('post_content' => 'Lauren is a duck she a big ole duck!', 'post_excerpt' => '') );
			$template = '{{ post.preview.length(3) }}';
			$post = new TimberPost($pid);
			$str = Timber::compile_string($template, array('post' => $post));
			echo $str;
		}

	}