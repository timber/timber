<?php

namespace Timber\Tests\Image\Operation;

use Timber\Tests\TimberIntegrationTestCase;
use Timber\Timber;

class ToWebpTest extends TimberIntegrationTestCase
{
    public function set_up()
    {
        parent::set_up();
        if (!\function_exists('imagewebp')) {
            self::markTestSkipped('WEBP conversion tests requires imagewebp function');
        }
    }

    public function testTIFtoWEBP()
    {
        $filename = $this->copyImageToUploads('white-castle.tif');
        $str = Timber::compile_string('{{file|towebp}}', [
            'file' => $filename,
        ]);
        $this->assertEquals($filename, $str);
    }

    public function testPNGtoWEBP()
    {
        $filename = $this->copyImageToUploads('flag.png');
        $str = Timber::compile_string('{{file|towebp}}', [
            'file' => $filename,
        ]);
        $renamed = \str_replace('.png', '.webp', $filename);
        $this->assertFileExists($renamed);
        $this->assertGreaterThan(1000, \filesize($renamed));
        $this->assertEquals('image/png', \mime_content_type($filename));
        $this->assertEquals('image/webp', \mime_content_type($renamed));
    }

    public function testGIFtoJPG()
    {
        $filename = $this->copyImageToUploads('boyer.gif');
        $str = Timber::compile_string('{{file|towebp}}', [
            'file' => $filename,
        ]);
        $renamed = \str_replace('.gif', '.webp', $filename);
        $this->assertFileExists($renamed);
        $this->assertGreaterThan(1000, \filesize($renamed));
        $this->assertEquals('image/gif', \mime_content_type($filename));
        $this->assertEquals('image/webp', \mime_content_type($renamed));
    }

    public function testJPGtoWEBP()
    {
        $filename = $this->copyImageToUploads('stl.jpg');
        $original_size = \filesize($filename);
        $str = Timber::compile_string('{{file|towebp(100)}}', [
            'file' => $filename,
        ]);
        $renamed = \str_replace('.jpg', '.webp', $filename);
        $this->assertGreaterThan(1000, \filesize($renamed));
        $this->assertEquals('image/jpeg', \mime_content_type($filename));
        $this->assertEquals('image/webp', \mime_content_type($renamed));
    }

    public function testJPEGtoJPG()
    {
        $filename = $this->copyImageToUploads('jarednova.jpeg');
        $str = Timber::compile_string('{{file|towebp}}', [
            'file' => $filename,
        ]);
        $renamed = \str_replace('.jpeg', '.webp', $filename);
        $this->assertFileExists($renamed);
        $this->assertGreaterThan(1000, \filesize($renamed));
        $this->assertEquals('image/jpeg', \mime_content_type($filename));
        $this->assertEquals('image/webp', \mime_content_type($renamed));
    }

    public function testWEBPtoWEBP()
    {
        $filename = $this->copyImageToUploads('mountains.webp');
        $original_size = \filesize($filename);
        $str = Timber::compile_string('{{file|towebp}}', [
            'file' => $filename,
        ]);
        $new_size = \filesize($filename);
        $this->assertEquals($original_size, $new_size);
        $this->assertEquals('image/webp', \mime_content_type($filename));
    }

    public function testSideloadedJPGToWEBP()
    {
        $url = 'https://pbs.twimg.com/profile_images/768086933310476288/acGwPDj4_400x400.jpg';
        $sideloaded = Timber::compile_string('{{ file|towebp }}', [
            'file' => $url,
        ]);

        $base_url = \str_replace(\basename($sideloaded), '', $sideloaded);
        $expected = $base_url . \md5($url) . '.webp';

        $this->assertEquals($expected, $sideloaded);
    }

    public function testSideloadedPNGToWEBP()
    {
        $url = 'https://user-images.githubusercontent.com/2084481/31230351-116569a8-a9e4-11e7-8310-48b7f679892b.png';
        $sideloaded = Timber::compile_string('{{ file|towebp }}', [
            'file' => $url,
        ]);

        $base_url = \str_replace(\basename($sideloaded), '', $sideloaded);
        $expected = $base_url . \md5($url) . '.webp';

        $this->assertEquals($expected, $sideloaded);
    }
}
