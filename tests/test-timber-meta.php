<?php

use Timber\Comment;
use Timber\Post;
use Timber\Term;
use Timber\Timber;
use Timber\User;

/**
 * Class TestTimberMeta
 */
class TestTimberMeta extends Timber_UnitTestCase {
	public function setUp() {
		parent::setUp();

		require_once 'php/MetaPost.php';
		require_once 'php/MetaTerm.php';
		require_once 'php/MetaUser.php';
		require_once 'php/MetaComment.php';
	}

	/**
	 * Tests accessing a post meta value through magic methods.
	 */
	function testRawMeta() {
		$post_id    = $this->factory->post->create();
		$term_id    = $this->factory->term->create();
		$user_id    = $this->factory->user->create();
		$comment_id = $this->factory->comment->create();

		update_post_meta( $post_id, 'my_custom_property', 'Sweet Honey' );
		update_term_meta( $term_id, 'my_custom_property', 'Sweet Honey' );
		update_user_meta( $user_id, 'my_custom_property', 'Sweet Honey' );
		update_comment_meta( $comment_id, 'my_custom_property', 'Sweet Honey' );

		$post    = new Post( $post_id );
		$term    = new Term( $term_id );
		$user    = new User( $user_id );
		$comment = new Comment( $comment_id );

		$post_string    = Timber::compile_string(
			"{{ post.raw_meta('my_custom_property') }}", [ 'post' => $post ]
		);
		$term_string    = Timber::compile_string(
			"{{ term.raw_meta('my_custom_property') }}", [ 'term' => $term ]
		);
		$user_string    = Timber::compile_string(
			"{{ user.raw_meta('my_custom_property') }}", [ 'user' => $user ]
		);
		$comment_string = Timber::compile_string(
			"{{ comment.raw_meta('my_custom_property') }}", [ 'comment' => $comment ]
		);

		$this->assertEquals( 'Sweet Honey', $post->raw_meta( 'my_custom_property' ) );
		$this->assertEquals( 'Sweet Honey', $post_string );

		$this->assertEquals( 'Sweet Honey', $term->raw_meta( 'my_custom_property' ) );
		$this->assertEquals( 'Sweet Honey', $term_string );

		$this->assertEquals( 'Sweet Honey', $user->raw_meta( 'my_custom_property' ) );
		$this->assertEquals( 'Sweet Honey', $user_string );

		$this->assertEquals( 'Sweet Honey', $comment->raw_meta( 'my_custom_property' ) );
		$this->assertEquals( 'Sweet Honey', $comment_string );
	}

	/**
	 * Tests accessing a post meta value through magic methods.
	 */
	function testRawMetaInexistent() {
		$post_id    = $this->factory->post->create();
		$term_id    = $this->factory->term->create();
		$user_id    = $this->factory->user->create();
		$comment_id = $this->factory->comment->create();

		$post    = new Post( $post_id );
		$term    = new Term( $term_id );
		$user    = new User( $user_id );
		$comment = new Comment( $comment_id );

		$post_string    = Timber::compile_string(
			"{{ post.raw_meta('my_custom_property_inexistent') }}", [ 'post' => $post ]
		);
		$term_string    = Timber::compile_string(
			"{{ term.raw_meta('my_custom_property_inexistent') }}", [ 'term' => $term ]
		);
		$user_string    = Timber::compile_string(
			"{{ user.raw_meta('my_custom_property_inexistent') }}", [ 'user' => $user ]
		);
		$comment_string = Timber::compile_string(
			"{{ comment.raw_meta('my_custom_property_inexistent') }}", [ 'comment' => $comment ]
		);

		$this->assertEquals( null, $post->raw_meta( 'my_custom_property_inexistent' ) );
		$this->assertEquals( null, $post_string );

		$this->assertEquals( null, $term->raw_meta( 'my_custom_property_inexistent' ) );
		$this->assertEquals( null, $term_string );

		$this->assertEquals( null, $user->raw_meta( 'my_custom_property_inexistent' ) );
		$this->assertEquals( null, $user_string );

		$this->assertEquals( null, $comment->raw_meta( 'my_custom_property_inexistent' ) );
		$this->assertEquals( null, $comment_string );
	}

