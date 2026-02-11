<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use Timber\Loader;
use Timber\Timber;

#[Group('attachments')]
class TimberWPVIPTest extends TimberAttachmentTestCase
{
    public function testDisableCache()
    {
        $filter = (fn () => 'none');
        \add_filter('timber/cache/mode', $filter);
        $loader = new Loader();
        $cache = $loader->set_cache('test', 'foobar');
        $cache = $loader->get_cache('test');
        $this->assertFalse($cache);
        \remove_filter('timber/cache/mode', $filter);
    }

    public function testImageResize()
    {
        \add_filter('timber/allow_fs_write', '__return_false');
        $data = [];
        $data['size'] = [
            'width' => 600,
            'height' => 400,
        ];
        $upload_dir = \wp_upload_dir();
        $this->copyImageToUploads('arch.jpg');
        $url = $upload_dir['url'] . '/arch.jpg';
        $data['test_image'] = $url;
        $data['crop'] = 'default';
        Timber::compile('assets/image-test.twig', $data);
        $resized_path = $upload_dir['path'] . '/arch-' . $data['size']['width'] . 'x' . $data['size']['height'] . '-c-' . $data['crop'] . '.jpg';
        $this->assertFileDoesNotExist($resized_path);
        \remove_filter('timber/allow_fs_write', '__return_false');
    }

    public function testImageResizeInTwig()
    {
        \add_filter('timber/allow_fs_write', '__return_false');
        $pid = static::factory()->post->create([
            'post_type' => 'post',
        ]);
        $attach_id = $this->createAttachmentWithImage($pid, 'arch.jpg');
        $image = Timber::get_post($attach_id);
        $template = '<img src="{{get_post(img).src|resize(200, 200)}}">';
        $str = Timber::compile_string($template, [
            'img' => $attach_id,
        ]);
        // When FS writes are disabled, resize should return original image URL
        $this->assertEquals('<img src="' . $image->src() . '">', $str);
        \remove_filter('timber/allow_fs_write', '__return_false');
    }

    public function testImageSrcThumbnail()
    {
        \add_filter('timber/allow_fs_write', '__return_false');
        $attach_id = $this->createAttachmentWithImage(0, 'arch.jpg');
        $image = Timber::get_post($attach_id);
        $str = '<img src="{{image.src(\'medium\')}}" />';
        $result = Timber::compile_string($str, [
            'image' => $image,
        ]);
        $upload_dir = \wp_upload_dir();
        $this->assertEquals('<img src="' . $upload_dir['url'] . '/' . $image->sizes['medium']['file'] . '" />', \trim($result));
        \remove_filter('timber/allow_fs_write', '__return_false');
    }
}
