<?php

namespace Timber\Tests\Image\Operation;

use Timber\Image\Operation\ToWebp;
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

    public function testCollidingBasenamesStillCollideByDefault()
    {
        // Documents the default (filter off) behavior on purpose: this is the bug reported in
        // https://github.com/timber/timber/issues/2850, left unchanged for sites that don't
        // opt in via the timber/image/collision_safe_filenames filter, since fixing it
        // unconditionally would change the generated filename for every towebp conversion of
        // a non-webp source, not just colliding ones - see testCollidingBasenamesProduceDistinctWebp
        // below for the opt-in fix.
        $jpgFile = $this->copyImageToUploads('stl.jpg', 'collision.jpg');
        $pngFile = $this->copyImageToUploads('flag.png', 'collision.png');

        Timber::compile_string('{{file|towebp}}', [
            'file' => $jpgFile,
        ]);
        Timber::compile_string('{{file|towebp}}', [
            'file' => $pngFile,
        ]);

        $jpgRenamed = \str_replace('.jpg', '.webp', $jpgFile);
        $pngRenamed = \str_replace('.png', '.webp', $pngFile);

        $this->assertEquals($jpgRenamed, $pngRenamed);
    }

    public function testCollidingBasenamesProduceDistinctWebpWhenFilterEnabled()
    {
        // Two different source images that share a basename but differ only in extension
        // used to collide on the exact same destination filename (both became
        // "collision.webp"): whichever converted first "won", and the second image's
        // towebp call silently served the first image's cached webp content instead of
        // converting its own. See https://github.com/timber/timber/issues/2850. Fixed only
        // when a site opts in via the timber/image/collision_safe_filenames filter - see
        // testCollidingBasenamesStillCollideByDefault above for the (intentional) default.
        $this->add_filter_temporarily('timber/image/collision_safe_filenames', '__return_true');

        $jpgFile = $this->copyImageToUploads('stl.jpg', 'collision.jpg');
        $pngFile = $this->copyImageToUploads('flag.png', 'collision.png');

        Timber::compile_string('{{file|towebp}}', [
            'file' => $jpgFile,
        ]);
        Timber::compile_string('{{file|towebp}}', [
            'file' => $pngFile,
        ]);

        $jpgRenamed = \str_replace('.jpg', '-jpg.webp', $jpgFile);
        $pngRenamed = \str_replace('.png', '-png.webp', $pngFile);

        $this->assertNotEquals($jpgRenamed, $pngRenamed);
        $this->assertFileExists($jpgRenamed);
        $this->assertFileExists($pngRenamed);
        $this->assertEquals('image/webp', \mime_content_type($jpgRenamed));
        $this->assertEquals('image/webp', \mime_content_type($pngRenamed));
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

    public function testFilenameFallsBackToBareNameForExtensionlessSourceEvenWithFilterEnabled()
    {
        // ImageHelper::get_url_components() can hand filename() an empty $src_extension for a
        // source with no extension in its path - not hypothetical, ImageHelper has its own
        // prior fix for exactly this (see #2773 / commit 028f6ac0's
        // `isset($parts['extension']) ? ... : ''` fallback), and the sibling
        // Resize::filename() already treats a falsy $src_extension as nothing to append rather
        // than a real value. Without the same falsy-check guard here,
        // enabling timber/image/collision_safe_filenames would fold that empty string in
        // literally, producing "name-.webp" instead of falling back to the bare name the way
        // the already-webp case does above.
        //
        // Direct call rather than through Timber::compile_string() like the rest of this file:
        // an extensionless source can't be round-tripped through the full towebp filter here,
        // since ToWebp::run() derives its GD decoder from wp_check_filetype($load_filename),
        // which needs a real extension to identify the source format - the pipeline would fail
        // before ever reaching the point this test needs to check.
        $this->add_filter_temporarily('timber/image/collision_safe_filenames', '__return_true');

        $op = new ToWebp(80);
        $this->assertEquals('my-pic.webp', $op->filename('my-pic', ''));
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
