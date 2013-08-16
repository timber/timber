<?php
/*
Plugin Name: Timber
Description: The WordPress Timber Library allows you to write themes using the power Twig templates
Author: Jared Novack + Upstatement
Version: 0.10.6
Author URI: http://timber.upstatement.com/
*/

global $wp_version;
global $timber;

require_once(__DIR__ . '/functions/functions-twig.php');
require_once(__DIR__ . '/functions/functions-post-master.php');
require_once(__DIR__ . '/functions/functions-php-helper.php');
require_once(__DIR__ . '/functions/functions-wp-helper.php');

require_once(__DIR__ . '/objects/timber-core.php');
require_once(__DIR__ . '/objects/timber-post.php');
require_once(__DIR__ . '/objects/timber-comment.php');
require_once(__DIR__ . '/objects/timber-user.php');
require_once(__DIR__ . '/objects/timber-term.php');
require_once(__DIR__ . '/objects/timber-term-getter.php');
require_once(__DIR__ . '/objects/timber-image.php');
require_once(__DIR__ . '/objects/timber-menu.php');

require_once(__DIR__ . '/objects/timber-loader.php');


/** Usage:
 *
 *  $posts = Timber::get_posts();
 *  $posts = Timber::get_posts('post_type = article')
 *  $posts = Timber::get_posts(array('post_type' => 'article', 'category_name' => 'sports')); // uses wp_query format
 *  $posts = Timber::get_posts(array(23,24,35,67), 'InkwellArticle');
 *
 *  $context = Timber::get_context(); // returns wp favorites!
 *
 *  Timber::render('index.twig', $context);
 */

class Timber {

    public static $locations;
    public static $dirname = 'views';
    public static $cache = false;

    protected $router;

    public function __construct(){
        $this->init_constants();
        add_action('init', array(&$this, 'init_routes'));
    }

    protected function init_constants() {
        $timber_loc = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', realpath(__DIR__));
        $plugin_url_path = str_replace($_SERVER['HTTP_HOST'], '', plugins_url());
        $plugin_url_path = str_replace('https://', '', $plugin_url_path);
        $plugin_url_path = str_replace('http://', '', $plugin_url_path);
        $timber_dirs = dirname(__FILE__);
        $timber_dirs = explode('/', $timber_dirs);
        $timber_dirname = array_pop($timber_dirs);
        define("TIMBER", $timber_loc);
        define("TIMBER_URL_PATH", trailingslashit($plugin_url_path) . trailingslashit($timber_dirname));
        define("TIMBER_URL", 'http://' . $_SERVER["HTTP_HOST"] . TIMBER);
        define("TIMBER_LOC", realpath(__DIR__));
    }

    /*  Post Retrieval
    ================================ */

    public static function get_post($query = false, $PostClass = 'TimberPost') {
        if (is_int($query)) {
            /* its a post id number */
            $query = array($query);
        }
        $posts = self::get_posts($query, $PostClass);
        if (count($posts) && is_array($posts)) {
            return $posts[0];
        }
        return $posts;
    }

    public static function get_posts($query = false, $PostClass = 'TimberPost'){
        if (self::is_post_class_or_class_map($query)) {
            $PostClass = $query;
            $query = false;
        }
        if (WPHelper::is_array_assoc($query) || (is_string($query) && strstr($query, '='))) {
        // we have a regularly formed WP query string or array to use
            return self::get_posts_from_wp_query($query, $PostClass);
        } else if (is_string($query) && !is_integer($query)) {
            // we have what could be a post name to pull out
            return self::get_posts_from_slug($query, $PostClass);
        } else if (is_array($query) && count($query) && (is_integer($query[0]) || is_string($query[0]))) {
            // we have a list of pids (post IDs) to extract from
            return self::get_posts_from_array_of_ids($query, $PostClass);
        } else if (is_array($query) && count($query) && isset($query[0]) && is_object($query[0])) {
            // maybe its an array of post objects that already have data
            return self::handle_post_results($query, $PostClass);
        } else if (have_posts()) {
            //lets just use the default WordPress current query
            return self::get_posts_from_loop($PostClass);
        } else if (!$query) {
            //okay, everything failed lets just return some posts so that the user has something to work with
            //this turns out to cause all kinds of awful behavior
            //return self::get_posts_from_wp_query(array(), $PostClass);
            return null;
        } else {
            error_log('I have failed you! in timber.php::94');
            WPHelper::error_log($query);
        }
        return $query;
    }

    public function get_pids($query = null) {
        $posts = get_posts($query);
        $pids = array();
        foreach ($posts as $post) {
            if ($post->ID) {
                $pids[] = $post->ID;
            }
        }
        return $pids;
    }

