<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

class TimberPostsIterator extends ArrayIterator
{

    public function __construct( $array = array(), $post_class = 'TimberPost' ) {
        $posts = array();

        foreach ( $array as $rid ) {
            $post_class_use = $post_class;

            if ( is_array( $post_class ) ) {
                $post_type      = get_post_type( $rid );
                $post_class_use = 'TimberPost';

                if ( isset( $post_class[$post_type] ) ) {
                    $post_class_use = $post_class[$post_type];

                } else {
                    if ( is_array( $post_class ) ) {
                        TimberHelper::error_log( $post_type . ' of ' . $rid . ' not found in ' . print_r( $post_class, true ) );

                    } else {
                        TimberHelper::error_log( $post_type . ' not found in ' . $post_class );

                    }
                }
            }

            // Don't create yet another object if $rid is already of the right type
            if ( is_a( $rid, $post_class_use ) ) {
                $post = $rid;
            } else {
                $post = new $post_class_use( $rid );
            }

            if ( isset( $post->ID ) ) {
                $posts[] = $post;
            }
        }

        parent::__construct( $posts );
    }

    public function current() {
        global $post;
        $post = parent::current();

        return $post;
    }

}
