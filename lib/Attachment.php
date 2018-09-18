<?php

namespace Timber;

/**
 * Class Attachment
 *
 * Objects of this class represent WordPress attachments. This is the basis that `Timber\Image`
 * objects build upon.
 *
 * @api
 * @since 2.0.0
 */
class Attachment extends Post implements CoreInterface {
	/**
	 * Representation.
	 *
	 * @var string What does this class represent in WordPress terms?
	 */
	public static $representation = 'attachment';

	/**
	 * Object type.
	 *
	 * @var string What the object represents in WordPress terms.
	 */
	public $object_type = 'attachment';

	/**
	 * File.
	 *
	 * @api
	 * @var mixed
	 */
	public $file;

	/**
	 * File location.
	 *
	 * @api
	 * @var string The absolute path to the attachmend file in the filesystem
	 *             (Example: `/var/www/htdocs/wp-content/uploads/2015/08/my-pic.jpg`)
	 */
	public $file_loc;

	/**
	 * Raw file size.
	 *
	 * @api
	 * @since 2.0.0
	 * @var int Raw file size in bytes.
	 */
	public $file_size_raw = null;

	/**
	 * Formatted file size.
	 *
	 * @api
	 * @since 2.0.0
	 * @var null|string File size string.
	 */
	public $file_size = null;

	/**
	 * File extension.
	 *
	 * @api
	 * @since 2.0.0
	 * @var null|string A file extension.
	 */
	public $file_extension = null;

	/**
	 * Absolute URL.
	 *
	 * @var string The absolute URL to the attachment.
	 */
	public $abs_url;

	/**
	 * Attachment ID.
	 *
	 * @api
	 * @var integer The attachment ID.
	 */
	public $id;

	/**
	 * Attached file.
	 *
	 * @var array The file as stored in the WordPress database.
	 */
	protected $_wp_attached_file;

	/**
	 * File types.
	 *
	 * @var array An array of supported relative file types.
	 */
	private $image_file_types = array(
		'jpg',
		'jpeg',
		'png',
		'svg',
		'bmp',
		'ico',
		'gif',
		'tiff',
		'pdf',
	);

	/**
	 * Caption text.
	 *
	 * @api
	 * @var string The caption that is stored as post_excerpt in the posts table in the database.
	 */
	public $caption;

	/**
	 * Creates a new `Timber\Attachment` object.
	 *
	 * @api
	 * @example
	 * ```php
	 * // You can pass it an ID number
	 * $myImage = new Timber\Attachment(552);
	 *
	 * // Or send it a URL to an image
	 * $myImage = new Timber\Attachment( 'http://google.com/logo.jpg' );
	 * ```
	 *
	 * @param int|mixed $attachment An attachment ID, a `Timber\Post`, a `WP_Post` object, an ACF
	 *                              image array, a path (absolute or relative) or an URL.
	 */
	public function __construct( $attachment ) {
		$this->init( $attachment );
	}

	/**
	 * Gets the src for an attachment.
	 *
	 * @api
	 *
	 * @return string The src of the attachment.
	 */
	public function __toString() {
		return $this->src();
	}

	/**
	 * Inits the object.
	 *
	 * @internal
	 *
	 * @param int|mixed $iid An attachment identifier.
	 */
	public function init( $iid = null ) {
		$iid = $this->determine_id( $iid );

		/**
		 * determine_id returns null when the attachment is a file path,
		 * thus there's nothing in the DB for us to do here 
		 */
		if ( null === $iid ) {
			return;
		}

		$attachment_info = $this->get_attachment_info( $iid );

		$this->import( $attachment_info );

		$basedir = self::wp_upload_dir();
		$basedir = $basedir['basedir'];

		if ( isset( $this->file ) ) {
			$this->file_loc = $basedir . DIRECTORY_SEPARATOR . $this->file;
		} elseif ( isset( $this->_wp_attached_file ) ) {
			$this->file     = $this->_wp_attached_file;
			$this->file_loc = $basedir . DIRECTORY_SEPARATOR . $this->file;
		}

		if ( isset( $attachment_info['id'] ) ) {
			$this->ID = $attachment_info['id'];
		} elseif ( is_numeric( $iid ) ) {
			$this->ID = $iid;
		}

		if ( isset( $this->ID ) ) {
			$this->import_custom( $this->ID );

			$this->id = $this->ID;
		}
	}

