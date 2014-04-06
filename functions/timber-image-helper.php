<?php

	class TimberImageHelper {

		function hexrgb($hexstr) {
		    $int = hexdec($hexstr);
		    return array("red" => 0xFF & ($int >> 0x10), "green" => 0xFF & ($int >> 0x8), "blue" => 0xFF & $int);
		}

		public static function get_image_path($iid) {
			$size = 'full';
			$src = wp_get_attachment_image_src($iid, $size);
			$src = $src[0];
			return self::get_rel_path($src);
		}

		public static function get_letterbox_file_rel($src, $w, $h) {
			$path_parts = pathinfo($src);
			$basename = $path_parts['filename'];
			$ext = $path_parts['extension'];
			$dir = $path_parts['dirname'];
			$newbase = $basename . '-lb-' . $w . 'x' . $h;
			$new_path = $dir . '/' . $newbase . '.' . $ext;
			return $new_path;
		}

		public static function get_letterbox_file_path($src, $w, $h) {
			$path_parts = pathinfo($src);
			$basename = $path_parts['filename'];
			$ext = $path_parts['extension'];
			$dir = $path_parts['dirname'];
			$newbase = $basename . '-lb-' . $w . 'x' . $h;
			$new_path = $dir . '/' . $newbase . '.' . $ext;
			$new_root_path = ABSPATH . $new_path;
			$new_root_path = str_replace('//', '/', $new_root_path);
			return $new_root_path;
		}

		function img_to_jpg($src, $bghex = '#FFFFFF'){
			$src = str_replace(site_url(), '', $src);
			$output = str_replace('.png', '.jpg', $src);
        	$input_file = ABSPATH . $src;
        	$output_file = ABSPATH . $output;
        	if (file_exists($output_file)) {
            	return $output;
        	}
        	$filename = $output;
			$input = imagecreatefrompng($input_file);
			list($width, $height) = getimagesize($input_file);
			$output = imagecreatetruecolor($width, $height);
			$c = self::hexrgb($bghex);
			$white = imagecolorallocate($output,  $c['red'], $c['green'], $c['blue']);
			imagefilledrectangle($output, 0, 0, $width, $height, $white);
			imagecopy($output, $input, 0, 0, 0, 0, $width, $height);
			imagejpeg($output, $output_file);
			return $filename;
		}

		public static function get_sideloaded_file_loc($file){
			$upload = wp_upload_dir();
			$dir = $upload['path'];
			$filename = $file;
			$file = parse_url($file);
			$path_parts = pathinfo($file['path']);
			$basename = md5($filename);
			$ext = 'jpg';
			if (isset($path_parts['extension'])){
				$ext = $path_parts['extension'];
			}
			return $dir . '/' . $basename. '.' . $ext;
		}

		public static function sideload_image($file) {
			$loc = self::get_sideloaded_file_loc($file);
			if (file_exists($loc)){
				return str_replace(ABSPATH, '', $loc);
			}
			// Download file to temp location
			if (!function_exists('download_url')){
				require_once(ABSPATH.'/wp-admin/includes/file.php');
			}
			$tmp = download_url($file);
			preg_match('/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches);
			$file_array['name'] = basename($matches[0]);
			$file_array['tmp_name'] = $tmp;
			// If error storing temporarily, unlink
			if (is_wp_error($tmp)) {
				@unlink($file_array['tmp_name']);
				$file_array['tmp_name'] = '';
			}
			// do the validation and storage stuff
			$locinfo = pathinfo($loc);
			$file = wp_upload_bits($locinfo['basename'], null, file_get_contents($file_array['tmp_name']));
			return $file['url'];
		}

		public static function resize($src, $w, $h = 0, $crop = 'default', $force_resize = false ){
			if (empty($src)){
				return '';
			}
			if (strstr($src, 'http') && !strstr($src, content_url())) {
				$src = self::sideload_image($src);
			}
			$abs = false;
			if (strstr($src, 'http')){
				$abs = true;
			}
			// Sanitize crop position
			$allowed_crop_positions = array( 'default', 'center', 'top', 'bottom', 'left', 'right');
			if ( $crop !== false && ! in_array( $crop, $allowed_crop_positions ) ) {
				$crop = $allowed_crop_positions[ 0 ];
			}
			//oh good, it's a relative image in the uploads folder!
			$path_parts = pathinfo($src);
			$basename = $path_parts['filename'];
			$ext = $path_parts['extension'];
			$dir = $path_parts['dirname'];
			$newbase = $basename . '-' . $w . 'x' . $h . '-c-' . ( $crop ? $crop : 'f' ); // Crop will be either d (default), c (center) or f (false)
			$new_path = $dir . '/' . $newbase . '.' . $ext;
			$new_path = str_replace(content_url(), '', $new_path);
			$new_root_path = WP_CONTENT_DIR . $new_path;
			$old_root_path = WP_CONTENT_DIR . str_replace(content_url(), '', $src);
			$old_root_path = str_replace('//', '/', $old_root_path);
			$new_root_path = str_replace('//', '/', $new_root_path);
			if ( file_exists($new_root_path) ) {
				if ( $force_resize ) {
					// Force resize - warning: will regenerate the image on every pageload, use for testing purposes only!
					unlink( $new_root_path );
				} else {
					if ($abs){
						return untrailingslashit(content_url()).$new_path;
					} else {
						return TimberURLHelper::preslashit($new_path);
					}
					return $new_path;
				}
			}

			$image = wp_get_image_editor($old_root_path);

			if (!is_wp_error($image)) {

				$current_size = $image->get_size();

				$src_w = $current_size['width'];
				$src_h = $current_size['height'];

				$src_ratio = $src_w / $src_h;
				if ( ! $h ) {
					$h = round( $w / $src_ratio);
				}

				// Get ratios
				$dest_ratio = $w / $h;
				$src_wt = $src_h * $dest_ratio;
				$src_ht = $src_w / $dest_ratio;

				if ( ! $crop ) {
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
					} else if ($crop == 'top') {

						error_log('found it on top');
						$src_y = 0;
					} else if ($crop == 'bottom') {
						$src_y = $src_h - $src_ht;
					} else if ($crop == 'left') {
						$src_x = 0;
					} else if ($crop == 'right') {
						$src_x = $src_w - $src_wt;
					}

					// Crop the image
					if ( $dest_ratio > $src_ratio ) {
						$image->crop( 0, $src_y, $src_w, $src_ht, $w, $h );
					} else {
						$image->crop( $src_x, 0, $src_wt, $src_h, $w, $h );
					}

				}

				$result = $image->save($new_root_path);
				if (is_wp_error($result)){
					error_log('Error resizing image');
					error_log(print_r($result, true));
				}
				if ($abs){
					return untrailingslashit(content_url()).$new_path;
				}
				return $new_path;
			} else if (isset($image->error_data['error_loading_image'])) {
				TimberHelper::error_log('Error loading '.$image->error_data['error_loading_image']);
			} else {
				TimberHelper::error_log($image);
			}
			return $src;
		}
	}
