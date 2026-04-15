<?php

namespace Timber\Tests\Image\Operation;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Timber\ImageHelper;
use Timber\Tests\TimberIntegrationTestCase;

/**
 * Tests for image operation validation.
 *
 * These tests verify that all image operations (resize, retina, letterbox, tojpg, towebp)
 * properly validate input files and fail gracefully when given non-image files.
 *
 * @see https://github.com/timber/timber/issues/3231
 */
#[Group('integrations')]
class ValidationTest extends TimberIntegrationTestCase
{
    public function set_up()
    {
        parent::set_up();
        if (!\extension_loaded('gd')) {
            self::markTestSkipped('Image operation tests require GD extension');
        }
    }

    /**
     * Provides different invalid file types to test.
     */
    public static function invalidFileProvider(): array
    {
        return [
            'HTML file' => ['page.html', '<html><body>This is a webpage</body></html>'],
            'Text file' => ['document.txt', 'This is a text file'],
            'PDF file' => ['document.pdf', '%PDF-1.4 fake pdf'],
            'PHP file' => ['script.php', '<?php echo "hello"; ?>'],
            'JSON file' => ['data.json', '{"key": "value"}'],
        ];
    }

    /**
     * Test that resize operation validates file extensions.
     */
    #[DataProvider('invalidFileProvider')]
    public function testResizeValidatesFileExtension(string $filename, string $content)
    {
        $upload_dir = \wp_get_upload_dir();

        // Ensure upload directory exists
        if (!\is_dir($upload_dir['path'])) {
            \wp_mkdir_p($upload_dir['path']);
        }

        $file_path = $upload_dir['path'] . '/' . $filename;
        \file_put_contents($file_path, $content);

        $result = ImageHelper::resize($file_path, 300, 200);

        // Should return the original source (failed gracefully)
        $this->assertEquals($file_path, $result, 'Resize should fail gracefully with ' . $filename);

        @\unlink($file_path);
    }

    /**
     * Test that retina operation validates file extensions.
     */
    #[DataProvider('invalidFileProvider')]
    public function testRetinaValidatesFileExtension(string $filename, string $content)
    {
        $upload_dir = \wp_get_upload_dir();

        // Ensure upload directory exists
        if (!\is_dir($upload_dir['path'])) {
            \wp_mkdir_p($upload_dir['path']);
        }

        $file_path = $upload_dir['path'] . '/' . $filename;
        \file_put_contents($file_path, $content);

        $result = ImageHelper::retina_resize($file_path, 2);

        // Should return the original source (failed gracefully)
        $this->assertEquals($file_path, $result, 'Retina should fail gracefully with ' . $filename);

        @\unlink($file_path);
    }

    /**
     * Test that letterbox operation validates file extensions.
     */
    #[DataProvider('invalidFileProvider')]
    public function testLetterboxValidatesFileExtension(string $filename, string $content)
    {
        $upload_dir = \wp_get_upload_dir();

        // Ensure upload directory exists
        if (!\is_dir($upload_dir['path'])) {
            \wp_mkdir_p($upload_dir['path']);
        }

        $file_path = $upload_dir['path'] . '/' . $filename;
        \file_put_contents($file_path, $content);

        $result = ImageHelper::letterbox($file_path, 300, 200, '#FF0000');

        // Should return the original source (failed gracefully)
        $this->assertEquals($file_path, $result, 'Letterbox should fail gracefully with ' . $filename);

        @\unlink($file_path);
    }

    /**
     * Test that tojpg operation validates file extensions.
     */
    #[DataProvider('invalidFileProvider')]
    public function testToJpgValidatesFileExtension(string $filename, string $content)
    {
        $upload_dir = \wp_get_upload_dir();

        // Ensure upload directory exists
        if (!\is_dir($upload_dir['path'])) {
            \wp_mkdir_p($upload_dir['path']);
        }

        $file_path = $upload_dir['path'] . '/' . $filename;
        \file_put_contents($file_path, $content);

        $result = ImageHelper::img_to_jpg($file_path, '#FFFFFF');

        // Should return the original source (failed gracefully)
        $this->assertEquals($file_path, $result, 'ToJpg should fail gracefully with ' . $filename);

        @\unlink($file_path);
    }

    /**
     * Test that towebp operation validates file extensions.
     */
    #[DataProvider('invalidFileProvider')]
    public function testToWebpValidatesFileExtension(string $filename, string $content)
    {
        $upload_dir = \wp_get_upload_dir();

        // Ensure upload directory exists
        if (!\is_dir($upload_dir['path'])) {
            \wp_mkdir_p($upload_dir['path']);
        }

        $file_path = $upload_dir['path'] . '/' . $filename;
        \file_put_contents($file_path, $content);

        $result = ImageHelper::img_to_webp($file_path, 80);

        // Should return the original source (failed gracefully)
        $this->assertEquals($file_path, $result, 'ToWebp should fail gracefully with ' . $filename);

        @\unlink($file_path);
    }

