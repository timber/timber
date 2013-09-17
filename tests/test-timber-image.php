<?php

class TimberImageTest extends WP_UnitTestCase {

	function testSample() {
		// replace this with some actual testing code
		$this->assertTrue( true );
	}

	function testExternalImageResize(){
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
		sleep(3);
		Timber::render('assets/image-test.twig', $data);
		$new_time = filemtime($resized_path);
		$this->assertEquals($old_time, $new_time);
	}

	function testImageResize(){
		$data = array();
		$data['size'] = array('width' => 600, 'height' => 400);
		$upload_dir = wp_upload_dir();
		copy(__DIR__.'/assets/arch.jpg', $upload_dir['path'].'/arch.jpg');
		$url = $upload_dir['url'].'/arch.jpg';
		$data['test_image'] = $url;
		Timber::render('assets/image-test.twig', $data);
		$resized_path = $upload_dir['path'].'/arch-r-'.$data['size']['width'].'x'.$data['size']['height'].'.jpg';
		$exists = file_exists($resized_path);
		$this->assertTrue($exists);
		//Now make sure it doesnt regenerage
		$old_time = filemtime($resized_path);
		sleep(3);
		Timber::render('assets/image-test.twig', $data);
		$new_time = filemtime($resized_path);
		error_log('time is '.$old_time);
		$this->assertEquals($old_time, $new_time);
	}

	


}

