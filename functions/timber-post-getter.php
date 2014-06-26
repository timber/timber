<?php

class TimberPostGetter 
{

    /**
     * @param mixed $query
     * @param string $PostClass
     * @return array|bool|null
     */
    public static function get_post($query = false, $PostClass = 'TimberPost') {
        $posts = self::get_posts( $query, $PostClass );
        if ( $post = reset( $posts ) ) {
            return $post;
        }
        return false;
    }

    public static function get_posts( $query = false, $PostClass = 'TimberPost', $return_collection = false ) {
        $posts = self::query_posts( $query, $PostClass );
        return $posts->get_posts( $return_collection );
    }

	/**
     * @param mixed $query
     * @param string $PostClass
     * @return array|bool|null
     */
    public static function query_posts($query = false, $PostClass = 'TimberPost'){
        if (self::is_post_class_or_class_map($query)) {
            $PostClass = $query;
            $query = false;
        }

        if (is_object($query) && !is_a('WP_Query' ) ){
            // The only object other than a query is a type of post object
            $query = array( $query );
        }

        if ( is_array( $query ) && count( $query ) && isset( $query[0] ) && is_object( $query[0] ) ) {
            // We have an array of post objects that already have data
            return new TimberPostsCollection( $query, $PostClass );
        } else {
            // We have a query (of sorts) to work with
            $tqi = new TimberQueryIterator( $query, $PostClass );            
            return $tqi;
        }
        return $posts;
    }

    /**
     * @param $query
     * @return int
     */
    static function get_pid($query) {
        $post = self::get_posts($query);
        return $post->ID;
    }

    /**
     * @param array|string $query
     * @return array
     */
    static function get_pids($query = null) {
        $posts = self::get_posts($query);
        $pids = array();
        foreach ($posts as $post) {
            if ($post->ID) {
                $pids[] = $post->ID;
            }
        }
        return $pids;
    }

    /**
     * @return array
     */
    static function get_pids_from_loop() {
        if (!self::wp_query_has_posts()) { return array(); }

        global $wp_query;
        return array_filter(array_map(function($p) {
            return ($p && property_exists($p, 'ID')) ? $p->ID : null;
        }, $wp_query->posts));
    }

    /**
     * @param string $PostClass
     * @return array
     */
    static function get_posts_from_loop($PostClass) {
        $results = self::get_pids_from_loop();
        return self::handle_post_results($results, $PostClass);
    }

	/**
     * @param array $query
     * @param string $PostClass
     * @return array
     */
	static function get_posts_from_wp_query($query = array(), $PostClass = 'TimberPost') {
        $results = get_posts($query);
        return self::handle_post_results($results, $PostClass);
    }

    /**
     * @param array $query
     * @param string $PostClass
     * @return array|null
     */
    static function get_posts_from_array_of_ids($query = array(), $PostClass = 'TimberPost') {
        if (!is_array($query) || !count($query)) {
            return null;
        }
        $results = get_posts(array('post_type'=>'any', 'post__in' =>$query, 'orderby' => 'post__in', 'numberposts' => -1));
        return self::handle_post_results($results, $PostClass);
    }

    /**
     * @param array $results
     * @param string $PostClass
     * @return array
     */
    static function handle_post_results($results, $PostClass = 'TimberPost') {
        $posts = array();
        foreach ($results as $rid) {
            $PostClassUse = $PostClass;
            if (is_array($PostClass)) {
                $post_type = get_post_type($rid);
                $PostClassUse = 'TimberPost';
                if (isset($PostClass[$post_type])) {
                    $PostClassUse = $PostClass[$post_type];
                } else {
                    if (is_array($PostClass)) {
                        TimberHelper::error_log($post_type.' of '.$rid.' not found in ' . print_r($PostClass, true));
                    } else {
                        TimberHelper::error_log($post_type.' not found in '.$PostClass);
                    }
                }
            }
            $post = new $PostClassUse($rid);
            if (isset($post->ID)) {
                $posts[] = $post;
            }
        }
        return new TimberPostsCollection( $posts, $PostClass );
    }

    /**
     * @return bool
     */
    static function wp_query_has_posts() {
        global $wp_query;
        return ($wp_query && property_exists($wp_query, 'posts') && $wp_query->posts);
    }

    /*  Deprecated
    ================================ */

    /**
     * @return bool|int
     */
    static function loop_to_id() {
        if (!self::wp_query_has_posts()) { return false; }

        global $wp_query;
        $post_num = property_exists($wp_query, 'current_post')
                  ? $wp_query->current_post + 1
                  : 0
                  ;

        if (!isset($wp_query->posts[$post_num])) { return false; }

        return $wp_query->posts[$post_num]->ID;
    }

    /**
     * @param string|array $arg
     * @return bool
     */
    static function is_post_class_or_class_map($arg){
        if (is_string($arg) && class_exists($arg)) {
            return true;
        }
        if (is_array($arg)) {
            foreach ($arg as $item) {
                if (is_string($item) && class_exists($item)) {
                    return true;
                }
            }
        }
        return false;
    }
}