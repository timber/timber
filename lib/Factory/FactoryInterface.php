<?php

namespace Timber\Factory;

/**
 * Interface FactoryInterface
 * @package Timber\Factory
 */
interface FactoryInterface {

	/**
	 * @param $object_class string The class to use to instantiate the retrieved object
	 *
	 * @return object
	 */
	public static function get( $object_class = '' );

	/**
	 * @return object
	 */
	public function get_object();

}