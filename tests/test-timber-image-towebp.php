<?php

	class TestTimberImageToWEBP extends Timber_UnitTestCase {

		function set_up() {
			parent::set_up();
			if ( ! function_exists( 'imagewebp' ) ) {
				self::markTestSkipped( 'WEBP conversion tests requires imagewebp function' );
			}
		}

		function testTIFtoWEBP() {
			$filename = TestTimberImage::copyTestImage( 'white-castle.tif' );
			$str = Timber::compile_string('{{file|towebp}}', array('file' => $filename));
			$this->assertEquals($filename, $str);
		}

		function testPNGtoWEBP() {
			$filename = TestTimberImage::copyTestImage( 'flag.png' );
			$str = Timber::compile_string('{{file|towebp}}', array('file' => $filename));
			$renamed = str_replace('.png', '-towebp.webp', $filename);
			$this->assertFileExists($renamed);
			$this->assertGreaterThan(1000, filesize($renamed));
			$this->assertEquals('image/png', mime_content_type($filename));
			$this->assertEquals('image/webp', mime_content_type($renamed));
		}

		function testGIFtoJPG() {
			$filename = TestTimberImage::copyTestImage( 'boyer.gif' );
			$str = Timber::compile_string('{{file|towebp}}', array('file' => $filename));
			$renamed = str_replace('.gif', '-towebp.webp', $filename);
			$this->assertFileExists($renamed);
			$this->assertGreaterThan(1000, filesize($renamed));
			$this->assertEquals('image/gif', mime_content_type($filename));
			$this->assertEquals('image/webp', mime_content_type($renamed));
		}

		function testJPGtoWEBP() {
			$filename = TestTimberImage::copyTestImage( 'stl.jpg' );
			$original_size = filesize($filename);
			$str = Timber::compile_string('{{file|towebp(100)}}', array('file' => $filename));
			$renamed = str_replace('.jpg', '-towebp.webp', $filename);
			$this->assertGreaterThan(1000, filesize($renamed));
			$this->assertEquals('image/jpeg', mime_content_type($filename));
			$this->assertEquals('image/webp', mime_content_type($renamed));
		}

		function testJPEGtoJPG() {
			$filename = TestTimberImage::copyTestImage( 'jarednova.jpeg' );
			$str = Timber::compile_string('{{file|towebp}}', array('file' => $filename));
			$renamed = str_replace('.jpeg', '-towebp.webp', $filename);
			$this->assertFileExists($renamed);
			$this->assertGreaterThan(1000, filesize($renamed));
			$this->assertEquals('image/jpeg', mime_content_type($filename));
			$this->assertEquals('image/webp', mime_content_type($renamed));
		}

		function testWEBPtoWEBP() {
			$filename = TestTimberImage::copyTestImage( 'mountains.webp' );
			$original_size = filesize($filename);
			$str = Timber::compile_string('{{file|towebp}}', array('file' => $filename));
			$new_size = filesize($filename);
			$this->assertEquals($original_size, $new_size);
			$this->assertEquals('image/webp', mime_content_type($filename));
		}
	}