    /**
     * Test that resize handles missing files gracefully.
     */
    public function testResizeHandlesMissingFiles()
    {
        $upload_dir = \wp_get_upload_dir();
        $missing_file = $upload_dir['path'] . '/non-existent-image.jpg';

        $result = ImageHelper::resize($missing_file, 300, 200);
        $this->assertEquals($missing_file, $result, 'Resize should handle missing file');
    }

    /**
     * Test that retina handles missing files gracefully.
     */
    public function testRetinaHandlesMissingFiles()
    {
        $upload_dir = \wp_get_upload_dir();
        $missing_file = $upload_dir['path'] . '/non-existent-image.jpg';

        $result = ImageHelper::retina_resize($missing_file, 2);
        $this->assertEquals($missing_file, $result, 'Retina should handle missing file');
    }

    /**
     * Test that letterbox handles missing files gracefully.
     */
    public function testLetterboxHandlesMissingFiles()
    {
        $upload_dir = \wp_get_upload_dir();
        $missing_file = $upload_dir['path'] . '/non-existent-image.jpg';

        $result = ImageHelper::letterbox($missing_file, 300, 200, '#FF0000');
        $this->assertEquals($missing_file, $result, 'Letterbox should handle missing file');
    }

    /**
     * Test that tojpg handles missing files gracefully.
     */
    public function testToJpgHandlesMissingFiles()
    {
        $upload_dir = \wp_get_upload_dir();
        $missing_file = $upload_dir['path'] . '/non-existent-image.jpg';

        $result = ImageHelper::img_to_jpg($missing_file, '#FFFFFF');
        $this->assertEquals($missing_file, $result, 'ToJpg should handle missing file');
    }

    /**
     * Test that towebp handles missing files gracefully.
     */
    public function testToWebpHandlesMissingFiles()
    {
        $upload_dir = \wp_get_upload_dir();
        $missing_file = $upload_dir['path'] . '/non-existent-image.jpg';

        $result = ImageHelper::img_to_webp($missing_file, 80);
        $this->assertEquals($missing_file, $result, 'ToWebp should handle missing file');
    }

    /**
     * Test that valid images still work correctly after validation changes.
     */
    public function testValidImagesStillWork()
    {
        $image = $this->copyImageToUploads('arch.jpg');

        // Test that resize still works with valid images
        $resized = ImageHelper::resize($image, 300, 200);
        $this->assertNotEquals($image, $resized, 'Valid image should be resized');
        $this->assertStringContainsString('-300x200-c-', $resized, 'Resized image should have dimensions in filename');

        // Test that retina still works with valid images
        $retina = ImageHelper::retina_resize($image, 2, true);
        $this->assertNotEquals($image, $retina, 'Valid image should have retina version');
        $this->assertStringContainsString('@2x', $retina, 'Retina image should have @2x in filename');
    }

    /**
     * Test that file with no extension fails gracefully.
     */
    public function testFileWithNoExtensionFailsGracefully()
    {
        $upload_dir = \wp_get_upload_dir();

        // Ensure upload directory exists
        if (!\is_dir($upload_dir['path'])) {
            \wp_mkdir_p($upload_dir['path']);
        }

        $file_path = $upload_dir['path'] . '/no-extension-file';
        \file_put_contents($file_path, 'Some content');

        $result = ImageHelper::resize($file_path, 300, 200);
        $this->assertEquals($file_path, $result, 'File without extension should fail gracefully');

        @\unlink($file_path);
    }

    /**
     * Test that operations don't crash the site when given page URLs.
     *
     * This simulates the exact scenario from issue #3231.
     */
    public function testDoesNotCrashWithPageUrl()
    {
        // Simulate a page URL being passed (create an HTML file)
        $upload_dir = \wp_get_upload_dir();

        // Ensure upload directory exists
        if (!\is_dir($upload_dir['path'])) {
            \wp_mkdir_p($upload_dir['path']);
        }

        $html_file = $upload_dir['path'] . '/index.html';
        $html_content = <<<'HTML'
<!DOCTYPE html>
<html>
<head><title>Example Page</title></head>
<body>
    <h1>This is a page, not an image</h1>
    <p>Attempting to resize this should fail gracefully.</p>
</body>
</html>
HTML;
        \file_put_contents($html_file, $html_content);

        // This should NOT crash the site
        $result = ImageHelper::resize($html_file, 300, 200);

        // Should return the original file path
        $this->assertEquals($html_file, $result);

        // Verify the operation completed without throwing exceptions
        $this->assertTrue(true, 'Operation completed without crashing');

        @\unlink($html_file);
    }
}
