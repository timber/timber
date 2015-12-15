<?php

class TestTimberImageResize extends Timber_UnitTestCase {

	function testCropCenter() {
		$cropper = TestTimberImage::copyTestImage('cropper.png');
		$resized = TimberImageHelper::resize($cropper, 1, 3, 'center');

		$resized = str_replace('http://example.org', '', $resized);
		$resized = TimberUrlHelper::url_to_file_system( $resized );

		$is_black = TestTimberImage::testPixel($resized, 0, 0, '#000');
		$is_white = TestTimberImage::testPixel($resized, 0, 1, '#FFFFFF');
		$is_gray = TestTimberImage::testPixel($resized, 0, 2, '#aaaaaa');

		$this->assertTrue( $is_white );
		$this->assertTrue( $is_black );
		$this->assertTrue( $is_gray );
	}

}
