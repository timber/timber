<?php

class TestTimberPostMeta extends Timber_UnitTestCase {

	function testPostCustomField(){
		$post_id = $this->factory->post->create();
		update_post_meta($post_id, 'gameshow', 'numberwang');
		$post = new Timber\Post($post_id);
		$this->assertEquals('numberwang', $post->gameshow);
	}

	function testPostMetaDirectAccess() {
			$post_id = $this->factory->post->create();

			update_post_meta( $post_id, 'my_custom_property', 'Sweet Honey' );

			$post   = new Timber\Post( $post_id );
			$string = Timber::compile_string( 'My {{ post.my_custom_property }}', [ 'post' => $post ] );

			$this->assertEquals( 'My Sweet Honey', $string );
		}

	/**
	 * Tests when you try to directly access a custom field value that is also the name of an
	 * existing method on the object.
	 *
	 * The result of the method should take precedence over the value of the custom field.
	 */
	function testPostMetaDirectAccessMethodConflict() {
		$post_id = $this->factory->post->create( [
			'post_title' => 'Smelly Fish',
		] );

		update_post_meta( $post_id, 'title', 'Sweet Honey' );

		$post   = new Timber\Post( $post_id );
		$string = Timber::compile_string( 'My {{ post.title }}', [ 'post' => $post ] );

		$this->assertEquals( 'My Smelly Fish', $string );
	}

	/**
	 * Tests when you try to directly access a custom field value that is also the name of an
	 * existing method on the object that has at least one required parameter.
	 *
	 * @expectedException \ArgumentCountError
	 */
	function testPostMetaDirectAccessMethodWithRequiredParametersConflict() {
		$post_id = $this->factory->post->create();

		update_post_meta( $post_id, 'meta', 'Sweet Honey' );

		$post   = new Timber\Post( $post_id );
		$string = Timber::compile_string( 'My {{ post.meta }}', [ 'post' => $post ] );

		$this->assertEquals( 'My Smelly Fish', $string );
	}

	/**
	 * Tests when you try to directly access a custom field value that is also the name of an
	 * existing public property on the object.
	 *
	 * The value of the property should take precedence over the value of the custom field.
	 */
	function testPostMetaDirectAccessPublicPropertyConflict() {
		$post_id = $this->factory->post->create( [
			'post_title' => 'Smelly Fish',
		] );

		update_post_meta( $post_id, 'post_title', 'Sweet Honey' );

		$post   = new Timber\Post( $post_id );
		$string = Timber::compile_string( 'My {{ post.post_title }}', [ 'post' => $post ] );

		$this->assertEquals( 'My Smelly Fish', $string );
	}

	/**
	 * Tests when you try to directly access a custom field value that is also the name of an
	 * existing inaccessible property on the object.
	 *
	 * The value of the custom field should take precedence over the value of the property.
	 */
	function testPostMetaDirectAccessInaccessiblePropertyConflict() {
		$post_id = $this->factory->post->create( [
			'post_title' => 'Smelly Fish',
		] );

		update_post_meta( $post_id, '_content', 'Sweet Honey' );

		$post   = new Timber\Post( $post_id );
		$string = Timber::compile_string( 'My {{ post._content }}', [ 'post' => $post ] );

		$this->assertEquals( 'My Smelly Fish', $string );
	}

	/**
	 * Tests when you try to directly access a custom field value through the custom property.
	 *
	 * @expectedDeprecated Accessing a meta value through {{ post.custom }}
	 */
	function testPostMetaDirectAccessInaccessibleCustomPropertyConflict() {
		$post_id = $this->factory->post->create();

		update_post_meta( $post_id, 'inaccessible', 'Boo!' );

		$post   = new Timber\Post( $post_id );
		$string = Timber::compile_string( 'My {{ post.custom.inaccessible }}', array( 'post' => $post ) );

		$this->assertEquals( 'My ', $string );
	}

	/**
	 * Tests when you try to directly access a custom field value that doesn’t exist on the
	 * object.
	 */
	function testPostMetaDirectAccessInexistent() {
		$post_id = $this->factory->post->create( [
			'post_title' => 'Smelly Fish',
		] );

		$post   = new Timber\Post( $post_id );
		$string = Timber::compile_string( 'My {{ post.inexistent }}', [ 'post' => $post ] );

		$this->assertEquals( 'My ', $string );
	}

	function testPreGetMetaValuesDisableFetch(){
		add_filter( 'timber/post/pre_get_meta_values', '__return_false' );

		$post_id = $this->factory->post->create();

		update_post_meta( $post_id, 'hidden_value', 'Super secret value' );

		$post = new Timber\Post($post_id);

		$this->assertEquals( 0, $post->raw_meta( 'hidden_value' ) );

		remove_filter( 'timber/post/pre_get_meta_values', '__return_false' );
	}

	function testPreGetMetaValuesCustomFetch(){
		$callable = function( $customs, $pid, $post ) {
			$key = 'critical_value';

			return [
				$key => get_post_meta( $pid, $key ),
			];
		};

		add_filter( 'timber/post/pre_get_meta_values', $callable , 10, 3 );

		$post_id = $this->factory->post->create();

		update_post_meta( $post_id, 'hidden_value', 'super-big-secret' );
		update_post_meta( $post_id, 'critical_value', 'I am needed, all the time' );

		$post = new Timber\Post( $post_id );
		$this->assertEquals( null, $post->raw_meta( 'hidden_value' ) );
		$this->assertEquals(
			'I am needed, all the time',
			$post->raw_meta( 'critical_value' )
		);

		remove_filter( 'timber/post/pre_get_meta_values', $callable );
	}

	/**
	 * This seems like an incredible edge case test from 1.x
	 * @ignore since 2.0
	 */
	/*
	function testMetaCustomArrayFilter(){
		add_filter('timber_post_get_meta', function($customs) {
			error_log('RUN FITER');
			print_r($customs);
			foreach( $customs as $key=>$value ){
				$flat_key = str_replace('-', '_', $key);
				$flat_key .= '_flat';
				$customs[$flat_key] = $value;
			}
			// print_r($customs);
			return $customs;
		});
		$post_id = $this->factory->post->create();
		update_post_meta($post_id, 'the-field-name', 'the-value');
		update_post_meta($post_id, 'with_underscores', 'the_value');
		$post = new Timber\Post($post_id);
		$this->assertEquals($post->with_underscores_flat, 'the_value');
		//$this->assertEquals($post->the_field_name_flat, 'the-value');
	}*/
}
