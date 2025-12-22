<?php

namespace Timber\Tests\Image;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Group;
use Timber\ImageHelper;
use Timber\Tests\TimberAttachmentTestCase;
use Timber\Timber;
use Timber\URLHelper;

#[Group('called-post-constructor')]
#[Group('image')]
class ImageHelperTest extends TimberAttachmentTestCase
{
    public function set_up()
    {
        \switch_theme('timber-test-theme');

        parent::set_up();
    }

    public function tear_down()
    {
        $img_dir = \get_stylesheet_directory_uri() . '/images';

        if (\file_exists($img_dir)) {
            \exec(\sprintf("rm -rf %s", \escapeshellarg($img_dir)));
        }

        $uploads = \wp_upload_dir();
        $files = \glob($uploads['basedir'] . \date('/Y/m/') . '*');

        foreach ($files as $file) {
            if (\is_file($file)) {
                \unlink($file);
            }
        }

        \switch_theme('default');

        parent::tear_down();
    }

    public function testHTTPAnalyze()
    {
        $url = 'http://example.org/wp-content/uploads/2017/06/dog.jpg';
        $info = ImageHelper::analyze_url($url);
        $this->assertEquals(ImageHelper::BASE_UPLOADS, $info['base']);
        $this->assertEquals('/2017/06', $info['subdir']);
    }

    public function testHTTPSAnalyze()
    {
        $url = 'https://example.org/wp-content/uploads/2017/06/dog.jpg';
        $info = ImageHelper::analyze_url($url);
        $this->assertEquals(ImageHelper::BASE_UPLOADS, $info['base']);
        $this->assertEquals('/2017/06', $info['subdir']);
    }

    /**
     * Tests "pre_analyze_url" and "analyze_url" filters where
     * "pre_analyze_url" IS NOT short-circuited which triggers
     * the helper's default behaviour.
     */
    public function testAnalyzeFilters1()
    {
        $src = 'https://example.org/wp-content/uploads/2017/06/dog.jpg';

        $pre_filter = function (?array $info, string $url) use ($src) {
            $this->assertEquals($src, $url);
            $this->assertNull($info);
            return $info;
        };

        $this->add_filter_temporarily('timber/image_helper/pre_analyze_url', $pre_filter, 10, 2);

        $filter = function (array $info, string $url) use ($src) {
            $this->assertEquals($src, $url);
            $this->assertSame(ImageHelper::BASE_UPLOADS, $info['base']);
            $this->assertEquals('/2017/06', $info['subdir']);
            return $info;
        };

        $this->add_filter_temporarily('timber/image_helper/analyze_url', $filter, 10, 2);

        $info = ImageHelper::analyze_url($src);
    }

    /**
     * Tests "pre_analyze_url" and "analyze_url" filters where
     * "pre_analyze_url" IS short-circuited which ignores
     * the helper's default behaviour.
     */
    public function testAnalyzeFilters2()
    {
        $src = 'https://example.org/wp-content/uploads/2017/06/dog.jpg';

        $pre_filter = (fn (?array $info, string $url) => [
            'url' => $url,
            'absolute' => URLHelper::is_absolute($url),
            'base' => -1,
            'subdir' => '',
            'filename' => '',
            'extension' => '',
            'basename' => '',
        ]);

        $this->add_filter_temporarily('timber/image_helper/pre_analyze_url', $pre_filter, 10, 2);

        $filter = function (array $info, string $url) {
            $this->assertSame(-1, $info['base']);
            $this->assertEquals('', $info['subdir']);
            return $info;
        };

        $this->add_filter_temporarily('timber/image_helper/analyze_url', $filter, 10, 2);

        $info = ImageHelper::analyze_url($src);
    }

    /**
     * Tests "pre_theme_url_to_dir" and "theme_url_to_dir" filters where
     * "pre_theme_url_to_dir" IS NOT short-circuited which triggers
     * the helper's default behaviour.
     */
    public function testThemeUrlToDirFilters1()
    {
        $dest = ExternalImageTest::copy_image_to_stylesheet('assets/images');
        $this->addFile($dest);
        $this->assertFileExists($dest);

        $image = Timber::get_external_image($dest);
        $src = $image->src();

        $pre_filter = function (?string $path, string $url) use ($src) {
            $this->assertEquals($src, $url);
            $this->assertNull($path);
            return $path;
        };

        $this->add_filter_temporarily('timber/image_helper/pre_theme_url_to_dir', $pre_filter, 10, 2);

        $filter = function (string $path, string $url) use ($dest, $src) {
            $this->assertEquals($src, $url);
            $this->assertEquals($dest, $path);
            return $path;
        };

        $this->add_filter_temporarily('timber/image_helper/theme_url_to_dir', $filter, 10, 2);

        $path = ImageHelper::theme_url_to_dir($src);
        $this->assertEquals($dest, $path);
    }

    /**
     * Tests "pre_theme_url_to_dir" and "theme_url_to_dir" filters where
     * "pre_theme_url_to_dir" IS short-circuited which ignores
     * the helper's default behaviour.
     */
    public function testThemeUrlToDirFilters2()
    {
        $dest = ExternalImageTest::copy_image_to_stylesheet('assets/images');
        $this->addFile($dest);
        $this->assertFileExists($dest);

        $image = Timber::get_external_image($dest);
        $src = $image->src();

        $pre_filter = (fn (?string $path, string $url) => '/path/to/' . \basename($url));

        $this->add_filter_temporarily('timber/image_helper/pre_theme_url_to_dir', $pre_filter, 10, 2);

        $filter = function (string $path, string $url) {
            $this->assertEquals('/path/to/' . \basename($url), $path);
            return $path;
        };

        $this->add_filter_temporarily('timber/image_helper/theme_url_to_dir', $filter, 10, 2);

        $path = ImageHelper::theme_url_to_dir($src);
        $this->assertEquals('/path/to/' . \basename($src), $path);
    }

