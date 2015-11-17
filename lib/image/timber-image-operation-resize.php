<?php
/**
	 * Changes image to new size, by shrinking/enlarging
	 * then cropping to respect new ratio.
	 *
	 * Arguments:
	 * - width of new image
	 * - height of new image
	 * - crop method
	 */
class TimberImageOperationResize extends TimberImageOperation {

   private $w, $h, $crop;

	/**
	 * @param int    $w    width of new image
	 * @param int    $h    height of new image
	 * @param string $crop cropping method, one of: 'default', 'center', 'top', 'bottom', 'left', 'right'.
	 */
	function __construct($w, $h, $crop) {
		$this->w = $w;
		$this->h = $h;
		// Sanitize crop position
		$allowed_crop_positions = array( 'default', 'center', 'top', 'bottom', 'left', 'right' );
		if ( $crop !== false && !in_array( $crop, $allowed_crop_positions ) ) {
			$crop = $allowed_crop_positions[0];
		}
		$this->crop = $crop;
	}

	/**
	 * @param   string    $src_filename     the basename of the file (ex: my-awesome-pic)
	 * @param   string    $src_extension    the extension (ex: .jpg)
	 * @return  string    the final filename to be used (ex: my-awesome-pic-300x200-c-default.jpg)
	 */
	public function filename($src_filename, $src_extension) {
		$result = $src_filename . '-' . $this->w . 'x' . $this->h . '-c-' . ( $this->crop ? $this->crop : 'f' ); // Crop will be either user named or f (false)
		if($src_extension) {
			$result .= '.'.$src_extension;
		}
		return $result;
	}

	protected function run_animated_gif( $load_filename, $save_filename ) {
		$image = wp_get_image_editor( $load_filename );
		$current_size = $image->get_size();
		$src_w = $current_size['width'];
		$src_h = $current_size['height'];
		$w = $this->w;
		$h = $this->h;
		// exec("sudo convert ".$load_filename." -coalesce coalesce.gif");
		// return exec("sudo convert -size ".$src_w."x".$src_h." coalesce.gif -resize ".$w."x".$h." ".$save_filename);$image = new Imagick($file_src);
		$image = new Imagick($load_filename);
		$image = $image->coalesceImages();
		$crop_x = 0;
		$crop_y = 0;
		foreach ($image as $frame) {

			$frame->cropImage($src_w, $src_h, $crop_x, $crop_y);
			$frame->thumbnailImage($w, $h);
			$frame->setImagePage($w, $h, 0, 0);
		}

		$image = $image->deconstructImages();
		return $image->writeImages($save_filename, true);
	}

	/**
	 * Performs the actual image manipulation,
	 * including saving the target file.
	 *
	 * @param  string $load_filename filepath (not URL) to source file
	 *                               (ex: /src/var/www/wp-content/uploads/my-pic.jpg)
	 * @param  string $save_filename filepath (not URL) where result file should be saved
	 *                               (ex: /src/var/www/wp-content/uploads/my-pic-300x200-c-default.jpg)
	 * @return bool                  true if everything went fine, false otherwise
	 */
	public function run($load_filename, $save_filename) {
		//should be resized by gif resizer
		if ( TimberImageHelper::is_animated_gif($load_filename) ) {
			//attempt to resize
			//return if successful
			//proceed if not
			$gif = self::run_animated_gif($load_filename, $save_filename);
			if ($gif) {
				return true;
			}
		}
		$image = wp_get_image_editor( $load_filename );
		if ( !is_wp_error( $image ) ) {
			$w = $this->w;
			$h = $this->h;
			$crop = $this->crop;

			$current_size = $image->get_size();
			$src_w = $current_size['width'];
			$src_h = $current_size['height'];
			$src_ratio = $src_w / $src_h;
			if ( !$h ) {
				$h = round( $w / $src_ratio );
			}
			if ( !$w ) {
				//the user wants to resize based on constant height
				$w = round( $h * $src_ratio );
			}
			// Get ratios
			$dest_ratio = $w / $h;
			$src_wt = $src_h * $dest_ratio;
			$src_ht = $src_w / $dest_ratio;
			if ( !$crop ) {
				// Always crop, to allow resizing upwards
				$image->crop( 0, 0, $src_w, $src_h, $w, $h );
			} else {
				//start with defaults:
				$src_x = $src_w / 2 - $src_wt / 2;
				$src_y = ( $src_h - $src_ht ) / 6;
				//now specific overrides based on options:
				if ( $crop == 'center' ) {
					// Get source x and y
					$src_x = round( ( $src_w - $src_wt ) / 2 );
					$src_y = round( ( $src_h - $src_ht ) / 2 );
				} else if ( $crop == 'top' ) {
						$src_y = 0;
					} else if ( $crop == 'bottom' ) {
						$src_y = $src_h - $src_ht;
					} else if ( $crop == 'left' ) {
						$src_x = 0;
					} else if ( $crop == 'right' ) {
						$src_x = $src_w - $src_wt;
					}
				// Crop the image
				if ( $dest_ratio > $src_ratio ) {
					$image->crop( 0, $src_y, $src_w, $src_ht, $w, $h );
				} else {
					$image->crop( $src_x, 0, $src_wt, $src_h, $w, $h );
				}
			}
			$result = $image->save( $save_filename );
			if ( is_wp_error( $result ) ) {
				error_log( 'Error resizing image' );
				error_log( print_r( $result, true ) );
				return false;
			} else {
				return true;
			}
		} else if ( isset( $image->error_data['error_loading_image'] ) ) {
			TimberHelper::error_log( 'Error loading ' . $image->error_data['error_loading_image'] );
		} else {
			TimberHelper::error_log( $image );
		}
	}
}
