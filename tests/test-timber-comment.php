<?php

class TimberCommentTest extends WP_UnitTestCase {

	function testComment(){
		$post_id = $this->factory->post->create();
		$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id));
		$comment = new TimberComment($comment_id);
		$this->assertEquals('TimberComment', get_class($comment));
		$this->assertEquals($comment_id, $comment->ID);
	}

	function testAvatar(){
		if (!TimberImageTest::is_connected()){
			$this->markTestSkipped('Cannot test avatar images when not connected to internet');
		}
		$post_id = $this->factory->post->create();
		$comment_id = $this->factory->comment->create(array('comment_post_ID' => $post_id));
		$comment = new TimberComment($comment_id);

		# test default gravatr holding image
		$avatar = $comment->avatar(32, "mystery");
		
		$this->assertTrue(substr ( $avatar , 0, 5 ) == "http:");

		# does it work if its SSL?
		$_SERVER['HTTPS'] = 'on';
		$avatar = $comment->avatar(32, "mystery");
		$this->assertTrue(200 === $this->crawl($avatar));
		$this->assertTrue(substr ( $avatar , 0, 6 ) == "https:");
		$_SERVER['HTTPS'] = 'off';

		# pass custom url on different domain. can't check by crawling as
		# i get a 302 regardless of default url
		# so just check it comes back with it in the url
		$this->valid_avatar($comment, "http://upload.wikimedia.org/wikipedia/en/b/bc/Wiki.png");

		# same domain.
		$this->valid_avatar($comment, get_template_directory_uri() . "/images/default.png");

		#relative
		$default_url = "/images/default.png";
		$avatar = $comment->avatar(32, $default_url );
		if (strstr($avatar, '?')){
  			list($url, $params) = explode('?', $avatar);
  			$default_url = get_template_directory_uri() . $default_url;
  			# you get back the absoulte url to default in the avatar url?
  			$this->assertEquals($params, "d=$default_url&amp;s=32");
  		}
  		# you get back url?
  		$this->assertTrue(substr ( get_template_directory_uri() . $avatar , 0, 5 ) == "http:");
	}


	function valid_avatar($comment, $default_url){
		$avatar = $comment->avatar(32, $default_url);
		if (strstr($avatar, '?')){
  			list($url, $params) = explode('?', $avatar);
  			# you get back the default in the avatar url?
  			$this->assertEquals($params, "d=$default_url&amp;s=32");
  		}
  		# you get back url?
  		$this->assertTrue(substr ( $avatar , 0, 5 ) == "http:");
	}


	function crawl($url){
		$handle = curl_init($url);
		curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
		/* Get the HTML or whatever is linked in $url. */
		$response = curl_exec($handle);
		/* Check for 404 (file not found). */
		$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
		curl_close($handle);
  		return $httpCode;
	}

}