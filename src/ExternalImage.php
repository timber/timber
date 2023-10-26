<?php

namespace Timber;

/**
 * Class ExternalImage
 *
 * The `Timber\ExternalImage` class represents an image that is not part of the WordPress content (Attachment).
 * Instead, it’s an image that can be either a path (relative/absolute) on the same server, or a URL (either from the
 * same or from a different website). When you use a URL of an image on a different website, Timber will load it into
 * your WordPress installation once and then load it from there.
 *
 * @api
 * @example
 * ```php
 * $context = Timber::context();
 *
 * // Lets say you have an external image that you want to use in your theme
 *
 * $context['cover_image'] = Timber::get_external_image($url);
 *
 * Timber::render('single.twig', $context);
 * ```
 *
 * ```twig
 * <article>
 *   <img src="{{ cover_image.src }}" class="cover-image" />
 *   <h1 class="headline">{{ post.title }}</h1>
 *   <div class="body">
 *     {{ post.content }}
 *   </div>
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
 * </article>
 * ```
 */
class ExternalImage implements ImageInterface
{
    /**
     * Alt text.
     *
     * @api
     * @var string
     */
    protected string $alt_text;

    /**
     * Alt text.
     *
     * @api
     * @var string
     */
    protected string $caption;

    /**
     * Representation.
     *
     * @var string What does this class represent in WordPress terms?
     */
    public static $representation = 'image';

    /**
     * File location.
     *
     * @api
     * @var string The absolute path to the attachmend file in the filesystem
     *             (Example: `/var/www/htdocs/wp-content/themes/my-theme/images/`)
     */
    protected string $file_loc;

    /**
     * File extension.
     *
     * @api
     * @since 2.0.0
     * @var string A file extension.
     */
    protected string $file_extension;

    /**
     * Absolute URL.
     *
     * @var string The absolute URL to the attachment.
     */
    public $abs_url;

    /**
     * Size.
     *
     * @var integer|null
     */
    protected ?int $size;

    /**
     * File types.
     *
     * @var array An array of supported relative file types.
     */
    private $image_file_types = [
        'jpg',
        'jpeg',
        'png',
        'svg',
        'bmp',
        'ico',
        'gif',
        'tiff',
        'pdf',
    ];

    /**
     * Image dimensions.
     *
     * @internal
     * @var ImageDimensions|null stores Image Dimensions in a structured way.
     */
    protected ?ImageDimensions $image_dimensions;

    protected function __construct()
    {
    }

    /**
     * Inits the ExternalImage object.
     *
     * @internal
     * @param $url string URL or path to load the image from.
     * @param $args array An array of arguments for the image.
     */
    public static function build($url, array $args = []): ?ExternalImage
    {
        if (!\is_string($url) || \is_numeric($url)) {
            return null;
        }

        $external_image = new static();

        if (!empty($args['alt'])) {
            $external_image->alt_text = (string) $args['alt'];
        }
        if (!empty($args['caption'])) {
            $external_image->caption = (string) $args['caption'];
        }

        if (\str_contains($url, '://')) {
            // Assume URL.
            $external_image->init_with_url($url);

            return $external_image;
        } elseif (\str_contains($url, ABSPATH)) {
            // Assume absolute path.
            $external_image->init_with_file_path($url);

            return $external_image;
        } else {
            // Check for image file types.
            foreach ($external_image->image_file_types as $type) {
                // Assume a relative path.
                if (\str_contains(\strtolower($url), $type)) {
                    $external_image->init_with_relative_path($url);

                    return $external_image;
                }
            }
        }

        return null;
    }

    /**
     * Gets the source URL for the image.
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
     * @param string $size Ignored. For compatibility with Timber\Image.
     *
     * @return string The src URL for the image.
     */
    public function src($size = 'full'): string
    {
        return URLHelper::maybe_secure_url($this->abs_url);
    }

    /**
     * Gets the relative path to the file.
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
     * @return string The relative path to the image file.
     */
    public function path(): string
    {
        return URLHelper::get_rel_path($this->file_loc());
    }

    /**
     * Gets the absolute path to the image.
     *
     * @api
     *
     * @return string
     */
    public function file_loc(): string
    {
        if (isset($this->file_loc)) {
            return $this->file_loc;
        }
        return '';
    }

