<?php

namespace Timber;

use InvalidArgumentException;

/**
 * Class FileSize
 *
 * Helper class to deal with Image Dimensions
 *
 * @api
 * @since 2.0.0
 */
class ImageDimensions
{
    /**
     * Image dimensions.
     *
     * @internal
     * @var array An index array of image dimensions, where the first is the width and the second
     *            item is the height of the image in pixels.
     */
    protected $dimensions;

    /**
     * File location.
     *
     * @api
     * @var string The absolute path to the image in the filesystem
     *             (Example: `/var/www/htdocs/wp-content/uploads/2015/08/my-pic.jpg`)
     */
    public $file_loc;

    public function __construct($file_loc = '')
    {
        $this->file_loc = $file_loc;
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
        return $this->get_dimension('width');
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
        return $this->get_dimension('height');
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
     * @return float|null The aspect ratio of the image. Null if the aspect ratio can’t be calculated.
     */
    public function aspect(): ?float
    {
        $w = \intval($this->width());
        $h = \intval($this->height());

        if ($w and $h > 0) {
            return $w / $h;
        }

        return null;
    }

    /**
     * Gets dimension for an image.
     *
     * @internal
     * @param string $dimension The requested dimension. Either `width` or `height`.
     * @return int|null The requested dimension. Null if image file couldn’t be found.
     */
    public function get_dimension($dimension): ?int
    {
        // Load from internal cache.
        if (isset($this->dimensions)) {
            return $this->get_dimension_loaded($dimension);
        }

        if (!ImageHelper::is_protocol_allowed($this->file_loc)) {
            throw new InvalidArgumentException('The output file scheme is not supported.');
        }

        // Load dimensions.
        if (\file_exists($this->file_loc) && \filesize($this->file_loc)) {
            if (ImageHelper::is_svg($this->file_loc)) {
                $svg_size = $this->get_dimensions_svg($this->file_loc);
                $this->dimensions = [(int) \round($svg_size->width), (int) \round($svg_size->height)];
            } else {
                list($width, $height) = \getimagesize($this->file_loc);

                $this->dimensions = [];
                $this->dimensions[0] = (int) $width;
                $this->dimensions[1] = (int) $height;
            }

            return $this->get_dimension_loaded($dimension);
        }

        return null;
    }

    /**
     * Gets already loaded dimension values.
     *
     * @internal
     * @param string|null $dim Optional. The requested dimension. Either `width` or `height`.
     * @return int The requested dimension in pixels.
     */
    protected function get_dimension_loaded($dim = null): int
    {
        $dim = \strtolower($dim);

        if ('h' === $dim || 'height' === $dim) {
            return $this->dimensions[1];
        }

        return $this->dimensions[0];
    }

    /**
     * Retrieve dimensions from SVG file.
     *
     * @internal
     * @param string $svg SVG Path
     * @return object
     */
    protected function get_dimensions_svg($svg)
    {
        $svg = \simplexml_load_file($svg);
        $width = 0;
        $height = 0;

        if (false !== $svg) {
            $attributes = $svg->attributes();
            if (isset($attributes->viewBox)) {
                $viewbox = \explode(' ', $attributes->viewBox);
                $width = $viewbox[2];
                $height = $viewbox[3];
            } elseif ($attributes->width && $attributes->height) {
                $width = $attributes->width;
                $height = $attributes->height;
            }
        }

        return (object) [
            'width' => (float) $width,
            'height' => (float) $height,
        ];
    }
}
