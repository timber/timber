<?php

class TestTimberImageResize extends Timber_UnitTestCase {

	function testCropCenter() {
		$cropper = TestTimberImage::copyTestImage('cropper.png');
		$resized = TimberImageHelper::resize($cropper, 100, 300, 'center');

		$resized = str_replace('http://example.org', '', $resized);
		$resized = TimberUrlHelper::url_to_file_system( $resized );

		$is_black = TestTimberImage::checkPixel($resized, 10, 20, '#000');
		$is_white = TestTimberImage::checkPixel($resized, 10, 120, '#FFFFFF');
		$is_gray = TestTimberImage::checkPixel($resized, 10, 220, '#aaa', '#ccc');

		$this->assertTrue( $is_white );
		$this->assertTrue( $is_black );
		$this->assertTrue( $is_gray );
	}

	function testCropFalse() {
		$cropper = TestTimberImage::copyTestImage('cropper.png');
		$resized = TimberImageHelper::resize($cropper, 100, 200, false);

		$resized = str_replace('http://example.org', '', $resized);
		$resized = TimberUrlHelper::url_to_file_system( $resized );

		$is_red = TestTimberImage::checkPixel($resized, 20, 20, '#ff0000', '#ff0800');
		$is_green = TestTimberImage::checkPixel($resized, 0, 100, '#00ff00');
		$is_magenta = TestTimberImage::checkPixel($resized, 90, 10, '#ff00ff');
		$is_cyan = TestTimberImage::checkPixel($resized, 90, 199, '#00ffff');
		$is_blue = TestTimberImage::checkPixel($resized, 90, 199, '#0000ff');
		$this->assertTrue( $is_red );
		$this->assertTrue( $is_green );
		$this->assertTrue( $is_magenta );
		$this->assertTrue( $is_cyan );
		$this->assertFalse( $is_blue );

		$is_1by2 = TestTimberImage::checkSize($resized, 100, 200);
		$this->assertTrue( $is_1by2 );
	}

	function testCropRight() {
		$cropper = TestTimberImage::copyTestImage('cropper.png');
		$resized = TimberImageHelper::resize($cropper, 100, 300, 'right');

		$resized = str_replace('http://example.org', '', $resized);
		$resized = TimberUrlHelper::url_to_file_system( $resized );

		$is_magenta = TestTimberImage::checkPixel($resized, 50, 50, '#ff00ff');
		$this->assertTrue( $is_magenta );
	}

	function testCropTop() {
		$cropper = TestTimberImage::copyTestImage('cropper.png');
		$resized = TimberImageHelper::resize($cropper, 300, 100, 'top');

		$resized = str_replace('http://example.org', '', $resized);
		$resized = TimberUrlHelper::url_to_file_system( $resized );

		$is_magenta = TestTimberImage::checkPixel($resized, 290, 90, '#ff00ff');
		$this->assertTrue( $is_magenta );
	}

	function testCropBottom() {
		$cropper = TestTimberImage::copyTestImage('cropper.png');
		$resized = TimberImageHelper::resize($cropper, 300, 100, 'bottom');

		$resized = str_replace('http://example.org', '', $resized);
		$resized = TimberUrlHelper::url_to_file_system( $resized );

		$is_teal = TestTimberImage::checkPixel($resized, 290, 90, '#00ffff');
		$this->assertTrue( $is_teal );
	}

	function testCropBottomCenter() {
		$cropper = TestTimberImage::copyTestImage('cropper.png');
		$resized = TimberImageHelper::resize($cropper, 300, 100, 'bottom-center');

		$resized = str_replace('http://example.org', '', $resized);
		$resized = TimberUrlHelper::url_to_file_system( $resized );

		$is_teal = TestTimberImage::checkPixel($resized, 200, 50, '#00ffff');
		$this->assertTrue( $is_teal );
	}

	function testCropTopCenter() {
		$cropper = TestTimberImage::copyTestImage('cropper.png');
		$resized = TimberImageHelper::resize($cropper, 300, 100, 'top-center');

		$resized = str_replace('http://example.org', '', $resized);
		$resized = TimberUrlHelper::url_to_file_system( $resized );

		$is_red = TestTimberImage::checkPixel($resized, 100, 50, '#ff0000', '#ff0800');
		$this->assertTrue( $is_red );
	}

	function testCropHeight() {
		$arch = TestTimberImage::copyTestImage('arch.jpg');
		$resized = TimberImageHelper::resize($arch, false, 250);

		$resized = str_replace('http://example.org', '', $resized);
		$resized = TimberUrlHelper::url_to_file_system( $resized );

		$is_sized = TestTimberImage::checkSize($resized, 375, 250);
		$this->assertTrue( $is_sized );
	}

}
