<?php

class TimberRoutes {

	protected $router;

	function __construct(){
		add_action('init', array($this, 'init'));
	}

	function init() {
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

    public static function add_route($route, $callback, $args = array()) {
        global $timber;
        if (!isset($timber->router)) {
            if (class_exists('PHPRouter\Router')){
                $timber->router = new PHPRouter\Router();
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
        if (class_exists('PHPRouter\Router')){
            $timber->router->map($route, $callback, $args);
        }
    }

    /**
     * @param array $template
     * @param mixed $query
     * @param int $force_header
     * @param bool $tparams
     * @return bool
     */
    public static function load_view($template, $query = false, $force_header = 200, $tparams = false) {
        $fullPath = is_readable($template);
        if (!$fullPath) {
            $template = locate_template($template);
        }
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
        	add_filter('template_include', function($t) use ($template) {
        		return $template;
        	});
            return true;
        }
        return false;
    }
}

global $timberRoutes;
$timberRoutes = new TimberRoutes();
