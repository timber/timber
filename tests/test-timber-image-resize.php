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

	function testCropFalse() {
		$cropper = TestTimberImage::copyTestImage('cropper.png');
		$resized = TimberImageHelper::resize($cropper, 1, 2, false);

		$resized = str_replace('http://example.org', '', $resized);
		$resized = TimberUrlHelper::url_to_file_system( $resized );

		$is_redish = TestTimberImage::testPixel($resized, 0, 0, '#aa5755');
		$is_greenish = TestTimberImage::testPixel($resized, 0, 1, '#6bbbbb');
		$this->assertTrue( $is_redish );
		$this->assertTrue( $is_greenish );

		$is_1by2 = TestTimberImage::testSize($resized, 1, 2);
		$this->assertTrue( $is_1by2 );
	}

	function testCropRight() {
		$cropper = TestTimberImage::copyTestImage('cropper.png');
		$resized = TimberImageHelper::resize($cropper, 1, 3, 'right');

		$resized = str_replace('http://example.org', '', $resized);
		$resized = TimberUrlHelper::url_to_file_system( $resized );

		$is_magenta = TestTimberImage::testPixel($resized, 0, 0, '#ff00ff');
		$this->assertTrue( $is_magenta );
	}

	function testCropHeight() {
		$arch = TestTimberImage::copyTestImage('arch.jpg');
		$resized = TimberImageHelper::resize($arch, false, 250);

		$resized = str_replace('http://example.org', '', $resized);
		$resized = TimberUrlHelper::url_to_file_system( $resized );

		$is_sized = TestTimberImage::testSize($resized, 375, 250);
		$this->assertTrue( $is_sized );
	}

}
