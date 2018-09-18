<?php

class TestTimberAttachment extends TimberAttachment_UnitTestCase {

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
