<?php

namespace Timber;

/**
 * Useful methods for working with file paths.
 */
class PathHelper {

	/**
	 *
	 * Unicode-friendly version of the PHP pathinfo() function.
	 * https://www.php.net/manual/en/function.pathinfo.php
	 *
	 * @param string $path the path.
	 * @param int    $options the path part to extract.
	 * @return mixed
	 *
	 * @package Timber
	 */
	public static function pathinfo( $path, $options = PATHINFO_DIRNAME |
		PATHINFO_BASENAME | PATHINFO_EXTENSION | PATHINFO_FILENAME
	) {
		$info = pathinfo(
			str_replace(
				array( '%2F', '%5C' ),
				array( '/', '\\' ),
				rawurlencode( $path )
			),
			$options
		);
		if ( is_array( $info ) ) {
			// decode all keys in the array.
			return array_map( 'rawurldecode', $info );
		} else {
			// decode the string when requesting a single path component.
			return rawurldecode( $info );
		}
	}

	/**
	 *
	 * Unicode-friendly version of the PHP basename() function.
	 * https://www.php.net/manual/en/function.basename.php
	 *
	 * @param string $path the path.
	 * @param string $suffix optional suffix.
	 * @return string
	 */
	public static function basename( $path, $suffix = '' ) {
		return rawurldecode(
			basename( str_replace( array( '%2F', '%5C' ), '/', rawurlencode( $path ) ), $suffix )
		);
	}
}
