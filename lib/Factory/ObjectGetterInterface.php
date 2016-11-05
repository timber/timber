<?php

namespace Timber\Factory;

/**
 * Interface ObjectGetterInterface
 * @package Timber\Factory
 */
interface ObjectGetterInterface {

	/**
	 * @param null $identifier
	 *
	 * @return mixed
	 */
	static function get_object( $identifier );
}