	/**
	 * Tries to figure out the attachment id you want or otherwise handle when
	 * a string or other data is sent (object, file path, etc.)
	 * @internal
	 * @param mixed a value to test against
	 * @return int|null the numberic id we should be using for this post object 
	 */
	protected function determine_id( $iid ) {
		// Make sure we actually have something to work with.
		if ( ! $iid ) {
			Helper::error_log( 'Initialized Timber\Attachment without providing first parameter.' );

			return null;
		}

		/**
		 * If passed a Timber\Attachment or WP_Post object, grab the ID and continue. Otherwise, try
		 * to check for an ACF image array an take the ID from that array.
		 */
		if ( $iid instanceof Attachment
		    || ( $iid instanceof \WP_Post && 'attachment' === $iid->post_type )
		) {
		    return (int) $iid->ID;
		} elseif ( is_array( $iid ) && isset( $iid['ID'] ) ) {
		    // Assume ACF image array.
		    $iid = $iid['ID'];
		}

		if ( ! is_numeric( $iid ) && is_string( $iid ) ) {
			if ( strstr( $iid, '://' ) ) {
				// Assume URL.
				$this->init_with_url( $iid );

				return null;
			} elseif ( strstr( $iid, ABSPATH ) ) {
				// Assume absolute path.
				$this->init_with_file_path( $iid );

				return null;
			} else {
				// Check for image file types.
				foreach ( $this->image_file_types as $type ) {
					// Assume a relative path.
					if ( strstr( strtolower( $iid ), $type ) ) {
						$this->init_with_relative_path( $iid );

						return null;
					}
				}
			}
		} 
		return $iid;
	}

	/**
	 * Inits the object with an absolute path.
	 *
	 * @internal
	 *
	 * @param string $file_path An absolute path to a file.
	 */
	protected function init_with_file_path( $file_path ) {
		$url = URLHelper::file_system_to_url( $file_path );

		$this->abs_url  = $url;
		$this->file_loc = $file_path;
		$this->file     = $file_path;
	}

	/**
	 * Inits the object with a relative path.
	 *
	 * @internal
	 *
	 * @param string $relative_path A relative path to a file.
	 */
	protected function init_with_relative_path( $relative_path ) {
		$file_path = URLHelper::get_full_path( $relative_path );

		$this->abs_url  = home_url( $relative_path );
		$this->file_loc = $file_path;
		$this->file     = $file_path;
	}

	/**
	 * Inits the object with an URL.
	 *
	 * @internal
	 *
	 * @param string $url An URL on the same host.
	 */
	protected function init_with_url( $url ) {
		$this->abs_url = $url;

		if ( URLHelper::is_local( $url ) ) {
			$this->file     = URLHelper::remove_double_slashes(
				ABSPATH . URLHelper::get_rel_url( $url )
			);
			$this->file_loc = URLHelper::remove_double_slashes(
				ABSPATH . URLHelper::get_rel_url( $url )
			);
		}
	}

	/**
	 * @internal
	 *
	 * @param  int $attachment_id the id number of the image in the WP database
	 */
	protected function get_attachment_info( $attachment_id ) {
		$image_info = $attachment_id;

		if ( is_numeric( $attachment_id ) ) {
			$image_info = wp_get_attachment_metadata( $attachment_id );

			if ( ! is_array( $image_info ) ) {
				$image_info = array();
			}

			$meta_values = $this->get_meta_values( $attachment_id );
			$post        = get_post( $attachment_id );

			if ( $post ) {
				if ( isset( $post->post_excerpt ) ) {
					$this->caption = $post->post_excerpt;
				}

				$meta_values = array_merge( $meta_values, get_object_vars( $post ) );
			}

			return array_merge( $image_info, $meta_values );
		}

		if ( is_array( $image_info ) && isset( $image_info['image'] ) ) {
			return $image_info['image'];
		}

		if ( is_object( $image_info ) ) {
			return get_object_vars( $image_info );
		}

		return $attachment_id;
	}

	/**
	 * Secures an URL based on the current environment.
	 *
	 * @param  string $url The URL to evaluate.
	 *
	 * @return string An URL with or without http/https, depending on what’s appropriate for server.
	 */
	protected function maybe_secure_url( $url ) {
		if ( is_ssl() && strpos( $url, 'https' ) !== 0 && strpos( $url, 'http' ) === 0 ) {
			$url = 'https' . substr( $url, strlen( 'http' ) );
		}

		return $url;
	}

	/**
	 * Gets the link to an attachment.
	 *
	 * This returns a link to an attachment’s page, but not the link to the image src itself.
	 *
	 * @api
	 * @example
	 * ```twig
	 * <a href="{{ image.link }}"><img src="{{ image.src }} "></a>
	 * ```
	 * ```html
	 * <a href="http://example.org/my-cool-picture">
	 *     <img src="http://example.org/wp-content/uploads/2015/whatever.jpg"/>
	 * </a>
	 * ```
	 */
	public function link() {
		if ( strlen( $this->abs_url ) ) {
			return $this->abs_url;
		}

		return get_permalink( $this->ID );
	}

