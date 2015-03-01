<?php

class ACFTimber {

    function __construct() {
        add_filter( 'timber_post_get_meta', array( $this, 'post_get_meta' ), 10, 2 );
        add_filter( 'timber_post_get_meta_field', array( $this, 'post_get_meta_field' ), 10, 3 );
        add_filter( 'timber_term_get_meta', array( $this, 'term_get_meta' ), 10, 3 );
        add_filter( 'timber_term_get_meta_field', array( $this, 'term_get_meta_field' ), 10, 4 );
        add_filter( 'timber_user_get_meta_field_pre', array( $this, 'user_get_meta_field' ), 10, 3 );
        add_filter( 'timber_term_set_meta', array( $this, 'term_set_meta'), 10, 4 );
    }

    function post_get_meta( $customs, $post_id ) {
        return $customs;
    }

    function post_get_meta_field( $value, $post_id, $field_name ) {
        return get_field( $field_name, $post_id );
    }

    function term_get_meta_field( $value, $term_id, $field_name, $term ) {
        $searcher = $term->taxonomy . "_" . $term->ID;
        return get_field( $field_name, $searcher );
    }

    function term_set_meta( $value, $field, $term_id, $term ) {
        $searcher = $term->taxonomy . "_" . $term->ID;
        update_field( $field, $value, $searcher );
        return $value;
    }

    function term_get_meta( $fields, $term_id, $term ) {
        $searcher = $term->taxonomy . "_" . $term->ID; // save to a specific category
        $fds = get_fields( $searcher );
        if ( is_array( $fds ) ) {
            foreach ( $fds as $key => $value ) {
                $key = preg_replace( '/_/', '', $key, 1 );
                $key = str_replace( $searcher, '', $key );
                $key = preg_replace( '/_/', '', $key, 1 );
                $field = get_field( $key, $searcher );
                $fields[$key] = $field;
            }
            $fields = array_merge( $fields, $fds );
        }
        return $fields;
    }

    function user_get_meta( $fields, $user_id ) {
        return $fields;
    }

    function user_get_meta_field( $value, $uid, $field ) {
        return get_field( $field, 'user_' . $uid );
    }
}


