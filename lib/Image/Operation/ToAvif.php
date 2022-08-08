<?php

namespace Timber\Image\Operation;

use Timber\Helper as Helper;
use Timber\Image\Operation as ImageOperation;
use Timber\ImageHelper;

/**
 * This class is used to process avif images. Not all server configurations support avif. 
 * If avif is not enabled, Timber will generate avif images instead
 * @codeCoverageIgnore
 */
class ToAvif extends ImageOperation {

	private $quality;

	/**
	 * @param string $quality  ranges from 0 (worst quality, smaller file) to 100 (best quality, biggest file)
	 */
	public function __construct( $quality ) {
		$this->quality = $quality;
	}

	/**
	 * @param   string    $src_filename     the basename of the file (ex: my-awesome-pic)
	 * @param   string    $src_extension    ignored
	 * @return  string    the final filename to be used (ex: my-awesome-pic.avif)
	 */
	public function filename( $src_filename, $src_extension = 'avif' ) {
		$new_name = $src_filename  . '.avif';
		return $new_name;
	}

	/**
	 * Performs the actual image manipulation,
	 * including saving the target file.
	 *
	 * @param  string $load_filename filepath (not URL) to source file (ex: /src/var/www/wp-content/uploads/my-pic.avif)
	 * @param  string $save_filename filepath (not URL) where result file should be saved
	 *                               (ex: /src/var/www/wp-content/uploads/my-pic.avif)
	 * @return bool                  true if everything went fine, false otherwise
	 */
	public function run( $load_filename, $save_filename ) {
        if (!is_file($load_filename)) {
            return false;
        }

        // Attempt to check if SVG.
		if ( ImageHelper::is_svg($load_filename) ) {
			return false;
		}

		$ext = wp_check_filetype($load_filename);
		if ( isset($ext['ext']) ) {
			$ext = $ext['ext'];
		}
		$ext = strtolower($ext);
		$ext = str_replace('jpg', 'jpeg', $ext);

		$imagecreate_function = 'imagecreatefrom' . $ext;
		if ( !function_exists($imagecreate_function) ) {
			return false;
		}

		$input = $imagecreate_function($load_filename);

        if ( !imageistruecolor($input) ) {
            imagepalettetotruecolor($input);
        }

		if (!function_exists('imageavif')) {
			Helper::error_log('The function imageavif does not exist on this server to convert image to '.$save_filename.'.');
			return false;
		}

		return imageavif($input, $save_filename, $this->quality);
    }
}