    public static function get_posts_from_loop($PostClass) {
        $results = self::get_pids_from_loop();
        return self::handle_post_results($results, $PostClass);
    }

    public static function get_pids_from_loop() {
        $posts = array();
        $i = 0;
        ob_start();
        while (have_posts() && $i < 99999) {
            the_post();
            $posts[] = get_the_ID();
            $i++;
        }
        //why is this here? seems to only cause pain.
        //wp_reset_query();
        ob_end_clean();
        return $posts;
    }

    public static function get_posts_from_slug($slug, $PostClass) {
        global $wpdb;
        $query = "SELECT ID FROM $wpdb->posts WHERE post_name = '$slug'";
        if (strstr($slug, '#')) {
            //we have a post_type directive here
            $q = explode('#', $slug);
            $q = array_filter($q);
            $q = array_values($q);
            if (count($q) == 1){
                $query = "SELECT ID FROM $wpdb->posts WHERE post_name = '$q[0]'";
            } else if (count($q) == 2){
                $query = "SELECT ID FROM $wpdb->posts WHERE post_name = '$q[1]' AND post_type = '$q[0]'";
            } else {
                error_log('something we dont understand about '.$slug);
            }
        }
        $results = $wpdb->get_col($query);
        return self::handle_post_results($results, $PostClass);
    }

    public static function get_posts_from_wp_query($query = array(), $PostClass = 'TimberPost') {
        $results = get_posts($query);
        return self::handle_post_results($results, $PostClass);
    }

    public static function get_posts_from_array_of_ids($query = array(), $PostClass = 'TimberPost') {
        if (!is_array($query) || !count($query)) {
            return null;
        }
        global $wpdb;
        $query_list = implode(', ', $query);
        $results = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE ID IN ($query_list)");
        $results = array_intersect($query, $results);
        return self::handle_post_results($results, $PostClass);
    }

