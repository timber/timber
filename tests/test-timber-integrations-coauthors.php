<?php

	class TestTimberIntegrationsCoAuthors extends Timber_UnitTestCase {

		/**
		 * Overload WP_UnitTestcase to ignore deprecated notices
		 * thrown by use of wp_title() in Timber
		 */
		public function expectedDeprecated() {
			if ( false !== ( $key = array_search( 'WP_User->id', $this->caught_deprecated ) ) ) {
				unset( $this->caught_deprecated[ $key ] );
			}
			parent::expectedDeprecated();
		}

		/* ----------------
		 * Helper functions
		 ---------------- */

		static function create_guest_author( $args ){
			$cap = new CoAuthors_Guest_Authors();
			$guest_id = $cap->create($args);

			return $guest_id;
		}

		static function attach_featured_image( $guest_id, $thumb ){
			$filename = self::copyTestImage( $thumb );
			$wp_filetype = wp_check_filetype( basename( $filename ), null );
			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
				'post_excerpt' => '',
				'post_status' => 'inherit'
			);
			$attach_id = wp_insert_attachment( $attachment, $filename, $guest_id );
			add_post_meta( $guest_id, '_thumbnail_id', $attach_id, true );
			return $attach_id;
		}


		static function copyTestImage( $img = 'avt-1.jpg', $dest_name = null ) {
			$upload_dir = wp_upload_dir();
			if ( is_null($dest_name) ) {
				$dest_name = $img;
			}
			$destination = $upload_dir['path'].'/'.$dest_name;
			copy( __DIR__.'/assets/'.$img, $destination );
			return $destination;
		}
		/* ----------------
		 * Tests
		 ---------------- */

		function testCoAuthors() {
			$uids = array();
			$uids[] = $this->factory->user->create(array('display_name' => 'Jared Novack', 'user_login' => 'jarednova'));
			$uids[] = $this->factory->user->create(array('display_name' => 'Tito Bottitta', 'user_login' => 'mbottitta'));
			$uids[] = $this->factory->user->create(array('display_name' => 'Mike Swartz', 'user_login' => 'm_swartz'));
			$uids[] = $this->factory->user->create(array('display_name' => 'JP Boneyard', 'user_login' => 'jpb'));
			$pid = $this->factory->post->create(array('post_author' => $uids[0]));
			$post = new TimberPost($pid);
			$cap = new CoAuthors_Plus();
			$added = $cap->add_coauthors($pid, array('mbottitta', 'm_swartz', 'jpb'));
			$this->assertTrue($added);
			$cai = new CoAuthorsIterator($pid);
			$authors = $post->authors();
			$str = Timber::compile_string('{{post.authors|pluck("name")|list(",", "and")}}', array('post' => $post));
			global $wp_version;
			if ( $wp_version >= 4.7 ) {
				$this->markTestSkipped('Ordering in Co-Authors Plus is broken in WordPress 4.7');
			} else {
				$this->assertEquals('Tito Bottitta, Mike Swartz and JP Boneyard', $str);
			}
		}

		function testAuthors() {
			$uid = $this->factory->user->create(array('display_name' => 'Jen Weinman', 'user_login' => 'aquajenus'));
			$pid = $this->factory->post->create(array('post_author' => $uid));
			$post = new TimberPost($pid);
			$template_string = '{% for author in post.authors %}{{author.name}}{% endfor %}';
			$str = Timber::compile_string($template_string, array('post' => $post));
			$this->assertEquals('Jen Weinman', $str);
		}

		function testGuestAuthor(){
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);

			$user_login = 'bmotia';
			$display_name = 'Motia';
			$guest_id = self::create_guest_author(
				array('user_login' => $user_login, 'display_name' => $display_name)
			);

			global $coauthors_plus;
			$coauthors_plus->add_coauthors($pid, array($user_login));

			$authors = $post->authors();
			$author = $authors[0];
			$this->assertEquals($display_name, $author->display_name);
			$this->assertInstanceOf('Timber\Integrations\CoAuthorsPlusUser', $author);
		}

		function testGuestAuthorWithRegularAuthor(){
			$uid = $this->factory->user->create(array('display_name' => 'Alexander Hamilton', 'user_login' => 'ahamilton'));
			$pid = $this->factory->post->create(array('post_author' => $uid));
			$post = new TimberPost($pid);

			$user_login = 'bmotia';
			$display_name = 'Motia';
			$guest_id = self::create_guest_author(
				array('user_login' => $user_login, 'display_name' => $display_name)
			);

			global $coauthors_plus;
			$coauthors_plus->add_coauthors($pid, array('ahamilton', $user_login));

			$authors = $post->authors();
			$author = $authors[1];
			$this->assertEquals($display_name, $author->display_name);
			$this->assertInstanceOf('Timber\Integrations\CoAuthorsPlusUser', $author);
			$template_string = '{% for author in post.authors %}{{author.name}}, {% endfor %}';
			$str = Timber::compile_string($template_string, array('post' => $post));
			$this->assertEquals('Alexander Hamilton, Motia,', trim($str));
		}

		function testLinkedGuestAuthor(){
			global $coauthors_plus;

			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);

			$user_login = 'truelogin';
			$display_name = 'True Name';

			$uid = $this->factory->user->create(array('display_name' => $display_name, 'user_login' => $user_login));
			$user = new Timber\User($uid);

			$guest_login = 'linkguestlogin';
			$guest_display_name = 'LGuest D Name';
			$guest_id = self::create_guest_author(
				array('user_login' => $guest_login, 'display_name' => $guest_display_name)
			);
			add_post_meta($guest_id, 'cap-linked_account', $user_login, true);

			$coauthors_plus->add_coauthors($pid, array($user_login));

			$coauthors_plus->force_guest_authors = false;
			$authors = $post->authors();
			$author = $authors[0];
			$this->assertEquals($author->display_name, $user->name);
			$this->assertInstanceOf('Timber\User', $author);
			$this->assertNotInstanceOf('Timber\Integrations\CoAuthorsPlusUser', $author);

			$coauthors_plus->force_guest_authors = true;
			$authors = $post->authors();
			$author = $authors[0];
			$this->assertEquals($author->display_name, $guest_display_name);
			$this->assertInstanceOf('Timber\Integrations\CoAuthorsPlusUser', $author);
		}

		function testGuestAuthorAvatar(){
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);
			$user_login = 'withfeaturedimage';
			$display_name = 'Have Featured';
			$email = 'admin@admin.com';
			$guest_id = self::create_guest_author(
				array(
					'user_email' => $email,
					'user_login' => $user_login,
					'display_name' => $display_name
				)
			);
			$attach_id = self::attach_featured_image($guest_id, 'avt-1.jpg');
			$image = new Timber\Image(array('ID' => $attach_id));

			global $coauthors_plus;
			$coauthors_plus->add_coauthors($pid, array($user_login));

			$template_string = '{% for author in post.authors %}{{author.avatar.src}}{% endfor %}';
			Timber\Integrations\CoAuthorsPlus::$prefer_gravatar = false;
			$str1 = Timber::compile_string($template_string, array('post' => $post));
			$this->assertEquals($image->src(), $str1);

			Timber\Integrations\CoAuthorsPlus::$prefer_gravatar = true;
			$str2 = Timber::compile_string($template_string, array('post' => $post));
			$this->assertEquals(get_avatar_url($email), $str2);
		}
	}
