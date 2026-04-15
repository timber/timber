<?php

namespace Timber\Tests\Image\Operation;

use PHPUnit\Framework\Attributes\Group;
use Timber\ImageHelper;
use Timber\Tests\Image\ImageTest;
use Timber\Tests\TimberIntegrationTestCase;
use Timber\URLHelper;

#[Group('integrations')]
class ResizeTest extends TimberIntegrationTestCase
{
    public function set_up()
    {
        parent::set_up();
        if (!\extension_loaded('gd')) {
            self::markTestSkipped('Image resizing tests requires GD extension');
        }
    }

    public function testCropCenter()
    {
        $cropper = $this->copyImageToUploads('cropper.png');
        $resized = ImageHelper::resize($cropper, 100, 300, 'center');

        $resized = \str_replace('http://example.org', '', $resized);
        $resized = URLHelper::url_to_file_system($resized);

        $is_black = ImageTest::checkPixel($resized, 10, 20, '#000');
        $is_white = ImageTest::checkPixel($resized, 10, 120, '#FFFFFF');
        $is_gray = ImageTest::checkPixel($resized, 10, 220, '#aaa', '#ccc');

        $this->assertTrue($is_white);
        $this->assertTrue($is_black);
        $this->assertTrue($is_gray);
    }

    public function testCropFalse()
    {
        $cropper = $this->copyImageToUploads('cropper.png');
        $resized = ImageHelper::resize($cropper, 100, 200, false);

        $resized = \str_replace('http://example.org', '', $resized);
        $resized = URLHelper::url_to_file_system($resized);

        $is_red = ImageTest::checkPixel($resized, 20, 20, '#ff0000', '#ff0800');
        $is_green = ImageTest::checkPixel($resized, 0, 100, '#00ff00');
        $is_magenta = ImageTest::checkPixel($resized, 90, 10, '#ff00ff');
        $is_cyan = ImageTest::checkPixel($resized, 90, 199, '#00ffff');
        $is_blue = ImageTest::checkPixel($resized, 90, 199, '#0000ff');
        $this->assertTrue($is_red);
        $this->assertTrue($is_green);
        $this->assertTrue($is_magenta);
        $this->assertTrue($is_cyan);
        $this->assertFalse($is_blue);

        $is_1by2 = ImageTest::checkSize($resized, 100, 200);
        $this->assertTrue($is_1by2);
    }

    public function testCropRight()
    {
        $cropper = $this->copyImageToUploads('cropper.png');
        $resized = ImageHelper::resize($cropper, 100, 300, 'right');

        $resized = \str_replace('http://example.org', '', $resized);
        $resized = URLHelper::url_to_file_system($resized);

        $is_magenta = ImageTest::checkPixel($resized, 50, 50, '#ff00ff');
        $this->assertTrue($is_magenta);
    }

    public function testCropTop()
    {
        $cropper = $this->copyImageToUploads('cropper.png');
        $resized = ImageHelper::resize($cropper, 300, 100, 'top');

        $resized = \str_replace('http://example.org', '', $resized);
        $resized = URLHelper::url_to_file_system($resized);

        $is_magenta = ImageTest::checkPixel($resized, 290, 90, '#ff00ff');
        $this->assertTrue($is_magenta);
    }

    public function testCropBottom()
    {
        $cropper = $this->copyImageToUploads('cropper.png');
        $resized = ImageHelper::resize($cropper, 300, 100, 'bottom');

        $resized = \str_replace('http://example.org', '', $resized);
        $resized = URLHelper::url_to_file_system($resized);

        $is_teal = ImageTest::checkPixel($resized, 290, 90, '#00ffff');
        $this->assertTrue($is_teal);
    }

    public function testCropBottomCenter()
    {
        $cropper = $this->copyImageToUploads('cropper.png');
        $resized = ImageHelper::resize($cropper, 300, 100, 'bottom-center');

        $resized = \str_replace('http://example.org', '', $resized);
        $resized = URLHelper::url_to_file_system($resized);

        $is_teal = ImageTest::checkPixel($resized, 200, 50, '#00ffff');
        $this->assertTrue($is_teal);
    }

    public function testCropTopCenter()
    {
        $cropper = $this->copyImageToUploads('cropper.png');
        $resized = ImageHelper::resize($cropper, 300, 100, 'top-center');

        $resized = \str_replace('http://example.org', '', $resized);
        $resized = URLHelper::url_to_file_system($resized);

        $is_red = ImageTest::checkPixel($resized, 100, 50, '#ff0000', '#ff0800');
        $this->assertTrue($is_red);
    }

    public function testCropHeight()
    {
        $arch = $this->copyImageToUploads('arch.jpg');
        $resized = ImageHelper::resize($arch, false, 250);

        $resized = \str_replace('http://example.org', '', $resized);
        $resized = URLHelper::url_to_file_system($resized);

        $is_sized = ImageTest::checkSize($resized, 375, 250);
        $this->assertTrue($is_sized);
    }

