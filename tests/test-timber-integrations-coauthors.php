<?php

	class TestTimberIntegrationsCoAuthors extends Timber_UnitTestCase {

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
			$authors = $post->authors();
			$str = Timber::compile_string('{{post.authors|pluck("name")|list(",", "and")}}', array('post' => $post));
			$this->assertEquals('Tito Bottitta, Mike Swartz and JP Boneyard', $str);
		}	

		function testAuthors() {
			$uid = $this->factory->user->create(array('display_name' => 'Jen Weinman', 'user_login' => 'aquajenus'));
			$pid = $this->factory->post->create(array('post_author' => $uid));
			$post = new TimberPost($pid);
			$template_string = '{% for author in post.authors %}{{author.name}}{% endfor %}';
			$str = Timber::compile_string($template_string, array('post' => $post));
			$this->assertEquals('Jen Weinman', $str);
		}	

	}