	/**
	 * Gets the relative path to an attachment.
	 *
	 * @api
	 * @example
	 * ```twig
	 * <img src="{{ image.path }}" />
	 * ```
	 * ```html
	 * <img src="/wp-content/uploads/2015/08/pic.jpg" />
	 * ```
	 *
	 * @return string The relative path to an attachment.
	 */
	public function path() {
		return URLHelper::get_rel_path( $this->src() );
	}

	/**
	 * Gets the source URL for an attachment.
	 *
	 * @api
	 * @example
	 * ```twig
	 * <a href="{{ Attachment(post.meta('job_pdf')).src }}" download>
	 * ```
	 * ```html
	 * <a href="http://example.org/wp-content/uploads/2015/08/job-ad-5noe2304i.pdf" download>
	 * ```
	 *
	 * @return bool|string
	 */
	public function src() {
		if ( isset( $this->abs_url ) ) {
			return $this->maybe_secure_url( $this->abs_url );
		}

		return wp_get_attachment_url( $this->ID );
	}

	/**
	 * Gets filesize in a human readable format.
	 *
	 * This can be useful if you want to display the human readable filesize for a file. It’s
	 * easier to read «16 KB» than «16555 bytes» or «1 MB» than «1048576 bytes».
	 *
	 * @api
	 * @since 2.0.0
	 * @example
	 *
	 * Use filesize information in a link that downloads a file:
	 *
	 * ```twig
	 * <a class="download" href="{{ attachment.src }}" download="{{ attachment.title }}">
	 *     <span class="download-title">{{ attachment.title }}</span>
	 *     <span class="download-info">(Download, {{ attachment.size }})</span>
	 * </a>
	 * ```
	 * @return mixed|null The filesize string in a human readable format.
	 */
	public function size() {
		if ( ! $this->file_size ) {
			$formatted_size  = size_format( $this->size_raw() );
			$this->file_size = str_replace( ' ', '&nbsp;', $formatted_size );
		}

		return $this->file_size;
	}

	/**
	 * Gets filesize in bytes.
	 *
	 * @api
	 * @since 2.0.0
	 * @example
	 *
	 * ```twig
	 * <table>
	 *     {% for attachment in Attachment(attachment_ids) %}
	 *         <tr>
	 *             <td>{{ attachment.title }}</td>
	 *             <td>{{ attachment.extension }}</td>
	 *             <td>{{ attachment.size_raw }} bytes</td>
	 *         </tr>
	 *     {% endfor %}
	 * </table>
	 * ```
	 *
	 * @return mixed|null The filesize string in bytes, or false if the filesize can’t be read.
	 */
	public function size_raw() {
		if ( ! $this->file_size_raw ) {
			$this->file_size_raw = filesize( $this->file_loc );
		}

		return $this->file_size_raw;
	}

	/**
	 * Gets the extension of the attached file.
	 *
	 * @api
	 * @since 2.0.0
	 * @example
	 *
	 * Use extension information in a link that downloads a file:
	 *
	 * ```twig
	 * <a class="download" href="{{ attachment.src }}" download="{{ attachment.title }}">
	 *     <span class="download-title">{{ attachment.title }}</span>
	 *     <span class="download-info">
	 *         (Download {{ attachment.extension|upper }}, {{ attachment.size }})
	 *     </span>
	 * </a>
	 * ```
	 *
	 * @return null|string An uppercase extension string.
	 */
	public function extension() {
		if ( ! $this->file_extension ) {
			$file_info = wp_check_filetype( $this->file );

			if ( ! empty( $file_info['ext'] ) ) {
				$this->file_extension = strtoupper( $file_info['ext'] );
			}
		}

		return $this->file_extension;
	}

	/**
	 * Gets the parent object.
	 *
	 * The parent object of an attachment is a post it is assigned to.
	 *
	 * @api
	 * @example
	 * ```twig
	 * This image is assigned to {{ image.parent.title }}
	 * ```
	 *
	 * @return false|\Timber\Post Parent object as a `Timber\Post`. Returns `false` if no parent
	 *                            object is defined.
	 */
	public function parent() {
		if ( ! $this->post_parent ) {
			return false;
		}

		return new $this->PostClass( $this->post_parent );
	}

	/**
	 * Get a PHP array with pathinfo() info from the file
	 *
	 * @api
	 *
	 * @return array
	 */
	public function get_pathinfo() {
		return pathinfo( $this->file );
	}
}
