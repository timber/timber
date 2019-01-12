<?php

	class TestTimberPostPreviewObject extends Timber_UnitTestCase {

		protected $gettysburg = 'Four score and seven years ago our fathers brought forth on this continent a new nation, conceived in liberty, and dedicated to the proposition that all men are created equal.';

		function test1886Error() {
			$expected = '<p>Govenment:</p> <ul> <li>of the <strong>people</strong></li> <li>by the people</li> <li>for the people</li> </ul>';
			$post_id = $this->factory->post->create(array('post_content' => $expected.'<blockquote>Lincoln</blockquote>', 'post_excerpt' => false));
			$post = new Timber\Post($post_id);
			$template = "{{ post.preview.strip('<p><strong><ul><ol><li><br>') }}";
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals($expected.' <p>Lincoln</p>&hellip; <a href="http://example.org/?p='.$post_id.'" class="read-more">Read More</a>', $str);
		}

		function test1886ErrorWithForce() {
			$expected = '<p>Govenment:</p> <ul> <li>of the <strong>people</strong></li> <li>by the people</li> <li>for the people</li> </ul>';
			$post_id = $this->factory->post->create(array('post_excerpt' => $expected, 'post_content' => $this->gettysburg));
			$post = new Timber\Post($post_id);
			$template = "{{ post.preview.strip('<ul><li>').length(10).force }}";
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('Govenment: <ul> <li>of the people</li> <li>by the people</li> <li>for the</li></ul>&hellip; <a href="http://example.org/?p='.$post_id.'" class="read-more">Read More</a>', $str);
		}

		function testPreviewWithStyleTags() {
			global $wpdb;
			$style = '<style>body { background-color: red; }</style><b>Yo.</b> ';
			$id = $wpdb->insert( 
				$wpdb->posts, 
				array( 
					'post_author' => '1', 
					'post_content' => $style.$this->gettysburg,
					'post_title' => 'Thing',
					'post_date' => '2017-03-01 00:21:40',
					'post_date_gmt' => '2017-03-01 00:21:40'
				)
			);
			$post_id = $wpdb->insert_id;
			$post = new TimberPost($post_id);
			$template = '{{ post.preview.length(9).read_more(false).strip(true) }}';
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('Yo. Four score and seven years ago our fathers&hellip;', $str);
		}

		function testPreviewTags() {
			$post_id = $this->factory->post->create(array('post_excerpt' => 'It turned out that just about anyone in authority — cops, judges, city leaders — was in on the game.'));
			$post = new TimberPost($post_id);
			$template = '{{post.preview.length(3).read_more(false).strip(false)}}';
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertNotContains('</p>', $str);
		}

		function testPostPreviewObjectWithCharAndWordLengthWordsWin() {
			$pid = $this->factory->post->create( array('post_content' => $this->gettysburg, 'post_excerpt' => '') );
			$template = '{{ post.preview.length(2).chars(20) }}';
			$post = new TimberPost($pid);
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('Four score&hellip; <a href="http://example.org/?p='.$pid.'" class="read-more">Read More</a>', $str);
		}

		function testPostPreviewObjectWithCharAndWordLengthCharsWin() {
			$pid = $this->factory->post->create( array('post_content' => $this->gettysburg, 'post_excerpt' => '') );
			$template = '{{ post.preview.length(20).chars(20) }}';
			$post = new TimberPost($pid);
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('Four score and seven&hellip; <a href="http://example.org/?p='.$pid.'" class="read-more">Read More</a>', $str);
		}

		function testPostPreviewObjectWithCharLength() {
			$pid = $this->factory->post->create( array('post_content' => $this->gettysburg, 'post_excerpt' => '') );
			$template = '{{ post.preview.chars(20) }}';
			$post = new TimberPost($pid);
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('Four score and seven&hellip; <a href="http://example.org/?p='.$pid.'" class="read-more">Read More</a>', $str);
		}

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
			$pid = $this->factory->post->create( array('post_content' => 'Great Gatsby', 'post_excerpt' => 'In my younger and more vulnerable years my father gave me some advice that I’ve been <a href="http://google.com">turning over</a> in my mind ever since.') );
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

		function testPreviewWithMoreTagAndForcedLength() {
			$pid = $this->factory->post->create( array('post_content' => 'Lauren is a duck<!-- more--> Lauren is not a duck', 'post_excerpt' => '') );
			$post = new TimberPost( $pid );

			$this->assertEquals('Lauren is a duck <a href="'.$post->link().'" class="read-more">Read More</a>', $post->preview());
		}

		function testPreviewWithCustomMoreTag() {
			$pid = $this->factory->post->create( array('post_content' => 'Eric is a polar bear <!-- more But what is Elaina? --> Lauren is not a duck', 'post_excerpt' => '') );
			$post = new TimberPost( $pid );
			$this->assertEquals('Eric is a polar bear <a href="'.$post->link().'" class="read-more">But what is Elaina?</a>', $post->preview());
		}

		function testPreviewWithSpaceInMoreTag() {
			$pid = $this->factory->post->create( array('post_content' => 'Lauren is a duck, but a great duck let me tell you why <!--more--> Lauren is not a duck', 'post_excerpt' => '') );
			$post = new TimberPost( $pid );
			$template = '{{post.preview.length(3).force}}';
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('Lauren is a&hellip; <a href="'.$post->link().'" class="read-more">Read More</a>', $str);
		}

		function testPreviewWithStripAndClosingPTag() {
			$pid = $this->factory->post->create( array('post_excerpt' => '<p>Lauren is a duck, but a great duck let me tell you why</p>') );
			$post = new TimberPost( $pid );
			$template = '{{post.preview.strip(false)}}';
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('<p>Lauren is a duck, but a great duck let me tell you why <a href="http://example.org/?p='.$pid.'" class="read-more">Read More</a></p>', $str);
		}

		function testPreviewWithStripAndClosingPTagForced() {
			$pid = $this->factory->post->create( array('post_excerpt' => '<p>Lauren is a duck, but a great duck let me tell you why</p>') );
			$post = new TimberPost( $pid );
			$template = '{{post.preview.strip(false).force(4)}}';
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('<p>Lauren is a duck, but a great duck let me tell you why&hellip;  <a href="http://example.org/?p='.$pid.'" class="read-more">Read More</a></p>', $str);
		}

		function testEmptyPreview() {
			$pid = $this->factory->post->create( array('post_excerpt' => '', 'post_content' => '') );
			$post = new TimberPost( $pid );
			$template = '{{ post.preview }}';
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('', $str);
		}


	}