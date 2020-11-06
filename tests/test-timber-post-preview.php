<?php

use Timber\PostPreview;

/**
 * @group called-post-constructor
 */
class TestTimberPostPreview extends Timber_UnitTestCase {
	function testDoubleEllipsis(){
		$post_id = $this->factory->post->create();
		$post = Timber::get_post($post_id);
		$post->post_excerpt = 'this is super dooper trooper long words';
		$preview = new PostPreview( $post, [
			'words' => 3,
			'force' => true,
		] );

		$this->assertEquals(1, substr_count((string) $preview, '&hellip;'));
	}

	function testReadMoreClassFilter() {
		$this->add_filter_temporarily('timber/post/preview/read_more_class', function($class) {
			return $class . ' and-foo';
		});
		$post_id = $this->factory->post->create(array('post_excerpt' => 'It turned out that just about anyone in authority — cops, judges, city leaders — was in on the game.'));
		$post = Timber::get_post($post_id);
		$text = new PostPreview( $post, [
			'words' => 10,
		] );
		$this->assertContains('and-foo', (string) $text);
	}

	function testPreviewTags() {
		$post_id = $this->factory->post->create(array('post_excerpt' => 'It turned out that just about anyone in authority — cops, judges, city leaders — was in on the game.'));
		$post = Timber::get_post($post_id);
		$text = new PostPreview( $post, [
			'words'    => 20,
			'force'    => false,
			'readmore' => '',
			'strip'    => false,
		] );
		$this->assertNotContains('</p>', (string) $text);
	}

	function testGetPreview() {
		global $wp_rewrite;
		$struc = false;
		$wp_rewrite->permalink_structure = $struc;
		update_option('permalink_structure', $struc);
		$post_id = $this->factory->post->create(array('post_content' => 'this is super dooper trooper long words'));
		$post = Timber::get_post($post_id);

		// no excerpt
		$post->post_excerpt = '';
		$str = Timber::compile_string('{{ post.excerpt({ words: 3 }) }}', [ 'post' => $post ] );
		$this->assertRegExp( '/this is super&hellip; <a href="http:\/\/example.org\/\?p=\d+" class="read-more">Read More<\/a>/', $str );

		// excerpt set, force is false, no read more
		$post->post_excerpt = 'this is excerpt longer than three words';
		$preview = new PostPreview( $post, [
			'words'    => 3,
			'force'    => false,
			'read_more' => '',
		] );
		$this->assertEquals( (string) $preview, $post->post_excerpt);

		// custom read more set
		$post->post_excerpt = '';
		$preview = new PostPreview( $post, [
			'words'    => 3,
			'force'    => false,
			'read_more' => 'Custom more',
		] );
		$this->assertRegExp('/this is super&hellip; <a href="http:\/\/example.org\/\?p=\d+" class="read-more">Custom more<\/a>/', (string) $preview);

		// content with <!--more--> tag, force false
		$post->post_content = 'this is super dooper<!--more--> trooper long words';
		$preview = new PostPreview( $post, [
			'words'    => 2,
			'force'    => false,
			'read_more' => '',
		] );
		$this->assertEquals('this is super dooper', (string) $preview);
	}

	function testShortcodesInPreviewFromContent() {
		add_shortcode('mythang', function($text) {
			return 'mythangy';
		});
		$pid = $this->factory->post->create( [ 'post_content' => 'jared [mythang]', 'post_excerpt' => '' ] );
		$post = Timber::get_post( $pid );
		$this->assertEquals( 'jared mythangy&hellip; <a href="'.$post->link().'" class="read-more">Read More</a>', $post->excerpt() );
	}

	function testShortcodesInPreviewFromContentWithMoreTag() {
		add_shortcode('duck', function($text) {
			return 'Quack!';
		});
		$pid = $this->factory->post->create( array('post_content' => 'jared [duck] <!--more--> joojoo', 'post_excerpt' => '') );
		$post = Timber::get_post( $pid );
		$this->assertEquals('jared Quack! <a href="'.$post->link().'" class="read-more">Read More</a>', $post->excerpt());
	}

	function testPreviewWithSpaceInMoreTag() {
		$pid = $this->factory->post->create( [
			'post_content' => 'Lauren is a duck, but a great duck let me tell you why <!--more--> Lauren is not a duck',
			'post_excerpt' => ''
		] );
		$post = Timber::get_post( $pid );
		$preview = new PostPreview( $post, [
			'words' => 3,
			'force' => true,
		] );

		$this->assertEquals(
			'Lauren is a&hellip; <a href="'.$post->link().'" class="read-more">Read More</a>',
			(string) $preview
		);
	}

	function testPreviewWithMoreTagAndForcedLength() {
		$pid = $this->factory->post->create( array('post_content' => 'Lauren is a duck<!-- more--> Lauren is not a duck', 'post_excerpt' => '') );
		$post = Timber::get_post( $pid );
		$this->assertEquals(
			'Lauren is a duck <a href="'.$post->link().'" class="read-more">Read More</a>',
			$post->excerpt()
		);
	}

	function testPreviewWithCustomMoreTag() {
		$pid = $this->factory->post->create( array('post_content' => 'Eric is a polar bear <!-- more But what is Elaina? --> Lauren is not a duck', 'post_excerpt' => '') );
		$post = Timber::get_post( $pid );
		$this->assertEquals(
			'Eric is a polar bear <a href="'.$post->link().'" class="read-more">But what is Elaina?</a>',
			$post->excerpt()
		);
	}

	function testPreviewWithCustomEnd() {
		$pid = $this->factory->post->create( [
			'post_content' => 'Lauren is a duck, but a great duck let me tell you why Lauren is a duck',
			'post_excerpt' => ''
		] );
		$post = Timber::get_post( $pid );
		$preview = new PostPreview( $post, [
			'words'    => 3,
			'force'    => true,
			'readmore' => 'Read More',
			'strip'    => true,
			'end'      => ' ???',
		] );
		$this->assertEquals(
			'Lauren is a ??? <a href="'.$post->link().'" class="read-more">Read More</a>',
			(string) $preview
		);
	}

	function testPreviewWithCustomStripTags() {
		$pid = $this->factory->post->create( [
			'post_content' => '<span>Even in the <a href="">world</a> of make-believe there have to be rules. The parts have to be consistent and belong together</span>'
		] );
		$post = Timber::get_post($pid);
		$post->post_excerpt = '';
		$preview = new PostPreview( $post, [
			'words'    => 6,
			'force'    => true,
			'readmore' => 'Read More',
			'strip'    => '<span>',
		] );
		$this->assertEquals(
			'<span>Even in the world of make-believe</span>&hellip; <a href="'.$post->link().'" class="read-more">Read More</a>',
			(string) $preview
		);
	}
}
