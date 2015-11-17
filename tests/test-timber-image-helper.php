<?php

	class TestTimberImageHelper extends Timber_UnitTestCase {

		function testIsAnimatedGif() {
			$image = TestTimberImage::copyTestImage('robocop.gif');
			$this->assertTrue( TimberImageHelper::is_animated_gif($image) );
		}

		function testIsRegularGif() {
			$image = TestTimberImage::copyTestImage('boyer.gif');
			$this->assertFalse( TimberImageHelper::is_animated_gif($image) );
		}

		function testIsNotGif() {
			$arch = TestTimberImage::copyTestImage('arch.jpg');
			$this->assertFalse( TimberImageHelper::is_animated_gif($arch) );
		}

	}
