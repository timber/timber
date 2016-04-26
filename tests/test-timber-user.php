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
			$uid = $this->factory->user->create(array('display_name' => 'Baberaham Lincoln'));
			update_user_meta($uid, 'description', 'Sixteenth President');
			$user = new TimberUser($uid);
			$this->assertEquals('Sixteenth President', $user->description);
			$pid = $this->factory->post->create(array('post_author' => $uid));
			$post = new TimberPost($pid);
			$str = Timber::compile_string('{{post.author.description}}', array('post' => $post));
			$this->assertEquals('Sixteenth President', $str);
		}

		function testInitWithObject(){
			$uid = $this->factory->user->create(array('display_name' => 'Baberaham Lincoln'));
			$uid = get_user_by('id', $uid);
			$user = new TimberUser($uid);
			$this->assertEquals('Baberaham Lincoln', $user->name);
		}

		function testLinks() {
			global $wp_rewrite;
			$struc = '/blog/%year%/%monthnum%/%postname%/';
			$wp_rewrite->permalink_structure = $struc;
			update_option('permalink_structure', $struc);
			$uid = $this->factory->user->create(array('display_name' => 'Baberaham Lincoln', 'user_login' => 'lincoln'));
			$uid = get_user_by('id', $uid);
			$user = new TimberUser($uid);
			$this->assertEquals('http://example.org/author/lincoln', $user->link());
			$this->assertEquals('/author/lincoln', $user->path());
			$user->president = '16th';
			$this->assertEquals('16th', $user->president);

		}
	}
