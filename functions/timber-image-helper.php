<?php

/**
 * Implements the Twig image filters:
 * https://github.com/jarednova/timber/wiki/Image-cookbook#arbitrary-resizing-of-images
 * - resize
 * - retina
 * - letterbox
 * - tojpg
 *
 * Implementation:
 * - public static functions provide the methods that are called by the filter
 * - most of the work is common to all filters (URL analysis, directory gymnastics, file caching, error management) and done by private static functions
 * - the specific part (actual image processing) is delegated to dedicated subclasses of TimberImageOperation
 */
class TimberImageHelper {
    /**
     * Generates a new image with the specified dimensions.
     * New dimensions are achieved by cropping to maintain ratio.
     * 
     * @param string  $src an URL (absolute or relative) to the original image
     * @param int     $w target width
     * @param int     $h target heighth
     * @param string  $crop
     * @param bool    $force_resize
     * @return string
     */
    public static function resize( $src, $w, $h = 0, $crop = 'default', $force = false ) {
        $op = new TimberImageOperationResize($w, $h, $crop);
        return self::_operate($src, $op, $force);
    }

    /**
     * Generates a new image with increased size, for display on Retina screens.
     *
     * @param string  $src
     * @param float   $multiplier
     * @param boolean $force
     *
     * @return string url to the new image
     */
    public static function retina_resize( $src, $factor = 2, $force = false) {
        $op = new TimberImageOperationRetina($factor);
        return self::_operate($src, $op, $force);
    }

    /**
     * Generate a new image with the specified dimensions.
     * New dimensions are achieved by adding colored bands to maintain ratio.
     *
     * @param string  $src
     * @param int     $w
     * @param int     $h
     * @param string  $color
     * @param bool    $force
     * @return mixed|null|string
     */
    public static function letterbox( $src, $w, $h, $color = '#000000', $force = false ) {
        $op = new TimberImageOperationLetterbox($w, $h, $color);
        return self::_operate($src, $op, $force);
    }

    /**
     * Generates a new image by converting the source PNG into JPG
     *
     * @param string  $src   a url or path to the image (http://example.org/wp-content/uploads/2014/image.jpg) or (/wp-content/uploads/2014/image.jpg)
     * @param string  $bghex
     * @return string
     */
    public static function img_to_jpg( $src, $bghex = '#FFFFFF', $force = false ) {
        $op = new TimberImageOperationPngToJpg($bghex);
        return self::_operate($src, $op, $force);
    }

    /**
     * Deletes all resized versions of an image when the source is deleted
     */
    static function add_actions() {
        add_action( 'delete_post', function ( $post_id ) {
                $post = get_post( $post_id );
                $image_types = array( 'image/jpeg', 'image/png', 'image/gif', 'image/jpg' );
                if ( $post->post_type == 'attachment' && in_array( $post->post_mime_type, $image_types ) ) {
                    TimberImageHelper::delete_resized_files_from_url( $post->guid );
                    TimberImageHelper::delete_letterboxed_files_from_url( $post->guid );
                }
            } );
    }

    /**
     * Adds a constant defining the path to the content directory relative to the site
     * for example /wp-content or /content
     */
    static function add_constants() {
        if ( !defined( 'WP_CONTENT_SUBDIR' ) ) {
            $wp_content_path = str_replace( home_url(), '', WP_CONTENT_URL );
            define( 'WP_CONTENT_SUBDIR', $wp_content_path );
        }
    }

    /**
     * adds a 'relative' key to wp_upload_dir() result.
     * It will contain the relative url to upload dir.
     * 
     */
    static function add_filters() {
        add_filter( 'upload_dir', function ( $arr ) {
            $arr['relative'] = str_replace( home_url(), '', $arr['baseurl'] );
            return $arr;
        } );
    }

//-- end of public methots

    /*
     * @return boolean true if $path is an absolute url, false if relative.
     */
    protected static function is_absolute($path) {
        return (boolean) (strstr($path, 'http' ));
    }

    /*
     * @return boolean true if $path is an external url, false if relative or local.
     */
    protected static function is_external($path) {
        return self::is_absolute($path) && !strstr($path, home_url());
    }

