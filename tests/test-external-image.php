<?php

/**
 * @group image
 */
class TestExternalImage extends TimberAttachment_UnitTestCase
{
    public function set_up()
    {
        switch_theme('timber-test-theme');

        parent::set_up();
    }

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

        switch_theme('default');

        parent::tear_down();
    }

    public static function copy_image_to_stylesheet($target_dir = '', $filename = 'cardinals.jpg')
    {
        if (!empty($target_dir)) {
            $target_dir = trailingslashit($target_dir);
        }

        $source = __DIR__ . '/assets/' . $filename;
        $dest_dir = trailingslashit(get_stylesheet_directory()) . $target_dir;
        $dest = $dest_dir . $filename;

        if (!file_exists($dest_dir)) {
            mkdir($dest_dir, 0777, true);
        }

        if (str_starts_with($dest, 'http')) {
            $dest = Timber\URLHelper::url_to_file_system($dest);
        }

        copy($source, $dest);

        return $dest;
    }

    public function delete_existing_sideloaded_image($file)
    {
        // @see \Timber\ImageHelper::sideload_image()
        add_filter('upload_dir', [Timber\ImageHelper::class, 'set_sideload_image_upload_dir']);

        $file_loc = Timber\ImageHelper::get_sideloaded_file_loc($file);

        if (file_exists($file_loc)) {
            unlink($file_loc);
        }

        remove_filter('upload_dir', [Timber\ImageHelper::class, 'set_sideload_image_upload_dir']);
    }

    public function testExternalImageWithInvalidUrl()
    {
        $image = Timber::get_external_image(78);

        $this->assertNull($image);
    }

    public function testExternalImageWithAbsolutePath()
    {
        $dest = self::copy_image_to_stylesheet('assets/images');
        $this->addFile($dest);
        $this->assertFileExists($dest);

        $image = Timber::get_external_image($dest);

        $this->assertSame(
            'http://example.org/wp-content/themes/timber-test-theme/assets/images/cardinals.jpg',
            $image->src()
        );

        $this->assertSame(
            'http://example.org/wp-content/themes/timber-test-theme/assets/images/cardinals.jpg',
            $image->src('medium')
        );
    }

    public function testExternalImageWithRelativePath()
    {
        $dest = self::copy_image_to_stylesheet('assets/images');
        $this->addFile($dest);
        $this->assertFileExists($dest);

        $image = Timber::get_external_image('/wp-content/themes/timber-test-theme/assets/images/cardinals.jpg');
        $image2 = Timber::get_external_image('wp-content/themes/timber-test-theme/assets/images/cardinals.jpg');

        $this->assertSame(
            'http://example.org/wp-content/themes/timber-test-theme/assets/images/cardinals.jpg',
            $image->src()
        );
        $this->assertSame(
            'http://example.org/wp-content/themes/timber-test-theme/assets/images/cardinals.jpg',
            $image2->src()
        );
    }

    public function testExternalImageWithUrl()
    {
        $dest = self::copy_image_to_stylesheet('assets/images');
        $this->addFile($dest);
        $this->assertFileExists($dest);

        $image = Timber::get_external_image(
            'http://example.org/wp-content/themes/timber-test-theme/assets/images/cardinals.jpg'
        );

        $this->assertSame(
            'http://example.org/wp-content/themes/timber-test-theme/assets/images/cardinals.jpg',
            $image->src()
        );
    }

    public function testExternalImageWithExternalUrl()
    {
        $filename = 'acGwPDj4_400x400';
        $image = Timber::get_external_image(
            'https://pbs.twimg.com/profile_images/768086933310476288/' . $filename . '.jpg'
        );

        $this->assertSame(
            'http://example.org/wp-content/uploads/external/634489eb6a8b95c9ef9fac9b119bd92a.jpg',
            $image->src()
        );
    }

    public function testExternalImageWithExternalUrlAndNoImageExtension()
    {
        $file = 'https://placehold.co/600x400';
        $filename = basename(Timber\ImageHelper::get_sideloaded_file_loc($file));
        $this->delete_existing_sideloaded_image($file);

        $image = Timber::get_external_image($file);

        $this->assertSame(
            'http://example.org/wp-content/uploads/external/' . $filename,
            $image->src()
        );
    }

    public function testExternalImageWithNonExistentImage()
    {
        $dest = trailingslashit(get_stylesheet_directory()) . 'non-existent-image.jpg';
        $this->assertFileDoesNotExist($dest);

        $image = Timber::get_external_image($dest);

        $this->assertSame(
            'http://example.org/wp-content/themes/timber-test-theme/non-existent-image.jpg',
            $image->src()
        );

        $this->assertNull($image->width());
        $this->assertNull($image->height());
        $this->assertNull($image->aspect());
    }

    public function testExternalImagePath()
    {
        $dest = self::copy_image_to_stylesheet('assets/images');
        $this->addFile($dest);
        $this->assertFileExists($dest);

        $image = Timber::get_external_image($dest);

        $this->assertSame(
            'wp-content/themes/timber-test-theme/assets/images/cardinals.jpg',
            $image->path()
        );
    }

    public function testExternalImageToString()
    {
        $dest = self::copy_image_to_stylesheet('assets/images');
        $this->addFile($dest);
        $this->assertFileExists($dest);

        $image = Timber::get_external_image($dest);

        $result = Timber::compile_string('{{ image }}', [
            'image' => $image,
        ]);
        $this->assertSame('http://example.org/wp-content/themes/timber-test-theme/assets/images/cardinals.jpg', $result);
    }

    public function testExternalImageSize()
    {
        $dest = self::copy_image_to_stylesheet('assets/images');
        $this->addFile($dest);
        $this->assertFileExists($dest);

        $image = Timber::get_external_image($dest);

        $this->assertSame(54996, $image->size());
    }

    public function testExternalImageExtension()
    {
        $dest = self::copy_image_to_stylesheet('assets/images');
        $this->addFile($dest);
        $this->assertFileExists($dest);

        $image = Timber::get_external_image($dest);

        $this->assertSame('jpg', $image->extension());
    }

    public function testExternalImageDimensions()
    {
        $dest = self::copy_image_to_stylesheet('assets/images');
        $this->addFile($dest);
        $this->assertFileExists($dest);

        $image = Timber::get_external_image($dest);

        $this->assertSame(200, $image->width());
        $this->assertSame(200, $image->height());
        $this->assertSame(1.0, $image->aspect());
    }

    public function testExternalImageDimensionsSvg()
    {
        $dest = self::copy_image_to_stylesheet('assets/images', 'icon-twitter.svg');
        $this->addFile($dest);
        $this->assertFileExists($dest);

        $image = Timber::get_external_image($dest);

        $this->assertSame(23, $image->width());
        $this->assertSame(20, $image->height());
        $this->assertSame(1.15, $image->aspect());
    }

    public function testExternalImageDimensionsSvgViewbox()
    {
        $dest = self::copy_image_to_stylesheet('assets/images', 'timber-logo.svg');
        $this->addFile($dest);
        $this->assertFileExists($dest);

        $image = Timber::get_external_image($dest);

        $this->assertSame(531, $image->width());
        $this->assertSame(158, $image->height());
        $this->assertSame(3.3607594936708862, $image->aspect());
    }

    public function testExternalImageAlt()
    {
        $dest = self::copy_image_to_stylesheet('assets/images');
        $this->addFile($dest);
        $this->assertFileExists($dest);

        $image = Timber::get_external_image($dest);
        $this->assertSame(null, $image->alt());

        $image = Timber::get_external_image($dest, [
            'alt' => 'Cardinals logo',
        ]);
        $this->assertSame('Cardinals logo', $image->alt());

        $image->set_alt('Cardinals logo with a cardinal');
        $this->assertSame('Cardinals logo with a cardinal', $image->alt());
    }

    public function testExternalImageCaption()
    {
        $dest = self::copy_image_to_stylesheet('assets/images');
        $this->addFile($dest);
        $this->assertFileExists($dest);

        $image = Timber::get_external_image($dest);
        $this->assertSame(null, $image->caption());

        $image = Timber::get_external_image($dest, [
            'caption' => 'Cardinals logo',
        ]);
        $this->assertSame('Cardinals logo', $image->caption());

        $image->set_caption('Cardinals logo with a cardinal');
        $this->assertSame('Cardinals logo with a cardinal', $image->caption());
    }

    public function testThemeImageResize()
    {
        $dest = self::copy_image_to_stylesheet();
        $this->addFile($dest);
        $this->assertFileExists($dest);

        $image = get_stylesheet_directory() . '/cardinals.jpg';
        $image = str_replace('http://example.org', '', $image);
        $str = Timber::compile('assets/image-test.twig', [
            'test_image' => $image,
            'size' => [
                'width' => 120,
                'height' => 120,
            ],
        ]);

        $file_location = get_stylesheet_directory_uri() . '/cardinals-120x120-c-default.jpg';
        if (str_starts_with($file_location, 'http')) {
            $file_location = Timber\URLHelper::url_to_file_system($file_location);
        }
        $file_location = self::maybe_realpath($file_location);

        $this->assertFileExists($file_location);
        $this->addFile($file_location);
    }

    /**
     * @group maybeSkipped
     */
    public function testThemeImageLetterbox()
    {
        if (!extension_loaded('gd')) {
            self::markTestSkipped('Letterbox image test requires GD extension');
        }

        $dest = self::copy_image_to_stylesheet();
        $this->addFile($dest);
        $this->assertFileExists($dest);

        $image = get_stylesheet_directory() . '/cardinals.jpg';
        $image = str_replace('http://example.org', '', $image);
        $letterboxed = Timber\ImageHelper::letterbox($image, 600, 300, '#FF0000');

        $file_location = realpath(get_template_directory() . '/cardinals-lbox-600x300-FF0000.jpg');

        $this->assertFileExists($file_location);
        $this->addFile($file_location);
    }

    public function maybe_realpath($path)
    {
        if (realpath($path)) {
            return realpath($path);
        }
        return $path;
    }
}