    /**
     * Gets filesize in a human-readable format.
     *
     * This can be useful if you want to display the human-readable filesize for a file. It’s
     * easier to read «16 KB» than «16555 bytes» or «1 MB» than «1048576 bytes».
     *
     * @api
     * @since 2.0.0
     * @example
     * Use filesize information in a link that downloads a file:
     *
     * ```twig
     * <a class="download" href="{{ attachment.src }}" download="{{ attachment.title }}">
     *     <span class="download-title">{{ attachment.title }}</span>
     *     <span class="download-info">(Download, {{ attachment.size }})</span>
     * </a>
     * ```
     *
     * @return null|int Filsize or null if the filesize couldn't be determined.
     */
    public function size(): ?int
    {
        if (isset($this->size)) {
            return $this->size;
        }

        /**
         * Filesize wasn't found in the metadata, so we'll try to get it from the file itself.
         *
         * We could have used `wp_filesize()` here, but it returns 0 when the file doesn't exist. Which is a perfectly valid filesize
         * and prevents us from telling the difference between a file that doesn't exist and a file that has a filesize of 0.
         *
         * @see https://developer.wordpress.org/reference/functions/wp_filesize/
         */
        $size = \filesize($this->file_loc());
        return $this->size = $size === false ? null : (int) $size;
    }

    /**
     * Gets the src for an attachment.
     *
     * @api
     *
     * @return string The src of the attachment.
     */
    public function __toString(): string
    {
        return $this->src();
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
     * @return string|null An uppercase extension string.
     */
    public function extension(): ?string
    {
        if (isset($this->file_extension)) {
            return $this->file_extension;
        }
        return $this->file_extension = \pathinfo($this->file_loc(), PATHINFO_EXTENSION);
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
     * @return int|null The width of the image in pixels. Null if the width can’t be read, e.g. because the file doesn’t
     *                  exist.
     */
    public function width(): ?int
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
     * @return int|null The height of the image in pixels. Null if the height can’t be read, e.g. because the file
     *                  doesn’t exist.
     */
    public function height(): ?int
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
     *     {# handle vertical image #}
     *     <img src="{{ post.thumbnail.src|resize(300, 500) }}" alt="A basketball player" />
     * {% else %}
     *     <img src="{{ post.thumbnail.src|resize(500) }}" alt="A sumo wrestler" />
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
     * Sets the relative alt text of the image.
     *
     * @param string $alt Alt text for the image.
     */
    public function set_alt(string $alt)
    {
        $this->alt_text = $alt;
    }

    /**
     * Sets the relative alt text of the image.
     *
     * @param string $caption Caption text for the image
     */
    public function set_caption(string $caption)
    {
        $this->caption = $caption;
    }

    /**
     * Inits the object with an absolute path.
     *
     * @internal
     *
     * @param string $file_path An absolute path to a file.
     */
    protected function init_with_file_path($file_path)
    {
        $url = URLHelper::file_system_to_url($file_path);

        $this->abs_url = $url;
        $this->file_loc = $file_path;
        $this->image_dimensions = new ImageDimensions($file_path);
    }

    /**
     * Inits the object with a relative path.
     *
     * @internal
     *
     * @param string $relative_path A relative path to a file.
     */
    protected function init_with_relative_path($relative_path)
    {
        $file_path = URLHelper::get_full_path($relative_path);

        $this->abs_url = \home_url($relative_path);
        $this->file_loc = $file_path;
        $this->image_dimensions = new ImageDimensions($file_path);
    }

    /**
     * Inits the object with an URL.
     *
     * @internal
     *
     * @param string $url An URL on the same host.
     */
    protected function init_with_url($url)
    {
        if (!URLHelper::is_local($url)) {
            $url = ImageHelper::sideload_image($url);
        }

        $this->abs_url = $url;

        if (URLHelper::is_local($url)) {
            $this->file_loc = URLHelper::remove_double_slashes(
                ABSPATH . URLHelper::get_rel_url($url)
            );
            $this->image_dimensions = new ImageDimensions($this->file_loc);
        }
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
     * <img
     *     src="http://example.org/wp-content/uploads/2015/08/pic.jpg"
     *     alt="You should always add alt texts to your images for better accessibility"
     * />
     * ```
     *
     * @return string Alt text stored in WordPress.
     */
    public function alt(): ?string
    {
        return $this->alt_text ?? null;
    }

    public function caption(): ?string
    {
        return $this->caption ?? null;
    }
}
