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

	const BASE_UPLOADS = 1;
	const BASE_CONTENT = 2;

	public static function init() {
		self::add_constants();
		self::add_actions();
		self::add_filters();
	}

	/**
	 * Generates a new image with the specified dimensions.
	 * New dimensions are achieved by cropping to maintain ratio.
	 *
	 * @param string  		$src an URL (absolute or relative) to the original image
	 * @param int|string	$w target width(int) or WordPress image size (WP-set or user-defined)
	 * @param int     		$h target height (ignored if $w is WP image size)
	 * @param string  		$crop
	 * @param bool    		$force_resize
	 * @return string (ex: )
	 */
	public static function resize( $src, $w, $h = 0, $crop = 'default', $force = false ) {
		if (!is_numeric($w) && is_string($w)) {
			if ($sizes = self::find_wp_dimensions($w)) {
				$w = $sizes['w'];
				$h = $sizes['h'];
			} else {
				return $src;
			}
		}
		$op = new TimberImageOperationResize($w, $h, $crop);
		return self::_operate($src, $op, $force);
	}

	/**
	 * Find the sizes of an image based on a defined image size
	 * @param  string $size the image size to search for
	 *                      can be WordPress-defined ("medium")
	 *                      or user-defined ("my-awesome-size")
	 * @return array {
	 *     @type int w
	 *     @type int h
	 * }
	 */
	private static function find_wp_dimensions($size) {

		// if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {

	 //                    $sizes[ $_size ]['width'] = get_option( $_size . '_size_w' );
	 //                    $sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
		global $_wp_additional_image_sizes;
		if (isset($_wp_additional_image_sizes[$size])) {
			$w = $_wp_additional_image_sizes[$size]['width'];
			$h = $_wp_additional_image_sizes[$size]['height'];
		} else if (in_array($size, array('thumbnail', 'medium', 'large'))) {
			$w = get_option($size.'_size_w');
			$h = get_option($size.'_size_h');
		}
		if (isset($w) && isset($h) && ($w || $h)) {
			return array('w' => $w, 'h' => $h);
		}
		return false;
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
	 * Generates a new image by converting the source GIF or PNG into JPG
	 *
	 * @param string  $src   a url or path to the image (http://example.org/wp-content/uploads/2014/image.jpg) or (/wp-content/uploads/2014/image.jpg)
	 * @param string  $bghex
	 * @return string
	 */
	public static function img_to_jpg( $src, $bghex = '#FFFFFF', $force = false ) {
		$op = new TimberImageOperationToJpg($bghex);
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
					$attachment = new TimberImage( $post_id );
					TimberImageHelper::delete_resized_files( $attachment->file_loc );
					TimberImageHelper::delete_letterboxed_files( $attachment->file_loc );
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
	 * @return void
	 */
	static function add_filters() {
		add_filter( 'upload_dir', function ( $arr ) {
			$arr['relative'] = str_replace( home_url(), '', $arr['baseurl'] );
			return $arr;
		} );
	}

	//-- end of public methods --//


	/**
	 * Deletes resized versions of the supplied file name.
	 * So if passed a value like my-pic.jpg, this function will delete my-pic-500x200-c-left.jpg, my-pic-400x400-c-default.jpg, etc.
	 *
	 * @param string  $local_file   ex: /var/www/wp-content/uploads/2015/my-pic.jpg
	 *                              ex: http://example.org/wp-content/uploads/2015/foo.png
	 */
	static function delete_resized_files( $local_file ) {
		if (TimberURLHelper::is_absolute( $local_file ) ) {
			$local_file = TimberURLHelper::url_to_file_system( $local_file );
		}
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
		if (TimberURLHelper::is_absolute( $local_file ) ) {
			$local_file = TimberURLHelper::url_to_file_system( $local_file );
		}
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
			'absolute' => TimberURLHelper::is_absolute($url), // is the url absolute or relative (to home_url)
			'base' => 0, // is the image in uploads dir, or in content dir (theme or plugin)
			'subdir' => '', // the path between base (uploads or content) and file
			'filename' => '', // the filename, without extension
			'extension' => '', // the file extension
			'basename' => '', // full file name
		);
		$upload_dir = wp_upload_dir();
		$tmp = $url;
		if ( 0 === strpos($tmp, ABSPATH) ) { // we've been given a dir, not an url
			$result['absolute'] = true;
			if ( 0 === strpos($tmp, $upload_dir['basedir']) ) {
				$result['base']= self::BASE_UPLOADS; // upload based
				$tmp = str_replace($upload_dir['basedir'], '', $tmp);
			}
			if ( 0 === strpos($tmp, WP_CONTENT_DIR) ) {
				$result['base']= self::BASE_CONTENT; // content based
				$tmp = str_replace(WP_CONTENT_DIR, '', $tmp);
			}
		} else {
			if (!$result['absolute']) {
				$tmp = home_url().$tmp;
			}
			if (0 === strpos($tmp, $upload_dir['baseurl'])) {
				$result['base']= self::BASE_UPLOADS; // upload based
				$tmp = str_replace($upload_dir['baseurl'], '', $tmp);
			}
			if (0 === strpos($tmp, content_url())) {
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
		if( self::BASE_UPLOADS == $base ) {
			$upload_dir = wp_upload_dir();
			$url = $upload_dir['baseurl'];
		}
		if( self::BASE_CONTENT == $base ) {
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
			$upload_dir = wp_upload_dir();
			$path = $upload_dir['basedir'];
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
	 *
	 */
	private static function _operate( $src, $op, $force = false ) {
		if ( empty( $src ) ) {
			return '';
		}
		// if external image, load it first
		if ( TimberURLHelper::is_external_content( $src ) ) {
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
