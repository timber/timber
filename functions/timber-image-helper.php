<?php

require_once 'timber-image-retina-helper.php';

class TimberImageHelper {

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

    static function add_filters() {
        add_filter( 'upload_dir', function ( $arr ) {
                $arr['relative'] = str_replace( home_url(), '', $arr['baseurl'] );
                return $arr;
            } );
    }

    /**
     *
     *
     * @param string  $hexstr
     * @return array
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

    static function delete_resized_files_from_url( $src ) {
        $local = TimberURLHelper::url_to_file_system( $src );
        self::delete_resized_files( $local );
    }

    static function delete_letterboxed_files_from_url( $src ) {
        $local = TimberURLHelper::url_to_file_system( $src );
        self::delete_letterboxed_files( $local );
    }

    /**
     *
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
     *
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
     *
     *
     * @param string  $src
     * @param int     $w
     * @param int     $h
     * @param string  $color
     * @return string
     */
    public static function get_letterbox_file_rel( $src, $w, $h, $color ) {
        if ( !strlen( $src ) ) {
            return null;
        }
        $new_path = self::get_letterbox_file_name_relative_to_content( $src, $w, $h, $color );
        return WP_CONTENT_SUBDIR . $new_path;
    }

    /**
     *
     *
     * @param string  $src The src of an image can be absolute, relative or server location
     * @return mixed|null
     */
    static function get_directory_relative_to_content( $src ) {
        if ( !strlen( $src ) ) {
            return null;
        }
        if ( !strlen( $src ) ) {
            return null;
        }
        $abs = false;
        if ( strstr( $src, 'http' ) ) {
            $abs = true;
        }
        $path_parts = pathinfo( $src );
        if ( $abs ) {
            $dir_relative_to_content = str_replace( WP_CONTENT_URL, '', $path_parts['dirname'] );
        } else {
            $dir_relative_to_content = str_replace( WP_CONTENT_DIR, '', $path_parts['dirname'] );
            $dir_relative_to_content = str_replace( WP_CONTENT_SUBDIR, '', $dir_relative_to_content );
        }
        return $dir_relative_to_content;
    }

    /**
     *
     *
     * @param string  $src
     * @param int     $w
     * @param int     $h
     * @param string  $color
     * @return string
     */
    static function get_letterbox_file_name_relative_to_content( $src, $w, $h, $color ) {
        $path_parts = pathinfo( $src );
        $dir_relative_to_content = self::get_directory_relative_to_content( $src );
        $color = str_replace( '#', '', $color );
        $newbase = $path_parts['filename'] . '-lbox-' . $w . 'x' . $h . '-' . $color;
        $new_name = $newbase . '.' . $path_parts['extension'];
        return $dir_relative_to_content . '/' . $new_name;
    }

    /**
     *
     *
     * @param string  $src
     * @param int     $w
     * @param int     $h
     * @param string  $color
     * @return string
     */
    public static function get_letterbox_file_path( $src, $w, $h, $color ) {
        $new_name = self::get_letterbox_file_name_relative_to_content( $src, $w, $h, $color );
        $new_server_path = WP_CONTENT_DIR . $new_name;
        $new_server_path = TimberURLHelper::remove_double_slashes( $new_server_path );
        return $new_server_path;
    }

    /**
     *
     *
     * @param string  $src
     * @param int     $w
     * @param int     $h
     * @param string  $crop
     * @return string
     */
    static function get_resize_file_rel( $src, $w, $h, $crop ) {
        if ( !strlen( $src ) ) {
            return null;
        }
        $new_path = self::get_resize_file_name_relative_to_content( $src, $w, $h, $crop );
        return WP_CONTENT_SUBDIR . $new_path;
    }

    /**
     *
     *
     * @param string  $src
     * @param int     $w
     * @param int     $h
     * @param string  $crop
     * @return string
     */
    static function get_resize_file_name_relative_to_content( $src, $w, $h, $crop ) {
        $path_parts = pathinfo( $src );
        $dir_relative_to_content = self::get_directory_relative_to_content( $src );
        $newbase = $path_parts['filename'] . '-' . $w . 'x' . $h . '-c-' . ( $crop ? $crop : 'f' ); // Crop will be either user named or f (false)
        $new_name = $newbase . '.' . $path_parts['extension'];
        return $dir_relative_to_content . '/' . $new_name;
    }

    /**
     *
     *
     * @param string  $src
     */
    public static function in_uploads( $src ) {
        $upload_dir = wp_upload_dir();
        if ( strstr( $src, $upload_dir['relative'] ) ) {
            return true;
        }
        return false;
    }