    /**
     * Deletes resized versions of the supplied file name
     *
     * @param string  $local_file
     */
    static function delete_resized_files( $local_file ) {
        $info = pathinfo( $local_file );
        $dir = $info['dirname'];
        $ext = $info['extension'];
        $filename = $info['filename'];
        $searcher = '/' . $filename . '-[0-9999999]*';
        foreach ( glob( $dir . $searcher ) as $found_file ) {
            $regexdir = str_replace( '/', '\/', $dir );
            $pattern = '/' . ( $regexdir ) . '\/' . $filename . '-[0-9]*x[0-9]*-c-[a-z]*.' . $ext . '/';
            $match = preg_match( $pattern, $found_file );
            //keeping these here so I know what the hell we're matching
            //$match = preg_match("/\/srv\/www\/wordpress-develop\/src\/wp-content\/uploads\/2014\/05\/$filename-[0-9]*x[0-9]*-c-[a-z]*.jpg/", $found_file);
            //$match = preg_match("/\/srv\/www\/wordpress-develop\/src\/wp-content\/uploads\/2014\/05\/arch-[0-9]*x[0-9]*-c-[a-z]*.jpg/", $filename);
            if ( $match ) {
                unlink( $found_file );
            }
        }
    }

    /**
     * Deletes letterboxed versions of the supplied file name
     *
     * @param string  $local_file
     */
    static function delete_letterboxed_files( $local_file ) {
        $info = pathinfo( $local_file );
        $dir = $info['dirname'];
        $ext = $info['extension'];
        $filename = $info['filename'];
        $searcher = '/' . $filename . '-lbox-[0-9999999]*';
        foreach ( glob( $dir . $searcher ) as $found_file ) {
            $regexdir = str_replace( '/', '\/', $dir );
            $pattern = '/' . ( $regexdir ) . '\/' . $filename . '-lbox-[0-9]*x[0-9]*-[a-zA-Z0-9]*.' . $ext . '/';
            $match = preg_match( $pattern, $found_file );
            if ( $match ) {
                unlink( $found_file );
            }
        }
    }


    /**
     * Determines the filepath corresponding to a given URL
     *
     * @param string  $url
     * @return string
     */
    public static function get_server_location( $url ) {
        // if we're already an absolute dir, just return
        if ( 0 === strpos( $url, ABSPATH ) ) {
            return $url;
        }
        // otherwise, analyze URL then build mapping path
        $au = self::analyze_url($url);
        $result = self::_get_file_path($au['base'], $au['subdir'], $au['basename']);
        return $result;
    }

    /**
     * Determines the filepath where a given external file will be stored.
     *
     * @param string  $file
     * @return string
     */
    public static function get_sideloaded_file_loc( $file ) {
        $upload = wp_upload_dir();
        $dir = $upload['path'];
        $filename = $file;
        $file = parse_url( $file );
        $path_parts = pathinfo( $file['path'] );
        $basename = md5( $filename );
        $ext = 'jpg';
        if ( isset( $path_parts['extension'] ) ) {
            $ext = $path_parts['extension'];
        }
        return $dir . '/' . $basename . '.' . $ext;
    }

