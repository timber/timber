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
interface ImageInterface
{
    /**
     * Gets the relative path to an attachment.
     *
     * @example
     * ```twig
     * <img src="{{ image.path }}" />
     * ```
     * ```html
     * <img src="/wp-content/uploads/2015/08/pic.jpg" />
     * ```
     *
     * @api
     * @return string The relative path to an attachment.
     */
    public function path(): string;

    /**
     * Gets the caption of an attachment.
     *
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
     * @api
     * @since 2.0
     * @return string
     */
    public function caption(): string;

    /**
     * Gets filesize in a human readable format.
     *
     * This can be useful if you want to display the human readable filesize for a file. It’s
     * easier to read «16 KB» than «16555 bytes» or «1 MB» than «1048576 bytes».
     *
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
     * @api
     * @since 2.0.0
     * @return string|null The filesize string in a human-readable format or null if the
     *                    filesize can’t be read.
     */
    public function size(): ?string;

    /**
     * Gets filesize in bytes.
     *
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
     * @api
     * @since 2.0.0
     * @return int|false The filesize string in bytes, or false if the filesize can’t be read.
     */
    public function size_raw();

    /**
     * Gets the extension of the attached file.
     *
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
     * @api
     * @since 2.0.0
     * @return string|null An uppercase extension string.
     */
    public function extension(): ?string;

    /**
     * @return string The src of the file.
     */
    public function __toString();

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
     * @example
     * ```twig
     * <img src="{{ post.thumbnail.src }}">
     * <img src="{{ post.thumbnail.src('medium') }}">
     * ```
     * ```html
     * <img src="http://example.org/wp-content/uploads/2015/08/pic.jpg" />
     * <img src="http://example.org/wp-content/uploads/2015/08/pic-800-600.jpg">
     * ```
     * @return string|bool The src URL for the image.
     */
    public function src($size = 'full');

    /**
     * Gets the width of the image in pixels.
     *
     * @example
     * ```twig
     * <img src="{{ image.src }}" width="{{ image.width }}" />
     * ```
     * ```html
     * <img src="http://example.org/wp-content/uploads/2015/08/pic.jpg" width="1600" />
     * ```
     *
     * @api
     * @return int The width of the image in pixels.
     */
    public function width();

    /**
     * Gets the height of the image in pixels.
     *
     * @example
     * ```twig
     * <img src="{{ image.src }}" height="{{ image.height }}" />
     * ```
     * ```html
     * <img src="http://example.org/wp-content/uploads/2015/08/pic.jpg" height="900" />
     * ```
     *
     * @api
     * @return int The height of the image in pixels.
     */
    public function height();

    /**
     * Gets the aspect ratio of the image.
     *
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
     * @example
     * ```twig
     * <img src="{{ image.src }}" alt="{{ image.alt }}" />
     * ```
     * ```html
     * <img src="http://example.org/wp-content/uploads/2015/08/pic.jpg"
     *     alt="You should always add alt texts to your images for better accessibility" />
     * ```
     *
     * @api
     * @return string Alt text stored in WordPress.
     */
    public function alt(): string;

    /**
     * @example
     * ```twig
     * <h1>{{ post.title }}</h1>
     * <img src="{{ post.thumbnail.src }}" srcset="{{ post.thumbnail.srcset }}" />
     * ```
     * ```html
     * <img src="http://example.org/wp-content/uploads/2018/10/pic.jpg" srcset="http://example.org/wp-content/uploads/2018/10/pic.jpg 1024w, http://example.org/wp-content/uploads/2018/10/pic-600x338.jpg 600w, http://example.org/wp-content/uploads/2018/10/pic-300x169.jpg 300w" />
     * ```
     * @api
     * @param string $size An image size known to WordPress (like "medium").
     * @return string
     */
    public function srcset(string $size = 'full'): ?string;

    /**
     * @example
     * ```twig
     * <h1>{{ post.title }}</h1>
     * <img src="{{ post.thumbnail.src }}" srcset="{{ post.thumbnail.srcset }}" sizes="{{ post.thumbnail.img_sizes }}" />
     * ```
     * ```html
     * <img src="http://example.org/wp-content/uploads/2018/10/pic.jpg" srcset="http://example.org/wp-content/uploads/2018/10/pic.jpg 1024w, http://example.org/wp-content/uploads/2018/10/pic-600x338.jpg 600w, http://example.org/wp-content/uploads/2018/10/pic-300x169.jpg 300w sizes="(max-width: 1024px) 100vw, 102" />
     * ```
     * @api
     * @param string $size An image size known to WordPress (like "medium").
     * @return string|null
     */
    public function img_sizes(string $size = 'full'): ?string;
}
