<?php

class TimberImageTest extends WP_UnitTestCase {

/* ----------------
 * Helper functions
 ---------------- */

	static function copyTestImage( $img = 'arch.jpg' ) {
		$upload_dir = wp_upload_dir();
		$destination = $upload_dir['path'].'/'.$img;
		if ( !file_exists( $destination ) ) {
			copy( __DIR__.'/assets/'.$img, $destination );
		}
		return $destination;
	}

	static function getTestImageURL( $img = 'arch.jpg', $relative = false) {
		$upload_dir = wp_upload_dir();
		$result = $upload_dir['url'].'/'.$img;
		if ( $relative ) {
			$result = str_replace(home_url(), '', $result);
		}
		return $result;
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

	function add_lang_to_home( $url, $path, $orig_scheme, $blog_id ){
		return "$url?lang=en";
	}

	function get_post_with_image() {
		$pid = $this->factory->post->create();
		$filename = self::copyTestImage( 'arch.jpg' );
		$attachment = array( 'post_title' => 'The Arch', 'post_content' => '' );
		$iid = wp_insert_attachment( $attachment, $filename, $pid );
		add_post_meta( $pid, '_thumbnail_id', $iid, true );
		$post = new TimberPost($pid);
		return $post;
	}

/* ----------------
 * Tests
 ---------------- */

	function testImageMeta() {
		$post = $this->get_post_with_image();
		$image = $post->thumbnail();
		update_post_meta( $image->ID, 'architect', 'Eero Saarinen' );
		$this->assertEquals( 'Eero Saarinen', $image->meta( 'architect' ) );
		$this->assertEquals( 'Eero Saarinen', $image->architect );
	}

	function testImageSizes() {
		$post = $this->get_post_with_image();
		$image = $post->thumbnail();
		$this->assertEquals( 1500, $image->width() );
		$this->assertEquals( 1000, $image->height() );
		$this->assertEquals( $post->ID, $image->parent()->id );
		$this->assertEquals( 1.5, $image->aspect() );
	}

	function testExternalImageResize() {
		if ( !self::is_connected() ) {
			$this->markTestSkipped('Cannot test external images when not connected to internet');
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
		$file = 'eastern.jpg';
		$file_loc = self::copyTestImage( $file );
		$upload_dir = wp_upload_dir();
		$filename = TimberImageHelper::get_resize_file_url( self::getTestImageURL($file, true), 300, 500, 'default' );
		$expected = $upload_dir['relative'].$upload_dir['subdir'].'/eastern-300x500-c-default.jpg';
		$this->assertEquals( $expected, $filename );
	}

	function testResizeFileNamingWithAbsoluteURL() {
		$file_loc = self::copyTestImage( 'eastern.jpg' );
		$upload_dir = wp_upload_dir();
		$url_src = $upload_dir['url'].'/eastern.jpg';
		$filename = TimberImageHelper::get_resize_file_url( $url_src, 300, 500, 'default' );
		$this->assertEquals( $upload_dir['url'].'/eastern-300x500-c-default.jpg', $filename );
	}

	function testResizeFileNamingWithLangHome() {
		add_filter( 'home_url', array($this,'add_lang_to_home') , 1, 4 );
		$file_loc = self::copyTestImage( 'eastern.jpg' );
		$upload_dir = wp_upload_dir();
		$url_src = $upload_dir['url'].'/eastern.jpg';
		$filename = TimberImageHelper::get_resize_file_url( $url_src, 300, 500, 'default' );
		$this->assertEquals( $upload_dir['url'].'/eastern-300x500-c-default.jpg', $filename );
	}

	function testLetterboxFileNaming() {
		$file_loc = self::copyTestImage( 'eastern.jpg' );
		$upload_dir = wp_upload_dir();
		$url_src = $upload_dir['url'].'/eastern.jpg';
		$filename = TimberImageHelper::get_letterbox_file_url( $url_src, 300, 500, '#FFFFFF' );
		// $filename = str_replace( ABSPATH, '', $filename );
		$this->assertEquals( $upload_dir['url'].'/eastern-lbox-300x500-FFFFFF.jpg', $filename );
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

	function testImageDeletionByURL() {
		$post_id = $this->factory->post->create();
		$filename = self::copyTestImage( 'flag.png' );
		$wp_filetype = wp_check_filetype( basename( $filename ), null );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content' => '',
			'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
		$data = array();
		$data['size'] = array( 'width' => 500, 'height' => 300 );
		$upload_dir = wp_upload_dir();
		$data['test_image'] = $upload_dir['url'].'/flag.png';
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
		TimberImageHelper::delete_resized_files( $data['test_image'] );
		//Have the children been deleted as well?
		$this->assertFileNotExists( $resized_520_file );
		$this->assertFileNotExists( $resized_500_file );
	}

	function testImageDeletionByAttachmentLocation() {
		$post_id = $this->factory->post->create();
		$filename = self::copyTestImage( 'flag.png' );
		$wp_filetype = wp_check_filetype( basename( $filename ), null );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content' => '',
			'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
		$data = array();
		$data['size'] = array( 'width' => 500, 'height' => 300 );
		$upload_dir = wp_upload_dir();
		$data['test_image'] = $upload_dir['url'].'/flag.png';
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
		$post = new TimberImage( $attach_id );
		TimberImageHelper::delete_resized_files( $post->file_loc );
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
		if (!file_exists(get_template_directory().'/images')) {
    		mkdir(get_template_directory().'/images', 0777, true);
		}
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

	function testWithOutputBuffer() {
		ob_start();
		$post = $this->get_post_with_image();
		$str = '<img src="{{ post.thumbnail.url|resize(510, 280) }}" />';
		Timber::render_string($str, array('post' => $post));
		$result = ob_get_contents();
		ob_end_clean();
		$m = date('m');
		$this->assertEquals('<img src="http://example.org/wp-content/uploads/2015/'.$m.'/arch-510x280-c-default.jpg" />', $result);
	}

	function testResizeNamed() {
		add_image_size('timber-testResizeNamed', $width = 600, $height = 400, $crop = true);
		$data = array();
		$data['size'] = 'timber-testResizeNamed';
		$upload_dir = wp_upload_dir();
		self::copyTestImage();
		$url = $upload_dir['url'].'/arch.jpg';
		$data['test_image'] = $url;
		Timber::compile('assets/image-resize-named.twig', $data);
		$resized_path = $upload_dir['path'].'/arch-'.$width.'x'.$height.'-c-default.jpg';
		$this->assertFileExists($resized_path);
		//Now make sure it doesn't regenerate
		$old_time = filemtime($resized_path);
		sleep(1);
		Timber::compile('assets/image-resize-named.twig', $data);
		$new_time = filemtime($resized_path);
		$this->assertEquals($old_time, $new_time);
	}

	function testBogusResizeNamed() {
		$data = array();
		$data['size'] = 'timber-foobar';
		$upload_dir = wp_upload_dir();
		self::copyTestImage();
		$url = $upload_dir['url'].'/arch.jpg';
		$data['test_image'] = $url;
		$result = Timber::compile('assets/image-resize-named.twig', $data);
		$this->assertEquals('<img src="'.$url.'" />', trim($result));
	}

	function testPostThumbnailsNamed() {
		add_image_size('timber-testPostThumbnailsNamed', $width = 100, $height = 50, $crop = true);
		$upload_dir = wp_upload_dir();
		$post_id = $this->factory->post->create();
		$filename = self::copyTestImage('flag.png');
		$destination_url = str_replace(ABSPATH, 'http://'.$_SERVER['HTTP_HOST'].'/', $filename);
		$wp_filetype = wp_check_filetype(basename($filename), null);
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
			'post_content' => '',
			'post_status' => 'inherit',
		);
		$attach_id = wp_insert_attachment($attachment, $filename, $post_id);
		add_post_meta($post_id, '_thumbnail_id', $attach_id, true);
		$data = array();
		$data['post'] = new TimberPost($post_id);
		$data['size'] = 'timber-testPostThumbnailsNamed';
		Timber::compile('assets/image-thumb-named.twig', $data);
		$resized_path = $upload_dir['path'].'/flag-'.$width.'x'.$height.'-c-default.png';
		$this->assertFileExists($resized_path);
	}

	function testPostThumbnailsWithWPName() {
		$upload_dir = wp_upload_dir();
		$post_id = $this->factory->post->create();
		$filename = self::copyTestImage('flag.png');
		$destination_url = str_replace(ABSPATH, 'http://'.$_SERVER['HTTP_HOST'].'/', $filename);
		$wp_filetype = wp_check_filetype(basename($filename), null);
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
			'post_content' => '',
			'post_status' => 'inherit',
		);
		$attach_id = wp_insert_attachment($attachment, $filename, $post_id);
		add_post_meta($post_id, '_thumbnail_id', $attach_id, true);
		$data = array();
		$data['post'] = new TimberPost($post_id);
		$data['size'] = 'medium';
		$result = Timber::compile('assets/image-thumb-named.twig', $data);
		$filename = 'flag-300x300-c-default.png';
		$resized_path = $upload_dir['path'].'/'.$filename;
		$this->assertFileExists($resized_path);
		$this->assertEquals('<img src="'.$upload_dir['url'].'/'.$filename.'" />', trim($result));
	}

	function testImageSizeWithWPNameUsingNative(){
		require_once('wp-overrides.php');
		$filename = __DIR__.'/assets/tom-brady.jpg';
		$filesize = filesize($filename);
		$data = array('tmp_name' => $filename, 'name' => 'tom-brady.jpg', 'type' => 'image/jpg', 'size' => $filesize, 'error' => 0);
		$this->assertTrue(file_exists($filename));
		$_FILES['tester'] = $data;
		$file_id = WP_Overrides::media_handle_upload('tester', 0, array(), array( 'test_form' => false));
		if (!is_int($file_id)) {
			error_log(print_r($file_id, true));
		}
		$image = new TimberImage($file_id);
		$str = '<img src="{{image.src(\'medium\')}}" />';
		$result = Timber::compile_string($str, array('image' => $image));
		$upload_dir = wp_upload_dir();
		$this->assertEquals('<img src="'.$upload_dir['url'].'/'.$image->sizes['medium']['file'].'" />', trim($result));
	}

	function testImageSizeWithWPNameUsingNativeGif(){
		require_once('wp-overrides.php');
		$filename = __DIR__.'/assets/boyer.gif';
		$filesize = filesize($filename);
		$data = array('tmp_name' => $filename, 'name' => 'boyer.gif', 'type' => 'image/gif', 'size' => $filesize, 'error' => 0);
		$this->assertTrue(file_exists($filename));
		$_FILES['tester'] = $data;
		$file_id = WP_Overrides::media_handle_upload('tester', 0, array(), array( 'test_form' => false));
		if (!is_int($file_id)) {
			error_log(print_r($file_id, true));
		}
		$image = new TimberImage($file_id);
		$str = '<img src="{{image.src(\'medium\')}}" />';
		$result = Timber::compile_string($str, array('image' => $image));
		$upload_dir = wp_upload_dir();
		$this->assertEquals('<img src="'.$upload_dir['url'].'/'.$image->sizes['medium']['file'].'" />', trim($result));
	}

	function testGifToJpg() {
		$filename = self::copyTestImage('loading.gif');
		$gif_url = str_replace(ABSPATH, 'http://'.$_SERVER['HTTP_HOST'].'/', $filename);
		$str = '<img src="{{'."'$gif_url'".'|tojpg}}" />';
		$result = Timber::compile_string($str);
		$jpg_url = str_replace('.gif', '.jpg', $gif_url);
		$this->assertEquals('<img src="'.$jpg_url.'" />', $result);
	}

	function testImageHelperInit() {
		$helper = TimberImageHelper::init();
		$this->assertTrue(defined('WP_CONTENT_SUBDIR'));
	}

	function testResizeGif() {
		$filename = self::copyTestImage('loading.gif');
		$gif_url = str_replace(ABSPATH, 'http://'.$_SERVER['HTTP_HOST'].'/', $filename);
		$str = '<img src="{{'."'$gif_url'".'|resize(200)}}" />';
		$result = Timber::compile_string($str);
		$resized_url = str_replace('loading.gif', 'loading-200x0-c-default.gif', $gif_url);
		$resized_path = str_replace('http://example.org', ABSPATH, $resized_url);
		$resized_path = TimberURLHelper::remove_double_slashes($resized_path);
		$this->assertFileExists($resized_path);
	}

}
