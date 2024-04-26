<?php

/**
 * @group integrations
 */
class TestTimberImageResize extends Timber_UnitTestCase
{
    public function set_up()
    {
        parent::set_up();
        if (!extension_loaded('gd')) {
            self::markTestSkipped('Image resizing tests requires GD extension');
        }
    }

    public function testCropCenter()
    {
        $cropper = TestTimberImage::copyTestAttachment('cropper.png');
        $resized = Timber\ImageHelper::resize($cropper, 100, 300, 'center');

        $resized = str_replace('http://example.org', '', $resized);
        $resized = Timber\URLHelper::url_to_file_system($resized);

        $is_black = TestTimberImage::checkPixel($resized, 10, 20, '#000');
        $is_white = TestTimberImage::checkPixel($resized, 10, 120, '#FFFFFF');
        $is_gray = TestTimberImage::checkPixel($resized, 10, 220, '#aaa', '#ccc');

        $this->assertTrue($is_white);
        $this->assertTrue($is_black);
        $this->assertTrue($is_gray);
    }

    public function testCropFalse()
    {
        $cropper = TestTimberImage::copyTestAttachment('cropper.png');
        $resized = Timber\ImageHelper::resize($cropper, 100, 200, false);

        $resized = str_replace('http://example.org', '', $resized);
        $resized = Timber\URLHelper::url_to_file_system($resized);

        $is_red = TestTimberImage::checkPixel($resized, 20, 20, '#ff0000', '#ff0800');
        $is_green = TestTimberImage::checkPixel($resized, 0, 100, '#00ff00');
        $is_magenta = TestTimberImage::checkPixel($resized, 90, 10, '#ff00ff');
        $is_cyan = TestTimberImage::checkPixel($resized, 90, 199, '#00ffff');
        $is_blue = TestTimberImage::checkPixel($resized, 90, 199, '#0000ff');
        $this->assertTrue($is_red);
        $this->assertTrue($is_green);
        $this->assertTrue($is_magenta);
        $this->assertTrue($is_cyan);
        $this->assertFalse($is_blue);

        $is_1by2 = TestTimberImage::checkSize($resized, 100, 200);
        $this->assertTrue($is_1by2);
    }

    public function testCropRight()
    {
        $cropper = TestTimberImage::copyTestAttachment('cropper.png');
        $resized = Timber\ImageHelper::resize($cropper, 100, 300, 'right');

        $resized = str_replace('http://example.org', '', $resized);
        $resized = Timber\URLHelper::url_to_file_system($resized);

        $is_magenta = TestTimberImage::checkPixel($resized, 50, 50, '#ff00ff');
        $this->assertTrue($is_magenta);
    }

    public function testCropTop()
    {
        $cropper = TestTimberImage::copyTestAttachment('cropper.png');
        $resized = Timber\ImageHelper::resize($cropper, 300, 100, 'top');

        $resized = str_replace('http://example.org', '', $resized);
        $resized = Timber\URLHelper::url_to_file_system($resized);

        $is_magenta = TestTimberImage::checkPixel($resized, 290, 90, '#ff00ff');
        $this->assertTrue($is_magenta);
    }

    public function testCropBottom()
    {
        $cropper = TestTimberImage::copyTestAttachment('cropper.png');
        $resized = Timber\ImageHelper::resize($cropper, 300, 100, 'bottom');

        $resized = str_replace('http://example.org', '', $resized);
        $resized = Timber\URLHelper::url_to_file_system($resized);

        $is_teal = TestTimberImage::checkPixel($resized, 290, 90, '#00ffff');
        $this->assertTrue($is_teal);
    }

    public function testCropBottomCenter()
    {
        $cropper = TestTimberImage::copyTestAttachment('cropper.png');
        $resized = Timber\ImageHelper::resize($cropper, 300, 100, 'bottom-center');

        $resized = str_replace('http://example.org', '', $resized);
        $resized = Timber\URLHelper::url_to_file_system($resized);

        $is_teal = TestTimberImage::checkPixel($resized, 200, 50, '#00ffff');
        $this->assertTrue($is_teal);
    }

    public function testCropTopCenter()
    {
        $cropper = TestTimberImage::copyTestAttachment('cropper.png');
        $resized = Timber\ImageHelper::resize($cropper, 300, 100, 'top-center');

        $resized = str_replace('http://example.org', '', $resized);
        $resized = Timber\URLHelper::url_to_file_system($resized);

        $is_red = TestTimberImage::checkPixel($resized, 100, 50, '#ff0000', '#ff0800');
        $this->assertTrue($is_red);
    }

    public function testCropHeight()
    {
        $arch = TestTimberImage::copyTestAttachment('arch.jpg');
        $resized = Timber\ImageHelper::resize($arch, false, 250);

        $resized = str_replace('http://example.org', '', $resized);
        $resized = Timber\URLHelper::url_to_file_system($resized);

        $is_sized = TestTimberImage::checkSize($resized, 375, 250);
        $this->assertTrue($is_sized);
    }

    public function testJPEGQualityDefault()
    {
        //make image at best quality
        $arch = TestTimberImage::copyTestAttachment('arch.jpg');
        $resized = Timber\ImageHelper::resize($arch, 500, 500, 'default', true);
        $resized = str_replace('http://example.org', '', $resized);
        $resized = Timber\URLHelper::url_to_file_system($resized);

        $fileSizeDefault = filesize($resized);
        $this->assertGreaterThan(20000, $fileSizeDefault);
        $this->assertLessThan(75000, $fileSizeDefault);
    }

    public function testJPEGQualityHigh()
    {
        //make image at best quality
        add_filter('wp_editor_set_quality', function () {
            return 100;
        });
        $arch = TestTimberImage::copyTestAttachment('arch.jpg');
        $resized = Timber\ImageHelper::resize($arch, 500, 500, 'default', true);
        $resized = str_replace('http://example.org', '', $resized);
        $resized = Timber\URLHelper::url_to_file_system($resized);

        $fileSizeBig = filesize($resized);
        $this->assertGreaterThan(43136, $fileSizeBig);
    }

    public function testJPEGQualityLow()
    {
        //make image at best quality
        add_filter('wp_editor_set_quality', function () {
            return 1;
        });
        $arch = TestTimberImage::copyTestAttachment('arch.jpg');
        $resized = Timber\ImageHelper::resize($arch, 500, 500, 'default', true);
        $resized = str_replace('http://example.org', '', $resized);
        $resized = Timber\URLHelper::url_to_file_system($resized);

        $fileSizeSmall = filesize($resized);
        $this->assertLessThan(43136, $fileSizeSmall);
    }

    public function testSideloadedResize()
    {
        $filename = 'acGwPDj4_400x400.jpg';
        $url = 'https://pbs.twimg.com/profile_images/768086933310476288/' . $filename;

        $sideloaded = Timber\ImageHelper::resize($url, 100, 300);

        $base_url = str_replace(basename($sideloaded), '', $sideloaded);
        $expected = $base_url . md5($url) . '-100x300-c-default.jpg';

        $this->assertEquals($expected, $sideloaded);
    }
}
