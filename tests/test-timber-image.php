<?php

use Timber\Attachment;
use Timber\Image;
use Timber\Image\Operation as ImageOperation;
use Timber\Post;

/**
 * @group posts-api
 * @group attachments
 * @group image
 */
class TestTimberImage extends TimberAttachment_UnitTestCase
{
    public function tear_down()
    {
        $img_dir = get_stylesheet_directory_uri() . '/images';
        if (file_exists($img_dir)) {
            exec(sprintf("rm -rf %s", escapeshellarg($img_dir)));
        }
        $uploads = wp_upload_dir();
        $files = glob($uploads['basedir'] . date('/Y/m/') . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        parent::tear_down();
    }

    /* ----------------
     * Helper functions
     * ---------------- */

    public function get_post_with_image()
    {
        $pid = $this->factory->post->create();
        $iid = self::get_attachment($pid);
        add_post_meta($pid, '_thumbnail_id', $iid, true);
        add_post_meta($iid, '_wp_attachment_metadata', wp_generate_attachment_metadata($iid, get_attached_file($iid)), true);
        $post = Timber::get_post($pid);
        return $post;
    }

    /* ----------------
     * Tests
     * ---------------- */

    /**
     * @group attachment-aliases
     */
    public function testGetImageAlias()
    {
        $pid = $this->factory->post->create();

        $image = Timber::get_image(self::get_attachment($pid, 'arch.jpg'));
        $attachment = Timber::get_image(self::get_attachment($pid, 'dummy-pdf.pdf'));
        $post = Timber::get_image($pid);

        // Image is good, but Timber should recognize that neither Attachment
        // or Post are actually Image subclasses.
        $this->assertInstanceOf(Image::class, $image);
        $this->assertNull($attachment);
        $this->assertNull($post);
    }

    /**
     * @group attachment-aliases
     */
    public function testGetAttachmentAlias()
    {
        $pid = $this->factory->post->create();

        $image = Timber::get_attachment(self::get_attachment($pid, 'arch.jpg'));
        $attachment = Timber::get_attachment(self::get_attachment($pid, 'dummy-pdf.pdf'));
        $post = Timber::get_image($pid);

        // Image and Attachment are *both* Attachment classes, so they're OK.
        // A Post is not an Attachment so it should not be treated as such.
        $this->assertInstanceOf(Image::class, $image);
        $this->assertInstanceOf(Attachment::class, $attachment);
        $this->assertNull($post);
    }

    /**
     * @group attachment-aliases
     */
    public function testGetImageTwigAlias()
    {
        $pid = $this->factory->post->create();

        $iid = self::get_attachment($pid, 'arch.jpg');
        $src = Timber::get_post($iid)->src();

        $this->assertEquals($src, Timber::compile_string('{{ get_image(iid).src }}', [
            'iid' => $iid,
        ]));
    }

    /**
     * @group attachment-aliases
     */
    public function testGetAttachmentTwigAlias()
    {
        $pid = $this->factory->post->create();

        $iid = self::get_attachment($pid, 'arch.jpg');
        $src = Timber::get_post($iid)->src();

        $this->assertEquals($src, Timber::compile_string('{{ get_attachment(iid).src }}', [
            'iid' => $iid,
        ]));
    }

    public function testTimberImageSrc()
    {
        $iid = self::get_attachment();
        $image = Timber::get_post($iid);
        $post = get_post($iid);
        $str = '{{ get_post(post).src }}';
        $result = Timber::compile_string($str, [
            'post' => $post,
        ]);
        $this->assertEquals($image->src(), $result);
    }

    public function testWithOutputBuffer()
    {
        ob_start();
        $post = $this->get_post_with_image();
        $str = '<img src="{{ post.thumbnail.src|resize(510, 280) }}" />';
        Timber::render_string($str, [
            'post' => $post,
        ]);
        $result = ob_get_contents();
        ob_end_clean();
        $m = date('m');
        $y = date('Y');
        $this->assertEquals('<img src="http://example.org/wp-content/uploads/' . $y . '/' . $m . '/arch-510x280-c-default.jpg" />', $result);
    }

    public function testReplacedImage()
    {
        $pid = $this->factory->post->create([
            'post_type' => 'post',
        ]);
        $attach_id = self::get_attachment($pid, 'arch.jpg');
        $template = '{{ get_post(img).src|resize(200, 200) }}';
        $str = Timber::compile_string($template, [
            'img' => $attach_id,
        ]);
        $resized_one = Timber\ImageHelper::get_server_location($str);
        sleep(1);
        $filename = self::copyTestAttachment('cardinals.jpg', 'arch.jpg');

        $str = Timber::compile_string($template, [
            'img' => $attach_id,
        ]);
        $resized_tester = Timber\ImageHelper::get_server_location($str);

        $attach_id = self::get_attachment($pid, 'cardinals.jpg');
        $str = Timber::compile_string($template, [
            'img' => $attach_id,
        ]);
        $resized_known = Timber\ImageHelper::get_server_location($str);
        $pixel = TestTimberImage::getPixel($resized_one, 5, 5);
        $is_white = TestTimberImage::checkPixel($resized_one, 5, 5, '#FFFFFF');
        $this->assertTrue($is_white);
        $is_also_white = TestTimberImage::checkPixel($resized_one, 5, 5, '#FFFFFF');
        $this->assertTrue($is_also_white);
    }

    public function testResizedReplacedImage()
    {
        $pid = $this->factory->post->create([
            'post_type' => 'post',
        ]);
        $attach_id = self::get_attachment($pid, 'arch.jpg');
        $template = '{{ get_post(img).src|resize(200, 200) }}';
        $str = Timber::compile_string($template, [
            'img' => $attach_id,
        ]);
        $new_id = self::get_attachment($pid, 'pizza.jpg');
        self::replace_attachment($attach_id, $new_id);
        $str = Timber::compile_string($template, [
            'img' => $attach_id,
        ]);
        $resized_path = Timber\ImageHelper::get_server_location($str);
        $test_md5 = md5(file_get_contents($resized_path));

        $str_pizza = Timber::compile_string($template, [
            'img' => $new_id,
        ]);
        $resized_pizza = Timber\ImageHelper::get_server_location($str);

        $pizza_md5 = md5(file_get_contents($resized_pizza));
        $this->assertEquals($pizza_md5, $test_md5);
    }

    public function testImageMeta()
    {
        $post = $this->get_post_with_image();
        $image = $post->thumbnail();
        update_post_meta($image->ID, 'architect', 'Eero Saarinen');
        $this->assertEquals('Eero Saarinen', $image->meta('architect'));
        $this->assertEquals('Eero Saarinen', $image->architect);
    }

    public function testImageSizes()
    {
        $post = $this->get_post_with_image();
        $image = $post->thumbnail();
        $this->assertSame(1500, $image->width());
        $this->assertSame(1000, $image->height());
        $this->assertEquals($post->ID, $image->parent()->id);
        $this->assertSame(1.5, $image->aspect());
    }

    public function testImageSrcset()
    {
        $post = $this->get_post_with_image();
        $img = $post->thumbnail();
        $mine = $img->srcset();

        $native = wp_get_attachment_image_srcset($img->ID, 'full');
        $this->assertEquals($native, $mine);

        $native = wp_get_attachment_image_srcset($img->ID, 'medium');
        $this->assertNotEquals($native, $mine);
    }

    public function testImageImgSizes()
    {
        $post = $this->get_post_with_image();
        $img = $post->thumbnail();
        $mine = $img->img_sizes();

        $native = wp_get_attachment_image_sizes($img->ID, 'full');
        $this->assertEquals($native, $mine);

        $native = wp_get_attachment_image_sizes($img->ID, 'medium');
        $this->assertNotEquals($native, $mine);
    }

    /**
     * @group maybeSkipped
     */
    public function testExternalImageResize()
    {
        if (!self::is_connected()) {
            $this->markTestSkipped('Cannot test external images when not connected to internet');
        }
        $data = [];
        $data['size'] = [
            'width' => 600,
            'height' => 400,
        ];
        $data['crop'] = 'default';
        $filename = 'St._Louis_Gateway_Arch.jpg';
        $data['test_image'] = 'http://upload.wikimedia.org/wikipedia/commons/a/aa/' . $filename;
        $md5 = md5($data['test_image']);
        Timber::compile('assets/image-test.twig', $data);
        $upload_dir = wp_upload_dir();
        $path = $upload_dir['basedir'] . '/external/' . $md5;
        /* was the external image D/Ld to the location? */
        $this->assertFileExists($path . '.jpg');
        /* does resize work on external image? */
        $resized_path = $path . '-' . $data['size']['width'] . 'x' . $data['size']['height'] . '-c-' . $data['crop'] . '.jpg';
        $this->assertFileExists($resized_path);
        $old_time = filemtime($resized_path);
        sleep(1);
        $str = Timber::compile('assets/image-test.twig', $data);
        $new_time = filemtime($resized_path);
        $this->assertEquals($old_time, $new_time);
    }

    public function testUpSizing()
    {
        $data = [];
        $file_loc = self::copyTestAttachment('stl.jpg');
        $upload_dir = wp_upload_dir();
        $new_file = Timber\ImageHelper::resize($upload_dir['url'] . '/stl.jpg', 500, 200, 'default', true);
        $location_of_image = Timber\ImageHelper::get_server_location($new_file);
        $size = getimagesize($location_of_image);
        $this->assertSame(500, $size[0]);
    }

    public function testUpSizing2Param()
    {
        $data = [];
        $file_loc = self::copyTestAttachment('stl.jpg');
        $upload_dir = wp_upload_dir();
        $new_file = Timber\ImageHelper::resize($upload_dir['url'] . '/stl.jpg', 500, 300, 'default', true);
        $location_of_image = Timber\ImageHelper::get_server_location($new_file);
        $size = getimagesize($location_of_image);
        $this->assertSame(500, $size[0]);
        $this->assertSame(300, $size[1]);
    }

    public function testImageResizeRelative()
    {
        $upload_dir = wp_upload_dir();
        self::copyTestAttachment();
        $url = $upload_dir['url'] . '/arch.jpg';
        $url = str_replace('http://example.org', '', $url);
        $data = [
            'crop' => 'default',
            'test_image' => $url,
        ];
        $data['size'] = [
            'width' => 300,
            'height' => 300,
        ];
        $html = Timber::compile('assets/image-test.twig', $data);
        $resized_path = $upload_dir['path'] . '/arch-' . $data['size']['width'] . 'x' . $data['size']['height'] . '-c-' . $data['crop'] . '.jpg';
        $this->assertFileExists($resized_path);
        //Now make sure it doesnt regenerage
        $old_time = filemtime($resized_path);
        sleep(1);
        Timber::compile('assets/image-test.twig', $data);
        $new_time = filemtime($resized_path);
        $this->assertEquals($old_time, $new_time);
    }

    public function testImageResize()
    {
        $data = [];
        $data['size'] = [
            'width' => 600,
            'height' => 400,
        ];
        $upload_dir = wp_upload_dir();
        self::copyTestAttachment();
        $url = $upload_dir['url'] . '/arch.jpg';
        $data['test_image'] = $url;
        $data['crop'] = 'default';
        Timber::compile('assets/image-test.twig', $data);
        $resized_path = $upload_dir['path'] . '/arch-' . $data['size']['width'] . 'x' . $data['size']['height'] . '-c-' . $data['crop'] . '.jpg';
        $this->assertFileExists($resized_path);
        //Now make sure it doesnt regenerage
        $old_time = filemtime($resized_path);
        sleep(1);
        Timber::compile('assets/image-test.twig', $data);
        $new_time = filemtime($resized_path);
        $this->assertEquals($old_time, $new_time);
    }

    public function testIsNotAGif()
    {
        $image = self::copyTestAttachment('arch.jpg');
        $this->assertFalse(Timber\ImageHelper::is_animated_gif($image));
    }

    public function testIsNotAGifFile()
    {
        $this->assertFalse(Timber\ImageHelper::is_animated_gif('notreal.gif'));
    }

    /**
     * @group maybeSkipped
     */
    public function testAnimatedGifResize()
    {
        if (!extension_loaded('imagick')) {
            self::markTestSkipped('Animated GIF resizing test requires Imagick extension');
        }
        $image = self::copyTestAttachment('robocop.gif');
        $data = [
            'crop' => 'default',
        ];
        $data['size'] = [
            'width' => 90,
            'height' => 90,
        ];
        $upload_dir = wp_upload_dir();
        $url = $upload_dir['url'] . '/robocop.gif';
        $data['test_image'] = $url;
        Timber::compile('assets/image-test.twig', $data);
        $resized_path = $upload_dir['path'] . '/robocop-' . $data['size']['width'] . 'x' . $data['size']['height'] . '-c-' . $data['crop'] . '.gif';
        $this->addFile($resized_path);
        $this->assertFileExists($resized_path);
        $this->assertTrue(Timber\ImageHelper::is_animated_gif($resized_path));
    }

    /**
     * @group maybeSkipped
     */
    public function testResizeTallImage()
    {
        $data = [];
        $data['size'] = [
            'width' => 600,
        ];
        $upload_dir = wp_upload_dir();
        self::copyTestAttachment('tall.jpg');
        $url = $upload_dir['url'] . '/tall.jpg';
        $data['test_image'] = $url;
        $data['crop'] = 'default';
        Timber::compile('assets/image-test-one-param.twig', $data);
        $resized_path = $upload_dir['path'] . '/tall-' . $data['size']['width'] . 'x0' . '-c-' . $data['crop'] . '.jpg';
        $exists = file_exists($resized_path);
        $this->assertTrue($exists);
        //make sure it's the width it's supposed to be
        $image = wp_get_image_editor($resized_path);
        if ($image instanceof WP_Error) {
            self::markTestSkipped('Tall image resizing test is skipped because no image editor is provided by WordPress, make sure that either GD or Imagick extension is installed');
        }
        $current_size = $image->get_size();
        $w = $current_size['width'];
        $this->assertEquals($w, 600);
    }

    public function testPostThumbnails()
    {
        $upload_dir = wp_upload_dir();
        $post_id = $this->factory->post->create();
        $filename = self::copyTestAttachment('flag.png');
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
        $data['size'] = [
            'width' => 100,
            'height' => 50,
        ];
        $data['crop'] = 'default';
        Timber::compile('assets/thumb-test.twig', $data);
        $exists = file_exists($filename);
        $this->assertTrue($exists);
        $resized_path = $upload_dir['path'] . '/flag-' . $data['size']['width'] . 'x' . $data['size']['height'] . '-c-' . $data['crop'] . '.png';
        $exists = file_exists($resized_path);
        $this->assertTrue($exists);
    }

    public function testImageAltText()
    {
        $upload_dir = wp_upload_dir();
        $thumb_alt = 'Thumb alt';
        $filename = self::copyTestAttachment('flag.png');
        $wp_filetype = wp_check_filetype(basename($filename), null);
        $post_id = $this->factory->post->create();
        $attachment = [
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
            'post_excerpt' => '',
            'post_status' => 'inherit',
        ];
        $attach_id = wp_insert_attachment($attachment, $filename, $post_id);
        add_post_meta($post_id, '_thumbnail_id', $attach_id, true);
        add_post_meta($attach_id, '_wp_attachment_image_alt', $thumb_alt, true);
        $data = [];
        $data['post'] = Timber::get_post($post_id);
        $this->assertEquals($data['post']->thumbnail()->alt(), $thumb_alt);
    }

    public function testResizeFileNaming()
    {
        $file = 'eastern.jpg';
        $file_loc = self::copyTestAttachment($file);
        $upload_dir = wp_upload_dir();
        $filename = Timber\ImageHelper::get_resize_file_url(self::getTestAttachmentURL($file, true), 300, 500, 'default');
        $expected = $upload_dir['relative'] . $upload_dir['subdir'] . '/eastern-300x500-c-default.jpg';
        $this->assertEquals($expected, $filename);
    }

    public function testResizeFileNamingWithAbsoluteURL()
    {
        $file_loc = self::copyTestAttachment('eastern.jpg');
        $upload_dir = wp_upload_dir();
        $url_src = $upload_dir['url'] . '/eastern.jpg';
        $filename = Timber\ImageHelper::get_resize_file_url($url_src, 300, 500, 'default');
        $this->assertEquals($upload_dir['url'] . '/eastern-300x500-c-default.jpg', $filename);
    }

    public function testResizeFileNamingWithLangHome()
    {
        add_filter('home_url', [$this, 'add_lang_to_home'], 1, 4);
        $file_loc = self::copyTestAttachment('eastern.jpg');
        $upload_dir = wp_upload_dir();
        $url_src = $upload_dir['url'] . '/eastern.jpg';
        $filename = Timber\ImageHelper::get_resize_file_url($url_src, 300, 500, 'default');
        $this->assertEquals($upload_dir['url'] . '/eastern-300x500-c-default.jpg', $filename);
        remove_filter('home_url', [$this, 'add_lang_to_home'], 1);
    }

    public function testLetterboxFileNaming()
    {
        $file_loc = self::copyTestAttachment('eastern.jpg');
        $upload_dir = wp_upload_dir();
        $url_src = $upload_dir['url'] . '/eastern.jpg';
        $filename = Timber\ImageHelper::get_letterbox_file_url($url_src, 300, 500, '#FFFFFF');
        $this->assertEquals($upload_dir['url'] . '/eastern-lbox-300x500-FFFFFF.jpg', $filename);
    }

    public static function is_png($file)
    {
        $file = strtolower($file);
        if (strpos($file, '.png') > 0) {
            return true;
        }
        return false;
    }

    public static function is_gif($file)
    {
        $file = strtolower($file);
        if (strpos($file, '.gif') > 0) {
            return true;
        }
        return false;
    }

    public static function checkSize($file, $width, $height)
    {
        $size = getimagesize($file);
        if ($width === $size[0] && $height === $size[1]) {
            return true;
        }
        return false;
    }

    public static function checkChannel($channel, $base, $compare, $upper = false)
    {
        if ($base[$channel] === $base[$channel]) {
            return true;
        }
        if ($upper) {
            if (($base[$channel] <= $compare[$channel]) && ($compare[$channel] <= $upper[$channel])) {
                return true;
            }
        }
        return false;
    }

    public static function checkPixel($file, $x, $y, $color = false, $upper_color = false)
    {
        if (self::is_png($file)) {
            $image = imagecreatefrompng($file);
        } elseif (self::is_gif($file)) {
            $image = imagecreatefromgif($file);
        } else {
            $image = imagecreatefromjpeg($file);
        }
        $pixel_rgba = imagecolorat($image, $x, $y);
        $colors_of_file = imagecolorsforindex($image, $pixel_rgba);
        if ($upper_color) {
            $upper_colors = ImageOperation::hexrgb($upper_color);
        }
        $test_colors = ImageOperation::hexrgb($color);
        if (false === $color) {
            $alpha = ($pixel_rgba & 0x7F000000) >> 24;
            return $alpha === 127;
        }
        if (isset($upper_colors) && $upper_colors) {
            if (self::checkChannel('red', $test_colors, $colors_of_file, $upper_colors) &&
                self::checkChannel('green', $test_colors, $colors_of_file, $upper_colors) &&
                self::checkChannel('blue', $test_colors, $colors_of_file, $upper_colors)
            ) {
                return true;
            }
            return false;
        }
        if ($test_colors['red'] === $colors_of_file['red'] &&
             $test_colors['blue'] === $colors_of_file['blue'] &&
             $test_colors['green'] === $colors_of_file['green']) {
            return true;
        }
        return false;
    }

    public function getPixel($file, $x, $y)
    {
        if (self::is_png($file)) {
            $image = imagecreatefrompng($file);
        } elseif (self::is_gif($file)) {
            $image = imagecreatefromgif($file);
        } else {
            $image = imagecreatefromjpeg($file);
        }
        $rgb = imagecolorat($image, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        return ImageOperation::rgbhex($r, $g, $b);
    }

    /**
     * @group maybeSkipped
     */
    public function testPNGtoJPG()
    {
        if (!extension_loaded('gd')) {
            self::markTestSkipped('PNG to JPEG conversion test requires GD extension');
        }
        $file_loc = self::copyTestAttachment('eastern-trans.png');
        $upload_dir = wp_upload_dir();
        $new_file = Timber\ImageHelper::img_to_jpg($upload_dir['url'] . '/eastern-trans.png', '#FFFF00');
        $location_of_image = Timber\ImageHelper::get_server_location($new_file);
        $this->assertFileExists($location_of_image);
        $image = imagecreatefromjpeg($location_of_image);
        $pixel_rgb = imagecolorat($image, 1, 1);
        $colors = imagecolorsforindex($image, $pixel_rgb);
        $this->assertSame(255, $colors['red']);
        $this->assertSame(255, $colors['green']);
        $this->assertSame(0, $colors['blue']);
    }

    public function testImageDeletionSimilarNames()
    {
        $data = [];
        $data['size'] = [
            'width' => 500,
            'height' => 300,
        ];
        $upload_dir = wp_upload_dir();
        $file = self::copyTestAttachment('arch-2night.jpg');
        $data['test_image'] = $upload_dir['url'] . '/arch-2night.jpg';
        $data['crop'] = 'default';
        $arch_2night = Timber\ImageHelper::get_resize_file_path($data['test_image'], $data['size']['width'], $data['size']['height'], $data['crop']);
        Timber::compile('assets/image-test.twig', $data);

        $file = self::copyTestAttachment('arch.jpg');
        $data['test_image'] = $upload_dir['url'] . '/arch.jpg';
        $data['size'] = [
            'width' => 520,
            'height' => 250,
        ];
        $data['crop'] = 'left';
        $arch_regular = Timber\ImageHelper::get_resize_file_path($data['test_image'], $data['size']['width'], $data['size']['height'], $data['crop']);
        Timber::compile('assets/image-test.twig', $data);
        $this->assertFileExists($arch_regular);
        $this->assertFileExists($arch_2night);
        //Delte the regular arch image
        Timber\ImageHelper::delete_generated_files($file);
        //The child of the regular arch image should be like
        //poof-be-gone
        $this->assertFileDoesNotExist($arch_regular);
        //...but the night image remains!
        $this->assertFileExists($arch_2night);
    }

    public function testImageDeletion()
    {
        $data = [];
        $data['size'] = [
            'width' => 500,
            'height' => 300,
        ];
        $upload_dir = wp_upload_dir();
        $file = self::copyTestAttachment('city-museum.jpg');
        $data['test_image'] = $upload_dir['url'] . '/city-museum.jpg';
        $data['crop'] = 'default';
        Timber::compile('assets/image-test.twig', $data);
        $resized_500_file = Timber\ImageHelper::get_resize_file_path($data['test_image'], $data['size']['width'], $data['size']['height'], $data['crop']);
        $data['size'] = [
            'width' => 520,
            'height' => 250,
        ];
        $data['crop'] = 'left';
        Timber::compile('assets/image-test.twig', $data);
        $resized_520_file = Timber\ImageHelper::get_resize_file_path($data['test_image'], $data['size']['width'], $data['size']['height'], $data['crop']);
        //make sure it generated the sizes we're expecting
        $this->assertFileExists($resized_500_file);
        $this->assertFileExists($resized_520_file);
        //Now delete the "parent" image
        Timber\ImageHelper::delete_generated_files($file);
        //Have the children been deleted as well?
        $this->assertFileDoesNotExist($resized_520_file);
        $this->assertFileDoesNotExist($resized_500_file);
    }

    public function testImageDeletionByURL()
    {
        $post_id = $this->factory->post->create();
        $filename = self::copyTestAttachment('flag.png');
        $wp_filetype = wp_check_filetype(basename($filename), null);
        $attachment = [
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
            'post_content' => '',
            'post_status' => 'inherit',
        ];
        $attach_id = wp_insert_attachment($attachment, $filename, $post_id);
        $data = [];
        $data['size'] = [
            'width' => 500,
            'height' => 300,
        ];
        $upload_dir = wp_upload_dir();
        $data['test_image'] = $upload_dir['url'] . '/flag.png';
        $data['crop'] = 'default';
        Timber::compile('assets/image-test.twig', $data);
        $resized_500_file = Timber\ImageHelper::get_resize_file_path($data['test_image'], $data['size']['width'], $data['size']['height'], $data['crop']);
        $data['size'] = [
            'width' => 520,
            'height' => 250,
        ];
        $data['crop'] = 'left';
        Timber::compile('assets/image-test.twig', $data);
        $resized_520_file = Timber\ImageHelper::get_resize_file_path($data['test_image'], $data['size']['width'], $data['size']['height'], $data['crop']);
        //make sure it generated the sizes we're expecting
        $this->assertFileExists($resized_500_file);
        $this->assertFileExists($resized_520_file);
        //Now delete the "parent" image
        Timber\ImageHelper::delete_generated_files($data['test_image']);
        //Have the children been deleted as well?
        $this->assertFileDoesNotExist($resized_520_file);
        $this->assertFileDoesNotExist($resized_500_file);
    }

    public function testImageDeletionByDeletingAttachment()
    {
        $post_id = $this->factory->post->create();
        $filename = self::copyTestAttachment('flag.png');
        $wp_filetype = wp_check_filetype(basename($filename), null);
        $attachment = [
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
            'post_content' => '',
            'post_status' => 'inherit',
        ];
        $attach_id = wp_insert_attachment($attachment, $filename, $post_id);
        $data = [];
        $data['size'] = [
            'width' => 500,
            'height' => 300,
        ];
        $upload_dir = wp_upload_dir();
        $data['test_image'] = $upload_dir['url'] . '/flag.png';
        $data['crop'] = 'default';
        Timber::compile('assets/image-test.twig', $data);
        $resized_500_file = Timber\ImageHelper::get_resize_file_path($data['test_image'], $data['size']['width'], $data['size']['height'], $data['crop']);
        $data['size'] = [
            'width' => 520,
            'height' => 250,
        ];
        $data['crop'] = 'left';
        Timber::compile('assets/image-test.twig', $data);
        $resized_520_file = Timber\ImageHelper::get_resize_file_path($data['test_image'], $data['size']['width'], $data['size']['height'], $data['crop']);
        //make sure it generated the sizes we're expecting
        $this->assertFileExists($resized_500_file);
        $this->assertFileExists($resized_520_file);
        //Now delete the "parent" image
        wp_delete_attachment($attach_id);
        //Have the children been deleted as well?
        $this->assertFileDoesNotExist($resized_520_file);
        $this->assertFileDoesNotExist($resized_500_file);
    }

    public function testImageDeletionByAttachmentLocation()
    {
        $post_id = $this->factory->post->create();
        $filename = self::copyTestAttachment('flag.png');
        $wp_filetype = wp_check_filetype(basename($filename), null);
        $attachment = [
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
            'post_content' => '',
            'post_status' => 'inherit',
        ];
        $attach_id = wp_insert_attachment($attachment, $filename, $post_id);
        $data = [];
        $data['size'] = [
            'width' => 500,
            'height' => 300,
        ];
        $upload_dir = wp_upload_dir();
        $data['test_image'] = $upload_dir['url'] . '/flag.png';
        $data['crop'] = 'default';
        Timber::compile('assets/image-test.twig', $data);
        $resized_500_file = Timber\ImageHelper::get_resize_file_path($data['test_image'], $data['size']['width'], $data['size']['height'], $data['crop']);
        $data['size'] = [
            'width' => 520,
            'height' => 250,
        ];
        $data['crop'] = 'left';
        Timber::compile('assets/image-test.twig', $data);
        $resized_520_file = Timber\ImageHelper::get_resize_file_path($data['test_image'], $data['size']['width'], $data['size']['height'], $data['crop']);
        //make sure it generated the sizes we're expecting
        $this->assertFileExists($resized_500_file);
        $this->assertFileExists($resized_520_file);
        //Now delete the "parent" image
        $post = Timber::get_post($attach_id);
        Timber\ImageHelper::delete_generated_files($post->file_loc);
        //Have the children been deleted as well?
        $this->assertFileDoesNotExist($resized_520_file);
        $this->assertFileDoesNotExist($resized_500_file);
    }

    /**
     *
     * @group maybeSkipped
     */
    public function testLetterboxImageDeletion()
    {
        if (!extension_loaded('gd')) {
            self::markTestSkipped('Letterbox image test requires GD extension');
        }
        $data = [];
        $file = self::copyTestAttachment('city-museum.jpg');
        $upload_dir = wp_upload_dir();
        $data['test_image'] = $upload_dir['url'] . '/city-museum.jpg';
        $new_file = Timber\ImageHelper::letterbox($data['test_image'], 500, 500, '#00FF00');
        $letterboxed_file = Timber\ImageHelper::get_letterbox_file_path($data['test_image'], 500, 500, '#00FF00');
        $this->assertFileExists($letterboxed_file);
        //Now delete the "parent" image
        Timber\ImageHelper::delete_generated_files($file);
        //Have the children been deleted as well?
        $this->assertFileDoesNotExist($letterboxed_file);
    }

    public function testGetAttachmentByInTwig()
    {
        $attachment = self::get_timber_attachment_object('arch.jpg');

        $src = Timber::compile_string('{{ get_attachment_by("url", url).src }}', [
            'url' => $attachment->src(),
        ]);

        $this->assertEquals('arch.jpg', basename($src));
    }

    public function testResizeNamed()
    {
        add_image_size('timber-testResizeNamed', $width = 600, $height = 400, $crop = true);
        $data = [];
        $data['size'] = 'timber-testResizeNamed';
        $upload_dir = wp_upload_dir();
        self::copyTestAttachment();
        $url = $upload_dir['url'] . '/arch.jpg';
        $data['test_image'] = $url;
        Timber::compile('assets/image-resize-named.twig', $data);
        $resized_path = $upload_dir['path'] . '/arch-' . $width . 'x' . $height . '-c-default.jpg';
        $this->assertFileExists($resized_path);
        //Now make sure it doesn't regenerate
        $old_time = filemtime($resized_path);
        sleep(1);
        Timber::compile('assets/image-resize-named.twig', $data);
        $new_time = filemtime($resized_path);
        $this->assertEquals($old_time, $new_time);
    }

    public function testBogusResizeNamed()
    {
        $data = [];
        $data['size'] = 'timber-foobar';
        $upload_dir = wp_upload_dir();
        self::copyTestAttachment();
        $url = $upload_dir['url'] . '/arch.jpg';
        $data['test_image'] = $url;
        $result = Timber::compile('assets/image-resize-named.twig', $data);
        $this->assertEquals('<img src="' . $url . '" />', trim($result));
    }

    public function testPostThumbnailsNamed()
    {
        add_image_size('timber-testPostThumbnailsNamed', $width = 100, $height = 50, $crop = true);
        $upload_dir = wp_upload_dir();
        $post_id = $this->factory->post->create();
        $filename = self::copyTestAttachment('flag.png');
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
        $data['size'] = 'timber-testPostThumbnailsNamed';
        Timber::compile('assets/image-thumb-named.twig', $data);
        $resized_path = $upload_dir['path'] . '/flag-' . $width . 'x' . $height . '-c-default.png';
        $this->assertFileExists($resized_path);
    }

    public function testPostThumbnailsWithWPName()
    {
        $upload_dir = wp_upload_dir();
        $post_id = $this->factory->post->create();
        $filename = self::copyTestAttachment('flag.png');
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
        $data['size'] = 'medium';
        $result = Timber::compile('assets/image-thumb-named.twig', $data);
        $filename = 'flag-300x300-c-default.png';
        $resized_path = $upload_dir['path'] . '/' . $filename;
        $this->assertFileExists($resized_path);
        $this->assertEquals('<img src="' . $upload_dir['url'] . '/' . $filename . '" />', trim($result));
    }

    public function testImageSizeWithWPNameUsingNative()
    {
        require_once('wp-overrides.php');
        $filename = __DIR__ . '/assets/tom-brady.jpg';
        $filesize = filesize($filename);
        $data = [
            'tmp_name' => $filename,
            'name' => 'tom-brady.jpg',
            'type' => 'image/jpg',
            'size' => $filesize,
            'error' => 0,
        ];
        $this->assertTrue(file_exists($filename));
        $_FILES['tester'] = $data;
        $file_id = WP_Overrides::media_handle_upload('tester', 0, [], [
            'test_form' => false,
        ]);
        if (!is_int($file_id)) {
            error_log(print_r($file_id, true));
        }
        $image = Timber::get_post($file_id);
        $str = '<img src="{{image.src(\'medium\')}}" />';
        $result = Timber::compile_string($str, [
            'image' => $image,
        ]);
        $upload_dir = wp_upload_dir();
        $this->assertEquals('<img src="' . $upload_dir['url'] . '/' . $image->sizes['medium']['file'] . '" />', trim($result));
    }

    public function testImageSizeWithWPNameUsingNativeGif()
    {
        require_once('wp-overrides.php');
        $filename = __DIR__ . '/assets/boyer.gif';
        $filesize = filesize($filename);
        $data = [
            'tmp_name' => $filename,
            'name' => 'boyer.gif',
            'type' => 'image/gif',
            'size' => $filesize,
            'error' => 0,
        ];
        $this->assertTrue(file_exists($filename));
        $_FILES['tester'] = $data;
        $file_id = WP_Overrides::media_handle_upload('tester', 0, [], [
            'test_form' => false,
        ]);
        if (!is_int($file_id)) {
            error_log(print_r($file_id, true));
        }
        $image = Timber::get_post($file_id);
        $str = '<img src="{{image.src(\'medium\')}}" />';
        $result = Timber::compile_string($str, [
            'image' => $image,
        ]);
        $upload_dir = wp_upload_dir();
        $this->assertEquals('<img src="' . $upload_dir['url'] . '/' . $image->sizes['medium']['file'] . '" />', trim($result));
    }

    /**
     * @group maybeSkipped
     */
    public function testGifToJpg()
    {
        if (!extension_loaded('gd')) {
            self::markTestSkipped('JPEG conversion test requires GD extension');
        }
        $filename = self::copyTestAttachment('loading.gif');
        $gif_url = str_replace(ABSPATH, 'http://' . $_SERVER['HTTP_HOST'] . '/', $filename);
        $str = '<img src="{{' . "'$gif_url'" . '|tojpg}}" />';
        $result = Timber::compile_string($str);
        $jpg_url = str_replace('.gif', '.jpg', $gif_url);
        $this->assertEquals('<img src="' . $jpg_url . '" />', $result);
    }

    public function testImageHelperInit()
    {
        $helper = Timber\ImageHelper::init();
        $this->assertTrue($helper);
    }

    /**
     * @group maybeSkipped
     */
    public function testResizeGif()
    {
        if (!extension_loaded('imagick')) {
            self::markTestSkipped('Animated GIF resizing test requires Imagick extension');
        }
        $filename = self::copyTestAttachment('loading.gif');
        $gif_url = str_replace(ABSPATH, 'http://' . $_SERVER['HTTP_HOST'] . '/', $filename);
        $str = '<img src="{{' . "'$gif_url'" . '|resize(200)}}" />';
        $result = Timber::compile_string($str);
        $resized_url = str_replace('loading.gif', 'loading-200x0-c-default.gif', $gif_url);
        $resized_path = str_replace('http://example.org', ABSPATH, $resized_url);
        $resized_path = Timber\URLHelper::remove_double_slashes($resized_path);
        $this->assertFileExists($resized_path);
    }

    public function testImageNoParent()
    {
        $this->assertNull($this->get_timber_attachment_object()->parent());
    }

    public function testImageParent()
    {
        $post = $this->get_post_with_image();
        $image = $post->thumbnail();
        $this->assertEquals($post->ID, $image->parent()->ID);
    }

    public function testTimberImageFromTimberImage()
    {
        $post = $this->get_post_with_image();
        $image = $post->thumbnail();
        $str = '{{ get_post(post).src }}';
        $post = Timber::get_post($image);
        $result = Timber::compile_string($str, [
            'post' => $post,
        ]);
        $this->assertEquals($image->src(), $result);
    }

    public function testTimberImageFromTimberImageID()
    {
        $post = $this->get_post_with_image();
        $image = $post->thumbnail();
        $str = '{{ get_post(post).src }}';
        $post = Timber::get_post($image->ID);
        $result = Timber::compile_string($str, [
            'post' => $post,
        ]);
        $this->assertEquals($image->src(), $result);
    }

    public function testTimberImageFromImageID()
    {
        $post = $this->get_post_with_image();
        $image = $post->thumbnail();
        $post = $image->ID;
        $str = '{{ get_post(post).src }}';
        $result = Timber::compile_string($str, [
            'post' => $post,
        ]);
        $this->assertEquals($image->src(), $result);
    }

    public function testNoThumbnail()
    {
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $str = Timber::compile_string('Image?{{post.thumbnail.src}}', [
            'post' => $post,
        ]);
        $this->assertEquals('Image?', $str);
    }

    public function testFilteredImageURL()
    {
        add_filter('wp_get_attachment_image_src', function ($image, $id, $size, $icon) {
            $image = str_replace('jpg', 'jpeg', $image);
            return $image;
        }, 10, 4);
        $post = $this->get_post_with_image();
        $image = $post->thumbnail();
        $str = '{{ post.thumbnail.src }}';
        $result = Timber::compile_string($str, [
            'post' => $post,
        ]);
        $this->assertEquals('http://example.org/wp-content/uploads/' . date('Y/m') . '/arch.jpeg', $result);
    }

    public function testTimberImageForExtraSlashes()
    {
        add_filter('upload_dir', [$this, '_filter_upload'], 10, 1);

        $post = $this->get_post_with_image();
        $image = $post->thumbnail();

        $resized_520_file = Timber\ImageHelper::resize($image->src, 520, 500);

        remove_filter('upload_dir', [$this, '_filter_upload']);

        $this->assertFalse(strpos($resized_520_file, '//arch-520x500-c-default.jpg') > -1);
    }

    public function _filter_upload($data)
    {
        $data['path'] = $data['basedir'];
        $data['url'] = $data['baseurl'];

        return $data;
    }

    public function testAnimagedGifResizeWithoutImagick()
    {
        $this->expectException(Twig\Error\RuntimeError::class);
        define('TEST_NO_IMAGICK', true);
        $image = self::copyTestAttachment('robocop.gif');
        $data = [
            'crop' => 'default',
        ];
        $data['size'] = [
            'width' => 90,
            'height' => 90,
        ];
        $upload_dir = wp_upload_dir();
        $url = $upload_dir['url'] . '/robocop.gif';
        $data['test_image'] = $url;
        $str = Timber::compile('assets/image-test.twig', $data);
        $resized_path = $upload_dir['path'] . '/robocop-' . $data['size']['width'] . 'x' . $data['size']['height'] . '-c-' . $data['crop'] . '.gif';
        $this->addFile($resized_path);
        $this->assertFileExists($resized_path);
        $this->assertFalse(TimberImageHelper::is_animated_gif($resized_path));
    }

    /**
     * Unlike raster (JPEG, PNG, etc.) SVG is vector-type file so resizing
     * shouldn't affect the file. Why is this necessary? B/C a user could have
     * uploaded an SVG or JPEG to a particular field and we need to handle
     * for either case.
     */
    public function testSVGResize()
    {
        $image = self::copyTestAttachment('icon-twitter.svg');
        $data = [];
        $data['size'] = [
            'width' => 100,
            'height' => 50,
        ];
        $upload_dir = wp_upload_dir();
        $data['test_image'] = $upload_dir['url'] . '/icon-twitter.svg';
        $str = Timber::compile('assets/image-test.twig', $data);
        $this->assertEquals('<img src="http://example.org/wp-content/uploads/' . date('Y/m') . '/icon-twitter.svg" />', trim($str));
    }

    public function testSVGLetterbox()
    {
        $image = self::copyTestAttachment('icon-twitter.svg');
        $data = [];
        $data['size'] = [
            'width' => 100,
            'height' => 50,
        ];
        $upload_dir = wp_upload_dir();
        $data['test_image'] = $upload_dir['url'] . '/icon-twitter.svg';
        $str = Timber::compile_string('<img src="{{ test_image|letterbox(size.width, size.height) }}" />', $data);
        $this->assertEquals('<img src="http://example.org/wp-content/uploads/' . date('Y/m') . '/icon-twitter.svg" />', trim($str));
    }

    public function testSVGRetina()
    {
        $image = self::copyTestAttachment('icon-twitter.svg');
        $data = [];
        $upload_dir = wp_upload_dir();
        $data['test_image'] = $upload_dir['url'] . '/icon-twitter.svg';
        $str = Timber::compile_string('<img src="{{ test_image|retina(2) }}" />', $data);
        $this->assertEquals('<img src="http://example.org/wp-content/uploads/' . date('Y/m') . '/icon-twitter.svg" />', trim($str));
    }

    public function testSVGtoJPG()
    {
        $image = self::copyTestAttachment('icon-twitter.svg');
        $data = [];
        $upload_dir = wp_upload_dir();
        $data['test_image'] = $upload_dir['url'] . '/icon-twitter.svg';
        $str = Timber::compile_string('<img src="{{ test_image|tojpg }}" />', $data);
        $this->assertEquals('<img src="http://example.org/wp-content/uploads/' . date('Y/m') . '/icon-twitter.svg" />', trim($str));
    }

    public function testSVGDimensions()
    {
        $pid = $this->factory->post->create();
        $image = Timber::get_image(self::get_attachment($pid, 'icon-twitter.svg'));
        $this->assertSame(23, $image->width());
        $this->assertSame(20, $image->height());
    }
}
