<?php

namespace Timber\Image;

/**
 * Each image filter is represented by a subclass of this class,m
 * and each filter call is a new instance, with call arguments as properties.
 * 
 * Only 3 methods need to be implemented:
 * - constructor, storing all filter arguments
 * - filename
 * - run
 */
abstract class Operation {
	/**
	 *
	 * Builds the result filename, based on source filename and extension
	 * 
	 * @param  string $src_filename  source filename (excluding extension and path)
	 * @param  string $src_extension source file extension
	 * @return string                resulting filename (including extension but excluding path)
	 *                               ex: my-awesome-file.jpg
	 */
	public abstract function filename( $src_filename, $src_extension );

	/**
	 * Performs the actual image manipulation,
	 * including saving the target file.
	 * 
	 * @param  string $load_filename filepath (not URL) to source file
	 * @param  string $save_filename filepath (not URL) where result file should be saved
	 * @return bool                  true if everything went fine, false otherwise
	 */
	public abstract function run( $load_filename, $save_filename );

	/**
	 * Helper method to convert hex string to rgb array
	 * 
	 * @param  string $hexstr hex color string (like '#FF1455', 'FF1455', '#CCC', 'CCC')
	 * @return array          array('red', 'green', 'blue') to int
	 *                        ex: array('red' => 255, 'green' => 20, 'blue' => 85);
	 */
	public static function hexrgb( $hexstr ) {
		$hexstr = str_replace('#', '', $hexstr);
		if ( strlen($hexstr) == 3 ) {
			$hexstr = $hexstr[0].$hexstr[0].$hexstr[1].$hexstr[1].$hexstr[2].$hexstr[2];
		}
		$int = hexdec($hexstr);
		return array("red" => 0xFF & ($int >> 0x10), "green" => 0xFF & ($int >> 0x8), "blue" => 0xFF & $int);
	}

	public static function rgbhex( $r, $g, $b ) {
		return '#' . sprintf('%02x', $r) . sprintf('%02x', $g) . sprintf('%02x', $b);
	}
}