	/**
	 * Tests accessing a post meta value through magic methods.
	 */
	function testMetaDirectAccess() {
		$post_id    = $this->factory->post->create();
		$term_id    = $this->factory->term->create();
		$user_id    = $this->factory->user->create();
		$comment_id = $this->factory->comment->create();

		update_post_meta( $post_id, 'my_custom_property', 'Sweet Honey' );
		update_term_meta( $term_id, 'my_custom_property', 'Sweet Honey' );
		update_user_meta( $user_id, 'my_custom_property', 'Sweet Honey' );
		update_comment_meta( $comment_id, 'my_custom_property', 'Sweet Honey' );

		$post    = new Post( $post_id );
		$term    = new Term( $term_id );
		$user    = new User( $user_id );
		$comment = new Comment( $comment_id );

		$post_string    = Timber::compile_string(
			'My {{ post.my_custom_property }}', [ 'post' => $post ]
		);
		$term_string    = Timber::compile_string(
			'My {{ term.my_custom_property }}', [ 'term' => $term ]
		);
		$user_string    = Timber::compile_string(
			'My {{ user.my_custom_property }}', [ 'user' => $user ]
		);
		$comment_string = Timber::compile_string(
			'My {{ comment.my_custom_property }}', [ 'comment' => $comment ]
		);

		$this->assertEquals( 'Sweet Honey', $post->my_custom_property );
		$this->assertEquals( 'My Sweet Honey', $post_string );

		$this->assertEquals( 'Sweet Honey', $term->my_custom_property );
		$this->assertEquals( 'My Sweet Honey', $term_string );

		$this->assertEquals( 'Sweet Honey', $user->my_custom_property );
		$this->assertEquals( 'My Sweet Honey', $user_string );

		$this->assertEquals( 'Sweet Honey', $comment->my_custom_property );
		$this->assertEquals( 'My Sweet Honey', $comment_string );
	}

	/**
	 * Tests when you try to directly access a custom field value that is also the name of an
	 * existing public method on the object.
	 *
	 * The result of the method should take precedence over the value of the custom field.
	 */
	function testMetaDirectAccessPublicMethodConflict() {
		$post_id    = $this->factory->post->create();
		$term_id    = $this->factory->term->create();
		$user_id    = $this->factory->user->create();
		$comment_id = $this->factory->comment->create();

		update_post_meta( $post_id, 'public_method', 'I am a meta value' );
		update_term_meta( $term_id, 'public_method', 'I am a meta value' );
		update_user_meta( $user_id, 'public_method', 'I am a meta value' );
		update_comment_meta( $comment_id, 'public_method', 'I am a meta value' );

		$post    = new MetaPost( $post_id );
		$term    = new MetaTerm( $term_id );
		$user    = new MetaUser( $user_id );
		$comment = new MetaComment( $comment_id );

		$post_string    = Timber::compile_string(
			'{{ post.public_method }}', [ 'post' => $post ]
		);
		$term_string    = Timber::compile_string(
			'{{ term.public_method }}', [ 'term' => $term ]
		);
		$user_string    = Timber::compile_string(
			'{{ user.public_method }}', [ 'user' => $user ]
		);
		$comment_string = Timber::compile_string(
			'{{ comment.public_method }}', [ 'comment' => $comment ]
		);

		$this->assertEquals( 'I am a public method', $post->public_method() );
		$this->assertEquals( 'I am a meta value', $post->public_method );
		$this->assertEquals( 'I am a public method', $post_string );

		$this->assertEquals( 'I am a public method', $term->public_method() );
		$this->assertEquals( 'I am a meta value', $term->public_method );
		$this->assertEquals( 'I am a public method', $term_string );

		$this->assertEquals( 'I am a public method', $user->public_method() );
		$this->assertEquals( 'I am a meta value', $user->public_method );
		$this->assertEquals( 'I am a public method', $user_string );

		$this->assertEquals( 'I am a public method', $comment->public_method() );
		$this->assertEquals( 'I am a meta value', $comment->public_method );
		$this->assertEquals( 'I am a public method', $comment_string );
	}

