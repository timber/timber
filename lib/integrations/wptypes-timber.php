<?php

namespace Timber\Integrations;

class WPTypes {

	function __construct() {
		add_filter( 'timber_post_get_meta', array( $this, 'post_get_meta' ), 10, 2 );
		add_filter( 'timber_post_get_meta_field', array( $this, 'post_get_meta_field' ), 10, 3 );
	}
	
	function post_get_meta( $customs ) {
		foreach($customs as $key=>$value){
			$no_wpcf_key = str_replace('wpcf-', '', $key);
			$customs[$no_wpcf_key] = $value;
		}
		return $customs;
	}
	
	function post_get_meta_field( $value, $post_id, $field_name ) {
		if( ! empty($value) ) {
			return $value;
		}
		$children = types_child_posts( $field_name, $post_id );
		if ( is_array( $children )) {
			foreach ( $children as &$child ) {
				$child = new TimberPost( $child->ID );
			}
			$children = array_values($children);
			return $children;
		}
		return false;
	}
}


