<?php

namespace Timber\Tests\Image\Operation;

use Timber\Image\Operation\ToJpg;
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
        $renamed = \str_replace('.png', '.jpg', $filename);
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
        $renamed = \str_replace('.gif', '.jpg', $filename);
        $this->assertFileExists($renamed);
        $this->assertGreaterThan(1000, \filesize($renamed));
        $this->assertEquals('image/gif', \mime_content_type($filename));
        $this->assertEquals('image/jpeg', \mime_content_type($renamed));
        \unlink($filename);
        \unlink($renamed);
    }

    public function testCollidingBasenamesStillCollideByDefault()
    {
        // Documents the default behavior on purpose: without the
        // timber/image/collision_safe_filenames filter, collision.png and collision.gif both
        // become collision.jpg. This is the ToJpg counterpart of the ToWebp bug reported in
        // https://github.com/timber/timber/issues/2850. It stays unchanged by default, because
        // fixing it would rename every converted image, not only the colliding ones.
        $pngFile = $this->copyImageToUploads('flag.png', 'collision.png');
        $gifFile = $this->copyImageToUploads('boyer.gif', 'collision.gif');

        Timber::compile_string('{{file|tojpg}}', [
            'file' => $pngFile,
        ]);
        Timber::compile_string('{{file|tojpg}}', [
            'file' => $gifFile,
        ]);

        $pngRenamed = \str_replace('.png', '.jpg', $pngFile);
        $gifRenamed = \str_replace('.gif', '.jpg', $gifFile);

        $this->assertEquals($pngRenamed, $gifRenamed);
        \unlink($pngFile);
        \unlink($gifFile);
        \unlink($pngRenamed);
    }

    public function testCollidingBasenamesProduceDistinctJpgWhenFilterEnabled()
    {
        // With the timber/image/collision_safe_filenames filter enabled, collision.png and
        // collision.gif get their own JPGs (collision-png-<hash>.jpg and collision-gif-<hash>.jpg), instead
        // of the second one reusing the first one's collision.jpg. This is the ToJpg
        // counterpart of the ToWebp bug reported in https://github.com/timber/timber/issues/2850.
        $this->add_filter_temporarily('timber/image/collision_safe_filenames', '__return_true');

        $pngFile = $this->copyImageToUploads('flag.png', 'collision.png');
        $gifFile = $this->copyImageToUploads('boyer.gif', 'collision.gif');

        Timber::compile_string('{{file|tojpg}}', [
            'file' => $pngFile,
        ]);
        Timber::compile_string('{{file|tojpg}}', [
            'file' => $gifFile,
        ]);

        $pngRenamed = \str_replace('.png', ToJpg::collision_safe_suffix('collision', 'png') . '.jpg', $pngFile);
        $gifRenamed = \str_replace('.gif', ToJpg::collision_safe_suffix('collision', 'gif') . '.jpg', $gifFile);

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

    public function testJPGtoJPGKeepsBareNameWhenFilterEnabled()
    {
        // A JPG source is never renamed, even with the filter enabled: it would otherwise be
        // converted to a needless copy of itself.
        $this->add_filter_temporarily('timber/image/collision_safe_filenames', '__return_true');

        $filename = $this->copyImageToUploads('stl.jpg');
        $original_size = \filesize($filename);
        $str = Timber::compile_string('{{file|tojpg}}', [
            'file' => $filename,
        ]);

        $this->assertStringEndsWith('/stl.jpg', $str);
        $this->assertFileDoesNotExist(\str_replace('.jpg', ToJpg::collision_safe_suffix('stl', 'jpg') . '.jpg', $filename));
        $this->assertEquals($original_size, \filesize($filename));
    }

    public function testUppercaseExtensionIsLowercasedWhenFilterEnabled()
    {
        // ImageHelper::get_url_components() lowercases the extension before it reaches
        // ToJpg::filename(), so uppercase.PNG becomes uppercase-png-<hash>.jpg, not
        // uppercase-PNG-<hash>.jpg.
        $this->add_filter_temporarily('timber/image/collision_safe_filenames', '__return_true');

        $filename = $this->copyImageToUploads('flag.png', 'uppercase.PNG');
        $str = Timber::compile_string('{{file|tojpg}}', [
            'file' => $filename,
        ]);

        $suffix = ToJpg::collision_safe_suffix('uppercase', 'png');
        $renamed = \str_replace('.PNG', $suffix . '.jpg', $filename);
        $this->assertStringEndsWith('/uppercase' . $suffix . '.jpg', $str);
        $this->assertFileExists($renamed);
        $this->assertEquals('image/jpeg', \mime_content_type($renamed));
        \unlink($renamed);
    }

    public function testJPEGtoJPG()
    {
        $filename = $this->copyImageToUploads('jarednova.jpeg');
        $str = Timber::compile_string('{{file|tojpg}}', [
            'file' => $filename,
        ]);
        $renamed = \str_replace('.jpeg', '.jpg', $filename);
        $this->assertFileExists($renamed);
        $this->assertGreaterThan(1000, \filesize($renamed));
        $this->assertEquals('image/jpeg', \mime_content_type($filename));
        $this->assertEquals('image/jpeg', \mime_content_type($renamed));
        \unlink($filename);
        \unlink($renamed);
    }

    public function testFilenameKeepsBareNameForExtensionlessSource()
    {
        // ImageHelper::get_url_components() can hand filename() an empty $src_extension for a
        // source with no extension in its path - not hypothetical, ImageHelper has its own
        // prior fix for exactly this (see #2773 / commit 028f6ac0's
        // `isset($parts['extension']) ? ... : ''` fallback), and the sibling
        // Resize::filename() already treats a falsy $src_extension as nothing to append rather
        // than a real value.
        //
        // This guard sits before the timber/image/collision_safe_filenames check, so it's
        // unconditional - there's no "-<ext>" to fold in either way, which is why this test
        // doesn't toggle the filter (doing so would test nothing: this branch returns before
        // apply_filters() is ever called).
        //
        // Direct call rather than through Timber::compile_string() like the rest of this file:
        // an extensionless source can't be round-tripped through the full tojpg filter here,
        // since ToJpg::run() derives its GD decoder from wp_check_filetype($load_filename),
        // which needs a real extension to identify the source format - the pipeline would fail
        // before ever reaching the point this test needs to check.
        $op = new ToJpg('#000000');
        $this->assertEquals('my-pic.jpg', $op->filename('my-pic', ''));
    }

    public function testFilenameAddsExtensionAndHashWhenFilterEnabled()
    {
        // Pins the naming format: <name>-<extension>-<first 8 characters of md5("<name>.<extension>")>.jpg.
        // Changing it renames every converted image on sites that use the filter.
        $this->add_filter_temporarily('timber/image/collision_safe_filenames', '__return_true');

        $op = new ToJpg('#000000');
        $this->assertEquals('pic-png-13cdb71f.jpg', $op->filename('pic', 'png'));
    }

    public function testSideloadedPNGToJPG()
    {
        $url = 'https://user-images.githubusercontent.com/2084481/31230351-116569a8-a9e4-11e7-8310-48b7f679892b.png';
        $sideloaded = Timber::compile_string('{{ file|tojpg }}', [
            'file' => $url,
        ]);

        $base_url = \str_replace(\basename($sideloaded), '', $sideloaded);
        $expected = $base_url . \md5($url) . '.jpg';

        $this->assertEquals($expected, $sideloaded);
    }
}
