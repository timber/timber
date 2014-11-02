<?php

class TimberImageTest extends WP_UnitTestCase {

	function testImageMeta() {
		$pid = $this->factory->post->create();
		$filename = self::copyTestImage( 'arch.jpg' );
		$attachment = array( 'post_title' => 'The Arch', 'post_content' => '' );
		$iid = wp_insert_attachment( $attachment, $filename, $pid );
		update_post_meta( $iid, 'architect', 'Eero Saarinen' );
		$image = new TimberImage( $iid );
		$this->assertEquals( 'Eero Saarinen', $image->meta( 'architect' ) );
		$this->assertEquals( 'Eero Saarinen', $image->architect );
	}

	function testImageSizes() {
		$pid = $this->factory->post->create();
		$filename = self::copyTestImage( 'arch.jpg' );
		$attachment = array( 'post_title' => 'The Arch', 'post_content' => '' );
		$iid = wp_insert_attachment( $attachment, $filename, $pid );
		$image = new TimberImage( $iid );
		$this->assertEquals( 1500, $image->width() );
		$this->assertEquals( 1000, $image->height() );
		$this->assertEquals( $pid, $image->parent()->id );
		$this->assertEquals( 1.5, $image->aspect() );
	}

	function testExternalImageResize() {
		if ( !self::is_connected() ) {
			return null;
		}
		$data = array();
		$data['size'] = array( 'width' => 600, 'height' => 400 );
		$data['crop'] = 'default';
		$filename = 'St._Louis_Gateway_Arch.jpg';
		$data['test_image'] = 'http://upload.wikimedia.org/wikipedia/commons/a/aa/'.$filename;
		$md5 = md5( $data['test_image'] );
		Timber::compile( 'assets/image-test.twig', $data );
		$upload_dir = wp_upload_dir();
		$path = $upload_dir['path'].'/'.$md5;
		/* was the external image D/Ld to the location? */
		$this->assertFileExists( $path.'.jpg' );
		/* does resize work on external image? */
		$resized_path = $path.'-'.$data['size']['width'].'x'.$data['size']['height'].'-c-'.$data['crop'].'.jpg';
		$this->assertFileExists( $resized_path );
		$old_time = filemtime( $resized_path );
		sleep( 1 );
		$str = Timber::compile( 'assets/image-test.twig', $data );
		$new_time = filemtime( $resized_path );
		$this->assertEquals( $old_time, $new_time );
	}

	static function copyTestImage( $img = 'arch.jpg' ) {
		$upload_dir = wp_upload_dir();
		$destination = $upload_dir['path'].'/'.$img;
		if ( !file_exists( $destination ) ) {
			copy( __DIR__.'/assets/'.$img, $destination );
		}
		return $destination;
	}

	function testUpSizing() {
		$data = array();
		$file_loc = self::copyTestImage( 'stl.jpg' );
		$upload_dir = wp_upload_dir();
		$new_file = TimberImageHelper::resize( $upload_dir['url'].'/stl.jpg', 500, 200, 'default', true );
		$location_of_image = TimberImageHelper::get_server_location( $new_file );
		$size = getimagesize( $location_of_image );
		$this->assertEquals( 500, $size[0] );
	}

	function testUpSizing2Param() {
		$data = array();
		$file_loc = self::copyTestImage( 'stl.jpg' );
		$upload_dir = wp_upload_dir();
		$new_file = TimberImageHelper::resize( $upload_dir['url'].'/stl.jpg', 500, 300, 'default', true );
		$location_of_image = TimberImageHelper::get_server_location( $new_file );
		$size = getimagesize( $location_of_image );
		$this->assertEquals( 500, $size[0] );
		$this->assertEquals( 300, $size[1] );
	}