	/**
	 * Tests when you try to directly access a custom field value that is also the name of an
	 * existing public method on the object.
	 *
	 * The result of the method should take precedence over the value of the custom field.
	 */
	function testMetaDirectAccessProtectedMethodConflict() {
		$post_id    = $this->factory->post->create();
		$term_id    = $this->factory->term->create();
		$user_id    = $this->factory->user->create();
		$comment_id = $this->factory->comment->create();

		update_post_meta( $post_id, 'protected_method', 'I am a meta value' );
		update_term_meta( $term_id, 'protected_method', 'I am a meta value' );
		update_user_meta( $user_id, 'protected_method', 'I am a meta value' );
		update_comment_meta( $comment_id, 'protected_method', 'I am a meta value' );

		$post    = new MetaPost( $post_id );
		$term    = new MetaTerm( $term_id );
		$user    = new MetaUser( $user_id );
		$comment = new MetaComment( $comment_id );

		$post_string    = Timber::compile_string(
			'{{ post.protected_method }}', [ 'post' => $post ]
		);
		$term_string    = Timber::compile_string(
			'{{ term.protected_method }}', [ 'term' => $term ]
		);
		$user_string    = Timber::compile_string(
			'{{ user.protected_method }}', [ 'user' => $user ]
		);
		$comment_string = Timber::compile_string(
			'{{ comment.protected_method }}', [ 'comment' => $comment ]
		);

		$this->assertEquals( 'I am a meta value', $post->protected_method() );
		$this->assertEquals( 'I am a meta value', $post->protected_method );
		$this->assertEquals( 'I am a meta value', $post_string );

		$this->assertEquals( 'I am a meta value', $term->protected_method() );
		$this->assertEquals( 'I am a meta value', $term->protected_method );
		$this->assertEquals( 'I am a meta value', $term_string );

		$this->assertEquals( 'I am a meta value', $user->protected_method() );
		$this->assertEquals( 'I am a meta value', $user->protected_method );
		$this->assertEquals( 'I am a meta value', $user_string );

		$this->assertEquals( 'I am a meta value', $comment->protected_method() );
		$this->assertEquals( 'I am a meta value', $comment->protected_method );
		$this->assertEquals( 'I am a meta value', $comment_string );
	}

	/**
	 * Tests when you try to directly access a custom field value that is also the name of an
	 * existing public method on the object that has at least one required parameter.
	 *
	 * @expectedException \ArgumentCountError
	 */
	function testPostMetaDirectAccessMethodWithRequiredParametersConflict() {
		$post_id = $this->factory->post->create();

		update_post_meta( $post_id, 'public_method_with_args', 'I am a meta value' );

		$post        = new MetaPost( $post_id );
		$post_string = Timber::compile_string( '{{ post.public_method_with_args }}', [ 'post' => $post ] );

		$this->assertEquals( 'I am a meta value', $post_string );
	}

	/**
	 * Tests when you try to directly access a custom field value that is also the name of an
	 * existing method on the object that has at least one required parameter.
	 *
	 * @expectedException \ArgumentCountError
	 */
	function testTermMetaDirectAccessMethodWithRequiredParametersConflict() {
		$term_id = $this->factory->term->create();

		update_term_meta( $term_id, 'public_method_with_args', 'I am a meta value' );

		$term        = new MetaTerm( $term_id );
		$term_string = Timber::compile_string( '{{ term.public_method_with_args }}', [ 'term' => $term ] );

		$this->assertEquals( 'I am a meta value', $term_string );
	}

	/**
	 * Tests when you try to directly access a custom field value that is also the name of an
	 * existing method on the object that has at least one required parameter.
	 *
	 * @expectedException \ArgumentCountError
	 */
	function testUserMetaDirectAccessMethodWithRequiredParametersConflict() {
		$user_id = $this->factory->user->create();

		update_user_meta( $user_id, 'public_method_with_args', 'I am a meta value' );

		$user        = new MetaUser( $user_id );
		$user_string = Timber::compile_string( '{{ user.public_method_with_args }}', [ 'user' => $user ] );

		$this->assertEquals( 'I am a meta value', $user_string );
	}

