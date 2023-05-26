<?php

namespace Timber;

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
     * Formatted file size.
     *
     * @api
     * @since 2.0.0
     * @var FileSize File size string.
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
     * Gets the src for an attachment.
     *
     * @api
     *
     * @return string The src of the attachment.
     */
    public function __toString()
    {
        return $this->src();
    }

    /**
     * Gets the attachment information.
     *
     * @internal
     *
     * @param array $data Data to update.
     * @return array Attachment info as an array.
     */
    protected function get_info(array $data): array
    {
        $post_data = parent::get_info($data);
        $image_info = wp_get_attachment_metadata($this->wp_object->ID) ?: [];
        $meta_values = $this->raw_meta();

        $data = array_merge($post_data, $image_info, $meta_values);

        $basedir = wp_get_upload_dir()['basedir'];

        if (isset($data['file'])) {
            $data['file_loc'] = $basedir . DIRECTORY_SEPARATOR . $data['file'];
        } elseif (isset($data['_wp_attached_file'])) {
            $data['file'] = $data['_wp_attached_file'];
            $data['file_loc'] = $basedir . DIRECTORY_SEPARATOR . $data['file'];
        }

        $data['file_size'] = new FileSize($data['file_loc']);

        return $data;
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

        return get_permalink($this->ID);
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
     * @return bool|string
     */
    public function src()
    {
        if (isset($this->abs_url)) {
            return URLHelper::maybe_secure_url($this->abs_url) ?: null;
        }

        return wp_get_attachment_url($this->ID) ?: null;
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
     * @return string
     */
    public function caption(): string
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
        return apply_filters('wp_get_attachment_caption', $this->post_excerpt, $this->ID);
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
     *
     * @return string|null The filesize string in a human-readable format or null if the
     *                     filesize can’t be read.
     */
    public function size(): ?string
    {
        return $this->file_size->size();
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
     * @return int|false The filesize string in bytes, or false if the filesize can’t be read.
     */
    public function size_raw()
    {
        return $this->file_size->size_raw();
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
     * @return string|null An uppercase extension string.
     */
    public function extension(): ?string
    {
        if (!$this->file_extension) {
            $file_info = wp_check_filetype($this->file);

            if (!empty($file_info['ext'])) {
                $this->file_extension = strtoupper($file_info['ext']);
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
    public function parent()
    {
        if (!$this->post_parent) {
            return false;
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
        return PathHelper::pathinfo($this->file);
    }

    /**
     * Get a PHP array with pathinfo() info from the file
     *
     * @return array
     */
    public function pathinfo()
    {
        return PathHelper::pathinfo($this->file);
    }
}