	function testImageResizeRelative() {
		$upload_dir = wp_upload_dir();
		self::copyTestImage();
		$url = $upload_dir['url'].'/arch.jpg';
		$url = str_replace( 'http://example.org', '', $url );
		$data = array( 'crop' => 'default', 'test_image' => $url );
		$data['size'] = array( 'width' => 300, 'height' => 300 );
		$html = Timber::compile( 'assets/image-test.twig', $data );
		$resized_path = $upload_dir['path'].'/arch-'.$data['size']['width'].'x'.$data['size']['height'].'-c-'.$data['crop'].'.jpg';
		$this->assertFileExists( $resized_path );
		//Now make sure it doesnt regenerage
		$old_time = filemtime( $resized_path );
		sleep( 1 );
		Timber::compile( 'assets/image-test.twig', $data );
		$new_time = filemtime( $resized_path );
		$this->assertEquals( $old_time, $new_time );
	}


	function testImageResize() {
		$data = array();
		$data['size'] = array( 'width' => 600, 'height' => 400 );
		$upload_dir = wp_upload_dir();
		self::copyTestImage();
		$url = $upload_dir['url'].'/arch.jpg';
		$data['test_image'] = $url;
		$data['crop'] = 'default';
		Timber::compile( 'assets/image-test.twig', $data );
		$resized_path = $upload_dir['path'].'/arch-'.$data['size']['width'].'x'.$data['size']['height'].'-c-'.$data['crop'].'.jpg';
		$this->assertFileExists( $resized_path );
		//Now make sure it doesnt regenerage
		$old_time = filemtime( $resized_path );
		sleep( 1 );
		Timber::compile( 'assets/image-test.twig', $data );
		$new_time = filemtime( $resized_path );
		$this->assertEquals( $old_time, $new_time );
	}

	function testResizeTallImage() {
		$data = array();
		$data['size'] = array( 'width' => 600 );
		$upload_dir = wp_upload_dir();
		self::copyTestImage( 'tall.jpg' );
		$url = $upload_dir['url'].'/tall.jpg';
		$data['test_image'] = $url;
		$data['crop'] = 'default';
		Timber::compile( 'assets/image-test-one-param.twig', $data );
		$resized_path = $upload_dir['path'].'/tall-'.$data['size']['width'].'x0'.'-c-'.$data['crop'].'.jpg';
		$exists = file_exists( $resized_path );
		$this->assertTrue( $exists );
		//make sure it's the width it's supposed to be
		$image = wp_get_image_editor( $resized_path );
		$current_size = $image->get_size();
		$w = $current_size['width'];
		$this->assertEquals( $w, 600 );
	}

	function testInitFromRelativePath() {
		$filename = self::copyTestImage( 'arch.jpg' );
		$path = str_replace(ABSPATH, '/', $filename);
		$image = new TimberImage( $path );
		$this->assertEquals( 1500, $image->width() );
	}

	function testInitFromID() {
		$pid = $this->factory->post->create();
		$filename = self::copyTestImage( 'arch.jpg' );
		$attachment = array( 'post_title' => 'The Arch', 'post_content' => '' );
		$iid = wp_insert_attachment( $attachment, $filename, $pid );
		$image = new TimberImage( $iid );
		$this->assertEquals( 1500, $image->width() );
	}

	function testInitFromFilePath() {
		$image_file = self::copyTestImage();
		$image = new TimberImage( $image_file );
		$this->assertEquals( 1500, $image->width() );
	}

	function testInitFromURL() {
		$destination_path = self::copyTestImage();
		$destination_path = TimberURLHelper::get_rel_path( $destination_path );
		$destination_url = 'http://'.$_SERVER['HTTP_HOST'].$destination_path;
		$image = new TimberImage( $destination_url );
		$this->assertEquals( $destination_url, $image->get_src() );
		$this->assertEquals( $destination_url, (string)$image );
	}

	function testPostThumbnails() {
		$upload_dir = wp_upload_dir();
		$post_id = $this->factory->post->create();
		$filename = self::copyTestImage( 'flag.png' );
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
	}

