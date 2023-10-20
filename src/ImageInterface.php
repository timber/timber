<?php

namespace Timber;

/**
 * Interface ImageInterface
 */
interface ImageInterface
{
    /**
     * Gets the relative path to an attachment.
     *
     * @api
     * @return string The relative path to an attachment.
     */
    public function path(): string;

    /**
     * Gets the caption of an attachment.
     *
     * @api
     * @since 2.0
     * @return string|null
     */
    public function caption(): ?string;

    /**
     * Gets filesize in bytes.
     *
     * @api
     * @since 2.0.0
     * @return int|null The filesize string in bytes, or null if the filesize can’t be read.
     */
    public function size(): ?int;

    /**
     * Gets the extension of the attached file.
     *
     * @api
     * @since 2.0.0
     * @return string|null An uppercase extension string.
     */
    public function extension(): ?string;

    /**
     * Gets the source URL for the image.
     *
     * @return string The src of the file.
     */
    public function __toString(): string;

    /**
     * Gets the source URL for the image.
     *
     * You can use WordPress image sizes (including the ones you registered with your theme or
     * plugin) by passing the name of the size to this function (like `medium` or `large`). If the
     * WordPress size has not been generated, it will return an empty string.
     *
     * @api
     * @param string $size Optional. The requested image size. This can be a size that was in
     *                     WordPress. Example: `medium` or `large`. Default `full`.
     *
     * @return string The src URL for the image.
     */
    public function src($size = 'full'): string;

    /**
     * Gets the width of the image in pixels.
     *
     * @api
     * @return int The width of the image in pixels.
     */
    public function width();

    /**
     * Gets the height of the image in pixels.
     *
     * @api
     * @return int The height of the image in pixels.
     */
    public function height();

    /**
     * Gets the aspect ratio of the image.
     *
     * @api
     * @return float The aspect ratio of the image.
     */
    public function aspect();

    /**
     * Gets the alt text for an image.
     *
     * For better accessibility, you should always add an alt attribute to your images, even if it’s
     * empty.
     *
     * @api
     * @return string|null Alt text stored in WordPress.
     */
    public function alt(): ?string;
}
