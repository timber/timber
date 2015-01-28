<?php

class TimberImageRetinaHelper {

	/**
     * @param string $src
     * @param int $w
     * @param int $h
     * @param string $crop
     * @return string
     */
    static function get_retina_file_path( $src, $mult = 2 ) {
        $new_name = self::get_retina_file_name_relative_to_content( $src, $mult );
        $new_server_path = WP_CONTENT_DIR . $new_name;
        $new_server_path = TimberURLHelper::remove_double_slashes( $new_server_path );
        return $new_server_path;
    }


	/**
	 *
	 *
	 * @param string  $src
	 * @param int     $w
	 * @param int     $h
	 * @param string  $crop
	 * @return string
	 */
	public static function get_retina_file_rel( $src, $factor = 2 ) {
		if ( !strlen( $src ) ) {
			return null;
		}
		$new_path = self::get_retina_file_name_relative_to_content( $src, $factor );
		return WP_CONTENT_SUBDIR . $new_path;
	}

	/**
	 *
	 *
	 * @param string  $src
	 * @param int     $w
	 * @param int     $h
	 * @param string  $crop
	 * @return string
	 */
	private static function get_retina_file_name_relative_to_content( $src, $mult = 2 ) {
		$path_parts = pathinfo( $src );
		$dir_relative_to_content = TimberImageHelper::get_directory_relative_to_content( $src );
		$newbase = $path_parts['filename'] . '@' . $mult . 'x'; // add @2x, @3x, @1.5x, etc.
		$new_name = $newbase . '.' . $path_parts['extension'];
		return $dir_relative_to_content . '/' . $new_name;
	}

}