    /**
     *
     *
     * @param string  $src
     * @param int     $w
     * @param int     $h
     * @param string  $crop
     * @return string
     */
    static function get_resize_file_path( $src, $w, $h, $crop ) {
        $new_name = self::get_resize_file_name_relative_to_content( $src, $w, $h, $crop );
        $new_server_path = WP_CONTENT_DIR . $new_name;
        $new_server_path = TimberURLHelper::remove_double_slashes( $new_server_path );
        return $new_server_path;
    }

    /**
     *
     *
     * @param int     $iid
     * @return string
     */
    public static function get_image_path( $iid ) {
        $size = 'full';
        $src = wp_get_attachment_image_src( $iid, $size );
        $src = $src[0];
        return self::get_rel_path( $src );
    }

    /**
     *
     *
     * @param string  $url
     */
    public static function get_server_location( $url ) {
        if ( strpos( $url, ABSPATH ) === 0 ) {
            return $url;
        }
        $upload_dir = wp_upload_dir();
        $abs = false;
        if ( strstr( $url, 'http' ) ) {
            $abs = true;
        }
        if ( self::in_uploads( $url ) ) {
            if ( $abs ) {
                $relative_to_uploads_dir = str_replace( $upload_dir['baseurl'], '', $url );
            } else {
                $relative_to_uploads_dir = str_replace( $upload_dir['relative'], '', $url );
            }
            return $upload_dir['basedir'] . $relative_to_uploads_dir;
        } else {
            if ( $abs ) {
                $relative_to_wp_content = str_replace( WP_CONTENT_URL, '', $url );
            } else {
                $relative_to_wp_content = str_replace( WP_CONTENT_SUBDIR, '', $url );
            }
            return WP_CONTENT_DIR . $relative_to_wp_content;
        }
    }

