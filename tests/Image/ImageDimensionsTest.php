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
