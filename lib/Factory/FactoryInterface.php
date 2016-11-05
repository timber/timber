<?php

namespace Timber\Factory;

/**
 * Interface FactoryInterface
 * @package Timber\Factory
 */
interface FactoryInterface {

	/**
	 * @return object
	 */
	public static function get();

	/**
	 * @return object
	 */
	public function get_object();

}