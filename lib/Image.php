<?php

namespace Timber;

/**
 * Class Image
 *
 * The `Timber\Image` class represents WordPress attachments that are images.
 *
 * @api
 * @example
 * ```php
 * $post = new Timber\Post();
 *
 * $context         = Timber::context();
 * $context['post'] = Timber::context_post( $post );
 *
 * // lets say you have an alternate large 'cover image' for your post stored in a custom field which returns an image ID
 * $cover_image_id = $post->cover_image;
 * $context['cover_image'] = new Timber\Image($cover_image_id);
 * Timber::render('single.twig', $context);
 * ```
 *
 * ```twig
 * <article>
 *   <img src="{{cover_image.src}}" class="cover-image" />
 *   <h1 class="headline">{{post.title}}</h1>
 *   <div class="body">
 *     {{post.content}}
 *   </div>
 *
 *  <img src="{{ Image(post.custom_field_with_image_id).src }}" alt="Another way to initialize images as Timber\Image objects, but within Twig" />
 * </article>
 * ```
 *
 * ```html
 * <article>
 *   <img src="http://example.org/wp-content/uploads/2015/06/nevermind.jpg" class="cover-image" />
 *   <h1 class="headline">Now you've done it!</h1>
 *   <div class="body">
 *     Whatever whatever
 *   </div>
 *   <img src="http://example.org/wp-content/uploads/2015/06/kurt.jpg" alt="Another way to initialize images as Timber\Image objects, but within Twig" />
 * </article>
 * ```
 */
class Image extends Attachment {
	/**
	 * Object type.
	 *
	 * @api
	 * @var string What the object represents in WordPress terms.
	 */
	public $object_type = 'image';

	/**
	 * Representation
	 *
	 * @api
	 * @var string What does this class represent in WordPress terms?
	 */
	public static $representation = 'image';

	/**
	 * Image sizes.
	 *
	 * @api
	 * @var array An array of available sizes for the image.
	 */
	public $sizes = array();

	/**
	 * Image dimensions.
	 *
	 * @internal
	 * @var array An index array of image dimensions, where the first is the width and the second
	 *            item is the height of the image in pixels.
	 */
	protected $dimensions;

	/**
	 * Gets the source URL for the image.
	 *
	 * You can use WordPress image sizes (including the ones you registered with your theme or
	 * plugin) by passing the name of the size to this function (like `medium` or `large`). If the
	 * WordPress size has not been generated, it will return an empty string.
	 *
	 * @api
	 * @example
	 * ```twig
	 * <img src="{{ post.thumbnail.src }}">
	 * <img src="{{ post.thumbnail.src('medium') }}">
	 * ```
	 * ```html
	 * <img src="http://example.org/wp-content/uploads/2015/08/pic.jpg" />
	 * <img src="http://example.org/wp-content/uploads/2015/08/pic-800-600.jpg">
	 * ```
	 *
	 * @param string $size Optional. The requested image size. This can be a size that was in
	 *                     WordPress. Example: `medium` or `large`. Default `full`.
	 *
	 * @return bool|string The src URL for the image.
	 */
	public function src( $size = 'full' ) {
		if ( isset( $this->abs_url ) ) {
			return $this->maybe_secure_url( $this->abs_url );
		}

		if ( ! $this->is_image() ) {
			return wp_get_attachment_url( $this->ID );
		}

		$src = wp_get_attachment_image_src( $this->ID, $size );
		$src = $src[0];

		/**
		 * Filters the src URL for a `Timber\Image`.
		 *
		 * @see \Timber\Image::src()
		 * @since 0.21.7
		 *
		 * @param string $src The image src.
		 * @param int    $id  The image ID.
		 */
		$src = apply_filters( 'timber/image/src', $src, $this->ID );

		/**
		 * Filters the src URL for a `Timber\Image`.
		 *
		 * @deprecated 2.0.0, use `timber/image/src`
		 */
		$src = apply_filters_deprecated(
			'timber_image_src',
			array( $src, $this->ID ),
			'2.0.0',
			'timber/image/src'
		);

		return $src;
	}

