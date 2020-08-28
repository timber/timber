<?php

/**
 * @group posts-api
 * @group attachments
 * @todo figure out how to distinguish Image instances in Class Maps
 */
class TestTimberImageRetina extends Timber_UnitTestCase {

	function testImageRetina() {
		$this->markTestSkipped('@todo ::from_url');
		$file = TestTimberImage::copyTestAttachment();
		$ret = Timber\ImageHelper::retina_resize($file, 2);
		$image = Attachment::from_url( $ret );
		$this->assertEquals( 3000, $image->width() );
	}

	function testImageBiggerRetina() {
		$this->markTestSkipped('@todo ::from_url');
		$file = TestTimberImage::copyTestAttachment();
		$ret = Timber\ImageHelper::retina_resize($file, 3);
		$image = Attachment::from_url( $ret );
		$this->assertEquals( 4500, $image->width() );
	}

	function testImageRetinaFilter() {
		$this->markTestSkipped('@todo ::from_url');
		$filename = TestTimberImage::copyTestAttachment( 'eastern.jpg' );
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

		$compiled = Timber::compile_string('{{post.thumbnail.src|retina}}', [
			'post' => Timber::get_post($post_id),
		]);
		$img = Attachment::from_url($compiled);

		$this->assertContains('@2x', $compiled);
		$this->assertEquals(500, $img->width());
	}

	function testImageRetinaFloatFilter() {
		$this->markTestSkipped('@todo ::from_url');
		$filename = TestTimberImage::copyTestAttachment( 'eastern.jpg' );
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
		$post = Timber::get_post( $post_id );
		$data['post'] = $post;
		$str = '{{post.thumbnail.src|retina(1.5)}}';
		$compiled = Timber::compile_string($str, $data);
		$this->assertContains('@1.5x', $compiled);
		$img = Attachment::from_url($compiled);
		$this->assertEquals(375, $img->width());
	}

	function testImageResizeRetinaFilter() {
		$this->markTestSkipped('@todo ::from_url');
		$filename = TestTimberImage::copyTestAttachment( 'eastern.jpg' );
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
		$data['post'] = Timber::get_post( $post_id );
		$str = '{{post.thumbnail.src|resize(100, 50)|retina(3)}}';
		$compiled = Timber::compile_string($str, $data);
		$img = Attachment::from_url($compiled);
		$this->assertContains('@3x', $compiled);
		$this->assertEquals(300, $img->width());
	}

	function testImageResizeRetinaFilterNotAnImage() {
		self::enable_error_log(false);
		$str = 'Image? {{"/wp-content/uploads/2016/07/stuff.jpg"|retina(3)}}';
		$compiled = Timber::compile_string($str);
		$this->assertEquals('Image? /wp-content/uploads/2016/07/stuff.jpg', $compiled);
		self::enable_error_log(true);
	}
}
