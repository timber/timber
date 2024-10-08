<?php

namespace Timber\Image\Operation;

use Timber\Helper;
use Timber\Image\Operation as ImageOperation;
use Timber\ImageHelper;

/**
 * Contains the class for running image retina-izing operations
 */

/**
 * Increases image size by a given factor
 * Arguments:
 * - factor by which to multiply image dimensions
 * @property float $factor the factor (ex: 2, 1.5, 1.75) to multiply dimension by
 */
class Retina extends ImageOperation
{
    /**
     * Construct our operation
     * @param float   $factor to multiply original dimensions by
     */
    public function __construct(
        private $factor
    ) {
    }

    /**
     * Generates the final filename based on the source's name and extension
     *
     * @param   string    $src_filename     the basename of the file (ex: my-awesome-pic)
     * @param   string    $src_extension    the extension (ex: .jpg)
     * @return  string    the final filename to be used (ex: my-awesome-pic@2x.jpg)
     */
    public function filename($src_filename, $src_extension)
    {
        $newbase = $src_filename . '@' . $this->factor . 'x'; // add @2x, @3x, @1.5x, etc.
        $new_name = $newbase . '.' . $src_extension;
        return $new_name;
    }

    /**
     * Performs the actual image manipulation,
     * including saving the target file.
     *
     * @param  string $load_filename filepath (not URL) to source file
     *                               (ex: /src/var/www/wp-content/uploads/my-pic.jpg)
     * @param  string $save_filename filepath (not URL) where result file should be saved
     *                               (ex: /src/var/www/wp-content/uploads/my-pic@2x.jpg)
     * @return bool                  true if everything went fine, false otherwise
     */
    public function run($load_filename, $save_filename)
    {
        // Attempt to check if SVG.
        if (ImageHelper::is_svg($load_filename)) {
            return false;
        }
        $image = \wp_get_image_editor($load_filename);
        if (!\is_wp_error($image)) {
            $current_size = $image->get_size();
            $src_w = $current_size['width'];
            $src_h = $current_size['height'];
            // Get ratios
            $w = \round($src_w * $this->factor);
            $h = \round($src_h * $this->factor);
            $image->crop(0, 0, $src_w, $src_h, $w, $h);
            $result = $image->save($save_filename);
            if (\is_wp_error($result)) {
                // @codeCoverageIgnoreStart
                Helper::error_log('Error resizing image');
                Helper::error_log($result);
                return false;
                // @codeCoverageIgnoreEnd
            }
            return true;
        } elseif (isset($image->error_data['error_loading_image'])) {
            Helper::error_log('Error loading ' . $image->error_data['error_loading_image']);
            return false;
        }
        Helper::error_log($image);
        return false;
    }
}
