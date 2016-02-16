<?php

/**
 * TimberRequest exposes $_GET and $_POST to the context
 */
class TimberRequest extends TimberCore implements TimberCoreInterface {

	$post = array();
	$get = array();
	
	/**
	 * Constructs a TimberRequest object
	 * @example
	 */
	function __construct() {
		$this->init();
	}

	/**
	 * @internal
	 */
	protected function init() {
		$this->post = $_POST;
		$this->get = $_GET;
	}
}
