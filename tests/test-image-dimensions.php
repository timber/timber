<?php

class ImageDimensionsTestable extends Timber\ImageDimensions
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

/**
 * @group image
 */
class TestImageDimensions extends Timber_UnitTestCase
{
    public function ratioProvider()
    {
        return [
            [200, 100, 2],
            [100, 200, 0.5],
        ];
    }

    /**
     * @dataProvider ratioProvider
     */
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