	function testImageAltText() {
		$upload_dir = wp_upload_dir();
		$thumb_alt = 'Thumb alt';
		$filename = self::copyTestImage( 'flag.png' );
		$wp_filetype = wp_check_filetype( basename( $filename ), null );
		$post_id = $this->factory->post->create();
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_excerpt' => '',
			'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
		add_post_meta( $post_id, '_thumbnail_id', $attach_id, true );
		add_post_meta( $attach_id, '_wp_attachment_image_alt', $thumb_alt, true );
		$data = array();
		$data['post'] = new TimberPost( $post_id );
		$this->assertEquals( $data['post']->thumbnail()->alt(), $thumb_alt );
	}

	function testResizeFileNaming() {
		$file_loc = self::copyTestImage( 'eastern.jpg' );
		$filename = TimberImageHelper::get_resize_file_rel( $file_loc, 300, 500, 'default' );
		$upload_dir = wp_upload_dir();
		$this->assertEquals( $upload_dir['relative'].$upload_dir['subdir'].'/eastern-300x500-c-default.jpg', $filename );
	}

	function testLetterboxFileNaming() {
		$file_loc = self::copyTestImage( 'eastern.jpg' );
		$filename = TimberImageHelper::get_letterbox_file_rel( $file_loc, 300, 500, '#FFFFFF' );
		$filename = str_replace( ABSPATH, '', $filename );
		$upload_dir = wp_upload_dir();
		$this->assertEquals( $upload_dir['relative'].$upload_dir['subdir'].'/eastern-lbox-300x500-FFFFFF.jpg', $filename );
	}

	function testLetterbox() {
		$file_loc = self::copyTestImage( 'eastern.jpg' );
		$upload_dir = wp_upload_dir();
		$image = $upload_dir['url'].'/eastern.jpg';
		$new_file = TimberImageHelper::letterbox( $image, 500, 500, '#CCC', true );
		$location_of_image = TimberImageHelper::get_server_location( $new_file );
		$size = getimagesize( $location_of_image );
		$this->assertEquals( 500, $size[0] );
		$this->assertEquals( 500, $size[1] );
		//whats the bg/color of the image
		$image = imagecreatefromjpeg( $location_of_image );
		$pixel_rgb = imagecolorat( $image, 1, 1 );
		$colors = imagecolorsforindex( $image, $pixel_rgb );
		$this->assertEquals( 204, $colors['red'] );
		$this->assertEquals( 204, $colors['blue'] );
		$this->assertEquals( 204, $colors['green'] );
	}

	function testLetterboxColorChange() {
		$file_loc = self::copyTestImage( 'eastern.jpg' );
		$upload_dir = wp_upload_dir();
		$new_file_red = TimberImageHelper::letterbox( $upload_dir['url'].'/eastern.jpg', 500, 500, '#FF0000' );
		$new_file = TimberImageHelper::letterbox( $upload_dir['url'].'/eastern.jpg', 500, 500, '#00FF00' );
		$location_of_image = TimberImageHelper::get_server_location( $new_file );
		$size = getimagesize( $location_of_image );
		$this->assertEquals( 500, $size[0] );
		$this->assertEquals( 500, $size[1] );
		//whats the bg/color of the image
		$image = imagecreatefromjpeg( $location_of_image );
		$pixel_rgb = imagecolorat( $image, 1, 1 );
		$colors = imagecolorsforindex( $image, $pixel_rgb );
		$this->assertEquals( 0, $colors['red'] );
		$this->assertEquals( 255, $colors['green'] );
	}

