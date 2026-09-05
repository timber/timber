<?php

namespace Timber\Image\Operation;

use Timber\Image\Operation as ImageOperation;
use Timber\ImageHelper;

/**
 * Implements converting a PNG file to JPG.
 * Argument:
 * - color to fill transparent zones
 */
class ToJpg extends ImageOperation
{
    /**
     * @param string $color hex string of color to use for transparent zones
     */
    public function __construct(
        private $color
    ) {
    }

    /**
     * @param   string    $src_filename     the basename of the file (ex: my-awesome-pic)
     * @param   string    $src_extension    the source file's extension (ex: png); folded into
     *                                      the generated name only when the
     *                                      `timber/image/collision_safe_filenames` filter is
     *                                      enabled (see below) - off by default
     * @return  string    the final filename to be used (ex: my-awesome-pic.jpg, or
     *                     my-awesome-pic-png.jpg with the filter enabled)
     */
    public function filename($src_filename, $src_extension = 'jpg')
    {
        // A source that's already jpg keeps the bare name regardless of the filter below:
        // ToJpg is then converting it to itself, and _operate()'s destination-already-exists
        // check treats that as a no-op, which is the existing, desired behavior.
        if ($src_extension === 'jpg') {
            return $src_filename . '.jpg';
        }

        /**
         * Filters whether ToJpg (and ToWebp) fold the source extension into the generated
         * filename, so that two different-format sources sharing a basename (ex: pic.png and
         * pic.gif) no longer collide on the same output file and silently serve one source's
         * content under the other's URL - see #2850.
         *
         * Off by default: enabling this changes the generated filename for every non-jpg
         * source going forward (ex: flag.png -> flag-png.jpg instead of flag.jpg), not just
         * ones that would actually collide, since there's no way to tell ahead of time which
         * ones will. Existing sites may not want that regeneration/URL-churn cost sprung on
         * them unprompted; new projects can enable it from the start with no such cost.
         *
         * ```php
         * add_filter('timber/image/collision_safe_filenames', '__return_true');
         * ```
         *
         * @since x.x.x
         *
         * @param bool $collision_safe Whether to use collision-safe (extension-suffixed)
         *                             filenames. Default `false`.
         */
        if (!\apply_filters('timber/image/collision_safe_filenames', false)) {
            return $src_filename . '.jpg';
        }

        return $src_filename . '-' . $src_extension . '.jpg';
    }

    /**
     * Performs the actual image manipulation,
     * including saving the target file.
     *
     * @param  string $load_filename filepath (not URL) to source file (ex: /src/var/www/wp-content/uploads/my-pic.jpg)
     * @param  string $save_filename filepath (not URL) where result file should be saved
     *                               (ex: /src/var/www/wp-content/uploads/my-pic.png)
     * @return bool                  true if everything went fine, false otherwise
     */
    public function run($load_filename, $save_filename)
    {
        // Attempt to check if SVG.
        if (ImageHelper::is_svg($load_filename)) {
            return false;
        }

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

        [$width, $height] = \getimagesize($load_filename);
        $output = \imagecreatetruecolor($width, $height);
        $c = self::hexrgb($this->color);
        $color = \imagecolorallocate($output, $c['red'], $c['green'], $c['blue']);
        \imagefilledrectangle($output, 0, 0, $width, $height, $color);
        \imagecopy($output, $input, 0, 0, 0, 0, $width, $height);
        \imagejpeg($output, $save_filename);
        return true;
    }
}
