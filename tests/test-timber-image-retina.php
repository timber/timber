<?php

class TestTimberImageRetina extends Timber_UnitTestCase {

	function testImageRetina() {
		$file = TestTimberImage::copyTestImage();
		$ret = TimberImageHelper::retina_resize($file, 2);
		$image = new TimberImage( $ret );
		$this->assertEquals( 3000, $image->width() );
	}

	function testImageBiggerRetina() {
		$file = TestTimberImage::copyTestImage();
		$ret = TimberImageHelper::retina_resize($file, 3);
		$image = new TimberImage( $ret );
		$this->assertEquals( 4500, $image->width() );
	}

	function testImageRetinaFilter() {
		$filename = TestTimberImage::copyTestImage( 'eastern.jpg' );
		$wp_filetype = wp_check_filetype( basename( $filename ), null );
		$post_id = $this->factory->post->create( array( 'post_title' => 'Thing One' ) );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_excerpt' => '',
			'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
		add_post_meta( $post_id, '_thumbnail_id', $attach_id, true );
		$data = array();
		$post = new TimberPost( $post_id );
		$data['post'] = $post;
		$str = '{{post.thumbnail.src|retina}}';
		$compiled = Timber::compile_string($str, $data);
		$this->assertContains('@2x', $compiled);
		$img = new TimberImage($compiled);
		$this->assertEquals(500, $img->width());
	}

	function testImageRetinaFloatFilter() {
		$filename = TestTimberImage::copyTestImage( 'eastern.jpg' );
		$wp_filetype = wp_check_filetype( basename( $filename ), null );
		$post_id = $this->factory->post->create( array( 'post_title' => 'Thing One' ) );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_excerpt' => '',
			'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
		add_post_meta( $post_id, '_thumbnail_id', $attach_id, true );
		$data = array();
		$post = new TimberPost( $post_id );
		$data['post'] = $post;
		$str = '{{post.thumbnail.src|retina(1.5)}}';
		$compiled = Timber::compile_string($str, $data);
		$this->assertContains('@1.5x', $compiled);
		$img = new TimberImage($compiled);
		$this->assertEquals(375, $img->width());
	}

	function testImageResizeRetinaFilter() {
		$filename = TestTimberImage::copyTestImage( 'eastern.jpg' );
		$wp_filetype = wp_check_filetype( basename( $filename ), null );
		$post_id = $this->factory->post->create();
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_excerpt' => '',
			'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
		add_post_meta( $post_id, '_thumbnail_id', $attach_id, true );
		$data = array();
		$data['post'] = new TimberPost( $post_id );
		$str = '{{post.thumbnail.src|resize(100, 50)|retina(3)}}';
		$compiled = Timber::compile_string($str, $data);
		$img = new TimberImage($compiled);
		$this->assertContains('@3x', $compiled);
		$this->assertEquals(300, $img->width());
	}
}