	function testLetterboxTransparent() {
		$base_file = 'eastern-trans.png';
		$file_loc = self::copyTestImage( $base_file );
		$upload_dir = wp_upload_dir();
		$new_file = TimberImageHelper::letterbox( $upload_dir['url'].'/'.$base_file, 500, 500, '#00FF00', true );
		$location_of_image = TimberImageHelper::get_server_location( $new_file );
		$size = getimagesize( $location_of_image );
		$this->assertEquals( 500, $size[0] );
		$this->assertEquals( 500, $size[1] );
		//whats the bg/color of the image
		$image = imagecreatefromjpeg( $location_of_image );
		$pixel_rgb = imagecolorat( $image, 250, 250 );
		$colors = imagecolorsforindex( $image, $pixel_rgb );
		$this->assertEquals( 0, $colors['red'] );
		$this->assertEquals( 255, $colors['green'] );
		$this->assertFileExists( $location_of_image );
	}

	function testLetterboxSixCharHex() {
		$data = array();
		$file_loc = self::copyTestImage( 'eastern.jpg' );
		$upload_dir = wp_upload_dir();
		$new_file = TimberImageHelper::letterbox( $upload_dir['url'].'/eastern.jpg', 500, 500, '#FFFFFF', true );
		$location_of_image = TimberImageHelper::get_server_location( $new_file );
		$size = getimagesize( $location_of_image );
		$this->assertEquals( 500, $size[0] );
		$this->assertEquals( 500, $size[1] );
		//whats the bg/color of the image
		$image = imagecreatefromjpeg( $location_of_image );
		$pixel_rgb = imagecolorat( $image, 1, 1 );
		$colors = imagecolorsforindex( $image, $pixel_rgb );
		$this->assertEquals( 255, $colors['red'] );
		$this->assertEquals( 255, $colors['blue'] );
		$this->assertEquals( 255, $colors['green'] );
	}

	function testPNGtoJPG() {
		$file_loc = self::copyTestImage( 'eastern-trans.png' );
		$upload_dir = wp_upload_dir();
		$new_file = TimberImageHelper::img_to_jpg( $upload_dir['url'].'/eastern-trans.png', '#FFFF00' );
		$location_of_image = TimberImageHelper::get_server_location( $new_file );
		$this->assertFileExists( $location_of_image );
		$image = imagecreatefromjpeg( $location_of_image );
		$pixel_rgb = imagecolorat( $image, 1, 1 );
		$colors = imagecolorsforindex( $image, $pixel_rgb );
		$this->assertEquals( 255, $colors['red'] );
		$this->assertEquals( 255, $colors['green'] );
		$this->assertEquals( 0, $colors['blue'] );
	}

	function testImageDeletionSimilarNames() {
		$data = array();
		$data['size'] = array( 'width' => 500, 'height' => 300 );
		$upload_dir = wp_upload_dir();
		$file = self::copyTestImage( 'arch-2night.jpg' );
		$data['test_image'] = $upload_dir['url'].'/arch-2night.jpg';
		$data['crop'] = 'default';
		$arch_2night = TimberImageHelper::get_resize_file_path( $data['test_image'], $data['size']['width'], $data['size']['height'], $data['crop'] );
		Timber::compile( 'assets/image-test.twig', $data );

		$file = self::copyTestImage( 'arch.jpg' );
		$data['test_image'] = $upload_dir['url'].'/arch.jpg';
		$data['size'] = array( 'width' => 520, 'height' => 250 );
		$data['crop'] = 'left';
		$arch_regular = TimberImageHelper::get_resize_file_path( $data['test_image'], $data['size']['width'], $data['size']['height'], $data['crop'] );
		Timber::compile( 'assets/image-test.twig', $data );
		$this->assertFileExists( $arch_regular );
		$this->assertFileExists( $arch_2night );
		//Delte the regular arch image
		TimberImageHelper::delete_resized_files( $file );
		//The child of the regular arch image should be like
		//poof-be-gone
		$this->assertFileNotExists( $arch_regular );
		//...but the night image remains!
		$this->assertFileExists( $arch_2night );

	}

