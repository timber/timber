<?php
/*
Plugin Name: Timber
Plugin URI: http://timber.upstatement.com
Description: The WordPress Timber Library allows you to write themes using the power Twig templates
Author: Jared Novack + Upstatement
Version: 0.20.8
Author URI: http://upstatement.com/
*/

global $wp_version;
global $timber;

$composer_autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($composer_autoload)){
	require_once($composer_autoload);
}

require_once(__DIR__ . '/functions/timber-twig.php');
require_once(__DIR__ . '/functions/timber-helper.php');
require_once(__DIR__ . '/functions/timber-url-helper.php');
require_once(__DIR__ . '/functions/timber-image-helper.php');

require_once(__DIR__ . '/functions/timber-core-interface.php');
require_once(__DIR__ . '/functions/timber-core.php');
require_once(__DIR__ . '/functions/timber-post.php');
require_once(__DIR__ . '/functions/timber-post-getter.php');
require_once(__DIR__ . '/functions/timber-comment.php');
require_once(__DIR__ . '/functions/timber-user.php');
require_once(__DIR__ . '/functions/timber-term.php');
require_once(__DIR__ . '/functions/timber-term-getter.php');
require_once(__DIR__ . '/functions/timber-image.php');
require_once(__DIR__ . '/functions/timber-menu-item.php');
require_once(__DIR__ . '/functions/timber-menu.php');
require_once(__DIR__ . '/functions/timber-query-iterator.php');
require_once(__DIR__ . '/functions/timber-posts-collection.php');

//Other 2nd-class citizens
require_once(__DIR__ . '/functions/timber-archives.php');
require_once(__DIR__ . '/functions/timber-routes.php');
require_once(__DIR__ . '/functions/timber-site.php');
require_once(__DIR__ . '/functions/timber-theme.php');
require_once(__DIR__ . '/functions/timber-loader.php');
require_once(__DIR__ . '/functions/timber-function-wrapper.php');
require_once(__DIR__ . '/functions/integrations/acf-timber.php');
require_once(__DIR__ . '/functions/integrations/wpcli-timber.php');

require_once(__DIR__ . '/functions/timber-admin.php');

/** Usage:
 *
 *  $posts = Timber::get_posts();
 *  $posts = Timber::get_posts('post_type = article')
 *  $posts = Timber::get_posts(array('post_type' => 'article', 'category_name' => 'sports')); // uses wp_query format
 *  $posts = Timber::get_posts(array(23,24,35,67), 'InkwellArticle');
 *
 *  $context = Timber::get_context(); // returns wp favorites!
 *  $context['posts'] = $posts;
 *  Timber::render('index.twig', $context);
 */



class Timber {

    public static $locations;
    public static $dirname;
    public static $twig_cache = false;
    public static $cache = false;
    public static $auto_meta = true;
    public static $autoescape = false;

    public function __construct(){
        $this->test_compatibility();
        $this->init_constants();
    }

    protected function test_compatibility(){
        if (is_admin() || $_SERVER['PHP_SELF'] == '/wp-login.php'){
            return;
        }
        if (version_compare(phpversion(), '5.3.0', '<') && !is_admin()) {
            trigger_error('Timber requires PHP 5.3.0 or greater. You have '.phpversion(), E_USER_ERROR);
        }
        if (!class_exists('Twig_Autoloader')) {
        	trigger_error('You have not run "composer install" to download required dependencies for Timber, you can read more on https://github.com/jarednova/timber#installation', E_USER_ERROR);
        }
    }

    function init_constants() {
        defined("TIMBER_LOC") or define("TIMBER_LOC", realpath(__DIR__));
    }

    /*  Post Retrieval
    ================================ */

    /**
     * @param mixed $query
     * @param string $PostClass
     * @return array|bool|null
     */
    public static function get_post($query = false, $PostClass = 'TimberPost') {
        return TimberPostGetter::get_post($query, $PostClass);
    }

    /**
     * @param mixed $query
     * @param string $PostClass
     * @return array|bool|null
     */
    public static function get_posts($query = false, $PostClass = 'TimberPost', $return_collection = false ){
        return TimberPostGetter::get_posts($query, $PostClass, $return_collection);
    }

    /**
     * @param mixed $query
     * @param string $PostClass
     * @return array|bool|null
     */
    public static function query_post($query = false, $PostClass = 'TimberPost') {
        return TimberPostGetter::query_post($query, $PostClass);
    }

    /**
     * @param mixed $query
     * @param string $PostClass
     * @return array|bool|null
     */
    public static function query_posts($query = false, $PostClass = 'TimberPost') {
        return TimberPostGetter::query_posts( $query, $PostClass );
    }

    /**
     * @param array|string $query
     * @return array
     * @deprecated since 0.20.0
     */
    static function get_pids($query = null) {
        return TimberPostGetter::get_pids($query);
    }

    /**
     * @param string $PostClass
     * @return array
     * @deprecated since 0.20.0
     */
    static function get_posts_from_loop($PostClass) {
        return TimberPostGetter::get_posts($PostClass);
    }

    /**
     * @param string $slug
     * @param string $PostClass
     * @return array
     * @deprecated since 0.20.0
     */
    static function get_posts_from_slug($slug, $PostClass = 'TimberPost') {
        return TimberPostGetter::get_posts($slug, $PostClass);
    }

    /**
     * @param array $query
     * @param string $PostClass
     * @return array
     * @deprecated since 0.20.0
     */
    static function get_posts_from_wp_query($query = array(), $PostClass = 'TimberPost') {
        return TimberPostGetter::query_posts($query, $PostClass);
    }

