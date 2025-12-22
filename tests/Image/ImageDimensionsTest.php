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
}
