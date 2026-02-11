<?php

namespace Timber\Tests\Image;

use Timber\ImageHelper;
use Timber\Tests\TimberAttachmentTestCase;
use Timber\Timber;

class PathHelperTest extends TimberAttachmentTestCase
{
    public function testImagePathLetterboxWithHebrew()
    {
        //path/to/איתין-נוף-נוסף.jpg

        $hebrew = $this->copyImageToUploads('hebrew.jpg', 'איתין-נוף-נוסף.jpg');
        $upload_dir = \wp_upload_dir();
        $image = $upload_dir['url'] . '/איתין-נוף-נוסף.jpg';
        $new_file = ImageHelper::letterbox($image, 500, 500, '#CCC', true);
        $location_of_image = ImageHelper::get_server_location($new_file);
        $this->addFile($location_of_image);
        $this->assertTrue(ImageTest::checkSize($location_of_image, 500, 500));
        //whats the bg/color of the image
        $this->assertTrue(ImageTest::checkPixel($location_of_image, 1, 1, "#CCC"));
    }

    public function testImagePathStartsWithSpecialChar()
    {
        $file_id = $this->createAttachmentWithImage(0, 'robocop.jpg');
        $image = Timber::get_post($file_id);
        $str = '<img src="{{image.src(\'medium\')}}" />';
        $result = Timber::compile_string($str, [
            'image' => $image,
        ]);
        $upload_dir = \wp_upload_dir();

        $this->assertEquals('<img src="' . $upload_dir['url'] . '/' . $image->sizes()['medium']['file'] . '" />', \trim($result));
    }
}
