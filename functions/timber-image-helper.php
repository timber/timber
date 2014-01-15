<?php

	class TimberImageHelper {

		function hexrgb($hexstr) {
		    $int = hexdec($hexstr);
		    return array("red" => 0xFF & ($int >> 0x10), "green" => 0xFF & ($int >> 0x8), "blue" => 0xFF & $int);
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

		public static function resize($src, $w, $h = 0){
			if (empty($src)){
				return '';
			}
			if (strstr($src, 'http') && !strstr($src, home_url())) {
				$src = self::sideload_image($src);
			}
			$abs = false;
			if (strstr($src, 'http')){
				$abs = true;
			}
			//oh good, it's a relative image in the uploads folder!
			$path_parts = pathinfo($src);
			$basename = $path_parts['filename'];
			$ext = $path_parts['extension'];
			$dir = $path_parts['dirname'];
			$newbase = $basename . '-r-' . $w . 'x' . $h;
			$new_path = $dir . '/' . $newbase . '.' . $ext;
			$new_path = str_replace(site_url(), '', $new_path);
			$new_root_path = ABSPATH . $new_path;
			$old_root_path = ABSPATH . str_replace(site_url(), '', $src);
			$old_root_path = str_replace('//', '/', $old_root_path);
			$new_root_path = str_replace('//', '/', $new_root_path);
			if (file_exists($new_root_path)) {
				if ($abs){
					return untrailingslashit(site_url()).$new_path;
				} else {
					return TimberHelper::preslashit($new_path);
				}
				return $new_path;
			}
			$image = wp_get_image_editor($old_root_path);
			if (!is_wp_error($image)) {
				$current_size = $image->get_size();
				$ow = $current_size['width'];
				$oh = $current_size['height'];
				$old_aspect = $ow / $oh;
				if ($h) {
					$new_aspect = $w / $h;
					if ($new_aspect > $old_aspect) {
						//cropping a vertical photo horitzonally
						$oht = $ow / $new_aspect;
						$oy = ($oh - $oht) / 6;
						$image->crop(0, $oy, $ow, $oht, $w, $h);
					} else {
						$owt = $oh * $new_aspect;
						$ox = $ow / 2 - $owt / 2;
						$image->crop($ox, 0, $owt, $oh, $w, $h);
					}
				} else {
					$h = $w;
					if ($old_aspect < 1){
						$h = $w / $old_aspect;
						$image->crop(0, 0, $ow, $oh, $w, $h);
					} else {
						$image->resize($w, $h);
					}
				}
				$result = $image->save($new_root_path);
				if (is_wp_error($result)){
					error_log('Error resizing image');
					error_log(print_r($result, true));
				}
				if ($abs){
					return untrailingslashit(site_url()).$new_path;
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

	class WPImageHelper extends TimberImageHelper {
	}
