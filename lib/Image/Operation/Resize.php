<?php

namespace Timber\Image\Operation;

use Timber\Helper;
use Timber\Image\Operation as ImageOperation;

/**
	 * Changes image to new size, by shrinking/enlarging
	 * then cropping to respect new ratio.
	 *
	 * Arguments:
	 * - width of new image
	 * - height of new image
	 * - crop method
	 */
class Resize extends ImageOperation {

   private $w, $h, $crop;

	/**
	 * @param int    $w    width of new image
	 * @param int    $h    height of new image
	 * @param string $crop cropping method, one of: 'default', 'center', 'top', 'bottom', 'left', 'right', 'top-center', 'bottom-center'.
	 */
	function __construct( $w, $h, $crop ) {
		$this->w = $w;
		$this->h = $h;
		// Sanitize crop position
		$allowed_crop_positions = array('default', 'center', 'top', 'bottom', 'left', 'right', 'top-center', 'bottom-center');
		if ( $crop !== false && !in_array($crop, $allowed_crop_positions) ) {
			$crop = $allowed_crop_positions[0];
		}
		$this->crop = $crop;
	}

	/**
	 * @param   string    $src_filename     the basename of the file (ex: my-awesome-pic)
	 * @param   string    $src_extension    the extension (ex: .jpg)
	 * @return  string    the final filename to be used (ex: my-awesome-pic-300x200-c-default.jpg)
	 */
	public function filename( $src_filename, $src_extension ) {
		$w = 0;
		$h = 0;
		if ( $this->w ) {
			$w = $this->w;
		}
		if ( $this->h ) {
			$h = $this->h;
		}
		$result = $src_filename.'-'.$w.'x'.$h.'-c-'.($this->crop ? $this->crop : 'f'); // Crop will be either user named or f (false)
		if ( $src_extension ) {
			$result .= '.'.$src_extension;
		}
		return $result;
	}

	/**
	 * @param string $load_filename
	 * @param string $save_filename
	 */
	protected function run_animated_gif( $load_filename, $save_filename ) {
		$image = wp_get_image_editor($load_filename);
		$current_size = $image->get_size();
		$src_w = $current_size['width'];
		$src_h = $current_size['height'];
		$w = $this->w;
		$h = $this->h;
		if ( !class_exists('Imagick') ) {
			return false;
		}
		$image = new \Imagick($load_filename);
		$image = $image->coalesceImages();
		$crop = self::get_target_sizes($load_filename);
		foreach ( $image as $frame ) {
			$frame->cropImage($crop['src_w'], $crop['src_h'], $crop['x'], $crop['y']);
			$frame->thumbnailImage($w, $h);
			$frame->setImagePage($w, $h, 0, 0);
		}
		$image = $image->deconstructImages();
		return $image->writeImages($save_filename, true);
	}

	/**
	 * @param string $load_filename
	 */
	protected function get_target_sizes( $load_filename ) {
		$image = wp_get_image_editor($load_filename);
		$w = $this->w;
		$h = $this->h;
		$crop = $this->crop;

		$current_size = $image->get_size();
		$src_w = $current_size['width'];
		$src_h = $current_size['height'];
		$src_ratio = $src_w / $src_h;
		if ( !$h ) {
			$h = round($w / $src_ratio);
		}
		if ( !$w ) {
			//the user wants to resize based on constant height
			$w = round($h * $src_ratio);
		}
		if ( !$crop ) {
			return array(
				'x' => 0, 'y' => 0,
				'src_w' => $src_w, 'src_h' => $src_h,
				'target_w' => $w, 'target_h' => $h
			);
		}
		// Get ratios
		$dest_ratio = $w / $h;
		$src_wt = $src_h * $dest_ratio;
		$src_ht = $src_w / $dest_ratio;
		$src_x = $src_w / 2 - $src_wt / 2;
		$src_y = ($src_h - $src_ht) / 6;
		//now specific overrides based on options:
		switch ( $crop ) {
			case 'center':
				// Get source x and y
				$src_x = round(($src_w - $src_wt) / 2);
				$src_y = round(($src_h - $src_ht) / 2);
				break;

			case 'top':
				$src_y = 0;
				break;

			case 'bottom':
				$src_y = $src_h - $src_ht;
				break;

			case 'top-center':
				$src_y = round(($src_h - $src_ht) / 4);
				break;

			case 'bottom-center':
				$src_y = $src_h - $src_ht - round(($src_h - $src_ht) / 4);
				break;

			case 'left':
				$src_x = 0;
				break;

			case 'right':
				$src_x = $src_w - $src_wt;
				break;
		}
		// Crop the image
		return ($dest_ratio > $src_ratio)
			? array(
				'x' => 0, 'y' => $src_y,
				'src_w' => $src_w, 'src_h' => $src_ht,
				'target_w' => $w, 'target_h' => $h
			)
			: array(
				'x' => $src_x, 'y' => 0,
				'src_w' => $src_wt, 'src_h' => $src_h,
				'target_w' => $w, 'target_h' => $h
			);
	}

	/**
	 * Performs the actual image manipulation,
	 * including saving the target file.
	 *
	 * @param  string $load_filename filepath (not URL) to source file
	 *                               (ex: /src/var/www/wp-content/uploads/my-pic.jpg)
	 * @param  string $save_filename filepath (not URL) where result file should be saved
	 *                               (ex: /src/var/www/wp-content/uploads/my-pic-300x200-c-default.jpg)
	 * @return boolean|null                  true if everything went fine, false otherwise
	 */
	public function run( $load_filename, $save_filename ) {
		//should be resized by gif resizer
		if ( \Timber\ImageHelper::is_animated_gif($load_filename) ) {
			//attempt to resize
			//return if successful
			//proceed if not
			$gif = self::run_animated_gif($load_filename, $save_filename);
			if ( $gif ) {
				return true;
			}
		}
		$image = wp_get_image_editor($load_filename);
		if ( !is_wp_error($image) ) {
			$crop = self::get_target_sizes($load_filename);
			$image->crop($crop['x'],
							$crop['y'],
							$crop['src_w'],
							$crop['src_h'],
							$crop['target_w'],
							$crop['target_h']
			);
			$result = $image->save($save_filename);
			if ( is_wp_error($result) ) {
				// @codeCoverageIgnoreStart
				Helper::error_log('Error resizing image');
				Helper::error_log($result);
				return false;
				// @codeCoverageIgnoreEnd
			} else {
				return true;
			}
		} else if ( isset($image->error_data['error_loading_image']) ) {
			// @codeCoverageIgnoreStart
			Helper::error_log('Error loading '.$image->error_data['error_loading_image']);
		} else {
			Helper::error_log($image);
			// @codeCoverageIgnoreEnd
		}
	}
}
