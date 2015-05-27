<?php
class WP_Overrides {

	public static function media_handle_upload( $file_id, $post_id, $post_data = array(), $overrides = array( 'test_form' => false ) ) {

		$time = current_time( 'mysql' );
		if ( $post = get_post( $post_id ) ) {
			if ( substr( $post->post_date, 0, 4 ) > 0 )
				$time = $post->post_date;
		}

		$name = $_FILES[$file_id]['name'];
		$file = self::wp_handle_upload( $_FILES[$file_id], $overrides, $time );

		if ( isset( $file['error'] ) )
			return new WP_Error( 'upload_error', $file['error'] );

		$name_parts = pathinfo( $name );
		$name = trim( substr( $name, 0, -( 1 + strlen( $name_parts['extension'] ) ) ) );

		$url = $file['url'];
		$type = $file['type'];
		$file = $file['file'];
		$title = $name;
		$content = '';

		if ( preg_match( '#^audio#', $type ) ) {
			$meta = wp_read_audio_metadata( $file );

			if ( ! empty( $meta['title'] ) )
				$title = $meta['title'];

			$content = '';

			if ( ! empty( $title ) ) {

				if ( ! empty( $meta['album'] ) && ! empty( $meta['artist'] ) ) {
					/* translators: 1: audio track title, 2: album title, 3: artist name */
					$content .= sprintf( __( '"%1$s" from %2$s by %3$s.' ), $title, $meta['album'], $meta['artist'] );
				} else if ( ! empty( $meta['album'] ) ) {
						/* translators: 1: audio track title, 2: album title */
						$content .= sprintf( __( '"%1$s" from %2$s.' ), $title, $meta['album'] );
					} else if ( ! empty( $meta['artist'] ) ) {
						/* translators: 1: audio track title, 2: artist name */
						$content .= sprintf( __( '"%1$s" by %2$s.' ), $title, $meta['artist'] );
					} else {
					$content .= sprintf( __( '"%s".' ), $title );
				}

			} else if ( ! empty( $meta['album'] ) ) {

					if ( ! empty( $meta['artist'] ) ) {
						/* translators: 1: audio album title, 2: artist name */
						$content .= sprintf( __( '%1$s by %2$s.' ), $meta['album'], $meta['artist'] );
					} else {
						$content .= $meta['album'] . '.';
					}

				} else if ( ! empty( $meta['artist'] ) ) {

					$content .= $meta['artist'] . '.';

				}

			if ( ! empty( $meta['year'] ) )
				$content .= ' ' . sprintf( __( 'Released: %d.' ), $meta['year'] );

			if ( ! empty( $meta['track_number'] ) ) {
				$track_number = explode( '/', $meta['track_number'] );
				if ( isset( $track_number[1] ) )
					$content .= ' ' . sprintf( __( 'Track %1$s of %2$s.' ), number_format_i18n( $track_number[0] ), number_format_i18n( $track_number[1] ) );
				else
					$content .= ' ' . sprintf( __( 'Track %1$s.' ), number_format_i18n( $track_number[0] ) );
			}

			if ( ! empty( $meta['genre'] ) )
				$content .= ' ' . sprintf( __( 'Genre: %s.' ), $meta['genre'] );

			// use image exif/iptc data for title and caption defaults if possible
		} elseif ( $image_meta = @wp_read_image_metadata( $file ) ) {
			if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) )
				$title = $image_meta['title'];
			if ( trim( $image_meta['caption'] ) )
				$content = $image_meta['caption'];
		}

		// Construct the attachment array
		$attachment = array_merge( array(
				'post_mime_type' => $type,
				'guid' => $url,
				'post_parent' => $post_id,
				'post_title' => $title,
				'post_content' => $content,
			), $post_data );

		// This should never be set as it would then overwrite an existing attachment.
		if ( isset( $attachment['ID'] ) )
			unset( $attachment['ID'] );

		// Save the data
		$id = wp_insert_attachment( $attachment, $file, $post_id );
		if ( !is_wp_error( $id ) ) {
			wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
		}

		return $id;

	}

	static function wp_handle_upload( &$file, $overrides = false, $time = null ) {
		// The default error handler.
		if ( ! function_exists( 'wp_handle_upload_error' ) ) {
			function wp_handle_upload_error( &$file, $message ) {
				return array( 'error'=>$message );
			}
		}

		/**
		 * Filter data for the current file to upload.
		 *
		 * @since 2.9.0
		 *
		 * @param array   $file An array of data for a single file.
		 */
		$file = apply_filters( 'wp_handle_upload_prefilter', $file );

		// You may define your own function and pass the name in $overrides['upload_error_handler']
		$upload_error_handler = 'wp_handle_upload_error';

		// You may have had one or more 'wp_handle_upload_prefilter' functions error out the file. Handle that gracefully.
		if ( isset( $file['error'] ) && !is_numeric( $file['error'] ) && $file['error'] )
			return $upload_error_handler( $file, $file['error'] );

		// You may define your own function and pass the name in $overrides['unique_filename_callback']
		$unique_filename_callback = null;

		// $_POST['action'] must be set and its value must equal $overrides['action'] or this:
		$action = 'wp_handle_upload';

		// Courtesy of php.net, the strings that describe the error indicated in $_FILES[{form field}]['error'].
		$upload_error_strings = array( false,
			__( "The uploaded file exceeds the upload_max_filesize directive in php.ini." ),
			__( "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form." ),
			__( "The uploaded file was only partially uploaded." ),
			__( "No file was uploaded." ),
			'',
			__( "Missing a temporary folder." ),
			__( "Failed to write file to disk." ),
			__( "File upload stopped by extension." ) );

		// All tests are on by default. Most can be turned off by $overrides[{test_name}] = false;
		$test_form = true;
		$test_size = true;
		$test_upload = true;

		// If you override this, you must provide $ext and $type!!!!
		$test_type = true;
		$mimes = false;

		// Install user overrides. Did we mention that this voids your warranty?
		if ( is_array( $overrides ) )
			extract( $overrides, EXTR_OVERWRITE );

		// A correct form post will pass this test.
		if ( $test_form && ( !isset( $_POST['action'] ) || ( $_POST['action'] != $action ) ) )
			return call_user_func( $upload_error_handler, $file, __( 'Invalid form submission.' ) );

		// A successful upload will pass this test. It makes no sense to override this one.
		if ( isset( $file['error'] ) && $file['error'] > 0 ) {
			return call_user_func( $upload_error_handler, $file, $upload_error_strings[ $file['error'] ] );
		}

		// A non-empty file will pass this test.
		if ( $test_size && !( $file['size'] > 0 ) ) {
			if ( is_multisite() )
				$error_msg = __( 'File is empty. Please upload something more substantial.' );
			else
				$error_msg = __( 'File is empty. Please upload something more substantial. This error could also be caused by uploads being disabled in your php.ini or by post_max_size being defined as smaller than upload_max_filesize in php.ini.' );
			return call_user_func( $upload_error_handler, $file, $error_msg );
		}

		// A properly uploaded file will pass this test. There should be no reason to override this one.
		if ( $test_upload && ! @ file_exists( $file['tmp_name'] ) )
			return call_user_func( $upload_error_handler, $file, __( 'Specified file failed upload test.' ) );

		// A correct MIME type will pass this test. Override $mimes or use the upload_mimes filter.
		if ( $test_type ) {
			$wp_filetype = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $mimes );

			extract( $wp_filetype );

			// Check to see if wp_check_filetype_and_ext() determined the filename was incorrect
			if ( $proper_filename )
				$file['name'] = $proper_filename;

			if ( ( !$type || !$ext ) && !current_user_can( 'unfiltered_upload' ) )
				return call_user_func( $upload_error_handler, $file, __( 'Sorry, this file type is not permitted for security reasons.' ) );

			if ( !$ext )
				$ext = ltrim( strrchr( $file['name'], '.' ), '.' );

			if ( !$type )
				$type = $file['type'];
		} else {
			$type = '';
		}

		// A writable uploads dir will pass this test. Again, there's no point overriding this one.
		if ( ! ( ( $uploads = wp_upload_dir( $time ) ) && false === $uploads['error'] ) )
			return call_user_func( $upload_error_handler, $file, $uploads['error'] );

		$filename = wp_unique_filename( $uploads['path'], $file['name'], $unique_filename_callback );

		// Move the file to the uploads dir
		$new_file = $uploads['path'] . "/$filename";
		if ( false === @ copy( $file['tmp_name'], $new_file ) ) {
			if ( 0 === strpos( $uploads['basedir'], ABSPATH ) )
				$error_path = str_replace( ABSPATH, '', $uploads['basedir'] ) . $uploads['subdir'];
			else
				$error_path = basename( $uploads['basedir'] ) . $uploads['subdir'];

			return $upload_error_handler( $file, sprintf( __( 'The uploaded file could not be moved to %s.' ), $error_path ) );
		}

		// Set correct file permissions
		$stat = stat( dirname( $new_file ) );
		$perms = $stat['mode'] & 0000666;
		@ chmod( $new_file, $perms );

		// Compute the URL
		$url = $uploads['url'] . "/$filename";

		if ( is_multisite() )
			delete_transient( 'dirsize_cache' );

		/**
		 * Filter the data array for the uploaded file.
		 *
		 * @since 2.1.0
		 *
		 * @param array   $upload  {
		 *     Array of upload data.
		 *
		 *     @type string $file Filename of the newly-uploaded file.
		 *     @type string $url  URL of the uploaded file.
		 *     @type string $type File type.
		 * }
		 * @param string  $context The type of upload action. Accepts 'upload' or 'sideload'.
		 */
		return apply_filters( 'wp_handle_upload', array( 'file' => $new_file, 'url' => $url, 'type' => $type ), 'upload' );
	}
}