	/**
	 * Tests when you try to directly access a custom field value that is also the name of an
	 * existing method on the object that has at least one required parameter.
	 *
	 * @expectedException \ArgumentCountError
	 */
	function testCommentMetaDirectAccessMethodWithRequiredParametersConflict() {
		$comment_id = $this->factory->comment->create();

		update_comment_meta( $comment_id, 'public_method_with_args', 'I am a meta value' );

		$comment        = new MetaComment( $comment_id );
		$comment_string = Timber::compile_string( '{{ comment.public_method_with_args }}', [ 'comment' => $comment ] );

		$this->assertEquals( 'I am a meta value', $comment_string );
	}

	/**
	 * Tests when you try to directly access a custom field value that is also the name of an
	 * existing public property on the object.
	 *
	 * The value of the property should take precedence over the value of the custom field.
	 */
	function testMetaDirectAccessPublicPropertyConflict() {
		$post_id    = $this->factory->post->create();
		$term_id    = $this->factory->term->create();
		$user_id    = $this->factory->user->create();
		$comment_id = $this->factory->comment->create();

		update_post_meta( $post_id, 'public_property', 'I am a meta value' );
		update_term_meta( $term_id, 'public_property', 'I am a meta value' );
		update_user_meta( $user_id, 'public_property', 'I am a meta value' );
		update_comment_meta( $comment_id, 'public_property', 'I am a meta value' );

		$post    = new MetaPost( $post_id );
		$term    = new MetaTerm( $term_id );
		$user    = new MetaUser( $user_id );
		$comment = new MetaComment( $user_id );

		$post_string    = Timber::compile_string(
			'{{ post.public_property }}', [ 'post' => $post ]
		);
		$term_string    = Timber::compile_string(
			'{{ term.public_property }}', [ 'term' => $term ]
		);
		$user_string    = Timber::compile_string(
			'{{ user.public_property }}', [ 'user' => $user ]
		);
		$comment_string = Timber::compile_string(
			'{{ comment.public_property }}', [ 'comment' => $comment ]
		);

		$this->assertEquals( 'I am a public property', $post_string );
		$this->assertEquals( 'I am a public property', $post->public_property );

		$this->assertEquals( 'I am a public property', $term_string );
		$this->assertEquals( 'I am a public property', $term->public_property );

		$this->assertEquals( 'I am a public property', $user_string );
		$this->assertEquals( 'I am a public property', $user->public_property );

		$this->assertEquals( 'I am a public property', $comment_string );
		$this->assertEquals( 'I am a public property', $comment->public_property );
	}

	/**
	 * Tests when you try to directly access a custom field value that is also the name of an
	 * existing inaccessible property on the object.
	 *
	 * The value of the custom field should take precedence over the value of the property.
	 */
	function testMetaDirectAccessInaccessiblePropertyConflict() {
		$post_id    = $this->factory->post->create();
		$term_id    = $this->factory->term->create();
		$user_id    = $this->factory->user->create();
		$comment_id = $this->factory->comment->create();

		update_post_meta( $post_id, 'protected_property', 'I am a meta value' );
		update_term_meta( $term_id, 'protected_property', 'I am a meta value' );
		update_user_meta( $user_id, 'protected_property', 'I am a meta value' );
		update_comment_meta( $comment_id, 'protected_property', 'I am a meta value' );

		$post    = new MetaPost( $post_id );
		$term    = new MetaTerm( $term_id );
		$user    = new MetaUser( $user_id );
		$comment = new MetaComment( $comment_id );

		$post_string    = Timber::compile_string(
			'{{ post.protected_property }}', [ 'post' => $post ]
		);
		$term_string    = Timber::compile_string(
			'{{ term.protected_property }}', [ 'term' => $term ]
		);
		$user_string    = Timber::compile_string(
			'{{ user.protected_property }}', [ 'user' => $user ]
		);
		$comment_string = Timber::compile_string(
			'{{ comment.protected_property }}', [ 'comment' => $comment ]
		);

		$this->assertEquals( 'I am a meta value', $post_string );
		$this->assertEquals( 'I am a meta value', $post->protected_property );

		$this->assertEquals( 'I am a meta value', $term_string );
		$this->assertEquals( 'I am a meta value', $term->protected_property );

		$this->assertEquals( 'I am a meta value', $user_string );
		$this->assertEquals( 'I am a meta value', $user->protected_property );

		$this->assertEquals( 'I am a meta value', $comment_string );
		$this->assertEquals( 'I am a meta value', $comment->protected_property );
	}

