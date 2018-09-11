<?php

namespace Timber;


interface Setupable
{
	/**
	 * Sets up an object
	 *
	 * @api
	 * @since 2.0.0
	 *
	 * @return \Timber\Core The affected object.
	 */
	public function setup();

	/**
	 * Resets variables after the loop
	 *
	 * @api
	 * @since 2.0.0
	 *
	 * @return \Timber\Core The affected object.
	 */
	public function teardown();
}
