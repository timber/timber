<?php
/*
 * Changes image to new size, by shrinking/enlarging then padding with colored bands,
 * so that no part of the image is cropped or stretched.
 * 
 * Arguments:
 * - width of new image
 * - height of new image
 * - color of padding 
 */
class TimberImageOperationLetterbox extends TimberImageOperation {
    
    private $w, $h, $color;

    /**
     * @param int    $w     width of result image
     * @param int    $h     height
     * @param string $color hex string, for color of padding bands
     */
    function __construct($w, $h, $color) {
        $this->w = $w;
        $this->h = $h;
        $this->color = $color;
    }

    /**
     * @param   string    $src_filename     the basename of the file (ex: my-awesome-pic)
     * @param   string    $src_extension    the extension (ex: .jpg)
     * @return  string    the final filename to be used 
     *                    (ex: my-awesome-pic-lbox-300x200-FF3366.jpg) 
     */
    public function filename($src_filename, $src_extension) {
        $color = str_replace( '#', '', $this->color );
        $newbase = $src_filename . '-lbox-' . $this->w . 'x' . $this->h . '-' . $color;
        $new_name = $newbase . '.' . $src_extension;
        return $new_name;
    }

    /**
     * Performs the actual image manipulation,
     * including saving the target file.
     * 
     * @param  string $load_filename filepath (not URL) to source file 
     *                               (ex: /src/var/www/wp-content/uploads/my-pic.jpg)
     * @param  string $save_filename filepath (not URL) where result file should be saved 
     *                               (ex: /src/var/www/wp-content/uploads/my-pic-lbox-300x200-FF3366.jpg)
     * @return bool                  true if everything went fine, false otherwise
     */
    public function run($load_filename, $save_filename) {
        $w = $this->w;
        $h = $this->h;

        $bg = imagecreatetruecolor( $w, $h );
        $c = self::hexrgb( $this->color );
        $bgColor = imagecolorallocate( $bg, $c['red'], $c['green'], $c['blue'] );
        imagefill( $bg, 0, 0, $bgColor );
        $image = wp_get_image_editor( $load_filename );
        if ( !is_wp_error( $image ) ) {
            $current_size = $image->get_size();
            $ow = $current_size['width'];
            $oh = $current_size['height'];
            $new_aspect = $w / $h;
            $old_aspect = $ow / $oh;
            if ( $new_aspect > $old_aspect ) {
                //taller than goal
                $h_scale = $h / $oh;
                $owt = $ow * $h_scale;
                $y = 0;
                $x = $w / 2 - $owt / 2;
                $oht = $h;
                $image->crop( 0, 0, $ow, $oh, $owt, $oht );
            } else {
                $w_scale = $w / $ow;
                $oht = $oh * $w_scale;
                $x = 0;
                $y = $h / 2 - $oht / 2;
                $owt = $w;
                $image->crop( 0, 0, $ow, $oh, $owt, $oht );
            }
            $image->save( $save_filename );
            $func = 'imagecreatefromjpeg';
            $ext = pathinfo( $save_filename, PATHINFO_EXTENSION );
            if ( $ext == 'gif' ) {
                $func = 'imagecreatefromgif';
            } else if ( $ext == 'png' ) {
                $func = 'imagecreatefrompng';
            }
            $image = $func( $save_filename );
            imagecopy( $bg, $image, $x, $y, 0, 0, $owt, $oht );
            imagejpeg( $bg, $save_filename );
            return true;
        } else {
            TimberHelper::error_log( $image );
        }
        return false;
    }
}
