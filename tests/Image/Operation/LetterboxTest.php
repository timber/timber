<?php

namespace Timber\Tests\Image\Operation;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use Timber\ImageHelper;
use Timber\Tests\Image\ImageTest;
use Timber\Tests\TimberAttachmentTestCase;
use Timber\Timber;

class LetterboxTest extends TimberAttachmentTestCase
{
    #[RequiresPhpExtension('gd')]
    public function set_up()
    {
        parent::set_up();
        if (!\extension_loaded('gd')) {
            self::markTestSkipped('Letterbox image operation tests requires GD extension');
        }
    }

    public function testLetterbox()
    {
        $file_loc = $this->copyImageToUploads('eastern.jpg');
        $upload_dir = \wp_upload_dir();
        $image = $upload_dir['url'] . '/eastern.jpg';
        $new_file = ImageHelper::letterbox($image, 500, 500, '#CCC', true);
        $location_of_image = ImageHelper::get_server_location($new_file);
        $this->addFile($location_of_image);
        $this->assertTrue(ImageTest::checkSize($location_of_image, 500, 500));
        //whats the bg/color of the image
        $this->assertTrue(ImageTest::checkPixel($location_of_image, 1, 1, "#CCC"));
    }

    public function testLetterboxColorChange()
    {
        $file_loc = $this->copyImageToUploads('eastern.jpg');
        $upload_dir = \wp_upload_dir();
        $new_file_red = ImageHelper::letterbox($upload_dir['url'] . '/eastern.jpg', 500, 500, '#FF0000');
        $new_file = ImageHelper::letterbox($upload_dir['url'] . '/eastern.jpg', 500, 500, '#00FF00');
        $location_of_image = ImageHelper::get_server_location($new_file);
        $this->addFile($location_of_image);
        $this->assertTrue(ImageTest::checkSize($location_of_image, 500, 500));
        //whats the bg/color of the image
        $image = \imagecreatefromjpeg($location_of_image);
        $pixel_rgb = \imagecolorat($image, 1, 1);
        $colors = \imagecolorsforindex($image, $pixel_rgb);
        $this->assertSame(0, $colors['red']);
        $this->assertSame(255, $colors['green']);
    }

    public function testLetterboxTransparent()
    {
        $base_file = 'eastern-trans.png';
        $file_loc = $this->copyImageToUploads($base_file);
        $upload_dir = \wp_upload_dir();
        $new_file = ImageHelper::letterbox($upload_dir['url'] . '/' . $base_file, 500, 500, '00FF00', true);
        $location_of_image = ImageHelper::get_server_location($new_file);
        $this->addFile($location_of_image);
        $this->assertTrue(ImageTest::checkSize($location_of_image, 500, 500));
        //whats the bg/color of the image
        $is_green = ImageTest::checkPixel($location_of_image, 250, 250, '#00FF00');
        $this->assertTrue($is_green);
        $this->assertFileExists($location_of_image);
    }

    public function testLetterboxTransparentBackground()
    {
        $base_file = 'eastern-trans.png';
        $file_loc = $this->copyImageToUploads($base_file);
        $upload_dir = \wp_upload_dir();
        $new_file = ImageHelper::letterbox($upload_dir['url'] . '/' . $base_file, 500, 500);
        $location_of_image = ImageHelper::get_server_location($new_file);
        $this->addFile($location_of_image);
        $this->assertFileExists($location_of_image);
        $this->assertTrue(ImageTest::checkSize($location_of_image, 500, 500));

        // Padding pixel must be transparent, not black (regression for imagealphablending fix).
        $this->assertTrue(ImageTest::checkPixel($location_of_image, 250, 1, false));

        // Transparent pixel from within the source image area must also be transparent.
        $this->assertTrue(ImageTest::checkPixel($location_of_image, 250, 250, false));

        // Opaque source pixel (183,5) maps to output (366, 127) after 2x scale + y-offset 117.
        // Verifies that disabling alpha blending does not break copying of opaque content.
        $this->assertTrue(ImageTest::checkPixel($location_of_image, 366, 127, '#0076FB'));
    }

    public function testLetterboxGif()
    {
        $base_file = 'panam.gif';
        $file_loc = $this->copyImageToUploads($base_file);
        $upload_dir = \wp_upload_dir();
        $new_file = ImageHelper::letterbox($upload_dir['url'] . '/' . $base_file, 300, 100, '00FF00', true);
        $location_of_image = ImageHelper::get_server_location($new_file);
        $this->addFile($location_of_image);
        $this->assertTrue(ImageTest::checkSize($location_of_image, 300, 100));
        //whats the bg/color of the image
        $this->assertTrue(ImageTest::checkPixel($location_of_image, 50, 10, "#00FF00", "#00FF10"));
        $this->assertFileExists($location_of_image);
    }

    public function testLetterboxSixCharHex()
    {
        $data = [];
        $file_loc = $this->copyImageToUploads('eastern.jpg');
        $upload_dir = \wp_upload_dir();
        $new_file = ImageHelper::letterbox($upload_dir['url'] . '/eastern.jpg', 500, 500, '#FFFFFF', true);
        $location_of_image = ImageHelper::get_server_location($new_file);
        $this->addFile($location_of_image);
        $this->assertTrue(ImageTest::checkSize($location_of_image, 500, 500));
        //whats the bg/color of the image
        $image = \imagecreatefromjpeg($location_of_image);
        $pixel_rgb = \imagecolorat($image, 1, 1);
        $colors = \imagecolorsforindex($image, $pixel_rgb);
        $this->assertSame(255, $colors['red']);
        $this->assertSame(255, $colors['blue']);
        $this->assertSame(255, $colors['green']);
    }

    public function testImageLetterboxFilterNotAnImage()
    {
        self::enable_error_log(false);
        $str = 'Image? {{"/wp-content/uploads/2016/07/stuff.jpg"|letterbox(500, 500)}}';
        $compiled = Timber::compile_string($str);
        $this->assertEquals('Image? /wp-content/uploads/2016/07/stuff.jpg', $compiled);
        self::enable_error_log(true);
    }

    public function testSideloadedJPGWithLetterbox()
    {
        $url = 'https://pbs.twimg.com/profile_images/768086933310476288/acGwPDj4_400x400.jpg';
        $sideloaded = Timber::compile_string('{{ file|letterbox(500, 500) }}', [
            'file' => $url,
        ]);

        $base_url = \str_replace(\basename($sideloaded), '', $sideloaded);
        $expected = $base_url . \md5($url) . '-lbox-500x500-trans.jpg';

        $this->assertEquals($expected, $sideloaded);
    }

    public function testSideloadedPNGWithLetterbox()
    {
        $url = 'https://user-images.githubusercontent.com/2084481/31230351-116569a8-a9e4-11e7-8310-48b7f679892b.png';
        $sideloaded = Timber::compile_string('{{ file|letterbox(500, 500) }}', [
            'file' => $url,
        ]);

        $base_url = \str_replace(\basename($sideloaded), '', $sideloaded);
        $expected = $base_url . \md5($url) . '-lbox-500x500-trans.png';

        $this->assertEquals($expected, $sideloaded);
    }
}
