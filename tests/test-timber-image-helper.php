<?php

	class TestTimberImageHelper extends TimberImage_UnitTestCase {

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

		function testServerLocation() {
			$arch = TestTimberImage::copyTestImage('arch.jpg');
			$this->assertEquals($arch, \Timber\ImageHelper::get_server_location($arch));
		}

		function testWeirdImageLocations_PR1343() {
			$old_WP_CONTENT_URL = WP_CONTENT_URL;
			$old_WP_CONTENT_DIR = WP_CONTENT_DIR;

			if ( version_compare(phpversion(), '7.0', '>=') ) {
				$this->markTestSkipped('Updates to constants cant be tested in PHP7');
				return;
			}

			runkit_constant_redefine("WP_CONTENT_URL", 'http://' . $_SERVER['HTTP_HOST'] . '/content');
			runkit_constant_redefine("WP_CONTENT_DIR", $_SERVER['DOCUMENT_ROOT'] . '/content');

			define('WP_SITEURL', 'http://example.org/wp/');
			define('WP_HOME', 'http://example.org/');

			$upload_dir = wp_upload_dir();
			$post_id = $this->factory->post->create();
			$filename = TestTimberImage::copyTestImage( 'flag.png' );
			$destination_url = str_replace( ABSPATH, 'http://'.$_SERVER['HTTP_HOST'].'/', $filename );
			$wp_filetype = wp_check_filetype( basename( $filename ), null );
			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
				'post_content' => '',
				'post_status' => 'inherit'
			);
			$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
			add_post_meta( $post_id, '_thumbnail_id', $attach_id, true );
			$data = array();
			$data['post'] = new TimberPost( $post_id );
			$data['size'] = array( 'width' => 100, 'height' => 50 );
			$data['crop'] = 'default';
			Timber::compile( 'assets/thumb-test.twig', $data );
			$exists = file_exists( $filename );
			$this->assertTrue( $exists );
			$resized_path = $upload_dir['path'].'/flag-'.$data['size']['width'].'x'.$data['size']['height'].'-c-'.$data['crop'].'.png';
			$exists = file_exists( $resized_path );
			$this->assertTrue( $exists );


			runkit_constant_redefine("WP_CONTENT_URL", $old_WP_CONTENT_URL);
			runkit_constant_redefine("WP_CONTENT_DIR", $old_WP_CONTENT_DIR);

			runkit_constant_redefine("WP_SITEURL", 'http://example.org/');


	
		}

		function testLetterbox() {
			$file_loc = TestTimberImage::copyTestImage( 'eastern.jpg' );
			$upload_dir = wp_upload_dir();
			$image = $upload_dir['url'].'/eastern.jpg';
			$new_file = TimberImageHelper::letterbox( $image, 500, 500, '#CCC', true );
			$location_of_image = TimberImageHelper::get_server_location( $new_file );
			$this->addFile( $location_of_image );
			$this->assertTrue (TestTimberImage::checkSize($location_of_image, 500, 500));
			//whats the bg/color of the image
			$this->assertTrue( TestTimberImage::checkPixel($location_of_image, 1, 1, "#CCC") );
		}

	}
