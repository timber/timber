<?php

/**
 * @group image
 */
class TestTimberImageAdvancedFileNames extends TimberAttachment_UnitTestCase
{
    public function testAdvancedFileNamesToJpeg()
    {
        $this->add_filter_temporarily('timber/image/advanced_file_names', '__return_true');

        $file = self::copyTestAttachment('city-museum.jpg');
        $src = Timber::compile_string('{{ file|tojpg }}', [
            'file' => $file,
        ]);
        $path = Timber\ImageHelper::get_server_location($src);

        $this->assertFileExists($path);
        $this->assertStringEndsWith('-tojpg.jpg', $path);

        Timber\ImageHelper::delete_generated_files($file);

        $this->assertFileDoesNotExist($path);
    }

    public function testAdvancedFileNamesToJpegResized()
    {
        $this->add_filter_temporarily('timber/image/advanced_file_names', '__return_true');

        $file = self::copyTestAttachment('city-museum.jpg');
        $src = Timber::compile_string('{{ file|tojpg|resize(200, 100) }}', [
            'file' => $file,
        ]);
        $path = Timber\ImageHelper::get_server_location($src);

        $this->assertFileExists($path);

        Timber\ImageHelper::delete_generated_files($file);

        $this->assertFileDoesNotExist($path);
    }

    public function testAdvancedFileNamesToJpegResizedInversed()
    {
        $this->add_filter_temporarily('timber/image/advanced_file_names', '__return_true');

        $file = self::copyTestAttachment('city-museum.jpg');
        $src = Timber::compile_string('{{ file|resize(200, 100)|tojpg }}', [
            'file' => $file,
        ]);
        $path = Timber\ImageHelper::get_server_location($src);

        $this->assertFileExists($path);

        Timber\ImageHelper::delete_generated_files($file);

        $this->assertFileDoesNotExist($path);
    }

    public function testAdvancedFileNamesToWebP()
    {
        $this->add_filter_temporarily('timber/image/advanced_file_names', '__return_true');

        $file = self::copyTestAttachment('city-museum.jpg');
        $src = Timber::compile_string('{{ file|towebp }}', [
            'file' => $file,
        ]);
        $path = Timber\ImageHelper::get_server_location($src);

        $this->assertFileExists($path);
        $this->assertStringEndsWith('-towebp.webp', $path);

        Timber\ImageHelper::delete_generated_files($file);

        $this->assertFileDoesNotExist($path);
    }

    public function testAdvancedFilesNamesWithScaledImage()
    {
        $this->add_filter_temporarily('timber/image/advanced_file_names', '__return_true');

        // Use a low image threshold.
        $this->add_filter_temporarily('big_image_size_threshold', function () {
            return 500;
        });

        $attachment_id = self::get_attachment(0, 'pizza.jpg');
        $attachment = Timber\Timber::get_attachment($attachment_id);

        $src = Timber::compile_string('{{ attachment.src|towebp }}', [
            'attachment' => $attachment,
        ]);

        $path = Timber\ImageHelper::get_server_location($src);

        $this->assertFileExists($path);
    }
}
