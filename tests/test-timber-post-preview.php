<?php

	class TestTimberPostPreview extends Timber_UnitTestCase {

		function testDoubleEllipsis(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$post->post_excerpt = 'this is super dooper trooper long words';
			$prev = $post->get_preview(3, true);
			$this->assertEquals(1, substr_count($prev, '&hellip;'));
		}

		function testReadMoreClassFilter() {
			add_filter('timber/post/get_preview/read_more_class', function($class) {
				return $class . ' and-foo';
			});
			$post_id = $this->factory->post->create(array('post_excerpt' => 'It turned out that just about anyone in authority — cops, judges, city leaders — was in on the game.'));
			$post = new TimberPost($post_id);
			$text = $post->get_preview(10);
			$this->assertContains('and-foo', $text);
		}

		function testPreviewTags() {
			$post_id = $this->factory->post->create(array('post_excerpt' => 'It turned out that just about anyone in authority — cops, judges, city leaders — was in on the game.'));
			$post = new TimberPost($post_id);
			$text = $post->get_preview(20, false, '', false);
			$this->assertNotContains('</p>', $text);
		}

		function testGetPreview() {
			global $wp_rewrite;
			$struc = false;
			$wp_rewrite->permalink_structure = $struc;
			update_option('permalink_structure', $struc);
			$post_id = $this->factory->post->create(array('post_content' => 'this is super dooper trooper long words'));
			$post = new TimberPost($post_id);

			// no excerpt
			$post->post_excerpt = '';
			$preview = $post->get_preview(3);
			$this->assertRegExp('/this is super&hellip; <a href="http:\/\/example.org\/\?p=\d+" class="read-more">Read More<\/a>/', $preview);

			// excerpt set, force is false, no read more
			$post->post_excerpt = 'this is excerpt longer than three words';
			$preview = $post->get_preview(3, false, '');
			$this->assertEquals($preview, $post->post_excerpt);

			// custom read more set
			$post->post_excerpt = '';
			$preview = $post->get_preview(3, false, 'Custom more');
			$this->assertRegExp('/this is super&hellip; <a href="http:\/\/example.org\/\?p=\d+" class="read-more">Custom more<\/a>/', $preview);

			// content with <!--more--> tag, force false
			$post->post_content = 'this is super dooper<!--more--> trooper long words';
			$preview = $post->get_preview(2, false, '');
			$this->assertEquals('this is super dooper', $preview);
		}

		function testShortcodesInPreviewFromContent() {
			add_shortcode('mythang', function($text) {
				return 'mythangy';
			});
			$pid = $this->factory->post->create( array('post_content' => 'jared [mythang]', 'post_excerpt' => '') );
			$post = new TimberPost( $pid );
			$this->assertEquals('jared mythangy&hellip; <a href="'.$post->link().'" class="read-more">Read More</a>', $post->get_preview());
		}

		function testShortcodesInPreviewFromContentWithMoreTag() {
			add_shortcode('duck', function($text) {
				return 'Quack!';
			});
			$pid = $this->factory->post->create( array('post_content' => 'jared [duck] <!--more--> joojoo', 'post_excerpt' => '') );
			$post = new TimberPost( $pid );
			$this->assertEquals('jared Quack! <a href="'.$post->link().'" class="read-more">Read More</a>', $post->get_preview());
		}

		function testPreviewWithSpaceInMoreTag() {
			$pid = $this->factory->post->create( array('post_content' => 'Lauren is a duck, but a great duck let me tell you why <!--more--> Lauren is not a duck', 'post_excerpt' => '') );
			$post = new TimberPost( $pid );
			$this->assertEquals('Lauren is a&hellip; <a href="'.$post->link().'" class="read-more">Read More</a>', $post->get_preview(3, true));
		}

		function testPreviewWithMoreTagAndForcedLength() {
			$pid = $this->factory->post->create( array('post_content' => 'Lauren is a duck<!-- more--> Lauren is not a duck', 'post_excerpt' => '') );
			$post = new TimberPost( $pid );
			$this->assertEquals('Lauren is a duck <a href="'.$post->link().'" class="read-more">Read More</a>', $post->get_preview());
		}

		function testPreviewWithCustomMoreTag() {
			$pid = $this->factory->post->create( array('post_content' => 'Eric is a polar bear <!-- more But what is Elaina? --> Lauren is not a duck', 'post_excerpt' => '') );
			$post = new TimberPost( $pid );
			$this->assertEquals('Eric is a polar bear <a href="'.$post->link().'" class="read-more">But what is Elaina?</a>', $post->get_preview());
		}

		function testPreviewWithCustomEnd() {
			$pid = $this->factory->post->create( array('post_content' => 'Lauren is a duck, but a great duck let me tell you why Lauren is a duck', 'post_excerpt' => '') );
			$post = new TimberPost( $pid );
			$this->assertEquals('Lauren is a ??? <a href="'.$post->link().'" class="read-more">Read More</a>', $post->get_preview(3, true, 'Read More', true, ' ???'));
		}

		/**
		 * @group failing
		 */
		function testPreviewWithCustomStripTags() {
			$pid = $this->factory->post->create(array(
				'post_content' => '<span>Even in the <a href="">world</a> of make-believe there have to be rules. The parts have to be consistent and belong together</span>'
			));
			$post = new TimberPost($pid);
			$post->post_excerpt = '';
			$preview = $post->get_preview(6, true, 'Read More', '<span>');
			$this->assertEquals('<span>Even in the world of</span>&hellip; <a href="'.$post->link().'" class="read-more">Read More</a>', $preview);
		}

	}
