<?php

	class TestTimberImageToAVIF extends Timber_UnitTestCase {

		function setUp() {
			parent::setUp();
			if ( ! function_exists( 'imageavif' ) ) {
				self::markTestSkipped( 'AVIF conversion tests requires imageavif function' );
			}
		}

		function testTIFtoAVIF() {
			$filename = TestTimberImage::copyTestImage( 'white-castle.tif' );
			$str = Timber::compile_string('{{file|toavif}}', array('file' => $filename));
			$this->assertEquals($filename, $str);
		}

		function testPNGtoAVIF() {
			$filename = TestTimberImage::copyTestImage( 'flag.png' );
			$str = Timber::compile_string('{{file|toavif}}', array('file' => $filename));
			$renamed = str_replace('.png', '.avif', $filename);
			$this->assertFileExists($renamed);
			$this->assertGreaterThan(1000, filesize($renamed));
			$this->assertEquals('image/png', mime_content_type($filename));
			$this->assertEquals('image/avif', mime_content_type($renamed));
		}

		function testGIFtoJPG() {
			$filename = TestTimberImage::copyTestImage( 'boyer.gif' );
			$str = Timber::compile_string('{{file|toavif}}', array('file' => $filename));
			$renamed = str_replace('.gif', '.avif', $filename);
			$this->assertFileExists($renamed);
			$this->assertGreaterThan(1000, filesize($renamed));
			$this->assertEquals('image/gif', mime_content_type($filename));
			$this->assertEquals('image/avif', mime_content_type($renamed));
		}

		function testJPGtoAVIF() {
			$filename = TestTimberImage::copyTestImage( 'stl.jpg' );
			$original_size = filesize($filename);
			$str = Timber::compile_string('{{file|toavif(100)}}', array('file' => $filename));
			$renamed = str_replace('.jpg', '.avif', $filename);
			$this->assertGreaterThan(1000, filesize($renamed));
			$this->assertEquals('image/jpeg', mime_content_type($filename));
			$this->assertEquals('image/avif', mime_content_type($renamed));
		}

		function testJPEGtoJPG() {
			$filename = TestTimberImage::copyTestImage( 'jarednova.jpeg' );
			$str = Timber::compile_string('{{file|toavif}}', array('file' => $filename));
			$renamed = str_replace('.jpeg', '.avif', $filename);
			$this->assertFileExists($renamed);
			$this->assertGreaterThan(1000, filesize($renamed));
			$this->assertEquals('image/jpeg', mime_content_type($filename));
			$this->assertEquals('image/avif', mime_content_type($renamed));
		}

		function testAVIFtoAVIF() {
			$filename = TestTimberImage::copyTestImage( 'mountains.avif' );
			$original_size = filesize($filename);
			$str = Timber::compile_string('{{file|toavif}}', array('file' => $filename));
			$new_size = filesize($filename);
			$this->assertEquals($original_size, $new_size);
			$this->assertEquals('image/avif', mime_content_type($filename));
		}
	}
