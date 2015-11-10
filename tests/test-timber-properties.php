<?php

class TestTimberProperty extends Timber_UnitTestCase {

	function testPropertyID() {
		$post_id = $this->factory->post->create();
		$user_id = $this->factory->user->create();
		$comment_id = $this->factory->comment->create( array( 'comment_post_ID' => $post_id ) );
		$term_id = wp_insert_term( 'baseball', 'post_tag' );
		$term_id = $term_id['term_id'];
		$post = new TimberPost( $post_id );
		$user = new TimberUser( $user_id );
		$term = new TimberTerm( $term_id );
		$comment = new TimberComment( $comment_id );
		$this->assertEquals( $post_id, $post->ID );
		$this->assertEquals( $post_id, $post->id );
		$this->assertEquals( $user_id, $user->ID );
		$this->assertEquals( $user_id, $user->id );
		$this->assertEquals( $term_id, $term->ID );
		$this->assertEquals( $term_id, $term->id );
		$this->assertEquals( $comment_id, $comment->ID );
		$this->assertEquals( $comment_id, $comment->id );
	}


	function _initObjects() {
		$post_id = $this->factory->post->create();
		$user_id = $this->factory->user->create();
		$comment_id = $this->factory->comment->create( array( 'comment_post_ID' => $post_id ) );
		$term_id = wp_insert_term( 'baseball', 'post_tag' );
		$term_id = $term_id['term_id'];
		$post = new TimberPost( $post_id );
		$user = new TimberUser( $user_id );
		$term = new TimberTerm( $term_id );
		$comment = new TimberComment( $comment_id );
		$site = new TimberSite();
		return array( 'post' => $post, 'user' => $user, 'term' => $term, 'comment' => $comment, 'site' => $site );
	}

	function testMeta() {
		$vars = $this->_initObjects();
		extract( $vars );
		$site->update( 'bill', 'clinton' );
		$post->update( 'thomas', 'jefferson' );
		$term->update( 'abraham', 'lincoln' );
		$user->update( 'dwight', 'einsenhower' );
		$user->update( 'teddy', 'roosevelt' );
		$user->update( 'john', 'kennedy' );
		$comment->update( 'george', 'washington' );
		$this->assertEquals( 'jefferson', $post->thomas );
		$this->assertEquals( 'lincoln', $term->abraham );
		$this->assertEquals( 'roosevelt', $user->teddy );
		$this->assertEquals( 'washington', $comment->george );
		$this->assertEquals( 'clinton', $site->bill );

		$this->assertEquals( 'jefferson', Timber::compile_string( '{{post.thomas}}', array( 'post' => $post ) ) );
		$this->assertEquals( 'lincoln', Timber::compile_string( '{{term.abraham}}', array( 'term' => $term ) ) );
		$this->assertEquals( 'roosevelt', Timber::compile_string( '{{user.teddy}}', array( 'user' => $user ) ) );
		$this->assertEquals( 'washington', Timber::compile_string( '{{comment.george}}', array( 'comment' => $comment ) ) );
		$this->assertEquals( 'clinton', Timber::compile_string( '{{site.bill}}', array( 'site' => $site ) ) );
	}

}
