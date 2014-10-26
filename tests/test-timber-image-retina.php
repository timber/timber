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
}