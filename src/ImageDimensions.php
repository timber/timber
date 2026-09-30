<?php

namespace Timber;

use XMLReader;

/**
 * Class ImageDimensions
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
     * @param string $file_loc
     */
    public function __construct(
        /**
         * File path.
         *
         * @api
         * @var string The absolute path to the image in the filesystem
         *             (Example: `/var/www/htdocs/wp-content/uploads/2015/08/my-pic.jpg`)
         */
        public string $file_loc = '',
        /**
         * Attachment ID.
         *
         * Used to retrieve image dimensions from the attachment metadata.
         *
         * @api
         * @var int|null If passed, image dimensions will be retrieved from the attachment metadata.
         */
        public ?int $attachment_id = null
    ) {
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
     * <img src="https://example.org/wp-content/uploads/2015/08/pic.jpg" width="1600" />
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
     * <img src="https://example.org/wp-content/uploads/2015/08/pic.jpg" height="900" />
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

        // Load dimensions from attachment metadata. This should be significantly faster than
        // reading from the file directly.
        if ($this->attachment_id) {
            $meta = \wp_get_attachment_metadata($this->attachment_id);

            if ($meta && isset($meta['width']) && isset($meta['height'])) {
                $this->dimensions = [
                    (int) $meta['width'],
                    (int) $meta['height'],
                ];

                return $this->get_dimension_loaded($dimension);
            }
        }

        // Load dimensions by reading file data.
        if (\file_exists($this->file_loc) && \filesize($this->file_loc)) {
            if (ImageHelper::is_svg($this->file_loc)) {
                $svg_size = $this->get_dimensions_svg($this->file_loc);
                $this->dimensions = [(int) \round($svg_size->width), (int) \round($svg_size->height)];
            } else {
                [$width, $height] = \getimagesize($this->file_loc);

                $this->dimensions = [(int) $width, (int) $height];
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
        $dim = \strtolower((string) $dim);

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
        $attributes = $this->get_svg_root_attributes($svg);
        $width = 0;
        $height = 0;

        if (null !== $attributes) {
            if (null !== $attributes['viewBox']) {
                // Four numbers separated by whitespace and/or commas.
                $viewbox = \preg_split('/[\s,]+/', \trim($attributes['viewBox']));
                $width = $viewbox[2] ?? 0;
                $height = $viewbox[3] ?? 0;
            } elseif ($attributes['width'] && $attributes['height']) {
                $width = $attributes['width'];
                $height = $attributes['height'];
            }
        }

        return (object) [
            'width' => (float) $width,
            'height' => (float) $height,
        ];
    }

    /**
     * Reads `width`, `height` and `viewBox` off the root element of an SVG file.
     *
     * Uses a pull parser that stops as soon as the root element is available, so the body of the
     * document is never parsed. This way, dimensions are cheap to read even
     * for a multi-megabyte SVG.
     *
     * The parse is also hardened against XXE. What keeps an external entity or DTD from being
     * loaded is that `LIBXML_NOENT` and `LIBXML_DTDLOAD` are deliberately never passed: libxml
     * only fetches what those options ask for. Without that, an attacker-supplied SVG could read
     * local files or drive SSRF. `LIBXML_NONET` is defence in depth only: it rejects `http://` and
     * `ftp://` URLs, but not the schemes PHP's stream wrappers add on top (`https://` included).
     *
     * @internal
     * @param string $file Path to the SVG file.
     * @return array{width: string|null, height: string|null, viewBox: string|null}|null
     *         Null when the file has no root element, or when its root is not an `<svg>`.
     */
    private function get_svg_root_attributes(string $file): ?array
    {
        // XMLReader::open() throws on an empty path where simplexml_load_file() answered false.
        if ('' === $file) {
            return null;
        }

        $reader = @XMLReader::open($file, null, \LIBXML_NONET | \LIBXML_NOERROR | \LIBXML_NOWARNING);

        if (!$reader instanceof XMLReader) {
            return null;
        }

        try {
            while (@$reader->read()) {
                if (XMLReader::ELEMENT !== $reader->nodeType) {
                    continue;
                }

                // The root element has to be an <svg>. localName ignores any namespace prefix, so
                // a document rooted in <svg:svg> is still recognised. The comparison folds case:
                // XML preserves tag case, so <SVG> is still an SVG.
                if (0 !== \strcasecmp('svg', $reader->localName)) {
                    return null;
                }

                // Attributes are keyed by lower-cased local name, so coarse markup that uppercases
                // them (WIDTH, VIEWBOX) reads the same as its canonical form.
                $found = [];

                if ($reader->moveToFirstAttribute()) {
                    do {
                        $found[\strtolower($reader->localName)] = $reader->value;
                    } while ($reader->moveToNextAttribute());
                }

                return [
                    'width' => $found['width'] ?? null,
                    'height' => $found['height'] ?? null,
                    'viewBox' => $found['viewbox'] ?? null,
                ];
            }

            return null;
        } finally {
            $reader->close();
        }
    }
}
