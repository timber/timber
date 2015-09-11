<?php

class WpTypesTimber {

	function __construct() {
		add_filter( 'timber_post_get_meta', array( $this, 'post_get_meta' ), 10, 2 );
	}

	function post_get_meta( $customs ) {
		foreach($customs as $key=>$value){
			$no_wpcf_key = str_replace('wpcf-', '', $key);
			$customs[$no_wpcf_key] = $value;
		}

		return $customs;
	}
}


