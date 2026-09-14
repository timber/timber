<?php

namespace Timber\Tests\Image;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Timber\ImageDimensions;
use Timber\Tests\TimberIntegrationTestCase;

class ImageDimensionsTestable extends ImageDimensions
{
    public function __construct($file_loc)
    {
        parent::__construct($file_loc);
    }

    public function set_dimensions($width, $height)
    {
        $this->dimensions = [$width, $height];
    }
}

#[Group('image')]
class ImageDimensionsTest extends TimberIntegrationTestCase
{
    /**
     * SVG files written into the uploads directory by these tests.
     */
    private array $svg_files = [];

    public function tear_down()
    {
        foreach ($this->svg_files as $file) {
            if (\file_exists($file)) {
                \unlink($file);
            }
        }
        $this->svg_files = [];

        parent::tear_down();
    }

    public static function ratioProvider()
    {
        return [
            [200, 100, 2],
            [100, 200, 0.5],
        ];
    }

    #[DataProvider('ratioProvider')]
    public function testRatio($w, $h, $r)
    {
        $imageDimensions = new ImageDimensionsTestable('');
        $imageDimensions->set_dimensions($w, $h);

        $this->assertEquals($r, $imageDimensions->aspect());
    }

    public function testDimensions()
    {
        $imageDimensions = new ImageDimensionsTestable('');
        $imageDimensions->set_dimensions(100, 200);

        $this->assertEquals(100, $imageDimensions->width());
        $this->assertEquals(200, $imageDimensions->height());
    }

    public function testDimensionsFromAttachmentMetadata()
    {
        $attachment_id = $this->createAttachmentWithImage();

        // Override the metadata with known dimensions to confirm they come from metadata, not file.
        $meta = \wp_get_attachment_metadata($attachment_id);
        $meta['width'] = 640;
        $meta['height'] = 480;
        \wp_update_attachment_metadata($attachment_id, $meta);

        $imageDimensions = new ImageDimensions('', $attachment_id);

        $this->assertEquals(640, $imageDimensions->width());
        $this->assertEquals(480, $imageDimensions->height());
    }

    public function testDimensionsFallbackToFileWhenMetadataMissingDimensions()
    {
        $attachment_id = $this->createAttachmentWithImage();
        $file_loc = \get_attached_file($attachment_id);

        // Remove width/height from metadata to trigger the file fallback.
        $meta = \wp_get_attachment_metadata($attachment_id);
        unset($meta['width'], $meta['height']);
        \wp_update_attachment_metadata($attachment_id, $meta);

        $imageDimensions = new ImageDimensions($file_loc, $attachment_id);

        // Dimensions should still be readable from the file itself.
        $this->assertIsInt($imageDimensions->width());
        $this->assertIsInt($imageDimensions->height());
        $this->assertGreaterThan(0, $imageDimensions->width());
        $this->assertGreaterThan(0, $imageDimensions->height());
    }

    public function testSvgDimensionsFromViewBox()
    {
        $imageDimensions = new ImageDimensions($this->getFixtureAsset('timber-logo.svg'));

        // viewBox="0 0 530.91 158"
        $this->assertSame(531, $imageDimensions->width());
        $this->assertSame(158, $imageDimensions->height());
    }

    public function testSvgDimensionsFromWidthAndHeightAttributes()
    {
        $imageDimensions = new ImageDimensions($this->getFixtureAsset('icon-twitter.svg'));

        $this->assertSame(23, $imageDimensions->width());
        $this->assertSame(20, $imageDimensions->height());
    }

    /**
     * The root element is matched on its local name, so a namespace-prefixed root — as produced
     * by e.g. Wikimedia — is still read.
     */
    public function testSvgDimensionsFromNamespacePrefixedRoot()
    {
        $svg = $this->writeSvgToUploads('prefixed-root.svg', <<<SVG
            <?xml version="1.0" encoding="UTF-8"?>
            <svg:svg xmlns:svg="http://www.w3.org/2000/svg" width="120" height="60"/>
            SVG);

        $imageDimensions = new ImageDimensions($svg);

        $this->assertSame(120, $imageDimensions->width());
        $this->assertSame(60, $imageDimensions->height());
    }

    /**
     * An XML document that merely happens to carry width/height on its root is not an SVG, and
     * must not be sized as one.
     */
    public function testNonSvgRootElementYieldsNoDimensions()
    {
        $svg = $this->writeSvgToUploads('not-really.svg', <<<SVG
            <?xml version="1.0" encoding="UTF-8"?>
            <html width="120" height="60"/>
            SVG);

        $imageDimensions = new ImageDimensions($svg);

        $this->assertSame(0, $imageDimensions->width());
        $this->assertSame(0, $imageDimensions->height());
    }

    /**
     * Reading the root element does not require the rest of the document to be well-formed, so a
     * truncated or corrupt SVG still reports its size.
     */
    public function testSvgDimensionsAreReadFromRootOfMalformedDocument()
    {
        $body = \str_repeat('<path d="M0 0 L1 1"/>', 1000);
        $svg = $this->writeSvgToUploads(
            'malformed-body.svg',
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="40">'
            . $body . '<unclosed></svg>'
        );

        $imageDimensions = new ImageDimensions($svg);

        $this->assertSame(80, $imageDimensions->width());
        $this->assertSame(40, $imageDimensions->height());
    }

    private function writeSvgToUploads(string $name, string $contents): string
    {
        $upload_dir = \wp_get_upload_dir();

        if (!\is_dir($upload_dir['path'])) {
            \wp_mkdir_p($upload_dir['path']);
        }

        $path = $upload_dir['path'] . '/' . $name;
        \file_put_contents($path, $contents);
        $this->svg_files[] = $path;

        return $path;
    }

    public function testDimensionsMetadataTakesPrecedenceOverFile()
    {
        $attachment_id = $this->createAttachmentWithImage();
        $file_loc = \get_attached_file($attachment_id);

        // Set metadata dimensions that differ from the actual file dimensions.
        $meta = \wp_get_attachment_metadata($attachment_id);
        $meta['width'] = 9999;
        $meta['height'] = 8888;
        \wp_update_attachment_metadata($attachment_id, $meta);

        $imageDimensions = new ImageDimensions($file_loc, $attachment_id);

        // Should return metadata values, not actual file dimensions.
        $this->assertEquals(9999, $imageDimensions->width());
        $this->assertEquals(8888, $imageDimensions->height());
    }
}