	function testImageDeletion() {
		$data = array();
		$data['size'] = array( 'width' => 500, 'height' => 300 );
		$upload_dir = wp_upload_dir();
		$file = self::copyTestImage( 'city-museum.jpg' );
		$data['test_image'] = $upload_dir['url'].'/city-museum.jpg';
		$data['crop'] = 'default';
		Timber::compile( 'assets/image-test.twig', $data );
		$resized_500_file = TimberImageHelper::get_resize_file_path( $data['test_image'], $data['size']['width'], $data['size']['height'], $data['crop'] );
		$data['size'] = array( 'width' => 520, 'height' => 250 );
		$data['crop'] = 'left';
		Timber::compile( 'assets/image-test.twig', $data );
		$resized_520_file = TimberImageHelper::get_resize_file_path( $data['test_image'], $data['size']['width'], $data['size']['height'], $data['crop'] );
		//make sure it generated the sizes we're expecting
		$this->assertFileExists( $resized_500_file );
		$this->assertFileExists( $resized_520_file );
		//Now delete the "parent" image
		TimberImageHelper::delete_resized_files( $file );
		//Have the children been deleted as well?
		$this->assertFileNotExists( $resized_520_file );
		$this->assertFileNotExists( $resized_500_file );
	}

	function testLetterboxImageDeletion() {
		$data = array();
		$file = self::copyTestImage( 'city-museum.jpg' );
		$upload_dir = wp_upload_dir();
		$data['test_image'] = $upload_dir['url'].'/city-museum.jpg';
		$new_file = TimberImageHelper::letterbox( $data['test_image'], 500, 500, '#00FF00' );
		$letterboxed_file = TimberImageHelper::get_letterbox_file_path( $data['test_image'], 500, 500, '#00FF00' );
		$this->assertFileExists( $letterboxed_file );
		//Now delete the "parent" image
		TimberImageHelper::delete_letterboxed_files( $file );
		//Have the children been deleted as well?
		$this->assertFileNotExists( $letterboxed_file );
	}

	function testThemeImageResize() {
		$dest = get_template_directory().'/images/cardinals.jpg';
		copy( __DIR__.'/assets/cardinals.jpg', $dest );
		$image = get_template_directory_uri().'/images/cardinals.jpg';
		$image = str_replace( 'http://example.org', '', $image );
		$data = array();
		$data['test_image'] = $image;
		$data['size'] = array( 'width' => 120, 'height' => 120 );
		$str = Timber::compile( 'assets/image-test.twig', $data );
		$this->assertFileExists( get_template_directory().'/images/cardinals-120x120-c-default.jpg' );
		unlink( get_template_directory().'/images/cardinals-120x120-c-default.jpg' );
	}

	function testThemeImageLetterbox() {
		$dest = get_template_directory().'/images/cardinals.jpg';
		copy( __DIR__.'/assets/cardinals.jpg', $dest );
		$image = get_template_directory_uri().'/images/cardinals.jpg';
		$image = str_replace( 'http://example.org', '', $image );
		$letterboxed = TimberImageHelper::letterbox( $image, 600, 300, '#FF0000' );
		$this->assertFileExists( get_template_directory().'/images/cardinals-lbox-600x300-FF0000.jpg' );
		unlink( get_template_directory().'/images/cardinals-lbox-600x300-FF0000.jpg' );
	}

	function testImageWidthWithFilter() {
		$pid = $this->factory->post->create();
		$photo = $this->copyTestImage();
		$photo = TimberURLHelper::get_rel_path($photo);
		update_post_meta($pid, 'custom_photo', '/'.$photo);
		$str = '{{TimberImage(post.custom_photo).width}}';
		$post = new TimberPost($pid);
		$rendered = Timber::compile_string( $str, array('post' => $post) );
		$this->assertEquals( 1500, $rendered );
	}

	public static function is_connected() {
		$connected = @fsockopen( "www.google.com", 80, $errno, $errstr, 3 );
		if ( $connected ) {
			$is_conn = true; //action when connected
			fclose( $connected );
		} else {
			$is_conn = false; //action in connection failure
		}
		return $is_conn;
	}


}
