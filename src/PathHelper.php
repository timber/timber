<?php

namespace Timber;

/**
 * Class PathHelper
 *
 * Useful methods for working with file paths.
 *
 * @api
 * @since 1.11.1
 */
class PathHelper {
	/**
	 * Returns information about a file path.
	 *
	 * Unicode-friendly version of PHP’s pathinfo() function.
	 *
	 * @link  https://www.php.net/manual/en/function.pathinfo.php
	 *
	 * @api
	 * @since 1.11.1
	 *
	 * @param string $path    The path to be parsed.
	 * @param int    $options The path part to extract. One of `PATHINFO_DIRNAME`,
	 *                        `PATHINFO_BASENAME`, `PATHINFO_EXTENSION` or `PATHINFO_FILENAME`. If
	 *                        not specified, returns all available elements.
	 *
	 * @return mixed
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
			// Decode all keys in the array.
			return array_map( 'rawurldecode', $info );
		} else {
			// Decode the string when requesting a single path component.
			return rawurldecode( $info );
		}
	}

	/**
	 * Returns trailing name component of path.
	 *
	 * Unicode-friendly version of the PHP basename() function.
	 *
	 * @link  https://www.php.net/manual/en/function.basename.php
	 *
	 * @api
	 * @since 1.11.1
	 *
	 * @param string $path   The path.
	 * @param string $suffix Optional. If the name component ends in suffix, this part will also be
	 *                       cut off.
	 *
	 * @return string
	 */
	public static function basename( $path, $suffix = '' ) {
		return rawurldecode(
			basename(
				str_replace( array( '%2F', '%5C' ), '/', rawurlencode( $path ) ),
				$suffix
			)
		);
	}
}
