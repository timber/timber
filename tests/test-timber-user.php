<?php

	class TestTimberUser extends Timber_UnitTestCase {

		function testIDDataType() {
			$uid = self::factory()->user->create(array('display_name' => 'James Marshall'));
			$user = new Timber\User($uid);
			$this->assertEquals('integer', gettype($user->id));
			$this->assertEquals('integer', gettype($user->ID));
		}

		function testInitWithID(){
			$uid = self::factory()->user->create(array('display_name' => 'Baberaham Lincoln'));
			$user = new TimberUser($uid);
			$this->assertEquals('Baberaham Lincoln', $user->name);
			$this->assertEquals($uid, $user->id);
		}

		function testInitWithSlug(){
			$uid = self::factory()->user->create(array('display_name' => 'Tito Bottitta', 'user_login' => 'mbottitta'));
			$user = new TimberUser('mbottitta');
			$this->assertEquals('Tito Bottitta', $user->name);
			$this->assertEquals($uid, $user->id);
		}

		function testPostWithBlankUser() {
			$post_id = wp_insert_post(
			    [ 'post_title' => 'Baseball'
			    , 'post_content' => 'is fine, I guess'
			    , 'post_status' => 'publish'
			    ]);
			$post = new Timber\Post($post_id);
			$template = '{{ post.title }} by {{ post.author }}';
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('Baseball by', trim($str));
		}

		function testUserCapability() {
			$uid = self::factory()->user->create(array('display_name' => 'Tito Bottitta', 'user_login' => 'mbottitta', 'role' => 'editor'));
			$user = new Timber\User('mbottitta');
			$this->assertTrue($user->can('edit_posts'));
			$this->assertFalse($user->can('activate_plugins'));
		}

		function testUserRole() {
			$uid = self::factory()->user->create(array('display_name' => 'Tito Bottitta', 'user_login' => 'mbottitta', 'role' => 'editor'));
			$user = new Timber\User('mbottitta');
			$this->assertArrayHasKey('editor', $user->roles());
		}

		function testDescription() {
			$uid = self::factory()->user->create(array('display_name' => 'Baberaham Lincoln', 'user_login' => 'blincoln'));
			update_user_meta($uid, 'description', 'Sixteenth President');
			$user = new TimberUser($uid);
			$this->assertEquals('Sixteenth President', $user->description);
			$pid = self::factory()->post->create(array('post_author' => $uid));
			$post = new TimberPost($pid);
			$str = Timber::compile_string('{{post.author.description}}', array('post' => $post));
			$this->assertEquals('Sixteenth President', $str);
		}

		function testInitShouldUnsetPassword() {
			$uid = self::factory()->user->create(array('display_name' => 'Tom Riddle'));
			$user = new TimberUser($uid );
			$this->assertFalse(property_exists( $user, 'user_pass'));
		}

		function testInitWithObject(){
			$uid = self::factory()->user->create(array('display_name' => 'Baberaham Lincoln'));
			$uid = get_user_by('id', $uid);
			$user = new TimberUser($uid);
			$this->assertEquals('Baberaham Lincoln', $user->name);
		}

		function testLinks() {
			$this->setPermalinkStructure('/blog/%year%/%monthnum%/%postname%/');
			$uid = self::factory()->user->create(array('display_name' => 'Baberaham Lincoln', 'user_login' => 'lincoln'));
			$uid = get_user_by('id', $uid);
			$user = new TimberUser($uid);
			$this->assertEquals('http://example.org/blog/author/lincoln/', trailingslashit($user->link()) );
			$this->assertEquals('/blog/author/lincoln/', trailingslashit($user->path()) );
			$user->president = '16th';
			$this->assertEquals('16th', $user->president);
		}
	}