    public function testJPEGQualityDefault()
    {
        //make image at best quality
        $arch = $this->copyImageToUploads('arch.jpg');
        $resized = ImageHelper::resize($arch, 500, 500, 'default', true);
        $resized = \str_replace('http://example.org', '', $resized);
        $resized = URLHelper::url_to_file_system($resized);

        $fileSizeDefault = \filesize($resized);
        $this->assertGreaterThan(20000, $fileSizeDefault);
        $this->assertLessThan(75000, $fileSizeDefault);
    }

    public function testJPEGQualityHigh()
    {
        //make image at best quality
        \add_filter('wp_editor_set_quality', fn () => 100);
        $arch = $this->copyImageToUploads('arch.jpg');
        $resized = ImageHelper::resize($arch, 500, 500, 'default', true);
        $resized = \str_replace('http://example.org', '', $resized);
        $resized = URLHelper::url_to_file_system($resized);

        $fileSizeBig = \filesize($resized);
        $this->assertGreaterThan(43136, $fileSizeBig);
    }

    public function testJPEGQualityLow()
    {
        //make image at best quality
        \add_filter('wp_editor_set_quality', fn () => 1);
        $arch = $this->copyImageToUploads('arch.jpg');
        $resized = ImageHelper::resize($arch, 500, 500, 'default', true);
        $resized = \str_replace('http://example.org', '', $resized);
        $resized = URLHelper::url_to_file_system($resized);

        $fileSizeSmall = \filesize($resized);
        $this->assertLessThan(43136, $fileSizeSmall);
    }

    public function testSideloadedResize()
    {
        $filename = 'acGwPDj4_400x400.jpg';
        $url = 'https://pbs.twimg.com/profile_images/768086933310476288/' . $filename;

        $sideloaded = ImageHelper::resize($url, 100, 300);

        $base_url = \str_replace(\basename($sideloaded), '', $sideloaded);
        $expected = $base_url . \md5($url) . '-100x300-c-default.jpg';

        $this->assertEquals($expected, $sideloaded);
    }

    /**
     * Test that resize fails gracefully when given a non-image file.
     *
     * @see https://github.com/timber/timber/issues/3231
     */
    public function testResizeFailsGracefullyWithNonImageFile()
    {
        // Create an HTML file in uploads directory (simulating a page URL)
        $upload_dir = \wp_get_upload_dir();

        // Ensure upload directory exists
        if (!\is_dir($upload_dir['path'])) {
            \wp_mkdir_p($upload_dir['path']);
        }

        $html_file = $upload_dir['path'] . '/page.html';
        \file_put_contents($html_file, '<html><body>This is a webpage, not an image</body></html>');

        // Attempt to resize the HTML file
        $result = ImageHelper::resize($html_file, 300, 200);

        // Should return the original source (failed gracefully)
        $this->assertEquals($html_file, $result);

        // Clean up
        @\unlink($html_file);
    }

    /**
     * Test that resize fails gracefully when given a text file.
     *
     * @see https://github.com/timber/timber/issues/3231
     */
    public function testResizeFailsGracefullyWithTextFile()
    {
        // Create a text file in uploads directory
        $upload_dir = \wp_get_upload_dir();

        // Ensure upload directory exists
        if (!\is_dir($upload_dir['path'])) {
            \wp_mkdir_p($upload_dir['path']);
        }

        $text_file = $upload_dir['path'] . '/document.txt';
        \file_put_contents($text_file, 'This is a text file, not an image');

        // Attempt to resize the text file
        $result = ImageHelper::resize($text_file, 300, 200);

        // Should return the original source (failed gracefully)
        $this->assertEquals($text_file, $result);

        // Clean up
        @\unlink($text_file);
    }

    /**
     * Test that resize fails gracefully when given a non-existent file.
     *
     * @see https://github.com/timber/timber/issues/3231
     */
    public function testResizeFailsGracefullyWithMissingFile()
    {
        $upload_dir = \wp_get_upload_dir();
        $missing_file = $upload_dir['path'] . '/non-existent-image.jpg';

        // Attempt to resize a file that doesn't exist
        $result = ImageHelper::resize($missing_file, 300, 200);

        // Should return the original source (failed gracefully)
        $this->assertEquals($missing_file, $result);
    }

    /**
     * Test that resize fails gracefully with a PDF file.
     *
     * @see https://github.com/timber/timber/issues/3231
     */
    public function testResizeFailsGracefullyWithPdfFile()
    {
        // Create a fake PDF file in uploads directory
        $upload_dir = \wp_get_upload_dir();

        // Ensure upload directory exists
        if (!\is_dir($upload_dir['path'])) {
            \wp_mkdir_p($upload_dir['path']);
        }

        $pdf_file = $upload_dir['path'] . '/document.pdf';
        \file_put_contents($pdf_file, '%PDF-1.4 fake pdf content');

        // Attempt to resize the PDF file
        $result = ImageHelper::resize($pdf_file, 300, 200);

        // Should return the original source (failed gracefully)
        $this->assertEquals($pdf_file, $result);

        // Clean up
        @\unlink($pdf_file);
    }
}
