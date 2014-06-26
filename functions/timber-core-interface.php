<?php

interface TimberCoreInterface {

	public function __call( $field, $args );

	public function __get( $field );

	public function __isset( $field );

	public function id();

	public function meta( $key );

	public function slug();

	public function title();

	public function name();

}
