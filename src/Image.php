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
 * $context = Timber::context();
 *
 * // Lets say you have an alternate large 'cover image' for your post
 * // stored in a custom field which returns an image ID.
 * $cover_image_id = $context['post']->cover_image;
 *
 * $context['cover_image'] = Timber::get_post($cover_image_id);
 *
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
 *  <img
 *    src="{{ get_image(post.custom_field_with_image_id).src }}"
 *    alt="Another way to initialize images as Timber\Image objects, but within Twig" />
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
 *   <img
 *     src="http://example.org/wp-content/uploads/2015/06/kurt.jpg"
 *     alt="Another way to initialize images as Timber\Image objects, but within Twig" />
 * </article>
 * ```
 */
class Image extends Attachment implements ImageInterface
{
    /**
     * Representation.
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
    protected array $sizes;

    /**
     * Image dimensions.
     *
     * @internal
     * @var ImageDimensions stores Image Dimensions in a structured way.
     */
    protected ImageDimensions $image_dimensions;

    /**
     * Gets the Image information.
     *
     * @internal
     *
     * @param array $data Data to update.
     * @return array
     */
    protected function get_info(array $data): array
    {
        $data = parent::get_info($data);

        if ($this->file_loc()) {
            $data['image_dimensions'] = new ImageDimensions($this->file_loc());
        }

        return $data;
    }

    /**
     * Processes an image's dimensions.
     * @deprecated 2.0.0, use `{{ image.width }}` or `{{ image.height }}` in Twig
     * @internal
     * @param string $dim
     * @return array|int
     */
    protected function get_dimensions($dim)
    {
        Helper::deprecated(
            'Image::get_dimensions()',
            'Image::get_width() | Image::get_height()',
            '2.0.0'
        );
        return [$this->image_dimensions->width(), $this->image_dimensions->height()];
    }

    /**
     * @deprecated 2.0.0, use Image::get_dimension_loaded
     * @internal
     * @param string|null $dim
     * @return array|int
     */
    protected function get_dimensions_loaded($dim)
    {
        Helper::deprecated(
            'Image::get_dimensions()',
            'Image::get_width() or Image::get_height()',
            '2.0.0'
        );

        return $this->image_dimensions->get_dimension($dim);
    }

    /**
     * @deprecated 2.0.0, use Image::meta to retrieve specific fields
     * @return array
     */
    protected function get_post_custom($iid)
    {
        Helper::deprecated(
            '{{ image.get_post_custom( image.id ) }}',
            "{{ image.meta('my_field') }}",
            '2.0.0'
        );
        $pc = \get_post_custom($iid);
        if (\is_bool($pc)) {
            return [];
        }
        return $pc;
    }

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
     * @return string The src URL for the image.
     */
    public function src($size = 'full'): string
    {
        if (isset($this->abs_url)) {
            return URLHelper::maybe_secure_url($this->abs_url);
        }

        $src = \wp_get_attachment_image_src($this->ID, $size);
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
        $src = \apply_filters('timber/image/src', $src, $this->ID);

        /**
         * Filters the src URL for a `Timber\Image`.
         *
         * @deprecated 2.0.0, use `timber/image/src`
         */
        $src = \apply_filters_deprecated(
            'timber_image_src',
            [$src, $this->ID],
            '2.0.0',
            'timber/image/src'
        );

        return $src;
    }

    /**
     * Get image sizes.
     *
     * @return array
     */
    public function sizes(): array
    {
        if (isset($this->sizes)) {
            return $this->sizes;
        }

        return $this->sizes = (array) $this->metadata('sizes');
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
    public function width()
    {
        return $this->image_dimensions->width();
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
    public function height()
    {
        return $this->image_dimensions->height();
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
     *
     * @return float The aspect ratio of the image.
     */
    public function aspect()
    {
        return $this->image_dimensions->aspect();
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
     *
     * @return string|null Alt text stored in WordPress.
     */
    public function alt(): ?string
    {
        $alt = $this->meta('_wp_attachment_image_alt');
        return \trim(\wp_strip_all_tags($alt));
    }

    /**
     * Gets dimension for an image.
     * @deprecated 2.0.0, use `{{ image.width }}` or `{{ image.height }}` in Twig
     * @internal
     *
     * @param string $dimension The requested dimension. Either `width` or `height`.
     * @return int|null The requested dimension. Null if image file couldn’t be found.
     */
    protected function get_dimension($dimension)
    {
        Helper::deprecated(
            'Image::get_dimension()',
            'Image::get_width() or Image::get_height()',
            '2.0.0'
        );
        return $this->image_dimensions->get_dimension($dimension);
    }

    /**
     * Gets already loaded dimension values.
     *
     * @internal
     *
     * @param string|null $dim Optional. The requested dimension. Either `width` or `height`.
     * @return int The requested dimension in pixels.
     */
    protected function get_dimension_loaded($dim = null)
    {
        return $this->image_dimensions->get_dimension($dim);
    }

    /**
     * Gets the srcset attribute for an image based on a WordPress image size.
     *
     * @api
     * @example
     * ```twig
     * <h1>{{ post.title }}</h1>
     * <img src="{{ post.thumbnail.src }}" srcset="{{ post.thumbnail.srcset }}" />
     * ```
     * ```html
     * <img src="http://example.org/wp-content/uploads/2018/10/pic.jpg" srcset="http://example.org/wp-content/uploads/2018/10/pic.jpg 1024w, http://example.org/wp-content/uploads/2018/10/pic-600x338.jpg 600w, http://example.org/wp-content/uploads/2018/10/pic-300x169.jpg 300w" />
     * ```
     * @param string $size An image size known to WordPress (like "medium").
     *
     * @return string|null
     */
    public function srcset(string $size = 'full'): ?string
    {
        return \wp_get_attachment_image_srcset($this->ID, $size) ?: null;
    }

    /**
     * Gets the sizes attribute for an image based on a WordPress image size.
     *
     * @api
     * @example
     * ```twig
     * <h1>{{ post.title }}</h1>
     * <img src="{{ post.thumbnail.src }}" srcset="{{ post.thumbnail.srcset }}" sizes="{{ post.thumbnail.img_sizes }}" />
     * ```
     * ```html
     * <img src="http://example.org/wp-content/uploads/2018/10/pic.jpg" srcset="http://example.org/wp-content/uploads/2018/10/pic.jpg 1024w, http://example.org/wp-content/uploads/2018/10/pic-600x338.jpg 600w, http://example.org/wp-content/uploads/2018/10/pic-300x169.jpg 300w sizes="(max-width: 1024px) 100vw, 102" />
     * ```
     *	@param string $size An image size known to WordPress (like "medium").
     * @return string|null
     */
    public function img_sizes(string $size = 'full'): ?string
    {
        return \wp_get_attachment_image_sizes($this->ID, $size) ?: null;
    }
}
