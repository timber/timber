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
		if (file_exists($path.'.jpg')){
			$this->assertTrue(true);
		} else {
			$this->assertTrue(false);
		}
		$resized_path = $path.'-r-'.$data['size']['width'].'x'.$data['size']['height'].'.jpg';
		if (file_exists($resized_path)){
			$this->assertTrue(true);
		} else {
			$this->assertTrue(false);
		}
	}

	function testImageResize(){
		return;
		$data = array();
		$data['size'] = array('width' => 600, 'height' => 400);
		//move arch to uploads
		wp_upload_dir();
		$data['test_image'] = '/wp-content/'.wp_upload_dir().'/arch.jpg';
		Timber::render('assets/image-test.twig', $data);
		/*if (file_exists()){
			
		}*/

	}


}

