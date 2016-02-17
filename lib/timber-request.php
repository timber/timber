<?php
/**
 * TimberRequest exposes $_GET and $_POST to the context
 */
class TimberRequest extends TimberCore implements TimberCoreInterface {
	public $post = array();
	public $get = array();
	
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

	public function __call( $field, $args ) {}

	public function __get( $field ) {}

	/**
	 * @return boolean
	 */
	public function __isset( $field ) {}

	public function meta( $key ) {}
}
