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

	}