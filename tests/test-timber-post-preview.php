<?php

	class TestTimberPostPreview extends Timber_UnitTestCase {

		function testDoubleEllipsis(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$post->post_excerpt = 'this is super dooper trooper long words';
			$prev = $post->get_preview(3, true);
			$this->assertEquals(1, substr_count($prev, '&hellip;'));
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
			$this->assertRegExp('/this is super &hellip;  <a href="http:\/\/example.org\/\?p=\d+" class="read-more">Read More<\/a>/', $preview);

			// excerpt set, force is false, no read more
			$post->post_excerpt = 'this is excerpt longer than three words';
			$preview = $post->get_preview(3, false, '');
			$this->assertEquals($preview, $post->post_excerpt);

			// custom read more set
			$post->post_excerpt = '';
			$preview = $post->get_preview(3, false, 'Custom more');
			$this->assertRegExp('/this is super &hellip;  <a href="http:\/\/example.org\/\?p=\d+" class="read-more">Custom more<\/a>/', $preview);

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
			$this->assertEquals('jared mythangy &hellip;  <a href="'.$post->link().'" class="read-more">Read More</a>', $post->get_preview());
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
			$pid = $this->factory->post->create( array('post_content' => 'Lauren is a duck<!-- more--> Lauren is not a duck', 'post_excerpt' => '') );
			$post = new TimberPost( $pid );
			$this->assertEquals('Lauren is a duck <a href="'.$post->link().'" class="read-more">Read More</a>', $post->get_preview());
		}

		function testPreviewWithCustomMoreTag() {
			$pid = $this->factory->post->create( array('post_content' => 'Eric is a polar bear <!-- more But what is Elaina? --> Lauren is not a duck', 'post_excerpt' => '') );
			$post = new TimberPost( $pid );
			$this->assertEquals('Eric is a polar bear <a href="'.$post->link().'" class="read-more">But what is Elaina?</a>', $post->get_preview());
		}

	}
