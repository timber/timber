<?php

namespace Timber;

class PostType {

	function __construct( $post_type ) {
		$this->name = $post_type;
		$this->init( $post_type );
	}

	public function __toString() {
		return $this->name;
	}

	protected function init( $post_type ) {
		$obj = get_post_type_object($post_type);
		print_r($obj);
		foreach (get_object_vars($obj) as $key => $value) {
			$this->$key = $value;
		}
	}

}