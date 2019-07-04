<?php

use Timber\User;

/**
 * Class TestTimberUserMeta
 */
class TestTimberUserMeta extends Timber_UnitTestCase {
	/**
	 * Function hit helper.
	 *
	 * @var bool
	 */
	protected $is_get_user_meta_hit;

	function testPreGetMetaValuesDisableFetch() {
		$this->is_get_user_meta_hit = false;

		add_filter( 'timber/user/pre_get_meta_values', '__return_false' );
		add_filter( 'get_user_metadata', function( $value, $object_id, $meta_key ) {
			if ( empty( $meta_key ) ) {
				$this->is_get_user_meta_hit = true;
			}

			return $value;
		}, 10, 3 );

		$user_id = $this->factory->user->create();
		$user    = new User( $user_id );

		$this->assertEquals( false, $this->is_get_user_meta_hit );

		remove_filter( 'timber/user/pre_get_meta_values', '__return_false' );
	}

	function testPreGetMetaValuesCustomFetch() {
		$callable = function( $user_meta, $pid, $post ) {
			$key = 'critical_value';

			return [
				$key => get_user_meta( $pid, $key ),
			];
		};

		add_filter( 'timber/user/pre_get_meta_values', $callable, 10, 3 );

		$user_id = $this->factory->user->create();

		update_user_meta( $user_id, 'hidden_value', 'super-big-secret' );
		update_user_meta( $user_id, 'critical_value', 'I am needed, all the time' );

		$user = new User( $user_id );

		$this->assertEquals( null, $user->raw_meta( 'hidden_value' ) );
		$this->assertEquals(
			'I am needed, all the time',
			$user->raw_meta( 'critical_value' )
		);

		remove_filter( 'timber/user/pre_get_meta_values', $callable );
	}
}
