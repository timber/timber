<?php

class TestTimberImageToJPG extends Timber_UnitTestCase
{
    public function set_up()
    {
        parent::set_up();
        if (!extension_loaded('gd')) {
            self::markTestSkipped('JPEG conversion tests requires GD extension');
        }
    }

    /**
     * This should fail silently as opposed to throwing an exception
     * see #1383 and #1192
     */
    public function testTIFtoJPG()
    {
        $filename = TestTimberImage::copyTestAttachment('white-castle.tif');
        $str = Timber::compile_string('{{file|tojpg}}', [
            'file' => $filename,
        ]);
        $this->assertEquals($filename, $str);
        unlink($filename);
    }

    public function testPNGtoJPG()
    {
        $filename = TestTimberImage::copyTestAttachment('flag.png');
        $str = Timber::compile_string('{{file|tojpg}}', [
            'file' => $filename,
        ]);
        $renamed = str_replace('.png', '.jpg', $filename);
        $this->assertFileExists($renamed);
        $this->assertGreaterThan(1000, filesize($renamed));
        $this->assertEquals('image/png', mime_content_type($filename));
        $this->assertEquals('image/jpeg', mime_content_type($renamed));
        unlink($filename);
        unlink($renamed);
    }

    public function testGIFtoJPG()
    {
        $filename = TestTimberImage::copyTestAttachment('boyer.gif');
        $str = Timber::compile_string('{{file|tojpg}}', [
            'file' => $filename,
        ]);
        $renamed = str_replace('.gif', '.jpg', $filename);
        $this->assertFileExists($renamed);
        $this->assertGreaterThan(1000, filesize($renamed));
        $this->assertEquals('image/gif', mime_content_type($filename));
        $this->assertEquals('image/jpeg', mime_content_type($renamed));
        unlink($filename);
        unlink($renamed);
    }

    public function testJPGtoJPG()
    {
        $filename = TestTimberImage::copyTestAttachment('stl.jpg');
        $original_size = filesize($filename);
        $str = Timber::compile_string('{{file|tojpg}}', [
            'file' => $filename,
        ]);
        $new_size = filesize($filename);
        $this->assertEquals($original_size, $new_size);
        $this->assertEquals('image/jpeg', mime_content_type($filename));
        unlink($filename);
    }

    public function testJPEGtoJPG()
    {
        $filename = TestTimberImage::copyTestAttachment('jarednova.jpeg');
        $str = Timber::compile_string('{{file|tojpg}}', [
            'file' => $filename,
        ]);
        $renamed = str_replace('.jpeg', '.jpg', $filename);
        $this->assertFileExists($renamed);
        $this->assertGreaterThan(1000, filesize($renamed));
        $this->assertEquals('image/jpeg', mime_content_type($filename));
        $this->assertEquals('image/jpeg', mime_content_type($renamed));
        unlink($filename);
        unlink($renamed);
    }

    public function testSideloadedPNGToJPG()
    {
        $url = 'https://user-images.githubusercontent.com/2084481/31230351-116569a8-a9e4-11e7-8310-48b7f679892b.png';
        $sideloaded = Timber::compile_string('{{ file|tojpg }}', [
            'file' => $url,
        ]);

        $base_url = str_replace(basename($sideloaded), '', $sideloaded);
        $expected = $base_url . md5($url) . '.jpg';

        $this->assertEquals($expected, $sideloaded);
    }
}
