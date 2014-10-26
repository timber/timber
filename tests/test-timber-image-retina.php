<?php

class TimberImageRetinaTest extends WP_UnitTestCase {

	function testImageRetina() {
		$file = TimberImageTest::copyTestImage();
		$ret = TimberImageHelper::retina_resize($file, 2);
		$image = new TimberImage( $ret );
		$this->assertEquals( 3000, $image->width() );
	}

	function testImageBiggerRetina() {
		$file = TimberImageTest::copyTestImage();
		$ret = TimberImageHelper::retina_resize($file, 3);
		$image = new TimberImage( $ret );
		$this->assertEquals( 4500, $image->width() );
	}

	function testImageRetinaFilter() {
		$filename = TimberImageTest::copyTestImage( 'eastern.jpg' );
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
		$str = '{{post.thumbnail.src|retina}}';
		$compiled = Timber::compile_string($str, $data);
		$this->assertContains('@2x', $compiled);
	}
}