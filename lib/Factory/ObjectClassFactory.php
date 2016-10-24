<?php

namespace Timber\Factory;
use Timber\Helper;

/**
 * Class ObjectFactoryClass
 * @package Timber
 */
class ObjectClassFactory {

	public static $TermClass = '\Timber\Term';
	public static $PostClass = '\Timber\Post';

	/**
	 * @param string $type The object type–"post" or "term"
	 * @param string|null $object_type The object's internal type–a post type or taxonomy
	 * @param object|null $object The object for which the class is being retrieved
	 * @param string|null $class The desired class; overrides results of Timber\${type}ClassMap filter
	 *
	 * @return mixed|string
	 */
	public static function get_class( $type, $object_type = null, $object = null, $class = null ) {

		$type = ucwords( $type );

		$class_to_use = $default_class = static::${"{$type}Class"};

		$class = apply_filters( "Timber\\${type}ClassMap", $class, $object_type, $object, $default_class );

		if ( is_string( $class ) ) {
			$class_to_use = $class;
		} else if ( is_array( $class ) && is_string( $object_type ) ) {
			if ( isset( $class[ $object_type ] ) ) {
				$class_to_use = $class[ $object_type ];
			} else {
				Helper::error_log( $object_type . ' not found in ' . print_r( $class, true ) );
			}
		} else {
			Helper::error_log( "Unexpected value for {$type}Class: " . print_r( $class, true ) );
		}

		if ( ! class_exists( $class_to_use ) || ! ( is_subclass_of( $class_to_use, $default_class ) || is_a( $class_to_use, $default_class, true ) ) ) {
			Helper::error_log( 'Class ' . $class_to_use . " either does not exist or implement $default_class" );
		}

		return apply_filters( 'Timber\PostClass', $class_to_use, $object_type, $object_type, $default_class );
	}

}