    public static function handle_post_results($results, $PostClass = 'TimberPost') {
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
                        error_log($post_type . ' not found in ' . print_r($PostClass, true));
                    } else {
                        error_log($post_type . ' not found in ' . $PostClass);
                    }
                }
            }
            $post = new $PostClassUse($rid);
            if (isset($post->post_title)) {
                $posts[] = $post;
            }
        }
        return $posts;
    }

    public function get_pid($query) {
        $post = self::get_posts($query);
        return $post->ID;
    }


    /*  Deprecated
    ================================ */

    public function loop_to_posts($PostClass = 'TimberPost') {
        return self::get_posts(false, $PostClass);
    }

    public function loop_to_id() {
        if (have_posts()) {
            the_post();
            wp_reset_query();
            return get_the_ID();
        }
        return false;
    }


    /* Term Retrieval
    ================================ */

    public static function get_terms($args, $TermClass = 'TimberTerm'){
        if (is_string($args) && strstr($args, '=')){
            //a string and a query string!
            $parsed = TimberTermGetter::get_term_query_from_query_string($args);
            return self::handle_term_query($parsed->taxonomies, $parsed->args, $TermClass);
        } else if (is_string($args)){
            //its just a string with a single taxonomy
            $parsed = TimberTermGetter::get_term_query_from_string($args);
            return self::handle_term_query($parsed->taxonomies, $parsed->args, $TermClass);
        } else if (is_array($args) && WPHelper::is_array_assoc($args)){
            //its an associative array, like a good ole query
            $parsed = TimberTermGetter::get_term_query_from_assoc_array($args);
            return self::handle_term_query($parsed->taxonomies, $parsed->args, $TermClass);
        } else if (is_array($args)){
            //its just an array of strings or IDs (hopefully)
            $parsed = TimberTermGetter::get_term_query_from_array($args);
            return self::handle_term_query($parsed->taxonomies, $parsed->args, $TermClass);
        } else {
            //no clue, what you talkin' bout?
        }

    }

    public static function handle_term_query($taxonomies, $args, $TermClass){
        $terms = get_terms($taxonomies, $args);
        foreach($terms as &$term){
            $term = new TimberTerm($term->term_id);
        }
        return $terms;
    }


    /*  Template Setup and Display
    ================================ */

    public static function get_context() {
        $data = array();
        $data['http_host'] = 'http://' . $_SERVER['HTTP_HOST'];
        $data['wp_title'] = get_bloginfo('name');
        $data['wp_head'] = WPHelper::ob_function('wp_head');
        $data['wp_footer'] = WPHelper::ob_function('wp_footer');
        $data['body_class'] = implode(' ', get_body_class());
        if (function_exists('wp_nav_menu')) {
            $data['wp_nav_menu'] = wp_nav_menu(array('container_class' => 'menu-header', 'theme_location' => 'primary', 'echo' => false, 'menu_class' => 'nav-menu'));
        }
        $data['theme_dir'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', get_stylesheet_directory());
        $data['language_attributes'] = WPHelper::ob_function('language_attributes');
        $data['stylesheet_uri'] = get_stylesheet_uri();
        $data['template_uri'] = get_template_directory_uri();
        $data = apply_filters('timber_context', $data);
        return $data;
    }

    public static function render($filenames, $data = array(), $echo = true) {
        $caller = self::get_calling_script_dir();
        $loader = new TimberLoader($caller);
        $file = $loader->choose_template($filenames);
        $output = '';
        if (strlen($file)) {
            $output = $loader->render($file, $data);
        }
        if ($echo) {
            echo $output;
        }
        return $output;
    }


    /*  Sidebar
    ================================ */

    public static function get_sidebar($sidebar = '', $data = array()) {
        if ($sidebar == '') {
            $sidebar = 'sidebar.php';
        }
        if (strstr(strtolower($sidebar), '.php')) {
            return self::get_sidebar_from_php($sidebar, $data);
        }
        return self::render($sidebar, $data, false);
    }

    public static function get_sidebar_from_php($sidebar = '', $data) {
        $caller = self::get_calling_script_dir();
        $loader = new TimberLoader();
        $uris = $loader->get_locations($caller);
        ob_start();
        $found = false;
        foreach ($uris as $uri) {
            if (file_exists(trailingslashit($uri) . $sidebar)) {
                include(trailingslashit($uri) . $sidebar);
                $found = true;
                break;
            }
        }
        if (!$found) {
            error_log('error loading your sidebar, check to make sure the file exists');
        }
        $ret = ob_get_contents();
        error_log($ret);
        ob_end_clean();
        return $ret;
    }


    /*  Routes
    ================================ */

    public function init_routes() {
        global $timber;
        if (isset($timber->router)) {
            $route = $timber->router->matchCurrentRequest();
            if ($route) {
                $callback = $route->getTarget();
                $params = $route->getParameters();
                $callback($params);
            }
        }
    }

    public static function add_route($route, $callback) {
        global $timber;
        if (!isset($timber->router)) {
            require_once('router/Router.php');
            require_once('router/Route.php');
            $timber->router = new Router();
            $timber->router->setBasePath('/');
        }
        $timber->router->map($route, $callback);
    }

    public function load_template($template, $query = false) {
        if ($query) {
            global $wp_query;
            $wp_query = new WP_Query($query);
        }
        $template = locate_template($template);
        $GLOBALS['timber_template'] = $template;
        add_action('send_headers', function () {
            header('HTTP/1.1 200 OK');
        });
        add_action('wp_loaded', function ($template) {
            if (isset($GLOBALS['timber_template'])) {
                load_template($GLOBALS['timber_template']);
                die;
            }
        }, 10, 1);
    }

    /*  Pagination
    ================================ */

    public function get_pagination(){
        global $wp_query;
        global $paged;
        $data = array();
        $data['pages'] = ceil($wp_query->found_posts / $wp_query->query_vars['posts_per_page']);
        $paged = 1;
        if (isset($wp_query->query_vars['paged'])) {
            $paged = $wp_query->query_vars['paged'];
        }
        $data['base'] = get_pagenum_link(0);
        $data['paged'] = $paged;
        if ($paged < $data['pages']) {
            $data['next'] = array('link' => next_posts(0, false), 'path' => next_posts(0, false));
        }
        if ($paged > 1) {
            $data['prev'] = array('link' => previous_posts(false), 'path' => previous_posts(false));
        }
        return $data;
    }

    /*  Utility
    ================================ */

    public function get_calling_script_path($offset = 0) {
        $dir = self::get_calling_script_dir($offset);
        return str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath($dir));
    }

    public static function get_calling_script_dir($offset = 0) {
        $caller = null;
        $backtrace = debug_backtrace();
        $i = 0;
        foreach ($backtrace as $trace) {
            if ($trace['file'] != __FILE__) {
                $caller = $trace['file'];
                break;
            }
            $i++;
        }
        if ($offset){
            $caller = $backtrace[$i + $offset]['file'];
        }
        if ($caller !== null) {
            $pathinfo = pathinfo($caller);
            $dir = $pathinfo['dirname'];
            return $dir;
        }
        return null;
    }

    public static function is_post_class_or_class_map($arg){
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

$timber = new Timber();
$GLOBALS['timber'] = $timber;
