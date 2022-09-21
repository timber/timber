<?php

/**
 * @group called-post-constructor
 * @group image
 */
class TestTimberImageHelper extends TimberAttachment_UnitTestCase
{
    public function testHTTPAnalyze()
    {
        $url = 'http://example.org/wp-content/uploads/2017/06/dog.jpg';
        $info = Timber\ImageHelper::analyze_url($url);
        $this->assertEquals('/2017/06', $info['subdir']);
    }

    public function testHTTPSAnalyze()
    {
        $url = 'https://example.org/wp-content/uploads/2017/06/dog.jpg';
        $info = Timber\ImageHelper::analyze_url($url);
        $this->assertEquals('/2017/06', $info['subdir']);
    }

    public function testIsAnimatedGif()
    {
        $image = TestTimberImage::copyTestAttachment('robocop.gif');
        $this->assertTrue(Timber\ImageHelper::is_animated_gif($image));
    }

    public function testIsRegularGif()
    {
        $image = TestTimberImage::copyTestAttachment('boyer.gif');
        $this->assertFalse(Timber\ImageHelper::is_animated_gif($image));
    }

    public function testIsNotGif()
    {
        $arch = TestTimberImage::copyTestAttachment('arch.jpg');
        $this->assertFalse(Timber\ImageHelper::is_animated_gif($arch));
    }

    public function testIsSVG()
    {
        $image = TestTimberImage::copyTestAttachment('timber-logo.svg');
        $this->assertTrue(Timber\ImageHelper::is_svg($image));
    }

    public function testServerLocation()
    {
        $arch = TestTimberImage::copyTestAttachment('arch.jpg');
        $this->assertEquals($arch, \Timber\ImageHelper::get_server_location($arch));
    }

    /**
     * @dataProvider customDirectoryData
        */
    public function testCustomWordPressDirectoryStructure($template, $size)
    {
        $this->setupCustomWPDirectoryStructure();

        $upload_dir = wp_upload_dir();
        $post_id = $this->factory->post->create();
        $filename = TestTimberImage::copyTestAttachment('flag.png');
        $destination_url = str_replace(ABSPATH, 'http://' . $_SERVER['HTTP_HOST'] . '/', $filename);
        $wp_filetype = wp_check_filetype(basename($filename), null);
        $attachment = [
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
            'post_content' => '',
            'post_status' => 'inherit',
        ];
        $attach_id = wp_insert_attachment($attachment, $filename, $post_id);
        add_post_meta($post_id, '_thumbnail_id', $attach_id, true);
        $data = [];
        $data['post'] = Timber::get_post($post_id);
        $data['size'] = $size;
        $data['crop'] = 'default';
        Timber::compile($template, $data);

        $this->tearDownCustomWPDirectoryStructure();

        $exists = file_exists($filename);
        $this->assertTrue($exists);
        $resized_path = $upload_dir['path'] . '/flag-' . $data['size']['width'] . 'x' . $data['size']['height'] . '-c-' . $data['crop'] . '.png';
        $exists = file_exists($resized_path);
        $this->assertTrue($exists);
    }

    public function testSideloadImageFolder()
    {
        $filename = 'acGwPDj4_400x400';
        $url = Timber\ImageHelper::sideload_image(
            'https://pbs.twimg.com/profile_images/768086933310476288/' . $filename . '.jpg'
        );

        $base_url = str_replace(basename($url), '', $url);

        $this->assertEquals('http://example.org/wp-content/uploads/external/', $base_url);
    }

    public function testSideloadImageFolderChanged()
    {
        $this->add_filter_temporarily('timber/sideload_image/subdir', function ($subdir) {
            return 'external';
        });

        $filename = 'acGwPDj4_400x400';
        $url = Timber\ImageHelper::sideload_image(
            'https://pbs.twimg.com/profile_images/768086933310476288/' . $filename . '.jpg'
        );

        $base_url = str_replace(basename($url), '', $url);

        $this->assertEquals('http://example.org/wp-content/uploads/external/', $base_url);
    }

    public function testSideloadImageFolderEmpty()
    {
        $this->add_filter_temporarily('timber/sideload_image/subdir', function ($subdir) {
            return '';
        });

        $filename = 'acGwPDj4_400x400';
        $url = Timber\ImageHelper::sideload_image(
            'https://pbs.twimg.com/profile_images/768086933310476288/' . $filename . '.jpg'
        );

        $base_url = untrailingslashit(str_replace(basename($url), '', $url));
        $upload_dir = wp_upload_dir();

        $this->assertEquals($upload_dir['url'], $base_url);
    }

    public function testSideloadImageFolderFalse()
    {
        $this->add_filter_temporarily('timber/sideload_image/subdir', '__return_false');

        $filename = 'acGwPDj4_400x400';
        $url = Timber\ImageHelper::sideload_image(
            'https://pbs.twimg.com/profile_images/768086933310476288/' . $filename . '.jpg'
        );

        $base_url = untrailingslashit(str_replace(basename($url), '', $url));
        $upload_dir = wp_upload_dir();

        $this->assertEquals($upload_dir['url'], $base_url);
    }

    public function testDeleteSideloadedFile()
    {
        $filename = 'acGwPDj4_400x400';
        $img = Timber\ImageHelper::sideload_image('https://pbs.twimg.com/profile_images/768086933310476288/' . $filename . '.jpg');
        $files = scandir('/tmp');
        $matches = false;
        foreach ($files as $file) {
            $substr = substr($file, 0, strlen($filename));
            if ($substr == $filename) {
                $matches = true;
            }
        }
        $this->assertFalse($matches);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testDeleteFalseFile()
    {
        Timber\ImageHelper::delete_generated_files('/etc/www/image.jpg');
    }

    public function testLetterbox()
    {
        $file_loc = TestTimberImage::copyTestAttachment('eastern.jpg');
        $upload_dir = wp_upload_dir();
        $image = $upload_dir['url'] . '/eastern.jpg';
        $new_file = Timber\ImageHelper::letterbox($image, 500, 500, '#CCC', true);
        $location_of_image = Timber\ImageHelper::get_server_location($new_file);
        $this->addFile($location_of_image);
        $this->assertTrue(TestTimberImage::checkSize($location_of_image, 500, 500));
        //whats the bg/color of the image
        $this->assertTrue(TestTimberImage::checkPixel($location_of_image, 1, 1, "#CCC"));
    }

    public function customDirectoryData()
    {
        return [
            [
                'assets/thumb-test.twig',
                [
                    'width' => 100,
                    'height' => 50,
                ],
            ], [
                'assets/thumb-test-relative.twig',
                [
                    'width' => 50,
                    'height' => 100,
                ],
            ],
        ];
    }
}
