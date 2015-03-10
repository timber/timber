<?php

	class TestTimberUser extends WP_UnitTestCase {

		function testInitWithID(){
			$uid = $this->factory->user->create(array('display_name' => 'Baberaham Lincoln'));
			$user = new TimberUser($uid);
			$this->assertEquals('Baberaham Lincoln', $user->name);
			$this->assertEquals($uid, $user->id);
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
			$this->assertEquals('http://example.org/author/lincoln', $user->permalink());
			$this->assertEquals('http://example.org/author/lincoln', $user->link());
			$this->assertEquals('/author/lincoln', $user->path());
			$user->president = '16th';
			$this->assertEquals('16th', $user->president);

		}
	}
