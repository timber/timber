<?php

/**
 * Increases image size by a given factor
 * Arguments:
 * - factor by which to multiply image dimensions
 */
class TimberImageOperationRetina extends TimberImageOperation {

    private $factor;

    /**
     * @param int   $factor to multiply original dimensions by
     */
    function __construct($factor) {
        $this->factor = $factor;
    }

    /**
     * @param   string    $src_filename     the basename of the file (ex: my-awesome-pic)
     * @param   string    $src_extension    the extension (ex: .jpg)
     * @return  string    the final filename to be used (ex: my-awesome-pic@2x.jpg) 
     */
    function filename($src_filename, $src_extension) {
        $newbase = $src_filename . '@' . $this->factor . 'x'; // add @2x, @3x, @1.5x, etc.
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
     *                               (ex: /src/var/www/wp-content/uploads/my-pic@2x.jpg)
     * @return bool                  true if everything went fine, false otherwise
     */
    function run($load_filename, $save_filename){
        $image = wp_get_image_editor( $load_filename );
        if ( !is_wp_error( $image ) ) {
            $current_size = $image->get_size();
            $src_w = $current_size['width'];
            $src_h = $current_size['height'];
            $src_ratio = $src_w / $src_h;
            // Get ratios
            $w = $src_w * $this->factor;
            $h = $src_h * $this->factor;
            $image->crop( 0, 0, $src_w, $src_h, $w, $h );
            $result = $image->save( $save_filename );
            if ( is_wp_error( $result ) ) {
                error_log( 'Error resizing image' );
                error_log( print_r( $result, true ) );
                return false;
            } else {
                return true;
            }
        } else if ( isset( $image->error_data['error_loading_image'] ) ) {
            TimberHelper::error_log( 'Error loading ' . $image->error_data['error_loading_image'] );
        } else {
            TimberHelper::error_log( $image );
        }
        return false;
    }
}
