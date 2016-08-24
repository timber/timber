<?php

	class TestTimberIntegrationsCoAuthors extends Timber_UnitTestCase {

		function testCoAuthorName() {
			$uids = array();
			$uids[] = $this->factory->user->create(array('display_name' => 'Jared Novack', 'user_login' => 'jarednova'));
			$uids[] = $this->factory->user->create(array('display_name' => 'Tito Bottitta', 'user_login' => 'mbottitta'));
			$uids[] = $this->factory->user->create(array('display_name' => 'Mike Swartz', 'user_login' => 'm_swartz'));
			$pid = $this->factory->post->create(array('post_author' => $uids[0]));
			$post = new TimberPost($pid);
			$cap = new CoAuthors_Plus();
			$added = $cap->add_coauthors($pid, array('mbottitta', 'm_swartz'));
			$this->assertTrue($added);
			$authors = $post->authors();
			$str = Timber::compile_string('{{post.authors|pluck("name")|list(",", "and")}}', array('post' => $post));
			$this->assertEquals('Jared Novack, Tito Bottitta and Mike Swartz', $str);
		}	

	}