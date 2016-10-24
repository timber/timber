<?php

namespace Timber\Factory;

/**
 * Class Factory
 * @package Timber\Factory
 */
abstract class Factory implements FactoryInterface {

	protected $object_class = null;

	/**
	 * Factory constructor.
	 *
	 * @param string $object_class
	 */
	function __construct( $object_class = '' ) {
		if ( $object_class && class_exists( $object_class ) ) {
			$this->object_class = $object_class;
		}
	}

}