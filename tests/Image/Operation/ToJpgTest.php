<?php

namespace Timber\Tests\Image\Operation;

use Timber\Tests\TimberIntegrationTestCase;
use Timber\Timber;

class ToJpgTest extends TimberIntegrationTestCase
{
    public function set_up()
    {
        parent::set_up();
        if (!\extension_loaded('gd')) {
            self::markTestSkipped('JPEG conversion tests requires GD extension');
        }
    }

    /**
     * This should fail silently as opposed to throwing an exception
     * see #1383 and #1192
     */
    public function testTIFtoJPG()
    {
        $filename = $this->copyImageToUploads('white-castle.tif');
        $str = Timber::compile_string('{{file|tojpg}}', [
            'file' => $filename,
        ]);
        $this->assertEquals($filename, $str);
        \unlink($filename);
    }

    public function testPNGtoJPG()
    {
        $filename = $this->copyImageToUploads('flag.png');
        $str = Timber::compile_string('{{file|tojpg}}', [
            'file' => $filename,
        ]);
        $renamed = \str_replace('.png', '-png.jpg', $filename);
        $this->assertFileExists($renamed);
        $this->assertGreaterThan(1000, \filesize($renamed));
        $this->assertEquals('image/png', \mime_content_type($filename));
        $this->assertEquals('image/jpeg', \mime_content_type($renamed));
        \unlink($filename);
        \unlink($renamed);
    }

    public function testGIFtoJPG()
    {
        $filename = $this->copyImageToUploads('boyer.gif');
        $str = Timber::compile_string('{{file|tojpg}}', [
            'file' => $filename,
        ]);
        $renamed = \str_replace('.gif', '-gif.jpg', $filename);
        $this->assertFileExists($renamed);
        $this->assertGreaterThan(1000, \filesize($renamed));
        $this->assertEquals('image/gif', \mime_content_type($filename));
        $this->assertEquals('image/jpeg', \mime_content_type($renamed));
        \unlink($filename);
        \unlink($renamed);
    }

    public function testCollidingBasenamesProduceDistinctJpg()
    {
        // Two different source images that share a basename but differ only in extension
        // used to collide on the exact same destination filename (both became
        // "collision.jpg"): whichever converted first "won", and the second image's
        // tojpg call silently served the first image's cached jpg content instead of
        // converting its own. Same bug class as https://github.com/timber/timber/issues/2850,
        // in the sibling ToJpg operation.
        $pngFile = $this->copyImageToUploads('flag.png', 'collision.png');
        $gifFile = $this->copyImageToUploads('boyer.gif', 'collision.gif');

        Timber::compile_string('{{file|tojpg}}', [
            'file' => $pngFile,
        ]);
        Timber::compile_string('{{file|tojpg}}', [
            'file' => $gifFile,
        ]);

        $pngRenamed = \str_replace('.png', '-png.jpg', $pngFile);
        $gifRenamed = \str_replace('.gif', '-gif.jpg', $gifFile);

        $this->assertNotEquals($pngRenamed, $gifRenamed);
        $this->assertFileExists($pngRenamed);
        $this->assertFileExists($gifRenamed);
        $this->assertEquals('image/jpeg', \mime_content_type($pngRenamed));
        $this->assertEquals('image/jpeg', \mime_content_type($gifRenamed));
        \unlink($pngFile);
        \unlink($gifFile);
        \unlink($pngRenamed);
        \unlink($gifRenamed);
    }

    public function testJPGtoJPG()
    {
        $filename = $this->copyImageToUploads('stl.jpg');
        $original_size = \filesize($filename);
        $str = Timber::compile_string('{{file|tojpg}}', [
            'file' => $filename,
        ]);
        $new_size = \filesize($filename);
        $this->assertEquals($original_size, $new_size);
        $this->assertEquals('image/jpeg', \mime_content_type($filename));
        \unlink($filename);
    }

    public function testJPEGtoJPG()
    {
        $filename = $this->copyImageToUploads('jarednova.jpeg');
        $str = Timber::compile_string('{{file|tojpg}}', [
            'file' => $filename,
        ]);
        $renamed = \str_replace('.jpeg', '-jpeg.jpg', $filename);
        $this->assertFileExists($renamed);
        $this->assertGreaterThan(1000, \filesize($renamed));
        $this->assertEquals('image/jpeg', \mime_content_type($filename));
        $this->assertEquals('image/jpeg', \mime_content_type($renamed));
        \unlink($filename);
        \unlink($renamed);
    }

    public function testSideloadedPNGToJPG()
    {
        $url = 'https://user-images.githubusercontent.com/2084481/31230351-116569a8-a9e4-11e7-8310-48b7f679892b.png';
        $sideloaded = Timber::compile_string('{{ file|tojpg }}', [
            'file' => $url,
        ]);

        $base_url = \str_replace(\basename($sideloaded), '', $sideloaded);
        $expected = $base_url . \md5($url) . '-png.jpg';

        $this->assertEquals($expected, $sideloaded);
    }
}
