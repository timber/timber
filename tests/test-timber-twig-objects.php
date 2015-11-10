<?php

	class TestTimberTwigObjects extends Timber_UnitTestCase {

		function testTimberImageInTwig() {
			$iid = TestTimberImage::get_image_attachment();
			$str = '{{TimberImage('.$iid.').src}}';
			$compiled = Timber::compile_string($str);
			$this->assertEquals('http://example.org/wp-content/uploads/'.date('Y').'/'.date('m').'/arch.jpg', $compiled);
		}

		function testImageInTwig() {
			$iid = TestTimberImage::get_image_attachment();
			$str = '{{Image('.$iid.').src}}';
			$compiled = Timber::compile_string($str);
			$this->assertEquals('http://example.org/wp-content/uploads/'.date('Y').'/'.date('m').'/arch.jpg', $compiled);
		}

		function testImagesInTwig() {
			$images = array();
			$images[] = TestTimberImage::get_image_attachment( 0, 'arch.jpg' );
			$images[] = TestTimberImage::get_image_attachment( 0, 'city-museum.jpg' );
			$str = '{% for image in Image(images) %}{{image.src}}{% endfor %}';
			$compiled = Timber::compile_string($str, array('images' => $images));
			$this->assertEquals('http://example.org/wp-content/uploads/'.date('Y').'/'.date('m').'/arch.jpghttp://example.org/wp-content/uploads/'.date('Y').'/'.date('m').'/city-museum.jpg', $compiled);
		}

		function testTimberImagesInTwig() {
			$images = array();
			$images[] = TestTimberImage::get_image_attachment( 0, 'arch.jpg' );
			$images[] = TestTimberImage::get_image_attachment( 0, 'city-museum.jpg' );
			$str = '{% for image in TimberImage(images) %}{{image.src}}{% endfor %}';
			$compiled = Timber::compile_string($str, array('images' => $images));
			$this->assertEquals('http://example.org/wp-content/uploads/'.date('Y').'/'.date('m').'/arch.jpghttp://example.org/wp-content/uploads/'.date('Y').'/'.date('m').'/city-museum.jpg', $compiled);
		}

		function testTimberImageInTwigToString() {
			$iid = TestTimberImage::get_image_attachment();
			$str = '{{TimberImage('.$iid.')}}';
			$compiled = Timber::compile_string($str);
			$this->assertEquals('http://example.org/wp-content/uploads/'.date('Y').'/'.date('m').'/arch.jpg', $compiled);
		}

		function testTimberPostInTwig(){
			$pid = $this->factory->post->create(array('post_title' => 'Foo'));
			$str = '{{TimberPost('.$pid.').title}}';
			$this->assertEquals('Foo', Timber::compile_string($str));
		}

		function testPostInTwig(){
			$pid = $this->factory->post->create(array('post_title' => 'Foo'));
			$str = '{{Post('.$pid.').title}}';
			$this->assertEquals('Foo', Timber::compile_string($str));
		}

		function testTimberPostsInTwig(){
			$pids[] = $this->factory->post->create(array('post_title' => 'Foo'));
			$pids[] = $this->factory->post->create(array('post_title' => 'Bar'));
			$str = '{% for post in TimberPost(pids) %}{{post.title}}{% endfor %}';
			$this->assertEquals('FooBar', Timber::compile_string($str, array('pids' => $pids)));
		}

		function testPostsInTwig(){
			$pids[] = $this->factory->post->create(array('post_title' => 'Foo'));
			$pids[] = $this->factory->post->create(array('post_title' => 'Bar'));
			$str = '{% for post in Post(pids) %}{{post.title}}{% endfor %}';
			$this->assertEquals('FooBar', Timber::compile_string($str, array('pids' => $pids)));
		}

		function testTimberUserInTwig(){
			$uid = $this->factory->user->create(array('display_name' => 'Pete Karl'));
			$str = '{{TimberUser('.$uid.').name}}';
			$this->assertEquals('Pete Karl', Timber::compile_string($str));
		}

		function testUsersInTwig(){
			$uids[] = $this->factory->user->create(array('display_name' => 'Mark Watabe'));
			$uids[] = $this->factory->user->create(array('display_name' => 'Austin Tzou'));
			$str = '{% for user in User(uids) %}{{user.name}} {% endfor %}';
			$this->assertEquals('Mark Watabe Austin Tzou', trim(Timber::compile_string($str, array('uids' => $uids))));
		}

		function testUserInTwig(){
			$uid = $this->factory->user->create(array('display_name' => 'Nathan Hass'));
			$str = '{{User('.$uid.').name}}';
			$this->assertEquals('Nathan Hass', Timber::compile_string($str));
		}

		function testTimberUsersInTwig() {
			$uids[] = $this->factory->user->create(array('display_name' => 'Estelle Getty'));
			$uids[] = $this->factory->user->create(array('display_name' => 'Bea Arthur'));
			$str = '{% for user in TimberUser(uids) %}{{user.name}} {% endfor %}';
			$this->assertEquals('Estelle Getty Bea Arthur', trim(Timber::compile_string($str, array('uids' => $uids))));
		}

		function testTimberTermInTwig(){
			$tid = $this->factory->term->create(array('name' => 'Golden Girls'));
			$str = '{{TimberTerm(tid).title}}';
			$this->assertEquals('Golden Girls', Timber::compile_string($str, array('tid' => $tid)));
		}

		function testTermInTwig(){
			$tid = $this->factory->term->create(array('name' => 'Mythbusters'));
			$str = '{{Term(tid).title}}';
			$this->assertEquals('Mythbusters', Timber::compile_string($str, array('tid' => $tid)));
		}

		function testTimberTermsInTwig(){
			$tids[] = $this->factory->term->create(array('name' => 'Foods'));
			$tids[] = $this->factory->term->create(array('name' => 'Cars'));
			$str = '{% for term in TimberTerm(tids) %}{{term.title}} {% endfor %}';
			$this->assertEquals('Foods Cars ', Timber::compile_string($str, array('tids' => $tids)));
		}

		function testTermsInTwig(){
			$tids[] = $this->factory->term->create(array('name' => 'Animals'));
			$tids[] = $this->factory->term->create(array('name' => 'Germans'));
			$str = '{% for term in Term(tids) %}{{term.title}} {% endfor %}';
			$this->assertEquals('Animals Germans ', Timber::compile_string($str, array('tids' => $tids)));
		}

	}
