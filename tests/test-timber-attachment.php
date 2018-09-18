<?php

class TestTimberAttachment extends TimberAttachment_UnitTestCase {

/* ----------------
 * Helper functions
 ---------------- */

 	static function replace_attachment( $old_id, $new_id ) {
		$uploadDir = wp_get_upload_dir();
		$newFile = $uploadDir['basedir'].'/'.get_post_meta($new_id, '_wp_attached_file', true);
		$oldFile = $uploadDir['basedir'].'/'.get_post_meta($old_id, '_wp_attached_file', true);
		if (!file_exists(dirname($oldFile)))
			mkdir(dirname($oldFile), 0777, true);
		copy($newFile, $oldFile);
		$meta = wp_generate_attachment_metadata($old_id, $oldFile);
		wp_update_attachment_metadata($old_id, $meta);
		wp_delete_post($new_id, true);
 	}

	static function copyTestAttachment( $img = 'arch.jpg', $dest_name = null ) {
		$upload_dir = wp_get_upload_dir();
		if ( is_null($dest_name) ) {
			$dest_name = $img;
		}
		$destination = $upload_dir['path'].'/'.$dest_name;
		copy( __DIR__.'/assets/'.$img, $destination );
		return $destination;
	}

	static function getTestAttachmentURL( $img = 'arch.jpg', $relative = false) {
		$upload_dir = wp_get_upload_dir();
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

	public static function get_attachment( $pid = 0, $file = 'arch.jpg' ) {
		$filename = self::copyTestAttachment( $file );
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment = array('post_title' => 'The Arch', 'post_content' => '', 'post_mime_type' => $filetype['type']);
		$iid = wp_insert_attachment( $attachment, $filename, $pid );
		return $iid;
	}

	public static function get_timber_attachment_object($file = 'cropper.png') {
		$iid = self::get_attachment(0, $file);
		return new Timber\Image($iid);
	}

/* ----------------
 * Tests
 ---------------- */

 	function testAttachmentLink() {
 		self::setPermalinkStructure();
 		$attach = self::get_attachment();
 		$image = new Timber\Attachment($attach);
 		$links = array();
 		$links[] = 'http://example.org/'.$image->post_name.'/';
 		$links[] = 'http://example.org/?attachment_id='.$image->ID;
 		$this->assertContains($image->link(), $links);
 	}

 	function testAttachmentInitWithWP_Post() {
 		$aid = self::get_attachment();
 		$wp_post = get_post($aid);
 		$attach = new Timber\Attachment($wp_post);
 		$this->assertEquals($wp_post->ID, $attach->id);
 	}

	function testAttachmentArray() {
		$post_id = $this->factory->post->create();
		$filename = self::copyTestAttachment('arch.jpg');
		$wp_filetype = wp_check_filetype( basename( $filename ), null );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content' => '',
			'post_status' => 'inherit'
		);
		$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
		$data = array('ID' => $attach_id);
		$image = new Timber\Image($data);
		$filename = explode('/', $image->file);
		$filename = array_pop($filename);
		$this->assertEquals('arch.jpg', $filename);
	}

	function testAttachmentPath() {
		$filename = self::copyTestAttachment( 'arch.jpg' );
		$image = new Timber\Image( $filename );
		$this->assertStringStartsWith('/wp-content', $image->path());
		$this->assertStringEndsWith('.jpg', $image->path());
	}

	function testInitFromID() {
		$pid = $this->factory->post->create();
		$filename = self::copyTestAttachment( 'arch.jpg' );
		$attachment = array( 'post_title' => 'The Arch', 'post_content' => '' );
		$iid = wp_insert_attachment( $attachment, $filename, $pid );
		$attachment = new Timber\Attachment( $iid );
		$this->assertEquals( 'The Arch', $attachment->title() );
	}

	function testInitFromFilePath() {
		$attachment_file = self::copyTestAttachment();
		$attachment = new Timber\Attachment( $attachment_file );
		$size = $attachment->size_raw();
		$this->assertEquals( 154752, $size );
	}

	function testInitFromRelativePath() {
		$filename = self::copyTestAttachment( 'arch.jpg' );
		$path = str_replace(ABSPATH, '/', $filename);
		$attachment = new Timber\Attachment( $path );
		$size = $attachment->size_raw();
		$this->assertEquals( 154752, $size );
	}

	function testInitFromURL() {
		$destination_path = self::copyTestAttachment();
		$destination_path = Timber\URLHelper::get_rel_path( $destination_path );
		$destination_url = 'http://'.$_SERVER['HTTP_HOST'].$destination_path;
		$image = new Timber\Attachment( $destination_url );
		$this->assertEquals( $destination_url, $image->src() );
		$this->assertEquals( $destination_url, (string)$image );
	}

	function testPathInfo() {
		$filename = self::copyTestAttachment( 'arch.jpg' );
		$image = new Timber\Attachment( $filename );
		$path_parts = $image->get_pathinfo();
		$this->assertEquals('jpg', $path_parts['extension']);
	}

	function testTimberAttachmentSrc() {
		$iid = self::get_attachment();
		$attachment = new Timber\Attachment($iid);
		$post = get_post($iid);
		$str = '{{ Attachment(post).src }}';
		$result = Timber::compile_string( $str, array('post' => $post) );
		$this->assertEquals($attachment->src(), $result);
	}

	// Test document like pdf, docx
	function testAttachmentSrc() {
		$pid = $this->factory->post->create();
		$iid = self::get_attachment($pid, 'dummy-pdf.pdf');
		$str = '{{ Attachment(post).src }}';
		$result = Timber::compile_string( $str, array('post' => $iid) );
		$this->assertEquals('http://example.org/wp-content/uploads/'.date('Y/m').'/dummy-pdf.pdf', $result);
	}

	function testFileSize() {
 		$pid = $this->factory->post->create();
		$iid = self::get_attachment( $pid, 'dummy-pdf.pdf' );
		$str = '{{ Attachment(post).size }}';
		$result = Timber::compile_string( $str, array( 'post' => $iid ) );
		$this->assertEquals('16&nbsp;KB', $result);
	}

	function testFileSizeRaw() {
 		$pid = $this->factory->post->create();
		$iid = self::get_attachment( $pid, 'dummy-pdf.pdf' );
		$str = '{{ Attachment(post).size_raw }}';
		$result = Timber::compile_string( $str, array( 'post' => $iid ) );
		$this->assertEquals('16555', $result);
	}

	function testFileExtension() {
 		$pid = $this->factory->post->create();
		$iid = self::get_attachment( $pid, 'dummy-pdf.pdf' );
		$str = '{{ Attachment(post).extension }}';
		$result = Timber::compile_string( $str, array( 'post' => $iid ) );
		$this->assertEquals('PDF', $result);
	}
}
