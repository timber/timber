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
     * @param string $src_filename          The basename of the file (ex: my-awesome-pic)
     * @param string $src_extension         The source file’s extension (ex: png). Only added to
     *                                      the generated name when the
     *                                      `timber/image/collision_safe_filenames` filter is
     *                                      enabled (see below), which is off by default.
     * @return  string                      The final filename to be used (ex: my-awesome-pic.jpg, or
     *                                      my-awesome-pic-png-1a2b3c4d.jpg with the filter enabled)
     */
    public function filename($src_filename, $src_extension = 'jpg')
    {
        // Keep the plain filename for JPG sources and sources without an extension, even
        // when the filter below is enabled.
        //
        // - A JPG source is "converted" to itself, so the existing file is simply reused.
        // - A source without an extension would otherwise end up as "name-.jpg". This
        //   matches how Resize::filename() handles a missing extension (see #2773).
        if ($src_extension === 'jpg' || !$src_extension) {
            return $src_filename . '.jpg';
        }

        /**
         * Filters whether |tojpg (and |towebp) add the original file extension and a short
         * hash to the generated filename.
         *
         * Without this, images with the same name but a different format (like pic.png and
         * pic.gif) both become pic.jpg, so one would show up in place of the other. With
         * it enabled, each gets its own name, like pic-png-13cdb71f.jpg. The hash makes sure
         * the generated name can’t accidentally match a real upload.
         *
         * This is off by default, because turning it on renames all converted images, not
         * only the ones with a name clash. On an existing site, this means all images will
         * be regenerated and get new URLs. For new projects, it’s safe to enable it right
         * away.
         *
         * ```php
         * add_filter('timber/image/collision_safe_filenames', '__return_true');
         * ```
         *
         * @since 2.6.0
         *
         * @param bool $collision_safe Whether to use collision-safe filenames. Default
         *                             `false`.
         */
        if (!\apply_filters('timber/image/collision_safe_filenames', false)) {
            return $src_filename . '.jpg';
        }

        return $src_filename . self::collision_safe_suffix($src_filename, $src_extension) . '.jpg';
    }

    /**
     * Returns the suffix added to collision-safe filenames (ex: -png-13cdb71f for pic.png).
     *
     * Used both to create the file and to delete it again, so both always agree on the name.
     *
     * @internal
     * @param string $src_filename  The basename of the file (ex: pic).
     * @param string $src_extension The lowercased source file extension (ex: png).
     * @return string
     */
    public static function collision_safe_suffix(string $src_filename, string $src_extension): string
    {
        $hash = \substr(\md5($src_filename . '.' . $src_extension), 0, 8);

        return '-' . $src_extension . '-' . $hash;
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
