<?php

namespace Timber\Image\Converter;

/**
 * Interface for WebP converters
 */
interface IConverter
{
    public function convert($load_filename, $save_filename);
}