	/**
	 * Tests when you try to directly access a custom field value through the custom property.
	 *
	 * @expectedDeprecated Accessing a meta value through {{ post.custom }}
	 */
	function testPostMetaDirectAccessInaccessibleCustomProperty() {
		$post_id = $this->factory->post->create();

		update_post_meta( $post_id, 'inaccessible', 'Boo!' );

		$post   = new Post( $post_id );
		$string = Timber::compile_string( '{{ post.custom.inaccessible }}', array( 'post' => $post ) );

		$this->assertEquals( '', $string );
		$this->assertEquals( false, $post->custom );
	}

	/**
	 * Tests when you try to directly access a custom field value through the custom property.
	 *
	 * @expectedDeprecated Accessing a meta value through {{ term.custom }}
	 */
	function testTermMetaDirectAccessInaccessibleCustomProperty() {
		$term_id = $this->factory->term->create();

		update_term_meta( $term_id, 'inaccessible', 'Boo!' );

		$term   = new Term( $term_id );
		$string = Timber::compile_string( '{{ term.custom.inaccessible }}', array( 'term' => $term ) );

		$this->assertEquals( '', $string );
		$this->assertEquals( false, $term->custom );
	}

	/**
	 * Tests when you try to directly access a custom field value through the custom property.
	 *
	 * @expectedDeprecated Accessing a meta value through {{ user.custom }}
	 */
	function testUserMetaDirectAccessInaccessibleCustomProperty() {
		$user_id = $this->factory->user->create();

		update_user_meta( $user_id, 'inaccessible', 'Boo!' );

		$user   = new User( $user_id );
		$string = Timber::compile_string( '{{ user.custom.inaccessible }}', array( 'user' => $user ) );

		$this->assertEquals( '', $string );
		$this->assertEquals( false, $user->custom );
	}

	/**
	 * Tests when you try to directly access a custom field value through the custom property.
	 *
	 * @expectedDeprecated Accessing a meta value through {{ comment.custom }}
	 */
	function testCommentMetaDirectAccessInaccessibleCustomProperty() {
		$comment_id = $this->factory->comment->create();

		update_comment_meta( $comment_id, 'inaccessible', 'Boo!' );

		$comment = new Comment( $comment_id );
		$string  = Timber::compile_string( '{{ comment.custom.inaccessible }}', array( 'comment' => $comment ) );

		$this->assertEquals( '', $string );
		$this->assertEquals( false, $comment->custom );
	}

	/**
	 * Tests when you try to directly access a custom field value that doesn’t exist on the
	 * object.
	 */
	function testPostMetaDirectAccessInexistent() {
		$post_id    = $this->factory->post->create();
		$term_id    = $this->factory->term->create();
		$user_id    = $this->factory->user->create();
		$comment_id = $this->factory->comment->create();

		update_post_meta( $post_id, 'protected_property', 'I am a meta value' );
		update_term_meta( $term_id, 'protected_property', 'I am a meta value' );
		update_user_meta( $user_id, 'protected_property', 'I am a meta value' );
		update_comment_meta( $comment_id, 'protected_property', 'I am a meta value' );

		$post    = new MetaPost( $post_id );
		$term    = new MetaTerm( $term_id );
		$user    = new MetaUser( $user_id );
		$comment = new MetaComment( $user_id );

		$post_string    = Timber::compile_string(
			'{{ post.inexistent }}', [ 'post' => $post ]
		);
		$term_string    = Timber::compile_string(
			'{{ term.inexistent }}', [ 'term' => $term ]
		);
		$user_string    = Timber::compile_string(
			'{{ user.inexistent }}', [ 'user' => $user ]
		);
		$comment_string = Timber::compile_string(
			'{{ comment.inexistent }}', [ 'comment' => $comment ]
		);

		$this->assertEquals( '', $post_string );
		$this->assertEquals( false, $post->inexistent );

		$this->assertEquals( '', $term_string );
		$this->assertEquals( false, $term->inexistent );

		$this->assertEquals( '', $user_string );
		$this->assertEquals( false, $user->inexistent );

		$this->assertEquals( '', $comment_string );
		$this->assertEquals( false, $comment->inexistent );
	}
}
