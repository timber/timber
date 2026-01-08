<?php

namespace Timber\Tests\Image;

use Timber\ImageHelper;
use Timber\Tests\TimberIntegrationTestCase;

class ImageIsolatedTest extends TimberIntegrationTestCase
{
    /* ----------------
     * Helper functions
     ---------------- */

    public static function copyTestImage($img = 'arch.jpg')
    {
        $upload_dir = \wp_upload_dir();
        $destination = $upload_dir['path'] . '/' . $img;
        if (!\file_exists($destination)) {
            \copy(__DIR__ . '/../Fixtures/assets/' . $img, $destination);
        }
        return $destination;
    }

    /*
     * Tests that image resizing works correctly with custom uploads directory.
     * Uses the upload_dir filter instead of defining the UPLOADS constant to avoid test pollution.
     */
    public function testResizeFileNamingWithCustomUploadsDir()
    {
        // Filter to modify the uploads directory path
        $custom_upload_filter = function ($upload) {
            $upload['subdir'] = '/my/up';
            $upload['path'] = $upload['basedir'] . '/my/up';
            $upload['url'] = $upload['baseurl'] . '/my/up';
            return $upload;
        };

        $this->add_filter_temporarily('upload_dir', $custom_upload_filter);

        $file_loc = self::copyTestImage('eastern.jpg');
        $upload_dir = \wp_upload_dir();
        $url_src = $upload_dir['url'] . '/eastern.jpg';
        $filename = ImageHelper::get_resize_file_url($url_src, 300, 500, 'default');
        $this->assertEquals($upload_dir['url'] . '/eastern-300x500-c-default.jpg', $filename);
    }
}
