<?php

	class TestTimberImageHelper extends Timber_UnitTestCase {

		function testIsAnimatedGif() {
			$image = TestTimberImage::copyTestImage('robocop.gif');
			$this->assertTrue( TimberImageHelper::is_animated_gif($image) );
		}

		// function testThemeURLToDir() {
		// 	$url = 'http://example.org/wp-content/themes/'.get_stylesheet().'/images/cardinals.jpg';
		// 	$result = Timber\ImageHelper::theme_url_to_dir($url);
		// }

		function testIsRegularGif() {
			$image = TestTimberImage::copyTestImage('boyer.gif');
			$this->assertFalse( TimberImageHelper::is_animated_gif($image) );
		}

		function testIsNotGif() {
			$arch = TestTimberImage::copyTestImage('arch.jpg');
			$this->assertFalse( TimberImageHelper::is_animated_gif($arch) );
		}

		function testServerLocation() {
			$arch = TestTimberImage::copyTestImage('arch.jpg');
			$this->assertEquals($arch, \Timber\ImageHelper::get_server_location($arch));
		}

	}