	/**
	 * Gets the width of the image in pixels.
	 *
	 * @api
	 * @example
	 * ```twig
	 * <img src="{{ image.src }}" width="{{ image.width }}" />
	 * ```
	 * ```html
	 * <img src="http://example.org/wp-content/uploads/2015/08/pic.jpg" width="1600" />
	 * ```
	 *
	 * @return int The width of the image in pixels.
	 */
	public function width() {
		return $this->get_dimension( 'width' );
	}

	/**
	 * Gets the height of the image in pixels.
	 *
	 * @api
	 * @example
	 * ```twig
	 * <img src="{{ image.src }}" height="{{ image.height }}" />
	 * ```
	 * ```html
	 * <img src="http://example.org/wp-content/uploads/2015/08/pic.jpg" height="900" />
	 * ```
	 *
	 * @return int The height of the image in pixels.
	 */
	public function height() {
		return $this->get_dimension( 'height' );
	}

	/**
	 * Gets the aspect ratio of the image.
	 *
	 * @api
	 * @example
	 * ```twig
	 * {% if post.thumbnail.aspect < 1 %}
	 *   {# handle vertical image #}
	 *   <img src="{{ post.thumbnail.src|resize(300, 500) }}" alt="A basketball player" />
	 * {% else %}
	 *   <img src="{{ post.thumbnail.src|resize(500) }}" alt="A sumo wrestler" />
	 * {% endif %}
	 * ```
	 * @return float
	 */
	public function aspect() {
		$w = intval( $this->width() );
		$h = intval( $this->height() );

		return $w / $h;
	}

	/**
	 * Gets the alt text for an image.
	 *
	 * For better accessibility, you should always add an alt attribute to your images, even if it’s
	 * empty.
	 *
	 * @api
	 * @example
	 * ```twig
	 * <img src="{{ image.src }}" alt="{{ image.alt }}" />
	 * ```
	 * ```html
	 * <img src="http://example.org/wp-content/uploads/2015/08/pic.jpg"
	 *     alt="You should always add alt texts to your images for better accessibility" />
	 * ```
	 * @return string alt text stored in WordPress
	 */
	public function alt() {
		$alt = $this->meta( '_wp_attachment_image_alt' );
		return trim( strip_tags( $alt ) );
	}

	/**
	 * Gets dimension for an image.
	 *
	 * @internal
	 *
	 * @param string $dimension The requested dimension. Either `width` or `height`.
	 *
	 * @return int|null The requested dimension. Null if image file couldn’t be found.
	 */
	protected function get_dimension( $dimension ) {
		// Load from internal cache.
		if ( isset( $this->dimensions ) ) {
			return $this->get_dimension_loaded( $dimension );
		}

		// Load dimensions.
		if ( file_exists( $this->file_loc ) && filesize( $this->file_loc ) ) {
			list( $width, $height ) = getimagesize( $this->file_loc );

			$this->dimensions    = array();
			$this->dimensions[0] = $width;
			$this->dimensions[1] = $height;

			return $this->get_dimension_loaded( $dimension );
		}

		return null;
	}

	/**
	 * Gets already loaded dimension values.
	 *
	 * @internal
	 *
	 * @param string|null $dim The requested dimension. Either `width` or `height`.
	 *
	 * @return int The requested dimension in pixels.
	 */
	protected function get_dimension_loaded( $dim = null ) {
		$dim = strtolower( $dim );

		if ( 'h' === $dim || 'height' === $dim ) {
			return $this->dimensions[1];
		}

		return $this->dimensions[0];
	}

	/**
	 * Checks whether the image is really an image.
	 *
	 * @internal
	 * @return bool Whether the attachment is really an image.
	 */
	protected function is_image() {
		$src        = wp_get_attachment_url( $this->ID );
		$check      = wp_check_filetype( basename( $src ), null );
		$image_exts = array( 'jpg', 'jpeg', 'jpe', 'gif', 'png' );

		return in_array( $check['ext'], $image_exts, true );
	}
}
