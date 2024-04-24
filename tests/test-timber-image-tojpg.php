<?php

	class TestTimberImageToJPG extends Timber_UnitTestCase {

		function set_up() {
			parent::set_up();
			if ( ! extension_loaded( 'gd' ) ) {
				self::markTestSkipped( 'JPEG conversion tests requires GD extension' );
			}
		}

		/**
		 * This should fail silently as opposed to throwing an exception
		 * see #1383 and #1192
		 */
		function testTIFtoJPG() {
			$filename = TestTimberImage::copyTestImage( 'white-castle.tif' );
			$str = Timber::compile_string('{{file|tojpg}}', array('file' => $filename));
			$this->assertEquals($filename, $str);
			unlink($filename);
		}

		function testPNGtoJPG() {
			$filename = TestTimberImage::copyTestImage( 'flag.png' );
			$str = Timber::compile_string('{{file|tojpg}}', array('file' => $filename));
			$renamed = str_replace('.png', '-tojpg.jpg', $filename);
			$this->assertFileExists($renamed);
			$this->assertGreaterThan(1000, filesize($renamed));
			$this->assertEquals('image/png', mime_content_type($filename));
			$this->assertEquals('image/jpeg', mime_content_type($renamed));
			unlink($filename);
			unlink($renamed);
		}

		function testGIFtoJPG() {
			$filename = TestTimberImage::copyTestImage( 'boyer.gif' );
			$str = Timber::compile_string('{{file|tojpg}}', array('file' => $filename));
			$renamed = str_replace('.gif', '-tojpg.jpg', $filename);
			$this->assertFileExists($renamed);
			$this->assertGreaterThan(1000, filesize($renamed));
			$this->assertEquals('image/gif', mime_content_type($filename));
			$this->assertEquals('image/jpeg', mime_content_type($renamed));
			unlink($filename);
			unlink($renamed);
		}

		function testJPGtoJPG() {
			$filename = TestTimberImage::copyTestImage( 'stl.jpg' );
			$original_size = filesize($filename);
			$str = Timber::compile_string('{{file|tojpg}}', array('file' => $filename));
			$new_size = filesize($filename);
			$this->assertEquals($original_size, $new_size);
			$this->assertEquals('image/jpeg', mime_content_type($filename));
			unlink($filename);
		}

		function testJPEGtoJPG() {
			$filename = TestTimberImage::copyTestImage( 'jarednova.jpeg' );
			$str = Timber::compile_string('{{file|tojpg}}', array('file' => $filename));
			$renamed = str_replace('.jpeg', '-tojpg.jpg', $filename);
			$this->assertFileExists($renamed);
			$this->assertGreaterThan(1000, filesize($renamed));
			$this->assertEquals('image/jpeg', mime_content_type($filename));
			$this->assertEquals('image/jpeg', mime_content_type($renamed));
			unlink($filename);
			unlink($renamed);
		}

	}