    /**
     *
     *
     * @param string  $src
     * @param int     $w
     * @param int     $h
     * @param string  $color
     * @param bool    $force
     * @return mixed|null|string
     */
    public static function letterbox( $src, $w, $h, $color = '#000000', $force = false ) {
        if ( strstr( $src, 'http' ) && !strstr( $src, home_url() ) ) {
            $src = self::sideload_image( $src );
        }
        $abs = false;
        if ( strstr( $src, 'http' ) ) {
            $abs = true;
        }
        $new_file_rel = self::get_letterbox_file_rel( $src, $w, $h, $color );
        $new_server_path = self::get_letterbox_file_path( $src, $w, $h, $color );
        $old_server_path = self::get_server_location( $src );
        $old_server_path = TimberURLHelper::remove_double_slashes( $old_server_path );
        $new_server_path = TimberURLHelper::remove_double_slashes( $new_server_path );
        if ( file_exists( $new_server_path ) && !$force ) {
            if ( $abs ) {
                return untrailingslashit( home_url() ) . $new_file_rel;
            } else {
                return TimberURLHelper::preslashit( $new_file_rel );
            }
        }
        $bg = imagecreatetruecolor( $w, $h );
        $c = self::hexrgb( $color );
        $white = imagecolorallocate( $bg, $c['red'], $c['green'], $c['blue'] );
        imagefill( $bg, 0, 0, $white );
        $image = wp_get_image_editor( $old_server_path );
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
            $image->save( $new_server_path );
            $func = 'imagecreatefromjpeg';
            $ext = pathinfo( $new_server_path, PATHINFO_EXTENSION );
            if ( $ext == 'gif' ) {
                $func = 'imagecreatefromgif';
            } else if ( $ext == 'png' ) {
                    $func = 'imagecreatefrompng';
                }
            $image = $func( $new_server_path );
            imagecopy( $bg, $image, $x, $y, 0, 0, $owt, $oht );
            imagejpeg( $bg, $new_server_path );
            $new_relative_path = TimberURLHelper::get_rel_path( $new_server_path );
            if ( $abs ) {
                return home_url( $new_relative_path );
            }
            return $new_relative_path;
        } else {
            TimberHelper::error_log( $image );
        }
        return null;
    }

    /**
     *
     *
     * @param string  $src   a url or path to the image (http://example.org/wp-content/uploads/2014/image.jpg) or (/wp-content/uploads/2014/image.jpg)
     * @param string  $bghex
     * @return string
     */
    public static function img_to_jpg( $src, $bghex = '#FFFFFF' ) {
        $path = str_replace( home_url(), '', $src );
        $output = str_replace( '.png', '.jpg', $path );
        $input_file = self::get_server_location( $path );
        $output_file = self::get_server_location( $output );
        if ( file_exists( $output_file ) ) {
            return $output;
        }
        $filename = $output;
        $input = imagecreatefrompng( $input_file );
        list( $width, $height ) = getimagesize( $input_file );
        $output = imagecreatetruecolor( $width, $height );
        $c = self::hexrgb( $bghex );
        $white = imagecolorallocate( $output, $c['red'], $c['green'], $c['blue'] );
        imagefilledrectangle( $output, 0, 0, $width, $height, $white );
        imagecopy( $output, $input, 0, 0, 0, 0, $width, $height );
        imagejpeg( $output, $output_file );
        return $filename;
    }

    /**
     *
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
     *
     *
     * @param string  $file
     * @return string
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
     *
     *
     * @param string  $src
     * @param float   $multiplier
     */
    public static function retina_resize( $src, $factor = 2 ) {
        if ( empty( $src ) ) {
            return '';
        }
        $abs = false;
        if ( strstr( $src, 'http' ) ) {
            $abs = true;
        }
        if ( strstr( $src, 'http' ) && !strstr( $src, home_url() ) ) {
            $src = self::sideload_image( $src );
        }
        $old_server_path = self::get_server_location( $src );
        $new_path = TimberImageRetinaHelper::get_retina_file_rel( $src, $factor );
        $new_server_path = TimberImageRetinaHelper::get_retina_file_path( $src, $factor );

        $old_server_path = TimberURLHelper::remove_double_slashes( $old_server_path );
        $new_server_path = TimberURLHelper::remove_double_slashes( $new_server_path );
        if ( file_exists( $new_server_path ) ) {
            if ( !$abs ) {
                return TimberURLHelper::preslashit( $new_path );
            }
            return untrailingslashit( home_url() ) . $new_path;
        }
        $image = wp_get_image_editor( $old_server_path );
        if ( !is_wp_error( $image ) ) {
            $current_size = $image->get_size();

            $src_w = $current_size['width'];
            $src_h = $current_size['height'];

            $src_ratio = $src_w / $src_h;

            // Get ratios
            $w = $src_w * $factor;
            $h = $src_h * $factor;
            $image->crop( 0, 0, $src_w, $src_h, $w, $h );
            $result = $image->save( $new_server_path );
            return $new_path;
        }
        return $src;

    }

    /**
     *
     *
     * @param string  $src
     * @param int     $w
     * @param int     $h
     * @param string  $crop
     * @param bool    $force_resize
     * @return string
     */
    public static function resize( $src, $w, $h = 0, $crop = 'default', $force_resize = false ) {
        if ( empty( $src ) ) {
            return '';
        }
        if ( strstr( $src, 'http' ) && !strstr( $src, home_url() ) ) {
            $src = self::sideload_image( $src );
        }
        $abs = false;
        if ( strstr( $src, 'http' ) ) {
            $abs = true;
        }
        // Sanitize crop position
        $allowed_crop_positions = array( 'default', 'center', 'top', 'bottom', 'left', 'right' );
        if ( $crop !== false && !in_array( $crop, $allowed_crop_positions ) ) {
            $crop = $allowed_crop_positions[0];
        }
        //oh good, it's a relative image in the uploads folder!
        $new_path = self::get_resize_file_rel( $src, $w, $h, $crop );
        $new_server_path = self::get_resize_file_path( $src, $w, $h, $crop );
        $old_server_path = self::get_server_location( $src );
        $old_server_path = TimberURLHelper::remove_double_slashes( $old_server_path );
        $new_server_path = TimberURLHelper::remove_double_slashes( $new_server_path );
        if ( file_exists( $new_server_path ) ) {
            if ( $force_resize ) {
                // Force resize - warning: will regenerate the image on every pageload, use for testing purposes only!
                unlink( $new_server_path );
            } else {
                if ( !$abs ) {
                    return TimberURLHelper::preslashit( $new_path );
                }
                return untrailingslashit( home_url() ) . $new_path;
            }
        }
        $image = wp_get_image_editor( $old_server_path );

        if ( !is_wp_error( $image ) ) {
            $current_size = $image->get_size();

            $src_w = $current_size['width'];
            $src_h = $current_size['height'];

            $src_ratio = $src_w / $src_h;
            if ( !$h ) {
                $h = round( $w / $src_ratio );
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
            $result = $image->save( $new_server_path );
            if ( is_wp_error( $result ) ) {
                error_log( 'Error resizing image' );
                error_log( print_r( $result, true ) );
            }
            if ( $abs ) {
                return untrailingslashit( home_url() ) . $new_path;
            }
            return $new_path;
        } else if ( isset( $image->error_data['error_loading_image'] ) ) {
                TimberHelper::error_log( 'Error loading ' . $image->error_data['error_loading_image'] );
            } else {
            TimberHelper::error_log( $image );
        }
        return $src;
    }
}

TimberImageHelper::add_constants();
TimberImageHelper::add_actions();
TimberImageHelper::add_filters();
