<?php

namespace Timber;

use InvalidArgumentException;
use Timber\Factory\PostFactory;

/**
 * Class Attachment
 *
 * Objects of this class represent WordPress attachments. This is the basis that `Timber\Image`
 * objects build upon.
 *
 * @api
 * @since 2.0.0
 */
class Attachment extends Post
{
    /**
     * Representation.
     *
     * @var string What does this class represent in WordPress terms?
     */
    public static $representation = 'attachment';

    /**
     * File.
     *
     * @api
     * @var string
     */
    protected string $file;

    /**
     * File location.
     *
     * @api
     * @var string The absolute path to the attachmend file in the filesystem
     *             (Example: `/var/www/htdocs/wp-content/uploads/2015/08/my-pic.jpg`)
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
     * Attachement metadata.
     *
     * @var array Attachment metadata.
     */
    protected array $metadata;

    /**
     * Size.
     *
     * @var integer|null
     */
    protected ?int $size;

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
     *
     * @return string The URL of the attachment.
     */
    public function link()
    {
        if ($this->abs_url) {
            return $this->abs_url;
        }

        return \get_permalink($this->ID);
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
    public function path(): string
    {
        return URLHelper::get_rel_path($this->src());
    }

    /**
     * Gets the relative path to the uploads folder of an attachment.
     *
     * @api
     *
     * @return string
     */
    public function file(): string
    {
        if (isset($this->file)) {
            return $this->file;
        }
        return $this->file = (string) \get_post_meta($this->ID, '_wp_attached_file', true);
    }

    /**
     * Gets the absolute path to an attachment.
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
        return $this->file_loc = (string) \get_attached_file($this->ID);
    }

    /**
     * Gets the source URL for an attachment.
     *
     * @api
     * @example
     * ```twig
     * <a href="{{ get_attachment(post.meta('job_pdf')).src }}" download>
     * ```
     * ```html
     * <a href="http://example.org/wp-content/uploads/2015/08/job-ad-5noe2304i.pdf" download>
     * ```
     *
     * @return string
     */
    public function src(): string
    {
        return (string) \wp_get_attachment_url($this->ID);
    }

    /**
     * Gets the caption of an attachment.
     *
     * @api
     * @since 2.0
     * @example
     * ```twig
     * <figure>
     *     <img src="{{ post.thumbnail.src }}">
     *
     *     {% if post.thumbnail is not empty %}
     *         <figcaption>{{ post.thumbnail.caption }}</figcaption
     *     {% endif %}
     * </figure>
     * ```
     *
     * @return string|null
     */
    public function caption(): ?string
    {
        /**
         * Filters the attachment caption.
         *
         * @since WordPress 4.6.0
         * @since 2.0.0
         *
         * @param string $caption Caption for the given attachment.
         * @param int    $post_id Attachment ID.
         */
        return \apply_filters('wp_get_attachment_caption', $this->post_excerpt, $this->ID);
    }

    /**
     * Gets the raw filesize in bytes.
     *
     * Use the `size_format` filter to format the raw size into a human readable size («1 MB» intead of «1048576»)
     *
     * @api
     * @since 2.0.0
     * @example
     * @see https://developer.wordpress.org/reference/functions/size_format/
     *
     * Use filesize information in a link that downloads a file:
     *
     * ```twig
     * <a class="download" href="{{ attachment.src }}" download="{{ attachment.title }}">
     *     <span class="download-title">{{ attachment.title }}</span>
     *     <span class="download-info">(Download, {{ attachment.size|size_format }})</span>
     * </a>
     * ```
     *
     * @return int|null The raw filesize or null if it could not be read.
     */
    public function size(): ?int
    {
        if (isset($this->size)) {
            return $this->size;
        }

        /**
         * Since 6.0.0, the filesize is stored in the attachment metadata.
         *
         * @see https://make.wordpress.org/core/2022/05/02/media-storing-file-size-as-part-of-metadata/
         */
        $size = $this->metadata('filesize');
        if ($size !== null && \is_numeric($size)) {
            return $this->size = (int) $size;
        }

        if (!ImageHelper::is_protocol_allowed($this->file_loc())) {
            throw new InvalidArgumentException('The output file scheme is not supported.');
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
     * Gets the extension of the attached file.
     *
     * @api
     * @since 2.0.0
     * @example
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
     * @return string An uppercase extension string.
     */
    public function extension(): string
    {
        if (isset($this->file_extension)) {
            return $this->file_extension;
        }
        return $this->file_extension = \pathinfo($this->file(), PATHINFO_EXTENSION);
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
     * @return null|\Timber\Post Parent object as a `Timber\Post`. Returns `false` if no parent
     *                            object is defined.
     */
    public function parent(): ?Post
    {
        if (!$this->post_parent) {
            return null;
        }

        $factory = new PostFactory();

        return $factory->from($this->post_parent);
    }

    /**
     * Get a PHP array with pathinfo() info from the file
     *
     * @deprecated 2.0.0, use Attachment::pathinfo() instead
     * @return array
     */
    public function get_pathinfo()
    {
        Helper::deprecated(
            "{{ image.get_pathinfo }}",
            "{{ image.pathinfo }}",
            '2.0.0'
        );
        return PathHelper::pathinfo($this->file());
    }

    /**
     * Get a PHP array with pathinfo() info from the file
     *
     * @return array
     */
    public function pathinfo()
    {
        return PathHelper::pathinfo($this->file());
    }

    /**
     * Get attachment metadata the lazy way.
     *
     * This method is used to retrieve the attachment metadata only when it's needed.
     *
     * @param string|null $key
     * @return array|string|int|null
     */
    protected function metadata(?string $key = null)
    {
        // We haven't retrived the metadata yet because it's wasn't needed until now.
        if (!isset($this->metadata)) {
            // Cache it so we don't have to retrieve it again.
            $this->metadata = (array) \wp_get_attachment_metadata($this->ID);
        }

        if ($key === null) {
            return $this->metadata;
        }

        return $this->metadata[$key] ?? null;
    }
}
