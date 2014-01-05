<?php

class TimberPostGetter 
{

    /**
     * @param mixed $query
     * @param string $PostClass
     * @return array|bool|null
     */
    public static function get_post($query = false, $PostClass = 'TimberPost') {
        $posts = self::get_posts($query, $PostClass);
        if ( $post = $posts->current() ) {
            return $post;
        }
        return false;
    }

	/**
     * @param mixed $query
     * @param string $PostClass
     * @return array|bool|null
     */
    public static function get_posts($query = false, $PostClass = 'TimberPost'){
        
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
            return new TimberQueryIterator( $query, $PostClass );
        }

        /*
        if (TimberHelper::is_array_assoc($query) || (is_string($query) && strstr($query, '='))) {
            // we have a regularly formed WP query string or array to use
            $posts = self::get_posts_from_wp_query($query, $PostClass);
        } else if (is_string($query) && !is_integer($query)) {
            // we have what could be a post name to pull out
            $posts = self::get_posts_from_slug($query, $PostClass);
        } else if (is_array($query) && count($query) && (is_integer($query[0]) || is_string($query[0]))) {
            // we have a list of pids (post IDs) to extract from
            $posts = self::get_posts_from_array_of_ids($query, $PostClass);
        } else if (is_array($query) && count($query) && isset($query[0]) && is_object($query[0])) {
            // maybe its an array of post objects that already have data
            $posts = self::handle_post_results($query, $PostClass);
        } else if (self::wp_query_has_posts()) {
            //lets just use the default WordPress current query
            $posts = self::get_posts_from_loop($PostClass);
        } else if (!$query) {
            //okay, everything failed lets just return some posts so that the user has something to work with
            //this turns out to cause all kinds of awful behavior
            //return self::get_posts_from_wp_query(array(), $PostClass);
            return null;
        } else {
            TimberHelper::error_log('I have failed you! in timber.php::94');
            TimberHelper::error_log($query);
            return $query;
        }*/

        return self::maybe_set_preview( $posts );
    }

    /**
     * @param string $slug
     * @param string $PostClass
     * @return array
     */
    static function get_posts_from_slug($slug, $PostClass) {
        global $wpdb;
        $query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s", $slug);
        if (strstr($slug, '#')) {
            //we have a post_type directive here
            $q = explode('#', $slug);
            $q = array_filter($q);
            $q = array_values($q);
            if (count($q) == 1){
                $query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s", $q[0]);
            } else if (count($q) == 2){
                $query = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = %s LIMIT 1", $q[1], $q[0]);
            } else {
                TimberHelper::error_log('something we dont understand about '.$slug);
            }
        }
        $results = $wpdb->get_col($query);
        return self::handle_post_results($results, $PostClass);
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
        return new TimberPostsCollection( $posts );
    }

    /**
     * @return bool
     */
    static function wp_query_has_posts() {
        global $wp_query;
        return ($wp_query && property_exists($wp_query, 'posts') && $wp_query->posts);
    }

     /**
     * @param array $posts
     * @return array
     */
    static function maybe_set_preview( $posts ) {
        if ( is_array( $posts ) && isset( $_GET['preview'] ) && $_GET['preview']
               && isset( $_GET['preview_id'] ) && $_GET['preview_id']
               && current_user_can( 'edit_post', $_GET['preview_id'] ) ) {

            // No need to check the nonce, that already happened in _show_post_preview on init

            $preview_id = $_GET['preview_id'];
            foreach( $posts as &$post ) {
                if ( is_object( $post ) && $post->ID == $preview_id ) {
                    // Based on _set_preview( $post ), but adds import_custom
                    $preview = wp_get_post_autosave( $preview_id );

                    if ( is_object($preview) ) {

                        $preview = sanitize_post($preview);

                        $post->post_content = $preview->post_content;
                        $post->post_title = $preview->post_title;
                        $post->post_excerpt = $preview->post_excerpt;
                        $post->import_custom( $preview_id );

                        add_filter( 'get_the_terms', '_wp_preview_terms_filter', 10, 3 );
                    }
                }
            }

        }

        return $posts;
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