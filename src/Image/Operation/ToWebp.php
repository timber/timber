<?php

namespace Timber\Image\Operation;

use Timber\Image\Converter\CWebPConverter;
use Timber\Image\Converter\GDConverter;
use Timber\Image\Converter\IConverter;
use Timber\Image\Converter\ImagickConverter;
use Timber\Image\Operation as ImageOperation;
use Timber\ImageHelper;

/**
 * This class is used to process webp images. Not all server configurations support webp.
 * If webp is not enabled, Timber will generate webp images instead
 * @codeCoverageIgnore
 */
class ToWebp extends ImageOperation
{
    private $converter_engine;

    /**
     * @param string $quality  ranges from 0 (worst quality, smaller file) to 100 (best quality, biggest file)
     */
    public function __construct(
        private $quality
    ) {
        $this->converter_engine = \apply_filters('webp_converter_engine', 'gd');
    }

    /**
     * @param   string    $src_filename     the basename of the file (ex: my-awesome-pic)
     * @param   string    $src_extension    ignored
     * @return  string    the final filename to be used (ex: my-awesome-pic.webp)
     */
    public function filename($src_filename, $src_extension = 'webp')
    {
        $new_name = $src_filename . '.webp';
        return $new_name;
    }

    /**
     * Factory method to get the appropriate converter instance
     *
     * @return IConverter
     */
    private function get_converter()
    {
        return match ($this->converter_engine) {
            'imagick' => new ImagickConverter($this->quality),
            'cwebp' => new CWebPConverter($this->quality),
            default => new GDConverter($this->quality)
        };
    }

    public function get_active_converter_class(): string
    {
        return $this->get_converter()::class;
    }

    /**
     * Performs the actual image manipulation,
     * including saving the target file.
     *
     * @param  string $load_filename filepath (not URL) to source file (ex: /src/var/www/wp-content/uploads/my-pic.webp)
     * @param  string $save_filename filepath (not URL) where result file should be saved
     *                               (ex: /src/var/www/wp-content/uploads/my-pic.webp)
     * @return bool                  true if everything went fine, false otherwise
     */
    public function run($load_filename, $save_filename)
    {
        if (!\is_file($load_filename)) {
            return false;
        }

        // Attempt to check if SVG.
        if (ImageHelper::is_svg($load_filename)) {
            return false;
        }

        $converter = $this->get_converter();
        return $converter->convert($load_filename, $save_filename);
    }
}
