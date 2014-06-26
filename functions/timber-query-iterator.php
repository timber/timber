<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

class TimberQueryIterator implements Iterator {

    /**
     *
     *
     * @var WP_Query
     */
    private $_query = null;
    private $_posts_class = 'TimberPost';

    public function __construct( $query = false, $posts_class = 'TimberPost' ) {

        if ( $posts_class )
            $this->_posts_class = $posts_class;

        if ( is_a( $query, 'WP_Query' ) ) {
            // We got a full-fledged WP Query, look no further!
            $the_query = $query;

        } elseif ( false === $query ) {
            // If query is explicitly set to false, use the main loop
            global $wp_query;
            $the_query =& $wp_query;

        } elseif ( TimberHelper::is_array_assoc( $query ) || ( is_string( $query ) && strstr( $query, '=' ) ) ) {
            // We have a regularly formed WP query string or array to use
            $the_query = new WP_Query( $query );

        } elseif ( is_numeric( $query ) || is_string( $query ) ) {
            // We have what could be a post name or post ID to pull out
            $the_query = self::get_query_from_string( $query );

        } elseif ( is_array( $query ) && count( $query ) && ( is_integer( $query[0] ) || is_string( $query[0] ) ) ) {
            // We have a list of pids (post IDs) to extract from
            $the_query = self::get_query_from_array_of_ids( $query );

        } else {
            TimberHelper::error_log( 'I have failed you! in ' . basename( __FILE__ ) . '::' . __LINE__ );
            TimberHelper::error_log( $query );

            // We have failed hard, at least let get something.
            $the_query = new WP_Query();
        }

        $this->_query = $the_query;

    }

    public function get_posts( $return_collection = false ) {
        $posts = new TimberPostsCollection( $this->_query->posts, $this->_posts_class );
        return ( $return_collection ) ? $posts : $posts->get_posts();
    }

    //
    // GET POSTS
    //
    public static function get_query_from_array_of_ids( $query = array() ) {
        if ( !is_array( $query ) || !count( $query ) )
            return null;

        return new WP_Query( array(
                'post_type'=> 'any',
                'post__in' => $query,
                'orderby'  => 'post__in',
                'numberposts' => -1
            ) );
    }

    public static function get_query_from_string( $string = '' ) {
        $post_type = false;

        if ( is_string( $string ) && strstr( $string, '#' ) ) {
            //we have a post_type directive here
            list( $post_type, $string ) = explode( '#', $string );
        }

        $query = array(
            'post_type' => ( $post_type ) ? $post_type : 'any'
        );

        if ( is_numeric( $string ) ) {
            $query['p'] = $string;

        } else {
            $query['name'] = $string;
        }

        return new WP_Query( $query );
    }

    //
    // Iterator Interface
    //

    public function valid() {
        return $this->_query->have_posts();
    }

    public function current() {
        global $post;

        $this->_query->the_post();

        // Sets up the global post, but also return the post, for use in Twig template
        $posts_class = $this->_posts_class;
        return new $posts_class( $post );
    }

    /**
     * Don't implement next, because current already advances the loop
     */
    final public function next() {}

    public function rewind() {
        $this->_query->rewind_posts();
    }

    public function key() {
        $this->_query->current_post;
    }

}
