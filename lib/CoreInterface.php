<?php

namespace Timber;

/**
 * Interface CoreInterface
 */
interface CoreInterface {

	public function __call( string $field, array $args );

	public function __get( string $field );

	/**
	 * @return boolean
	 */
	public function __isset( $field );
}
