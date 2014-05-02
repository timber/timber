<?php
/*
Plugin Name: Timber
Plugin URI: http://timber.upstatement.com
Description: The WordPress Timber Library allows you to write themes using the power Twig templates
Author: Jared Novack + Upstatement
Version: 0.18.0
Author URI: http://upstatement.com/
*/

global $wp_version;
global $timber;

require_once(__DIR__ . '/functions/functions-twig.php');
require_once(__DIR__ . '/functions/timber-helper.php');
require_once(__DIR__ . '/functions/timber-url-helper.php');
require_once(__DIR__ . '/functions/timber-image-helper.php');

require_once(__DIR__ . '/functions/timber-core.php');
require_once(__DIR__ . '/functions/timber-post.php');
require_once(__DIR__ . '/functions/timber-comment.php');
require_once(__DIR__ . '/functions/timber-user.php');
require_once(__DIR__ . '/functions/timber-term.php');
require_once(__DIR__ . '/functions/timber-term-getter.php');
require_once(__DIR__ . '/functions/timber-image.php');
require_once(__DIR__ . '/functions/timber-menu.php');

//Other 2nd-class citizens
require_once(__DIR__ . '/functions/timber-archives.php');
require_once(__DIR__ . '/functions/timber-site.php');
require_once(__DIR__ . '/functions/timber-theme.php');


require_once(__DIR__ . '/functions/timber-loader.php');
require_once(__DIR__ . '/functions/timber-function-wrapper.php');
require_once(__DIR__ . '/functions/integrations/acf-timber.php');
if ( defined('WP_CLI') && WP_CLI ) {
    require_once(__DIR__ . '/functions/integrations/wpcli-timber.php');
}