    /**
     * @param array $query
     * @param string $PostClass
     * @return array|null
     * @deprecated since 0.20.0
     */
    static function get_posts_from_array_of_ids($query = array(), $PostClass = 'TimberPost') {
        return TimberPostGetter::get_posts($query, $PostClass);
    }

    /**
     * @param array $results
     * @param string $PostClass
     * @return TimberPostsCollection
     * @deprecated since 0.20.0
     */
    static function handle_post_results($results, $PostClass = 'TimberPost') {
        return TimberPostGetter::handle_post_results($results, $PostClass);
    }

    /**
     * @param $query
     * @return int
     * @deprecated since 0.20.0
     */
    static function get_pid($query) {
        $pids = TimberPostGetter::get_pids($query);
        if (is_array($pids) && count($pids)){
            return $pids[0];
        }
    }

    /**
     * @return bool
     * @deprecated since 0.20.0
     */
    static function wp_query_has_posts() {
        return TimberPostGetter::wp_query_has_posts();
    }

    /* Term Retrieval
    ================================ */

    /**
     * @param string|array $args
     * @param array $maybe_args
     * @param string $TermClass
     * @return mixed
     */
    public static function get_terms($args = null, $maybe_args = array(), $TermClass = 'TimberTerm'){
        return TimberTermGetter::get_terms($args, $maybe_args, $TermClass);
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
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
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

        $data['site'] = new TimberSite();
        $data['theme'] = $data['site']->theme;
        //deprecated, these should be fetched via TimberSite or TimberTheme
        $data['theme_dir'] = WP_CONTENT_SUBDIR.str_replace(WP_CONTENT_DIR, '', get_stylesheet_directory());
        $data['language_attributes'] = TimberHelper::function_wrapper('language_attributes');
        $data['stylesheet_uri'] = get_stylesheet_uri();
        $data['template_uri'] = get_template_directory_uri();

        $data['posts'] = Timber::query_posts();
        
        //deprecated, this should be fetched via TimberMenu
        if (function_exists('wp_nav_menu')) {
            $locations = get_nav_menu_locations();
            if (count($locations)){
                $data['wp_nav_menu'] = wp_nav_menu(array('container_class' => 'menu-header', 'echo' => false, 'menu_class' => 'nav-menu'));
            }
        }
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
        $caller_file = self::get_calling_script_file();
        $caller_file = apply_filters('timber_calling_php_file', $caller_file);
        $loader = new TimberLoader($caller);
        $file = $loader->choose_template($filenames);
        $output = '';
        if (is_null($data)){
            $data = array();
        }
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
        do_action('timber_compile_done');
        return $output;
    }

    /**
     * @param  string $string a string with twig variables
     * @param  array $data an array with data in it
     * @return  bool|string
     */
    public static function compile_string($string, $data = array()){
        $dummy_loader = new TimberLoader();
        $dummy_loader->get_twig();
        $loader = new Twig_Loader_String();
        $twig = new Twig_Environment($loader);
        $twig = apply_filters('twig_apply_filters', $twig);
        return $twig->render($string, $data);
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

    /**
     * @param  string $string a string with twig variables
     * @param  array $data an array with data in it
     * @return  bool|string
     */
    public static function render_string($string, $data = array()){
        $compiled = self::compile_string($string, $data);
        echo $compiled;
        return $compiled;
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
        return self::compile($sidebar, $data);
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

    function init_routes(){
        global $timberRoutes;
        $timberRoutes->init();
    }

    /**
     * @param string $route
     * @param callable $callback
     * @param array $args
     * @deprecated since 0.20.0
     */
    public static function add_route($route, $callback, $args = array()) {
        TimberRoutes::add_route($route, $callback, $args);
    }

    public function cancel_query(){
        add_action('posts_request', array($this, 'cancel_query_posts_request'));
    }

    function cancel_query_posts_request(){
        if (is_main_query()){
            wp_reset_query();
        }
    }

    /**
     * @deprecated since 0.20.0
     */
    public static function load_template($template, $query = false, $status_code = 200, $tparams = false) {
        return TimberRoutes::load_view($template, $query, $status_code, $tparams);
    }

    /**
     * @deprecated since 0.20.2
     */
    public static function load_view($template, $query = false, $status_code = 200, $tparams = false) {
        return TimberRoutes::load_view($template, $query, $status_code, $tparams);
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
            $data['next'] = array('link' => untrailingslashit($next), 'class' => 'page-numbers next');
        }
        $prev = previous_posts(false);
        if ($prev){
            $data['prev'] = array('link' => untrailingslashit($prev), 'class' => 'page-numbers prev');
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
     * @deprecated since 0.20.0
     */
    public static function get_calling_script_path($offset = 0) {
        $dir = self::get_calling_script_dir($offset);
        return str_replace(ABSPATH, '', realpath($dir));
    }

    /**
     * @return boolean|string
     */
    public static function get_calling_script_dir($offset = 0) {
        $caller = self::get_calling_script_file($offset);
        if (!is_null($caller)){
            $pathinfo = pathinfo($caller);
            $dir = $pathinfo['dirname'];
            return $dir;
        }
        return null;
    }

    /**
     * @param int $offset
     * @return string|null
     * @deprecated since 0.20.0
     */
    public static function get_calling_script_file($offset = 0) {
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
        return $caller;
    }

    /**
     * @param string|array $args
     * @return bool
     * @deprecated since 0.20.0
     */
    public static function is_post_class_or_class_map($args){
        return TimberPostGetter::is_post_class_or_class_map($args);
    }

}

$timber = new Timber();
$GLOBALS['timber'] = $timber;
Timber::$dirname = 'views';