    public function testIsAnimatedGif()
    {
        $image = $this->copyImageToUploads('robocop.gif');
        $this->assertTrue(ImageHelper::is_animated_gif($image));
    }

    public function testIsRegularGif()
    {
        $image = $this->copyImageToUploads('boyer.gif');
        $this->assertFalse(ImageHelper::is_animated_gif($image));
    }

    public function testIsNotGif()
    {
        $arch = $this->copyImageToUploads('arch.jpg');
        $this->assertFalse(ImageHelper::is_animated_gif($arch));
    }

    public function testIsSVG()
    {
        $image = $this->copyImageToUploads('timber-logo.svg');
        $this->assertTrue(ImageHelper::is_svg($image));
    }

    public function testServerLocation()
    {
        $arch = $this->copyImageToUploads('arch.jpg');
        $this->assertEquals($arch, ImageHelper::get_server_location($arch));
    }

    #[DataProvider('customDirectoryData')]
    public function testCustomWordPressDirectoryStructure($template, $size)
    {
        $this->setupCustomWPDirectoryStructure();

        $upload_dir = \wp_upload_dir();
        $post_id = static::factory()->post->create();
        $filename = $this->copyImageToUploads('flag.png');
        $destination_url = \str_replace(ABSPATH, 'http://' . $_SERVER['HTTP_HOST'] . '/', $filename);
        $wp_filetype = \wp_check_filetype(\basename($filename), null);
        $attachment = [
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => \preg_replace('/\.[^.]+$/', '', \basename($filename)),
            'post_content' => '',
            'post_status' => 'inherit',
        ];
        $attach_id = \wp_insert_attachment($attachment, $filename, $post_id);
        \add_post_meta($post_id, '_thumbnail_id', $attach_id, true);
        $data = [];
        $data['post'] = Timber::get_post($post_id);
        $data['size'] = $size;
        $data['crop'] = 'default';
        Timber::compile($template, $data);

        $this->tearDownCustomWPDirectoryStructure();

        $exists = \file_exists($filename);
        $this->assertTrue($exists);
        $resized_path = $upload_dir['path'] . '/flag-' . $data['size']['width'] . 'x' . $data['size']['height'] . '-c-' . $data['crop'] . '.png';
        $exists = \file_exists($resized_path);
        $this->assertTrue($exists);
    }

    public function testSideloadImageFolder()
    {
        $filename = 'acGwPDj4_400x400';
        $url = ImageHelper::sideload_image(
            'https://pbs.twimg.com/profile_images/768086933310476288/' . $filename . '.jpg'
        );

        $base_url = \str_replace(\basename($url), '', $url);

        $this->assertEquals('http://example.org/wp-content/uploads/external/', $base_url);
    }

    public function testSideloadImageFolderChanged()
    {
        $this->add_filter_temporarily('timber/sideload_image/subdir', fn ($subdir) => 'external');

        $filename = 'acGwPDj4_400x400';
        $url = ImageHelper::sideload_image(
            'https://pbs.twimg.com/profile_images/768086933310476288/' . $filename . '.jpg'
        );

        $base_url = \str_replace(\basename($url), '', $url);

        $this->assertEquals('http://example.org/wp-content/uploads/external/', $base_url);
    }

    public function testSideloadImageFolderEmpty()
    {
        $this->add_filter_temporarily('timber/sideload_image/subdir', fn ($subdir) => '');

        $filename = 'acGwPDj4_400x400';
        $url = ImageHelper::sideload_image(
            'https://pbs.twimg.com/profile_images/768086933310476288/' . $filename . '.jpg'
        );

        $base_url = \untrailingslashit(\str_replace(\basename($url), '', $url));
        $upload_dir = \wp_upload_dir();

        $this->assertEquals($upload_dir['url'], $base_url);
    }

    public function testSideloadImageFolderFalse()
    {
        $this->add_filter_temporarily('timber/sideload_image/subdir', '__return_false');

        $filename = 'acGwPDj4_400x400';
        $url = ImageHelper::sideload_image(
            'https://pbs.twimg.com/profile_images/768086933310476288/' . $filename . '.jpg'
        );

        $base_url = \untrailingslashit(\str_replace(\basename($url), '', $url));
        $upload_dir = \wp_upload_dir();

        $this->assertEquals($upload_dir['url'], $base_url);
    }

    public function testDeleteSideloadedFile()
    {
        $filename = 'acGwPDj4_400x400';
        $img = ImageHelper::sideload_image('https://pbs.twimg.com/profile_images/768086933310476288/' . $filename . '.jpg');
        $files = \scandir('/tmp');
        $matches = false;
        foreach ($files as $file) {
            $substr = \substr($file, 0, \strlen($filename));
            if ($substr == $filename) {
                $matches = true;
            }
        }
        $this->assertFalse($matches);
    }

    #[DoesNotPerformAssertions]
    public function testDeleteFalseFile()
    {
        ImageHelper::delete_generated_files('/etc/www/image.jpg');
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

    public static function customDirectoryData()
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
