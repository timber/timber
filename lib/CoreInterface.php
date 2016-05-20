<?php

namespace Timber;

interface CoreInterface {

	public function __call( $field, $args );

	public function __get( $field );

	/**
	 * @return boolean
	 */
	public function __isset( $field );

	public function meta( $key );

}