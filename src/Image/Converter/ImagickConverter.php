<?php

namespace Timber\Image\Converter;

use Exception;
use Imagick;
use Timber\Helper;

/**
 * ImageMagick implementation
 */
class ImagickConverter implements IConverter
{
    public function __construct(
        private $quality
    ) {
    }

    public function convert($load_filename, $save_filename)
    {
        if (!\class_exists('Imagick')) {
            Helper::error_log('Imagick is not installed on this server.');
            return false;
        }

        try {
            $image = new Imagick($load_filename);
            $image->setImageFormat('webp');
            $image->setImageCompressionQuality($this->quality);
            return $image->writeImage($save_filename);
        } catch (Exception $e) {
            Helper::error_log('Imagick conversion failed: ' . $e->getMessage());
            return false;
        }
    }
}