    /**
     * downloads an external image to the server and stores it on the server
     *
     * @param string  $file the URL to the original file
     * @return string the URL to the downloaded file
     */
    public static function sideload_image( $file ) {
        $loc = self::get_sideloaded_file_loc( $file );
        if ( file_exists( $loc ) ) {
            return TimberURLHelper::preslashit( TimberURLHelper::get_rel_path( $loc ) );
        }
        // Download file to temp location
        if ( !function_exists( 'download_url' ) ) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
        }
        $tmp = download_url( $file );
        preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
        $file_array = array();
        $file_array['name'] = basename( $matches[0] );
        $file_array['tmp_name'] = $tmp;
        // If error storing temporarily, unlink
        if ( is_wp_error( $tmp ) ) {
            @unlink( $file_array['tmp_name'] );
            $file_array['tmp_name'] = '';
        }
        // do the validation and storage stuff
        $locinfo = pathinfo( $loc );
        $file = wp_upload_bits( $locinfo['basename'], null, file_get_contents( $file_array['tmp_name'] ) );
        return $file['url'];
    }

    /**
     * Takes in an URL and breaks it into components,
     * that will then be used in the different steps of image processing.
     * The image is expected to be either part of a theme, plugin, or an upload.
     * 
     * @param  string $url an URL (absolute or relative) pointing to an image
     * @return array       an array (see keys in code below)
     */
    private static function analyze_url($url) {
        $result = array(
            'url' => $url, // the initial url
            'absolute' => self::is_absolute($url), // is the url absolute or relative (to home_url)
            'base' => 0, // is the image in uploads dir, or in content dir (theme or plugin)
            'subdir' => '', // the path between base (uploads or content) and file
            'filename' => '', // the filename, without extension
            'extension' => '', // the file extension
            'basename' => '', // full file name
        );
        $tmp = $url;
        if(0 === strpos($tmp, ABSPATH)){ // we've been given a dir, not an url
            $result['absolute'] = true;
            if(0 === strpos($tmp, wp_upload_dir()['basedir'])) {
                $result['base']= self::BASE_UPLOADS; // upload based
                $tmp = str_replace(wp_upload_dir()['basedir'], '', $tmp);
            }
            if(0 === strpos($tmp, WP_CONTENT_DIR)) {
                $result['base']= self::BASE_CONTENT; // content based
                $tmp = str_replace(WP_CONTENT_DIR, '', $tmp);
            }
        } else {
            if(!$result['absolute']) {
                $tmp = home_url().$tmp;
            }
            if(0 === strpos($tmp, wp_upload_dir()['baseurl'])) {
                $result['base']= self::BASE_UPLOADS; // upload based
                $tmp = str_replace(wp_upload_dir()['baseurl'], '', $tmp);
            }
            if(0 === strpos($tmp, content_url())) {
                $result['base']= self::BASE_CONTENT; // content-based
                $tmp = str_replace(content_url(), '', $tmp);
            }
        }
        $parts = pathinfo($tmp);
        $result['subdir'] = $parts['dirname'];
        $result['filename'] = $parts['filename'];
        $result['extension'] = $parts['extension'];
        $result['basename'] = $parts['basename'];
        // todo filename
        return $result;
    }

    const BASE_UPLOADS = 1;
    const BASE_CONTENT = 2;

    /**
     * Builds the public URL of a file based on its different components
     * 
     * @param  int    $base     one of self::BASE_UPLOADS, self::BASE_CONTENT to indicate if file is an upload or a content (theme or plugin)
     * @param  string $subdir   subdirectory in which file is stored, relative to $base root folder
     * @param  string $filename file name, including extension (but no path)
     * @param  bool   $absolute should the returned URL be absolute (include protocol+host), or relative
     * @return string           the URL
     */
    private static function _get_file_url($base, $subdir, $filename, $absolute) {
        $url = '';
        if(self::BASE_UPLOADS == $base) {
            $url = wp_upload_dir()['baseurl'];
        }
        if(self::BASE_CONTENT == $base) {
            $url = content_url();
        }
        if(!empty($subdir)) {
            $url .= $subdir;
        }
        $url .= '/'.$filename;
        if(!$absolute) {
            $url = str_replace(home_url(), '', $url);
        }
        // $url = TimberURLHelper::remove_double_slashes( $url);
        return $url;
    }

    /**
     * Builds the absolute file system location of a file based on its different components
     * 
     * @param  int    $base     one of self::BASE_UPLOADS, self::BASE_CONTENT to indicate if file is an upload or a content (theme or plugin)
     * @param  string $subdir   subdirectory in which file is stored, relative to $base root folder
     * @param  string $filename file name, including extension (but no path)
     * @return string           the file location
     */
    private static function _get_file_path($base, $subdir, $filename) {
        $path = '';
        if(self::BASE_UPLOADS == $base) {
            $path = wp_upload_dir()['basedir'];
        }
        if(self::BASE_CONTENT == $base) {
            $path = WP_CONTENT_DIR;
        }
        if(!empty($subdir)) {
            $path .= $subdir;
        }
        $path .= '/'.$filename;
        return $path;
    }


    /**
     * Main method that applies operation to src image:
     * 1. break down supplied URL into components
     * 2. use components to determine result file and URL
     * 3. check if a result file already exists
     * 4. otherwise, delegate to supplied TimberImageOperation
     * 
     * @param  string  $src   an URL (absolute or relative) to an image
     * @param  object  $op    object of class TimberImageOperation
     * @param  boolean $force if true, remove any already existing result file and forces file generation
     * @return string         URL to the new image - or the source one if error
     */
    private static function _operate( $src, $op, $force = false ) {
        if ( empty( $src ) ) {
            return '';
        }
        // if external image, load it first
        if ( self::is_external( $src ) ) {
            $src = self::sideload_image( $src );
        }
        // break down URL into components
        $au = self::analyze_url($src);
        // build URL and filenames
        $new_url = self::_get_file_url(
            $au['base'],
            $au['subdir'],
            $op->filename($au['filename'], $au['extension']),
            $au['absolute']
        );
        $new_server_path = self::_get_file_path(
            $au['base'],
            $au['subdir'],
            $op->filename($au['filename'], $au['extension'])
        );
        $old_server_path = self::_get_file_path(
            $au['base'],
            $au['subdir'],
            $au['basename']
        );
        // if already exists...
        if ( file_exists( $new_server_path ) ) {
            if ( $force ) {
                // Force operation - warning: will regenerate the image on every pageload, use for testing purposes only!
                unlink( $new_server_path );
            } else {
                // return existing file (caching)
                return $new_url;
            }
        }
        // otherwise generate result file
        if($op->run($old_server_path, $new_server_path)) {
            return $new_url;
        } else {
            // in case of error, we return source file itself
            return $src;
        }
    }


