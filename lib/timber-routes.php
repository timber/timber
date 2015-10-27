<?php

class TimberRoutes {

	/**
	 * @deprecated since 0.21.1 use Upstatement/routes instead
	 */
	public static function init( $timber ) {
		// Install ourselves in Timber
		$timber->routes = new TimberRoutes();
	}

	/**
	 * @param string $route
	 * @param callable $callback
	 * @deprecated since 0.21.1 use Upstatement/routes instead
	 */
	public static function add_route($route, $callback, $args = array()) {
		Routes::map($route, $callback, $args);
	}

	/**
	 * @param array $template
	 * @param mixed $query
	 * @param int $status_code
	 * @param bool $tparams
	 * @return bool
	 * @deprecated since 0.21.1 use Upstatement/routes instead
	 */
	public static function load_view($template, $query = false, $status_code = 200, $tparams = false) {
		Routes::load($template, $tparams, $query, $status_code);
	}
}
