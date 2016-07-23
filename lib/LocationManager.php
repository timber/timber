<?php

namespace Timber;

class LocationManager {

	/**
	 * Get calling script file.
	 * @api
	 * @param int     $offset
	 * @return string|null
	 * @deprecated since 0.20.0
	 */
	public static function get_calling_script_file( $offset = 0 ) {
		$callers = array();
		$backtrace = debug_backtrace();
		foreach ( $backtrace as $trace ) {
			if ( array_key_exists('file', $trace) && $trace['file'] != __FILE__ ) {
				$callers[] = $trace['file'];
			}
		}		
		$callers = array_unique($callers);
		$callers = array_values($callers);
		return $callers[$offset];
	}

	/**
	 * Get calling script dir.
	 * @api
	 * @return string
	 */
	public static function get_calling_script_dir( $offset = 0 ) {
		$caller = self::get_calling_script_file($offset);
		if ( !is_null($caller) ) {
			$pathinfo = pathinfo($caller);
			$dir = $pathinfo['dirname'];
			return $dir;
		}
	}

	/**
	 * returns an array of the directory inside themes that holds twig files
	 * @return string[] the names of directores, ie: array('templats', 'views');
	 */
	public static function get_locations_theme_dir() {
		if ( is_string(Timber::$dirname) ) {
			return array(Timber::$dirname);
		}
		return Timber::$dirname;
	}


}