// -- the below methods are just used for unit testing the URL generation code
// 
    static function get_letterbox_file_url($url, $w, $h, $color) {
        $au = self::analyze_url($url);
        $op = new TimberImageOperationLetterbox($w, $h, $color);
        $new_url = self::_get_file_url(
            $au['base'],
            $au['subdir'],
            $op->filename($au['filename'], $au['extension']),
            $au['absolute']
        );
        return $new_url;
    }
    public static function get_letterbox_file_path($url, $w, $h, $color ) {
        $au = self::analyze_url($url);
        $op = new TimberImageOperationLetterbox($w, $h, $color);
        $new_path = self::_get_file_path(
            $au['base'],
            $au['subdir'],
            $op->filename($au['filename'], $au['extension'])
        );
        return $new_path;
    }
    static function get_resize_file_url($url, $w, $h, $crop) {
        $au = self::analyze_url($url);
        $op = new TimberImageOperationResize($w, $h, $crop);
        $new_url = self::_get_file_url(
            $au['base'],
            $au['subdir'],
            $op->filename($au['filename'], $au['extension']),
            $au['absolute']
        );
        return $new_url;
    }
    static function get_resize_file_path($url, $w, $h, $crop) {
        $au = self::analyze_url($url);
        $op = new TimberImageOperationResize($w, $h, $crop);
        $new_path = self::_get_file_path(
            $au['base'],
            $au['subdir'],
            $op->filename($au['filename'], $au['extension'])
        );
        return $new_path;
    }


}

/**
 * Each image filter is represented by a subclass of this class,m
 * and each filter call is a new instance, with call arguments as properties.
 * 
 * Only 3 methods need to be implemented:
 * - constructor, storing all filter arguments
 * - filename
 * - run
 */
abstract class TimberImageOperation {
    /**
     * Builds the result filename, based on source filename and extension
     * 
     * @param  string $src_filename  source filename (excluding extension and path)
     * @param  string $src_extension source file extension
     * @return string                resulting filename (including extension but excluding path)
     */
    public abstract function filename($src_filename, $src_extension);

    /**
     * Performs the actual image manipulation,
     * including saving the target file.
     * 
     * @param  string $load_filename filepath (not URL) to source file
     * @param  string $save_filename filepath (not URL) where result file should be saved
     * @return bool                  true if everything went fine, false otherwise
     */
    public abstract function run($load_filename, $save_filename);

    /**
     * Helper method to convert hex string to rgb array
     * 
     * @param  string $hexstr hex color string (like '#FF1455')
     * @return array          array('red', 'green', 'blue') to int
     */
    public static function hexrgb( $hexstr ) {
        if ( !strstr( $hexstr, '#' ) ) {
            $hexstr = '#' . $hexstr;
        }
        if ( strlen( $hexstr ) == 4 ) {
            $hexstr = '#' . $hexstr[1] . $hexstr[1] . $hexstr[2] . $hexstr[2] . $hexstr[3] . $hexstr[3];
        }
        $int = hexdec( $hexstr );
        return array( "red" => 0xFF & ( $int >> 0x10 ), "green" => 0xFF & ( $int >> 0x8 ), "blue" => 0xFF & $int );
    }
}

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

