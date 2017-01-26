<?php

	class TestTimberUser extends Timber_UnitTestCase {

		function testInitWithID(){
			$uid = $this->factory->user->create(array('display_name' => 'Baberaham Lincoln'));
			$user = new TimberUser($uid);
			$this->assertEquals('Baberaham Lincoln', $user->name);
			$this->assertEquals($uid, $user->id);
		}

		function testInitWithSlug(){
			$uid = $this->factory->user->create(array('display_name' => 'Tito Bottitta', 'user_login' => 'mbottitta'));
			$user = new TimberUser('mbottitta');
			$this->assertEquals('Tito Bottitta', $user->name);
			$this->assertEquals($uid, $user->id);
		}

		function testDescription() {
			$uid = $this->factory->user->create(array('display_name' => 'Baberaham Lincoln', 'user_login' => 'blincoln'));
			update_user_meta($uid, 'description', 'Sixteenth President');
			$user = new TimberUser($uid);
			$this->assertEquals('Sixteenth President', $user->description);
			$pid = $this->factory->post->create(array('post_author' => $uid));
			$post = new TimberPost($pid);
			$str = Timber::compile_string('{{post.author.description}}', array('post' => $post));
			$this->assertEquals('Sixteenth President', $str);
		}

		function testInitShouldUnsetPassword() {
			$uid = $this->factory->user->create(array('display_name' => 'Tom Riddle'));
			$user = new TimberUser($uid );
			$this->assertFalse(property_exists( $user, 'user_pass'));
		}

		function testInitWithObject(){
			$uid = $this->factory->user->create(array('display_name' => 'Baberaham Lincoln'));
			$uid = get_user_by('id', $uid);
			$user = new TimberUser($uid);
			$this->assertEquals('Baberaham Lincoln', $user->name);
		}

		function testLinks() {
			$this->setPermalinkStructure('/blog/%year%/%monthnum%/%postname%/');
			$uid = $this->factory->user->create(array('display_name' => 'Baberaham Lincoln', 'user_login' => 'lincoln'));
			$uid = get_user_by('id', $uid);
			$user = new TimberUser($uid);
			$this->assertEquals('http://example.org/blog/author/lincoln/', trailingslashit($user->link()) );
			$this->assertEquals('/blog/author/lincoln/', trailingslashit($user->path()) );
			$user->president = '16th';
			$this->assertEquals('16th', $user->president);

		}
	}
