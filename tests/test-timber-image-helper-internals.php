<?php

class TestTimberImageHelperInternals extends TimberAttachment_UnitTestCase
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

    public function testAnalyzeURLUploads()
    {
        $src = 'http://' . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/myimage.jpg';

        $parts = Timber\ImageHelper::analyze_url($src);

        $this->assertEquals('http://' . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/myimage.jpg', $parts['url']);
        $this->assertSame(true, $parts['absolute']);
        $this->assertSame(1, $parts['base']);
        $this->assertSame('', $parts['subdir']);
        $this->assertEquals('myimage', $parts['filename']);
        $this->assertEquals('jpg', $parts['extension']);
        $this->assertEquals('myimage.jpg', $parts['basename']);
    }

    public function testAnalyzeURLUploadsWithDate()
    {
        $src = 'http://' . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/2017/02/myimage.jpg';

        $parts = Timber\ImageHelper::analyze_url($src);

        $this->assertEquals('http://' . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/2017/02/myimage.jpg', $parts['url']);
        $this->assertSame(true, $parts['absolute']);
        $this->assertSame(1, $parts['base']);
        $this->assertEquals('/2017/02', $parts['subdir']);
        $this->assertEquals('myimage', $parts['filename']);
        $this->assertEquals('jpg', $parts['extension']);
        $this->assertEquals('myimage.jpg', $parts['basename']);
    }

    public function testAnalyzeURLUploadsWithQuery()
    {
        $src = 'http://' . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/2017/02/myimage.jpg?foo=A&baz=B';

        $parts = Timber\ImageHelper::analyze_url($src);

        $this->assertEquals('http://' . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/2017/02/myimage.jpg?foo=A&baz=B', $parts['url']);
        $this->assertSame(true, $parts['absolute']);
        $this->assertSame(1, $parts['base']);
        $this->assertEquals('/2017/02', $parts['subdir']);
        $this->assertEquals('myimage', $parts['filename']);
        $this->assertEquals('jpg', $parts['extension']);
        $this->assertEquals('myimage.jpg', $parts['basename']);
    }

    public function testAnalyzeURLUploadsWithFragment()
    {
        $src = 'http://' . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/2017/02/myimage.jpg#foo';

        $parts = Timber\ImageHelper::analyze_url($src);

        $this->assertEquals('http://' . $_SERVER['HTTP_HOST'] . '/wp-content/uploads/2017/02/myimage.jpg#foo', $parts['url']);
        $this->assertSame(true, $parts['absolute']);
        $this->assertSame(1, $parts['base']);
        $this->assertEquals('/2017/02', $parts['subdir']);
        $this->assertEquals('myimage', $parts['filename']);
        $this->assertEquals('jpg', $parts['extension']);
        $this->assertEquals('myimage.jpg', $parts['basename']);
    }

    public function testAnalyzeURLTheme()
    {
        $dest = TestExternalImage::copy_image_to_stylesheet('assets/images');
        $this->addFile($dest);
        $this->assertFileExists($dest);

        $image = Timber::get_external_image($dest);
        $src = $image->src();

        $parts = Timber\ImageHelper::analyze_url($src);

        $this->assertEquals('http://example.org/wp-content/themes/timber-test-theme/assets/images/cardinals.jpg', $parts['url']);
        $this->assertTrue($parts['absolute']);
        $this->assertSame(2, $parts['base']);
        $this->assertEquals('/themes/timber-test-theme/assets/images', $parts['subdir']);
        $this->assertEquals('cardinals', $parts['filename']);
        $this->assertEquals('jpg', $parts['extension']);
        $this->assertEquals('cardinals.jpg', $parts['basename']);
    }
}