require_once(__DIR__ . '/functions/timber-admin.php');

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
    public static $auto_meta = true;
    public static $autoescape = false;

    protected $router;

    public function __construct(){
        $this->test_compatibility();
        $this->init_constants();
        add_action('init', array($this, 'init_routes'));
    }

    protected function test_compatibility(){
        if (is_admin() || $_SERVER['PHP_SELF'] == '/wp-login.php'){
            return;
        }
        if (version_compare(phpversion(), '5.3.0', '<') && !is_admin()) {
            trigger_error('Timber requires PHP 5.3.0 or greater. You have '.phpversion(), E_USER_ERROR);
        }
    }

    protected function init_constants() {
        $timber_loc = str_replace(realpath(ABSPATH), '', realpath(__DIR__));
        $plugin_url_path = str_replace($_SERVER['HTTP_HOST'], '', plugins_url());
        $plugin_url_path = str_replace('https://', '', $plugin_url_path);
        $plugin_url_path = str_replace('http://', '', $plugin_url_path);
        $timber_dirs = dirname(__FILE__);
        $timber_dirs = str_replace('\\', '/', $timber_dirs);
        $timber_dirs = explode('/', $timber_dirs);
        $timber_dirname = array_pop($timber_dirs);
        define("TIMBER", $timber_loc);
        define("TIMBER_URL_PATH", trailingslashit($plugin_url_path) . trailingslashit($timber_dirname));
        define("TIMBER_URL", 'http://' . $_SERVER["HTTP_HOST"] . TIMBER);
        define("TIMBER_LOC", realpath(__DIR__));
    }

    /*  Post Retrieval
    ================================ */

    /**
     * @param mixed $query
     * @param string $PostClass
     * @return array|bool|null
     */
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
        } else if (have_posts()) {
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
        }

        return self::maybe_set_preview( $posts );
    }

    /**
     * @param array|string $query
     * @return array
     */
    public static function get_pids($query = null) {
        $posts = get_posts($query);
        $pids = array();
        foreach ($posts as $post) {
            if ($post->ID) {
                $pids[] = $post->ID;
            }
        }
        return $pids;
    }

    /**
     * @param string $PostClass
     * @return array
     */
    public static function get_posts_from_loop($PostClass) {
        $results = self::get_pids_from_loop();
        return self::handle_post_results($results, $PostClass);
    }

    /**
     * @return array
     */
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

    /**
     * @param string $slug
     * @param string $PostClass
     * @return array
     */
    public static function get_posts_from_slug($slug, $PostClass) {
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
     * @param array $query
     * @param string $PostClass
     * @return array
     */
    public static function get_posts_from_wp_query($query = array(), $PostClass = 'TimberPost') {
        $results = get_posts($query);
        return self::handle_post_results($results, $PostClass);
    }

    /**
     * @param array $query
     * @param string $PostClass
     * @return array|null
     */
    public static function get_posts_from_array_of_ids($query = array(), $PostClass = 'TimberPost') {
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
        return $posts;
    }

    /**
     * @param $query
     * @return mixed
     */
    public function get_pid($query) {
        $post = self::get_posts($query);
        return $post->ID;
    }

    /* Post Previews
    ================================ */

    /**
     * @param array $posts
     * @return array
     */
    public static function maybe_set_preview( $posts ) {
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
     * @param string $PostClass
     * @return bool|null
     */
    public function loop_to_posts($PostClass = 'TimberPost') {
        return self::get_posts(false, $PostClass);
    }

    /**
     * @return bool|int
     */
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

    /**
     * @param string|array $args
     * @param array $maybe_args
     * @param string $TermClass
     * @return mixed
     */
    public static function get_terms($args, $maybe_args = array(), $TermClass = 'TimberTerm'){
        if (is_string($maybe_args) && !strstr($maybe_args, '=')){
            //the user is sending the $TermClass in the second argument
            $TermClass = $maybe_args;
        }
        if (is_string($maybe_args) && strstr($maybe_args, '=')){
            parse_str($maybe_args, $maybe_args);
        }
        if (is_string($args) && strstr($args, '=')){
            //a string and a query string!
            $parsed = TimberTermGetter::get_term_query_from_query_string($args);
            if (is_array($maybe_args)){
                $parsed->args = array_merge($parsed->args, $maybe_args);
            }
            return self::handle_term_query($parsed->taxonomies, $parsed->args, $TermClass);
        } else if (is_string($args)){
            //its just a string with a single taxonomy
            $parsed = TimberTermGetter::get_term_query_from_string($args);
            if (is_array($maybe_args)){
                $parsed->args = array_merge($parsed->args, $maybe_args);
            }
            return self::handle_term_query($parsed->taxonomies, $parsed->args, $TermClass);
        } else if (is_array($args) && TimberHelper::is_array_assoc($args)){
            //its an associative array, like a good ole query
            $parsed = TimberTermGetter::get_term_query_from_assoc_array($args);
            return self::handle_term_query($parsed->taxonomies, $parsed->args, $TermClass);
        } else if (is_array($args)){
            //its just an array of strings or IDs (hopefully)
            $parsed = TimberTermGetter::get_term_query_from_array($args);
            if (is_array($maybe_args)){
                $parsed->args = array_merge($parsed->args, $maybe_args);
            }
            return self::handle_term_query($parsed->taxonomies, $parsed->args, $TermClass);
        } else {
            //no clue, what you talkin' bout?
        }

    }

    /**
     * @param string|array $taxonomies
     * @param string|array $args
     * @param $TermClass
     * @return mixed
     */
    public static function handle_term_query($taxonomies, $args, $TermClass){
        if (!isset($args['hide_empty'])){
            $args['hide_empty'] = false;
        }
        $terms = get_terms($taxonomies, $args);
        foreach($terms as &$term){
            $term = new $TermClass($term->term_id, $term->taxonomy);
        }
        return $terms;
    }

    /* Site Retrieval
    ================================ */

    /**
     * @param array|bool $blog_ids
     * @return array
     */
    public static function get_sites($blog_ids = false){
        if (!is_array($blog_ids)){
            global $wpdb;
            $site_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
        }
        $return = array();
        foreach($blog_ids as $blog_id){
            $return[] = new TimberSite($blog_id);
        }
        return $return;
    }


    /*  Template Setup and Display
    ================================ */

    /**
     * @return array
     */
    public static function get_context() {
        $data = array();
        $data['http_host'] = 'http://' . $_SERVER['HTTP_HOST'];
        $data['wp_title'] = TimberHelper::get_wp_title();
        $data['wp_head'] = TimberHelper::function_wrapper('wp_head');
        $data['wp_footer'] = TimberHelper::function_wrapper('wp_footer');
        $data['body_class'] = implode(' ', get_body_class());
        if (function_exists('wp_nav_menu')) {
            $locations = get_nav_menu_locations();
            if (count($locations)){
                $data['wp_nav_menu'] = wp_nav_menu(array('container_class' => 'menu-header', 'echo' => false, 'menu_class' => 'nav-menu'));
            }
        }
        $data['theme_dir'] = str_replace(ABSPATH, '', get_stylesheet_directory());
        $data['language_attributes'] = TimberHelper::function_wrapper('language_attributes');
        $data['stylesheet_uri'] = get_stylesheet_uri();
        $data['template_uri'] = get_template_directory_uri();
        $data['theme'] = new TimberTheme();
        $data['site'] = new TimberSite();
        $data['site']->theme = $data['theme'];
        $data = apply_filters('timber_context', $data);
        return $data;
    }

    /**
     * @param array $filenames
     * @param array $data
     * @param bool $expires
     * @param string $cache_mode
     * @param bool $via_render
     * @return bool|string
     */
    public static function compile($filenames, $data = array(), $expires = false, $cache_mode = TimberLoader::CACHE_USE_DEFAULT, $via_render = false) {
        $caller = self::get_calling_script_dir();
        $loader = new TimberLoader($caller);
        $file = $loader->choose_template($filenames);
        $output = '';
        if (strlen($file)) {
            if ($via_render){
                $file = apply_filters('timber_render_file', $file);
                $data = apply_filters('timber_render_data', $data);
            } else {
                $file = apply_filters('timber_compile_file', $file);
                $data = apply_filters('timber_compile_data', $data);
            }
            $output = $loader->render($file, $data, $expires, $cache_mode);
        }
        return $output;
    }

    /**
     * @param array $filenames
     * @param array $data
     * @param bool $expires
     * @param string $cache_mode
     * @return bool|string
     */
    public static function render($filenames, $data = array(), $expires = false, $cache_mode = TimberLoader::CACHE_USE_DEFAULT) {
        if ($expires === true){
            //if this is reading as true; the user probably is using the old $echo param
            //so we should move all vars up by a spot
            $expires = $cache_mode;
            $cache_mode = TimberLoader::CACHE_USE_DEFAULT;
        }
        $output = self::compile($filenames, $data, $expires, $cache_mode, true);
        $output = apply_filters('timber_compile_result', $output);
        echo $output;
        return $output;
    }


    /*  Sidebar
    ================================ */

    /**
     * @param string $sidebar
     * @param array $data
     * @return bool|string
     */
    public static function get_sidebar($sidebar = '', $data = array()) {
        if ($sidebar == '') {
            $sidebar = 'sidebar.php';
        }
        if (strstr(strtolower($sidebar), '.php')) {
            return self::get_sidebar_from_php($sidebar, $data);
        }
        return self::render($sidebar, $data, false);
    }

    /**
     * @param string $sidebar
     * @param array $data
     * @return string
     */
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
            TimberHelper::error_log('error loading your sidebar, check to make sure the file exists');
        }
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    }

    /* Widgets
    ================================ */

    /**
     * @param int $widget_id
     * @return TimberFunctionWrapper
     */
    public static function get_widgets($widget_id){
        return TimberHelper::function_wrapper('dynamic_sidebar', array($widget_id), true);
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

    /**
     * @param string $route
     * @param callable $callback
     * @param array $args
     */
    public static function add_route($route, $callback, $args = array()) {
        global $timber;
        if (!isset($timber->router)) {
            require_once(__DIR__.'/functions/router/Router.php');
            require_once(__DIR__.'/functions/router/Route.php');
            if (class_exists('Router')){
                $timber->router = new Router();
                $site_url = get_bloginfo('url');
                $site_url_parts = explode('/', $site_url);
                $site_url_parts = array_slice($site_url_parts, 3);
                $base_path = implode('/', $site_url_parts);
                if (!$base_path || strpos($route, $base_path) === 0) {
                    $base_path = '/';
                } else {
                    $base_path = '/' . $base_path . '/';
                }
                $timber->router->setBasePath($base_path);
            }
        }
        if (class_exists('Router')){
            $timber->router->map($route, $callback, $args);
        }
    }

    public static function cancel_query(){
        add_action('posts_request', function(){
            if (is_main_query()){
                wp_reset_query();
            }
        });
    }

    /**
     * @param array $template
     * @param bool $query
     * @param int $force_header
     * @param bool $tparams
     */
    public static function load_template($template, $query = false, $force_header = 0, $tparams = false) {
        $template = locate_template($template);
        if ($tparams){
            global $params;
            $params = $tparams;
        }
        if ($force_header) {
            add_filter('status_header', function($status_header, $header, $text, $protocol) use ($force_header) {
                $text = get_status_header_desc($force_header);
                $header_string = "$protocol $force_header $text";
                return $header_string;
            }, 10, 4 );
            if (404 != $force_header) {
                add_action('parse_query', function($query) {
                    if ($query->is_main_query()){
                        $query->is_404 = false;
                    }
                },1);
                add_action('template_redirect', function(){
                    global $wp_query;
                    $wp_query->is_404 = false;
                },1);
            }
        }

        if ($query) {
            add_action('do_parse_request', function() use ($query) {
                global $wp;

                if ( is_callable($query) )
                    $query = call_user_func($query);

                if ( is_array($query) )
                    $wp->query_vars = $query;
                elseif ( !empty($query) )
                    parse_str($query, $wp->query_vars);
                else
                    return true; // Could not interpret query. Let WP try.

                return false;
            });
        }
        if ($template) {
            add_action('wp_loaded', function() use ($template) {
                wp();
                do_action('template_redirect');
                load_template($template);
                die;
            });
        }
    }

    /*  Pagination
    ================================ */

    /**
     * @param array $prefs
     * @return array mixed
     */
    public static function get_pagination($prefs = array()){
        global $wp_query;
        global $paged;
        global $wp_rewrite;
        $args['total'] = ceil($wp_query->found_posts / $wp_query->query_vars['posts_per_page']);
        if ($wp_rewrite->using_permalinks()){
            $url = explode('?', get_pagenum_link(0));
            if (isset($url[1])){
               parse_str($url[1], $query);
               $args['add_args'] = $query;
            }
            $args['format'] = 'page/%#%';
            $args['base'] = trailingslashit($url[0]).'%_%';
        } else {
            $big = 999999999;
            $args['base'] = str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) );
        }
        $args['type'] = 'array';
        $args['current'] = max( 1, get_query_var('paged') );
        $args['mid_size'] = max(9 - $args['current'], 3);
        $args['prev_next'] = false;
        if (is_int($prefs)){
            $args['mid_size'] = $prefs - 2;
        } else {
            $args = array_merge($args, $prefs);
        }
        $data['pages'] = TimberHelper::paginate_links($args);
        $next = get_next_posts_page_link($args['total']);
        if ($next){
            $data['next'] = array('link' => $next, 'class' => 'page-numbers next');
        }
        $prev = previous_posts(false);
        if ($prev){
            $data['prev'] = array('link' => $prev, 'class' => 'page-numbers prev');
        }
        if ($paged < 2){
            $data['prev'] = '';
        }
        return $data;
    }

    /*  Utility
    ================================ */

    /**
     * @param int $offset
     * @return string
     */
    public static function get_calling_script_path($offset = 0) {
        $dir = self::get_calling_script_dir($offset);
        return str_replace(ABSPATH, '', realpath($dir));
    }

    /**
     * @param int $offset
     * @return string|null
     */
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

    /**
     * @param string|array $arg
     * @return bool
     */
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
