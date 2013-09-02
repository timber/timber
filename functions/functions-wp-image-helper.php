<?php

	class WPImageHelper {

		function hexrgb($hexstr) {
		    $int = hexdec($hexstr);
		    return array("red" => 0xFF & ($int >> 0x10), "green" => 0xFF & ($int >> 0x8), "blue" => 0xFF & $int);
		}

		function img_to_jpg($src, $bghex = '#FFFFFF'){
			//WPHelper::error_log('$src = '.$src);
			$output = str_replace('.png', '.jpg', $src);
        	$input_file = $_SERVER['DOCUMENT_ROOT'] . $src;
        	$output_file = $_SERVER['DOCUMENT_ROOT'] . $output;
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

		public static function sideload_image($file) {
			require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-admin/includes/file.php');
			require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-admin/includes/media.php');
			if (empty($file)) {
				return null;
			}
			// Download file to temp location
			$tmp = download_url($file);
			preg_match('/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches);
			$file_array['name'] = basename($matches[0]);
			$file_array['tmp_name'] = $tmp;
			// If error storing temporarily, unlink
			if (is_wp_error($tmp)) {
				error_log('theres an error');
				@unlink($file_array['tmp_name']);
				$file_array['tmp_name'] = '';
			}
			// do the validation and storage stuff
			$file = wp_upload_bits($file_array['name'], null, file_get_contents($file_array['tmp_name']));
			WPHelper::error_log($file);
			return $file;
		}

		function get_external_dl_location($src, $w, $h) {
			$upload = wp_upload_dir();
			$dir = $upload['path'];
			$file = parse_url($src);
			$path_parts = pathinfo($file['path']);
			$basename = $path_parts['filename'];
			$newbase = $basename . '-r-' . $w . 'x' . $h;
			$ext = $path_parts['extension'];

			$new_root_path = $dir . '/' . $newbase . '.' . $ext;

			$new_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $new_root_path);
			if (strpos($new_path, '/') != 0) {
				$new_path = '/' . $new_path;
			}
			return $new_path;
		}

		public static function resize_external($src, $w, $h = 0){
			//Its a URL so we need to fetch it
			$external = self::get_external_dl_location($src, $w, $h);
			if (!file_exists($_SERVER['DOCUMENT_ROOT'].$external)) {
				error_log('side load the image');
				$image = self::sideload_image($src);
			} else {
				error_log('but there is no need');
			}
			return self::resize($external, $w, $h);
		}

		public static function resize($src, $w, $h = 0){
			if (strstr($src, 'http') && !strstr($src, site_url())) {
				return self::resize_external($src, $w, $h);
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
			$new_root_path = $_SERVER['DOCUMENT_ROOT'] . $new_path;
			$old_root_path = $_SERVER['DOCUMENT_ROOT'] . $src;

			$old_root_path = str_replace('//', '/', $old_root_path);
			$new_root_path = str_replace('//', '/', $new_root_path);

			if (file_exists($new_root_path)) {
				if ($abs){
					return untrailingslashit(site_url()).$new_path;
				}
				return $new_path;
			}
			$image = wp_get_image_editor($old_root_path);
			if (!is_wp_error($image)) {
				$current_size = $image->get_size();
				$ow = $current_size['width'];
				$oh = $current_size['height'];
				if ($h) {
					$new_aspect = $w / $h;
					$old_aspect = $ow / $oh;

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
					$image->resize($w, $w);
				}
				$image->save($new_root_path);
				if ($abs){
					return untrailingslashit(site_url()).$new_path;
				}
				return $new_path;
			}
			return $src;
		}
	}