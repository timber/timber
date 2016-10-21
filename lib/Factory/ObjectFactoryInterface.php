<?php

namespace Timber\Factory;

/**
 * Interface ObjectFactoryInterface
 * @package Timber\Factory
 */
interface ObjectFactoryInterface {

	/**
	 * @param null $identifier
	 *
	 * @return mixed
	 */
	static function get_object( $identifier );
}