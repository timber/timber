<?php

use Timber\User;

/**
 * Class TestTimberUserMeta
 */
class TestTimberUserMeta extends Timber_UnitTestCase {
	function testPreGetMetaValuesDisableFetch() {
		add_filter( 'timber/user/pre_get_meta_values', '__return_false' );

		$user_id = $this->factory->user->create();

		update_user_meta( $user_id, 'hidden_value', 'Super secret value' );

		$user = new User( $user_id );

		$this->assertEquals( 0, $user->raw_meta( 'hidden_value' ) );

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