/**
 * Increases image size by a given factor
 * Arguments:
 * - factor by which to multiply image dimensions
 */
class TimberImageOperationRetina extends TimberImageOperation {
    private $factor;

    /**
     * @param int $factor to multiply original dimensions by
     */
    function __construct($factor) {
        $this->factor = $factor;
    }

    function filename($src_filename, $src_extension) {
        $newbase = $src_filename . '@' . $this->factor . 'x'; // add @2x, @3x, @1.5x, etc.
        $new_name = $newbase . '.' . $src_extension;
        return $new_name;
    }

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

/**
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

    public function filename($src_filename, $src_extension) {
        $color = str_replace( '#', '', $this->color );
        $newbase = $src_filename . '-lbox-' . $this->w . 'x' . $this->h . '-' . $color;
        $new_name = $newbase . '.' . $src_extension;
        return $new_name;
    }

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

/**
 * Changes image to new size, by shrinking/enlarging
 * then cropping to respect new ratio.
 * 
 * Arguments:
 * - width of new image
 * - height of new image
 * - crop method 
 */
class TimberImageOperationResize extends TimberImageOperation {
    private $w, $h, $crop;

    /**
     * @param int    $w    width of new image
     * @param int    $h    height of new image
     * @param string $crop cropping method, one of: 'default', 'center', 'top', 'bottom', 'left', 'right'.
     */
    function __construct($w, $h, $crop) {
        $this->w = $w;
        $this->h = $h;
        // Sanitize crop position
        $allowed_crop_positions = array( 'default', 'center', 'top', 'bottom', 'left', 'right' );
        if ( $crop !== false && !in_array( $crop, $allowed_crop_positions ) ) {
            $crop = $allowed_crop_positions[0];
        }
        $this->crop = $crop;
    }

    public function filename($src_filename, $src_extension) {
        $result = $src_filename . '-' . $this->w . 'x' . $this->h . '-c-' . ( $this->crop ? $this->crop : 'f' ); // Crop will be either user named or f (false)
        if($src_extension) {
            $result .= '.'.$src_extension;
        }
        return $result;
    }

    public function run($load_filename, $save_filename) {
        $image = wp_get_image_editor( $load_filename );
        if ( !is_wp_error( $image ) ) {
            $w = $this->w;
            $h = $this->h;
            $crop = $this->crop;

            $current_size = $image->get_size();
            $src_w = $current_size['width'];
            $src_h = $current_size['height'];
            $src_ratio = $src_w / $src_h;
            if ( !$h ) {
                $h = round( $w / $src_ratio );
            }
            if ( !$w ) {
                //the user wants to resize based on constant height
                $w = round( $h * $src_ratio );
            }
            // Get ratios
            $dest_ratio = $w / $h;
            $src_wt = $src_h * $dest_ratio;
            $src_ht = $src_w / $dest_ratio;
            if ( !$crop ) {
                // Always crop, to allow resizing upwards
                $image->crop( 0, 0, $src_w, $src_h, $w, $h );
            } else {
                //start with defaults:
                $src_x = $src_w / 2 - $src_wt / 2;
                $src_y = ( $src_h - $src_ht ) / 6;
                //now specific overrides based on options:
                if ( $crop == 'center' ) {
                    // Get source x and y
                    $src_x = round( ( $src_w - $src_wt ) / 2 );
                    $src_y = round( ( $src_h - $src_ht ) / 2 );
                } else if ( $crop == 'top' ) {
                        $src_y = 0;
                    } else if ( $crop == 'bottom' ) {
                        $src_y = $src_h - $src_ht;
                    } else if ( $crop == 'left' ) {
                        $src_x = 0;
                    } else if ( $crop == 'right' ) {
                        $src_x = $src_w - $src_wt;
                    }
                // Crop the image
                if ( $dest_ratio > $src_ratio ) {
                    $image->crop( 0, $src_y, $src_w, $src_ht, $w, $h );
                } else {
                    $image->crop( $src_x, 0, $src_wt, $src_h, $w, $h );
                }
            }
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

TimberImageHelper::add_constants();
TimberImageHelper::add_actions();
TimberImageHelper::add_filters();