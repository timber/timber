<?php

use Timber\Comment;
use Timber\Post;
use Timber\Term;
use Timber\Timber;
use Timber\User;

/**
 * Class TestTimberMeta
 */
class TestTimberMetaDeprecated extends Timber_UnitTestCase {
	/**
	 * @expectedDeprecated timber_post_get_meta_field_pre
	 */
	function testDeprecatedTimberPostGetMetaFieldPreFilter() {
		$filter = function ( $meta, $object_id, $field_name, $object ) {
			$this->assertEquals( 'name', $field_name );
			$this->assertEquals( 'A girl has no name.', $meta );
			$this->assertSame( $object->ID, $object_id );

			return $meta;
		};

		add_filter( 'timber_post_get_meta_field_pre', $filter, 10, 4 );

		$post_id = $this->factory->post->create();
		$post    = new Post( $post_id );

		update_post_meta( $post_id, 'name', 'A girl has no name.' );

		$this->assertEquals( 'A girl has no name.', $post->meta( 'name' ) );

		remove_filter( 'timber_post_get_meta_field_pre', $filter );
	}

	/**
	 * @expectedDeprecated timber_post_get_meta_pre
	 */
	function testDeprecatedTimberPostGetMetaPreAction() {
		$action = function ( $meta, $object_id, $object ) {
			$this->assertEquals( 'A girl has no name.', $meta );
			$this->assertSame( $object->ID, $object_id );
		};

		add_action( 'timber_post_get_meta_pre', $action, 10, 3 );

		$post_id = $this->factory->post->create();
		$post    = new Post( $post_id );

		update_post_meta( $post_id, 'name', 'A girl has no name.' );

		$this->assertEquals( 'A girl has no name.', $post->meta( 'name' ) );

		remove_action( 'timber_post_get_meta_field_pre', $action );
	}

	/**
	 * @expectedDeprecated timber_post_get_meta_field
	 */
	function testDeprecatedTimberPostGetMetaFieldFilter() {
		$filter = function ( $meta, $object_id, $field_name, $object ) {
			$this->assertEquals( 'name', $field_name );
			$this->assertEquals( 'A girl has no name.', $meta );
			$this->assertSame( $object->ID, $object_id );

			return $meta;
		};

		add_filter( 'timber_post_get_meta_field', $filter, 10, 4 );

		$post_id = $this->factory->post->create();
		$post    = new Post( $post_id );

		update_post_meta( $post_id, 'name', 'A girl has no name.' );

		$this->assertEquals( 'A girl has no name.', $post->meta( 'name' ) );

		remove_filter( 'timber_post_get_meta_field', $filter );
	}

	/**
	 * @expectedDeprecated timber_post_get_meta
	 */
	function testDeprecatedTimberPostGetMetaFilter() {
		$filter = function ( $meta, $object_id, $object ) {
			$this->assertEquals( 'A girl has no name.', $meta );
			$this->assertSame( $object->ID, $object_id );

			return $meta;
		};

		add_filter( 'timber_post_get_meta', $filter, 10, 3 );

		$post_id = $this->factory->post->create();
		$post    = new Post( $post_id );

		update_post_meta( $post_id, 'name', 'A girl has no name.' );

		$this->assertEquals( 'A girl has no name.', $post->meta( 'name' ) );

		remove_filter( 'timber_post_get_meta', $filter );
	}
}
