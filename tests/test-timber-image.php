<?php

class TimberImageTest extends WP_UnitTestCase {

	function testExternalImageResize(){
		if (!self::
			is_connected()){
			return null;
		}
		$data = array();
		$data['size'] = array('width' => 600, 'height' => 400);
		$filename = 'St._Louis_Gateway_Arch.jpg';
		$data['test_image'] = 'http://upload.wikimedia.org/wikipedia/commons/a/aa/'.$filename;
		$md5 = md5($data['test_image']);
		Timber::render('assets/image-test.twig', $data);
		$upload_dir = wp_upload_dir();
		$path = $upload_dir['path'].'/'.$md5;
		$exists = file_exists($path.'.jpg');
		/* was the external image D/Ld to the location? */
		$this->assertTrue($exists);
		/* does resize work on external image? */
		$resized_path = $path.'-r-'.$data['size']['width'].'x'.$data['size']['height'].'.jpg';
		$exists = file_exists($resized_path);
		$this->assertTrue($exists);
		$old_time = filemtime($resized_path);
		sleep(2);
		Timber::render('assets/image-test.twig', $data);
		$new_time = filemtime($resized_path);
		$this->assertEquals($old_time, $new_time);
	}

	function copyTestImage($img = 'arch.jpg'){
		$upload_dir = wp_upload_dir();
		$destination = $upload_dir['path'].'/'.$img;
		if (!file_exists($destination)){
			copy(__DIR__.'/assets/'.$img, $destination);
		}
		return $destination;
	}

	function testImageResize(){
		$data = array();
		$data['size'] = array('width' => 600, 'height' => 400);
		$upload_dir = wp_upload_dir();
		$this->copyTestImage();
		$url = $upload_dir['url'].'/arch.jpg';
		$data['test_image'] = $url;
		Timber::render('assets/image-test.twig', $data);
		$resized_path = $upload_dir['path'].'/arch-r-'.$data['size']['width'].'x'.$data['size']['height'].'.jpg';
		$exists = file_exists($resized_path);
		$this->assertTrue($exists);
		//Now make sure it doesnt regenerage
		$old_time = filemtime($resized_path);
		sleep(2);
		Timber::render('assets/image-test.twig', $data);
		$new_time = filemtime($resized_path);
		error_log('time is '.$old_time);
		$this->assertEquals($old_time, $new_time);
	}

	function testResizeTallImage(){
		$data = array();
		$data['size'] = array('width' => 600);
		$upload_dir = wp_upload_dir();
		$this->copyTestImage('tall.jpg');
		$url = $upload_dir['url'].'/tall.jpg';
		$data['test_image'] = $url;
		Timber::render('assets/image-test-one-param.twig', $data);
		$resized_path = $upload_dir['path'].'/tall-r-'.$data['size']['width'].'x0.jpg';
		$exists = file_exists($resized_path);
		$this->assertTrue($exists);
		//make sure it's the width it's supposed to be
		$image = wp_get_image_editor($resized_path);
		$current_size = $image->get_size();
		$w = $current_size['width'];
		$this->assertEquals($w, 600);
	}

	function testInitFromURL(){
		$destination_path = $this->copyTestImage();
		$destination_url = str_replace(ABSPATH, 'http://'.$_SERVER['HTTP_HOST'].'/', $destination_path);
		$image = new TimberImage($destination_url);
		$this->assertEquals($destination_url, $image->get_src());
		$this->assertEquals($destination_url, (string)$image);
	}

	function testPostThumbnails(){
		$upload_dir = wp_upload_dir();
		$post_id = $this->factory->post->create();
		$filename = $this->copyTestImage('flag.png');
		$destination_url = str_replace(ABSPATH, 'http://'.$_SERVER['HTTP_HOST'].'/', $filename);
		$wp_filetype = wp_check_filetype(basename($filename), null );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
			'post_content' => '',
			'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
		add_post_meta($post_id, '_thumbnail_id', $attach_id, true);
		$data = array();
		$data['post'] = new TimberPost($post_id);
		$data['size'] = array('width' => 100, 'height' => 50);
		Timber::render('assets/thumb-test.twig', $data);
		$exists = file_exists($filename);
		$this->assertTrue($exists);
		$resized_path = $upload_dir['path'].'/flag-r-'.$data['size']['width'].'x'.$data['size']['height'].'.png';
		error_log($resized_path);
		$exists = file_exists($resized_path);
		$this->assertTrue($exists);
	}

	public static function is_connected() {
	    $connected = @fsockopen("www.google.com", [80|443]);
	    if ($connected){
	        $is_conn = true; //action when connected
	        fclose($connected);
	    } else {
	        $is_conn = false; //action in connection failure
	    }
	    return $is_conn;

	}


}

