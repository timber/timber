<?php

/**
 * Implements converting a PNG file to JPG.
 * Argument:
 * - color to fill transparent zones
 */
class TimberImageOperationPngToJpg extends TimberImageOperation {
    private $color;

    /**
     * @param string $color hex string of color to use for transparent zones
     */
    function __construct($color) {
        $this->color = $color;
    }

    function filename($src_filename, $src_extension) {
        $new_name = $src_filename . '.jpg';
        return $new_name;
    }

    function run($load_filename, $save_filename){
        $input = imagecreatefrompng( $load_filename );
        list( $width, $height ) = getimagesize( $load_filename );
        $output = imagecreatetruecolor( $width, $height );
        $c = self::hexrgb( $this->color );
        $color = imagecolorallocate( $output, $c['red'], $c['green'], $c['blue'] );
        imagefilledrectangle( $output, 0, 0, $width, $height, $color );
        imagecopy( $output, $input, 0, 0, 0, 0, $width, $height );
        imagejpeg( $output, $save_filename );
        return true;
    }
}
