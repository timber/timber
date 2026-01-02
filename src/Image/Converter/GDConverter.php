<?php

namespace Timber\Image\Converter;

use Timber\Helper;

/**
 * Original GD implementation
 */
class GDConverter implements IConverter
{
    public function __construct(
        private $quality
    ) {
    }

    public function convert($load_filename, $save_filename)
    {
        $ext = \wp_check_filetype($load_filename);
        if (isset($ext['ext'])) {
            $ext = $ext['ext'];
        }
        $ext = \strtolower((string) $ext);
        $ext = \str_replace('jpg', 'jpeg', $ext);

        $imagecreate_function = 'imagecreatefrom' . $ext;
        if (!\function_exists($imagecreate_function)) {
            return false;
        }

        $input = $imagecreate_function($load_filename);

        if ($input === false) {
            return false;
        }

        if (!\imageistruecolor($input)) {
            \imagepalettetotruecolor($input);
        }

        if (!\function_exists('imagewebp')) {
            Helper::error_log('The function imagewebp does not exist on this server to convert image to ' . $save_filename . '.');
            return false;
        }

        return \imagewebp($input, $save_filename, $this->quality);
    }
}
