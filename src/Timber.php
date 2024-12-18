<?php

namespace Timber;

use Timber\Factory\CommentFactory;
use Timber\Factory\MenuFactory;
use Timber\Factory\PagesMenuFactory;
use Timber\Factory\PostFactory;
use Timber\Factory\TermFactory;
use Timber\Factory\UserFactory;
use Timber\Integration\IntegrationInterface;
use WP_Comment;
use WP_Comment_Query;
use WP_Post;
use WP_Query;
use WP_Term;
use WP_User;

/**
 * Class Timber
 *
 * Main class called Timber for this plugin.
 *
 * @api
 * @example
 * ```php
 * // Get default posts on an archive page
 * $posts = Timber::get_posts();
 *
 * // Query for some posts
 * $posts = Timber::get_posts( [
 *     'post_type' => 'article',
 *     'category_name' => 'sports',
 * ] );
 *
 * $context = Timber::context( [
 *     'posts' => $posts,
 * ] );
 *
 * Timber::render( 'index.twig', $context );
 * ```
 */
class Timber
{
    public static $version = '2.3.1'; // x-release-please-version

    public static $locations;

    public static $dirname = 'views';

    public static $auto_meta = true;

    /**
     * Global context cache.
     *
     * @var array An array containing global context variables.
     */
    public static $context_cache = [];

    /**
     * Caching option for Twig.
     *
     * @deprecated 2.0.0
     * @var bool
     */
    public static $twig_cache = false;

    /**
     * Caching option for Twig.
     *
     * Alias for `Timber::$twig_cache`.
     *
     * @deprecated 2.0.0
     * @var bool
     */
    public static $cache = false;

    /**
     * Autoescaping option for Twig.
     *
     * @deprecated 2.0.0
     * @var bool
     */
    public static $autoescape = false;

    /**
     * Timber should be loaded with Timber\Timber::init() and not new Timber\Timber();
     *
     * @codeCoverageIgnore
     */
    protected function __construct()
    {
    }

    protected function init_constants()
    {
        \defined("TIMBER_LOC") or \define("TIMBER_LOC", \realpath(\dirname(__DIR__)));
    }

    /**
     * @codeCoverageIgnore
     */
    public static function init(): void
    {
        if (
            !\defined('ABSPATH')
            || !\class_exists('\WP')
            || \defined('TIMBER_LOADED')
        ) {
            return;
        }

        $self = new self();
        $self->init_constants();

        Twig::init();
        ImageHelper::init();

        \add_action('init', [self::class, 'init_integrations']);
        \add_action('admin_init', [Admin::class, 'init']);

        \add_filter('timber/post/import_data', [self::class, 'handle_preview'], 10, 2);

        /**
         * Make an alias for the Timber class.
         *
         * This way, developers can use Timber::render() instead of Timber\Timber::render, which
         * is more user-friendly.
         */
        \class_alias(Timber::class, 'Timber');

        \define('TIMBER_LOADED', true);
    }

    /**
     * Initializes Timber's integrations.
     *
     * @return void
     */
    public static function init_integrations(): void
    {
        $integrations = [
            new Integration\AcfIntegration(),
            new Integration\CoAuthorsPlusIntegration(),
            new Integration\WpCliIntegration(),
            new Integration\WpmlIntegration(),
        ];

        /**
         * Filters the integrations that should be initialized by Timber.
         *
         * @since 2.0.0
         *
         * @param IntegrationInterface[] $integrations An array of PHP class names. Default: array of
         *                            integrations that Timber initializes by default.
         */
        $integrations = \apply_filters('timber/integrations', $integrations);

        // Integration classes must implement the IntegrationInterface.
        $integrations = \array_filter($integrations, static fn ($integration) => $integration instanceof IntegrationInterface);

        foreach ($integrations as $integration) {
            if (!$integration->should_init()) {
                continue;
            }
            $integration->init();
        }
    }

    /**
     * Handles previewing posts.
     *
     * @param array $data
     * @param Post $post
     * @return array
     */
    public static function handle_preview($data, $post)
    {
        if (!isset($_GET['preview']) || !isset($_GET['preview_id'])) {
            return $data;
        }

        $preview_post_id = (int) $_GET['preview_id'];
        $current_post_id = $post->ID ?? null;
        // ⚠️ Don't filter imported data if the current post ID is not the preview post ID.
        // You might alter every `Timber::get_post()`!
        if ($current_post_id !== $preview_post_id) {
            return $data;
        }

        $preview = \wp_get_post_autosave($preview_post_id);

        if (\is_object($preview)) {
            $preview = \sanitize_post($preview);

            $data['post_content'] = $preview->post_content;
            $data['post_title'] = $preview->post_title;
            $data['post_excerpt'] = $preview->post_excerpt;

            \add_filter('get_the_terms', '_wp_preview_terms_filter', 10, 3);
        }

        return $data;
    }

    /* Post Retrieval Routine
    ================================ */

    /**
     * Gets a Timber Post from a post ID, WP_Post object, a WP_Query object, or an associative
     * array of arguments for WP_Query::__construct().
     *
     * By default, Timber will use the `Timber\Post` class to create a new post object. To control
     * which class is instantiated for your Post object, use [Class Maps](https://timber.github.io/docs/v2/guides/class-maps/)
     *
     * @api
     * @example
     * ```php
     * // Using a post ID.
     * $post = Timber::get_post( 75 );
     *
     * // Using a WP_Post object.
     * $wp_post = get_post( 123 );
     * $post    = Timber::get_post( $wp_post );
     *
     * // Using a WP_Query argument array
     * $post = Timber::get_post( [
     *   'post_type' => 'page',
     * ] );
     *
     * // Use currently queried post. Same as using get_the_ID() as a parameter.
     * $post = Timber::get_post();
     *
     * // From an associative array with an `ID` key. For ACF compatibility. Using this
     * // approach directly is not recommended. If you can, configure the return type of your
     * // ACF field to just the ID.
     * $post = Timber::get_post( get_field('associated_post_array') ); // Just OK.
     * $post = Timber::get_post( get_field('associated_post_id') ); // Better!
     * ```
     * @see https://developer.wordpress.org/reference/classes/wp_query/__construct/
     *
     * @param mixed $query   Optional. Post ID or query (as an array of arguments for WP_Query).
     * 	                     If a query is provided, only the first post of the result will be
     *                       returned. Default false.
     * @param array $options Optional associative array of options. Defaults to an empty array.
     *
     * @return Post|null Timber\Post object if a post was found, null if no post was
     *                           found.
     */
    public static function get_post(mixed $query = false, $options = [])
    {
        self::check_post_api_deprecations($query, $options, 'Timber::get_post()');

        if (\is_string($options)) {
            $options = [];
        }

        $factory = new PostFactory();

        global $wp_query;

        $options = \wp_parse_args($options, [
            'merge_default' => false,
        ]);

        // Has WP already queried and found a post?
        if ($query === false && ($wp_query->queried_object instanceof WP_Post)) {
            $query = $wp_query->queried_object;
        } elseif (\is_array($query) && $options['merge_default']) {
            $query = \wp_parse_args($wp_query->query_vars);
        }

        // Default to the global query.
        $result = $factory->from($query ?: $wp_query);

        // If we got a Collection, return the first Post.
        if ($result instanceof PostCollectionInterface) {
            return $result[0] ?? null;
        }

        return $result;
    }

    /**
     * Gets an attachment.
     *
     * Behaves just like Timber::get_post(), except that it returns null if it finds a Timber\Post
     * that is not an Attachment. Honors Class Maps and falsifies return value *after* Class Map for
     * the found Timber\Post has been resolved.
     *
     * @api
     * @since 2.0.0
     * @see Timber::get_post()
     * @see https://timber.github.io/docs/v2/guides/class-maps/
     *
     * @param mixed $query   Optional. Query or post identifier. Default false.
     * @param array $options Optional. Options for Timber\Timber::get_post().
     *
     * @return Attachment|null Timber\Attachment object if an attachment was found, null if no
     *                         attachment was found.
     */
    public static function get_attachment(mixed $query = false, $options = [])
    {
        self::check_post_api_deprecations($query, $options, 'Timber::get_attachment()');

        $post = static::get_post($query, $options);

        // No need to instantiate a Post we're not going to use.
        return ($post instanceof Attachment) ? $post : null;
    }

    /**
     * Gets an image.
     *
     * Behaves just like Timber::get_post(), except that it returns null if it finds a Timber\Post
     * that is not an Image. Honors Class Maps and falsifies return value *after* Class Map for the
     * found Timber\Post has been resolved.
     *
     * @api
     * @since 2.0.0
     * @see Timber::get_post()
     * @see https://timber.github.io/docs/v2/guides/class-maps/
     *
     * @param mixed $query   Optional. Query or post identifier. Default false.
     * @param array $options Optional. Options for Timber\Timber::get_post().
     *
     * @return Image|null
     */
    public static function get_image(mixed $query = false, $options = [])
    {
        self::check_post_api_deprecations($query, $options, 'Timber::get_image()');

        $post = static::get_post($query, $options);

        // No need to instantiate a Post we're not going to use.
        return ($post instanceof Image) ? $post : null;
    }

    /**
     * Gets an external image.
     *
     * Behaves just like Timber::get_image(), except that you can use an absolute or relative path or a URL to load an
     * image. You can also pass in an external URL. In that case, Timber will sideload the image and store it in the
     * uploads folder of your WordPress installation. The next time the image is accessed, it will be loaded from there.
     *
     * @api
     * @since 2.0.0
     * @see Timber::get_image()
     * @see ImageHelper::sideload_image()
     *
     * @param bool  $url Image path or URL. The path can be absolute or relative to the WordPress installation.
     * @param array $args {
     *     An associative array with additional arguments for the image.
     *
     *     @type string $alt Alt text for the image.
     *     @type string $caption Caption text for the image.
     * }
     *
     * @return ExternalImage|null
     */
    public static function get_external_image($url = false, array $args = []): ?ExternalImage
    {
        $args = \wp_parse_args($args, [
            'alt' => '',
            'caption' => '',
        ]);

        return ExternalImage::build($url, $args);
    }

    /**
     * Checks for deprecated Timber::get_post() API usage.
     *
     * @param $query
     * @param $options
     * @param $function_name
     */
    private static function check_post_api_deprecations($query = false, $options = [], string $function_name = 'Timber::get_post()')
    {
        if (\is_string($query) && !\is_numeric($query)) {
            Helper::doing_it_wrong(
                $function_name,
                'Getting a post by post slug or post name was removed from Timber::get_post() in Timber 2.0. Use Timber::get_post_by() instead.',
                '2.0.0'
            );
        }

        if (\is_string($options)) {
            Helper::doing_it_wrong(
                $function_name,
                'The $PostClass parameter for passing in the post class to use in Timber::get_posts() was replaced with an $options array in Timber 2.0. To customize which class to instantiate for your post, use Class Maps instead: https://timber.github.io/docs/v2/guides/class-maps/',
                '2.0.0'
            );
        }
    }

    /**
     * Gets a collection of posts.
     *
     * Refer to the official documentation for
     * [WP_Query](https://developer.wordpress.org/reference/classes/wp_query/) for a list of all
     * the arguments that can be used for the `$query` parameter.
     *
     * @api
     * @example
     * ```php
     * // Use the global query.
     * $posts = Timber::get_posts();
     *
     * // Using the WP_Query argument format.
     * $posts = Timber::get_posts( [
     *    'post_type'     => 'article',
     *    'category_name' => 'sports',
     * ] );
     *
     * // Using a WP_Query instance.
     * $posts = Timber::get_posts( new WP_Query( [ 'post_type' => 'any' ) );
     *
     * // Using an array of post IDs.
     * $posts = Timber::get_posts( [ 47, 543, 3220 ] );
     * ```
     *
     * @param mixed $query  Optional. Query args. Default `false`, which means that Timber will use
     *                      the global query. Accepts an array of `WP_Query` arguments, a `WP_Query`
     *                      instance or a list of post IDs.
     * @param array $options {
     *     Optional. Options for the query.
     *
     *     @type bool $merge_default    Merge query parameters with the default query parameters of
     *                                  the current template. Default false.
     * }
     *
     * @return PostCollectionInterface|null Null if no query could be run with the used
     *                                              query parameters.
     */
    public static function get_posts(mixed $query = false, $options = [])
    {
        if (\is_string($query)) {
            Helper::doing_it_wrong(
                'Timber::get_posts()',
                "Querying posts by using a query string was removed in Timber 2.0. Pass in the query string as an options array instead. For example, change Timber::get_posts( 'post_type=portfolio&posts_per_page=3') to Timber::get_posts( [ 'post_type' => 'portfolio', 'posts_per_page' => 3 ] ). Learn more: https://timber.github.io/docs/v2/reference/timber-timber/#get_posts",
                '2.0.0'
            );

            $query = new WP_Query($query);
        }

        if (\is_string($options)) {
            Helper::doing_it_wrong(
                'Timber::get_posts()',
                'The $PostClass parameter for passing in the post class to use in Timber::get_posts() was replaced with an $options array in Timber 2.0. To customize which class to instantiate for your post, use Class Maps instead: https://timber.github.io/docs/v2/guides/class-maps/',
                '2.0.0'
            );
            $options = [];
        }

        if (3 === \func_num_args()) {
            Helper::doing_it_wrong(
                'Timber::get_posts()',
                'The $return_collection parameter to control whether a post collection is returned in Timber::get_posts() was removed in Timber 2.0.',
                '2.0.0'
            );
        }

        if (\is_array($query) && isset($query['numberposts'])) {
            Helper::doing_it_wrong(
                'Timber::get_posts()',
                'Using `numberposts` only works when using `get_posts()`, but not for Timber::get_posts(). Use `posts_per_page` instead.',
                '2.0.0'
            );
        }

        /**
         * @todo Are there any more default options to support?
         */
        $options = \wp_parse_args($options, [
            'merge_default' => false,
        ]);

        global $wp_query;

        if (\is_array($query) && $options['merge_default']) {
            $query = \wp_parse_args($query, $wp_query->query_vars);
        }

        $factory = new PostFactory();

        // Default to the global query.
        return $factory->from($query ?: $wp_query);
    }

    /**
     * Gets a post by title or slug.
     *
     * @api
     * @since 2.0.0
     * @example
     * ```php
     * // By slug
     * $post = Timber::get_post_by( 'slug', 'about-us' );
     *
     * // By title
     * $post = Timber::get_post_by( 'title', 'About us' );
     * ```
     *
     * @param string       $type         The type to look for. One of `slug` or `title`.
     * @param string       $search_value The post slug or post title to search for. When searching
     *                                   for `title`, this parameter doesn’t need to be
     *                                   case-sensitive, because the `=` comparison is used in
     *                                   MySQL.
     * @param array        $args {
     *     Optional. An array of arguments to configure what is returned.
     *
     * 	   @type string|array     $post_type   Optional. What WordPress post type to limit the
     *                                         results to. Defaults to 'any'
     *     @type string           $order_by    Optional. The field to sort by. Defaults to
     *                                         'post_date'
     *     @type string           $order       Optional. The sort to apply. Defaults to ASC
     *
     * }
     *
     * @return Post|null A Timber post or `null` if no post could be found. If multiple
     *                           posts with the same slug or title were found, it will select the
     *                           post with the oldest date.
     */
    public static function get_post_by($type, $search_value, $args = [])
    {
        $post_id = false;
        $args = \wp_parse_args($args, [
            'post_type' => 'any',
            'order_by' => 'post_date',
            'order' => 'ASC',
        ]);
        if ('slug' === $type) {
            $args = \wp_parse_args($args, [
                'name' => $search_value,
                'fields' => 'ids',
            ]);
            $query = new WP_Query($args);

            if ($query->post_count < 1) {
                return null;
            }

            $posts = $query->get_posts();
            $post_id = \array_shift($posts);
        } elseif ('title' === $type) {
            /**
             * The following section is inspired by post_exists() as well as get_page_by_title().
             *
             * These two functions always return the post with lowest ID. However, we want the post
             * with oldest post date.
             *
             * @see \post_exists()
             * @see \get_page_by_title()
             */
            global $wpdb;

            $sql = "SELECT ID FROM $wpdb->posts WHERE post_title = %s";
            $query_args = [$search_value];
            if (\is_array($args['post_type'])) {
                $post_type = \esc_sql($args['post_type']);
                $post_type_in_string = "'" . \implode("','", $args['post_type']) . "'";

                $sql .= " AND post_type IN ($post_type_in_string)";
            } elseif ('any' !== $args['post_type']) {
                $sql .= ' AND post_type = %s';
                $query_args[] = $args['post_type'];
            }

            // Always return the oldest post first.
            $sql .= ' ORDER BY post_date ASC';

            $post_id = $wpdb->get_var($wpdb->prepare($sql, $query_args));
        }

        if (!$post_id) {
            return null;
        }

        return self::get_post($post_id);
    }

    /**
     * Query post.
     *
     * @api
     * @deprecated since 2.0.0 Use `Timber::get_post()` instead.
     *
     *
     * @return Post|array|bool|null
     */
    public static function query_post(mixed $query = false, array $options = [])
    {
        Helper::deprecated('Timber::query_post()', 'Timber::get_post()', '2.0.0');

        return self::get_post($query, $options);
    }

    /**
     * Query posts.
     *
     * @api
     * @deprecated since 2.0.0 Use `Timber::get_posts()` instead.
     *
     *
     * @return PostCollectionInterface
     */
    public static function query_posts(mixed $query = false, array $options = [])
    {
        Helper::deprecated('Timber::query_posts()', 'Timber::get_posts()', '2.0.0');

        return self::get_posts($query, $options);
    }

    /**
     * Gets an attachment by its URL or absolute file path.
     *
     * Honors the `timber/post/image_extensions` filter, returning a Timber\Image if the found
     * attachment is identified as an image. Also honors Class Maps.
     *
     * @api
     * @since 2.0.0
     * @example
     * ```php
     * // Get attachment by URL.
     * $attachment = Timber::get_attachment_by( 'url', 'https://example.com/uploads/2020/09/cat.gif' );
     *
     * // Get attachment by filepath.
     * $attachment = Timber::get_attachment_by( 'path', '/path/to/wp-content/uploads/2020/09/cat.gif' );
     *
     * // Try to handle either case.
     * $mystery_string = some_function();
     * $attachment     = Timber::get_attachment_by( $mystery_string );
     * ```
     *
     * @param string $field_or_ident Can be "url", "path", an attachment URL, or the absolute
     *                               path of an attachment file. If "url" or "path" is given, a
     *                               second arg is required.
     * @param string $ident          Optional. An attachment URL or absolute path. Default empty
     *                               string.
     *
     * @return Attachment|null
     */
    public static function get_attachment_by(string $field_or_ident, string $ident = '')
    {
        if ($field_or_ident === 'url') {
            if (empty($ident)) {
                Helper::doing_it_wrong(
                    'Timber::get_attachment_by()',
                    'Passing "url" as the first arg requires passing a URL as the second arg.',
                    '2.0.0'
                );

                return null;
            }

            $id = \attachment_url_to_postid($ident);

            return $id ? (new PostFactory())->from($id) : null;
        }

        if ($field_or_ident === 'path') {
            if (empty($ident)) {
                Helper::doing_it_wrong(
                    'Timber::get_attachment_by()',
                    'Passing "path" as the first arg requires passing an absolute path as the second arg.',
                    '2.0.0'
                );

                return null;
            }

            if (!\file_exists($ident)) {
                // Deal with a relative path.
                $ident = URLHelper::get_full_path($ident);
            }

            return self::get_attachment_by('url', URLHelper::file_system_to_url($ident));
        }

        if (empty($ident)) {
            $field = URLHelper::starts_with($field_or_ident, ABSPATH) ? 'path' : 'url';

            return self::get_attachment_by($field, $field_or_ident);
        }

        return null;
    }

    /* Term Retrieval
    ================================ */

    /**
     * Gets terms.
     *
     * @api
     * @see https://developer.wordpress.org/reference/classes/wp_term_query/__construct/
     * @example
     * ```php
     * // Get all tags.
     * $tags = Timber::get_terms( 'post_tag' );
     * // Note that this is equivalent to:
     * $tags = Timber::get_terms( 'tag' );
     * $tags = Timber::get_terms( 'tags' );
     *
     * // Get all categories.
     * $cats = Timber::get_terms( 'category' );
     *
     * // Get all terms in a custom taxonomy.
     * $cats = Timber::get_terms('my_taxonomy');
     *
     * // Perform a custom Term query.
     * $cats = Timber::get_terms( [
     *   'taxonomy' => 'my_taxonomy',
     *   'orderby'  => 'slug',
     *   'order'    => 'DESC',
     * ] );
     * ```
     *
     * @param string|array $args    A string or array identifying the taxonomy or
     *                              `WP_Term_Query` args. Numeric strings are treated as term IDs;
     *                              non-numeric strings are treated as taxonomy names. Numeric
     *                              arrays are treated as a list a of term identifiers; associative
     *                              arrays are treated as args for `WP_Term_Query::__construct()`
     *                              and accept any valid parameters to that constructor.
     *                              Default `null`, which will get terms from all queryable
     *                              taxonomies.
     * @param array        $options Optional. None are currently supported. Default empty array.
     *
     * @return iterable
     */
    public static function get_terms($args = null, array $options = []): iterable
    {
        // default to all queryable taxonomies
        $args ??= [
            'taxonomy' => \get_taxonomies(),
        ];

        $factory = new TermFactory();

        return $factory->from($args);
    }

    /**
     * Gets a term.
     *
     * @api
     * @param int|WP_Term $term A WP_Term or term_id
     * @return Term|null
     * @example
     * ```php
     * // Get a Term.
     * $tag = Timber::get_term( 123 );
     * ```
     */
    public static function get_term($term = null)
    {
        if (null === $term) {
            // get the fallback term_id from the current query
            global $wp_query;
            $term = $wp_query->queried_object->term_id ?? null;
        }

        if (null === $term) {
            // not able to get term_id from the current query; bail
            return null;
        }

        $factory = new TermFactory();
        $terms = $factory->from($term);

        if (\is_array($terms)) {
            $terms = $terms[0];
        }

        return $terms;
    }

    /**
     * Gets a term by field.
     *
     * This function works like
     * [`get_term_by()`](https://developer.wordpress.org/reference/functions/get_term_by/), but
     * returns a `Timber\Term` object.
     *
     * @api
     * @since 2.0.0
     * @example
     * ```php
     * // Get a term by slug.
     * $term = Timber::get_term_by( 'slug', 'security' );
     *
     * // Get a term by name.
     * $term = Timber::get_term_by( 'name', 'Security' );
     *
     * // Get a term by slug from a specific taxonomy.
     * $term = Timber::get_term_by( 'slug', 'security', 'category' );
     * ```
     *
     * @param string     $field    The name of the field to retrieve the term with. One of: `id`,
     *                             `ID`, `slug`, `name` or `term_taxonomy_id`.
     * @param int|string $value    The value to search for by `$field`.
     * @param string     $taxonomy The taxonomy you want to retrieve from. Empty string will search
     *                             from all.
     *
     * @return Term|null
     */
    public static function get_term_by(string $field, $value, string $taxonomy = '')
    {
        $wp_term = \get_term_by($field, $value, $taxonomy);

        if ($wp_term === false) {
            if (empty($taxonomy) && $field != 'term_taxonomy_id') {
                $search = [
                    $field => $value,
                    $taxonomy => 'any',
                    'hide_empty' => false,
                ];
                return static::get_term($search);
            }

            return null;
        }

        return static::get_term($wp_term);
    }

    /* User Retrieval
    ================================ */

    /**
     * Gets one or more users as an array.
     *
     * By default, Timber will use the `Timber\User` class to create a your post objects. To
     * control which class is used for your post objects, use [Class Maps]().
     *
     * @api
     * @since 2.0.0
     * @example
     * ```php
     * // Get users with on an array of user IDs.
     * $users = Timber::get_users( [ 24, 81, 325 ] );
     *
     * // Get all users that only have a subscriber role.
     * $subscribers = Timber::get_users( [
     *     'role' => 'subscriber',
     * ] );
     *
     * // Get all users that have published posts.
     * $post_authors = Timber::get_users( [
     *     'has_published_posts' => [ 'post' ],
     * ] );
     * ```
     *
     * @todo  Add links to Class Maps documentation in function summary.
     *
     * @param array $query   Optional. A WordPress-style query or an array of user IDs. Use an
     *                       array in the same way you would use the `$args` parameter in
     *                       [WP_User_Query](https://developer.wordpress.org/reference/classes/wp_user_query/).
     *                       See
     *                       [WP_User_Query::prepare_query()](https://developer.wordpress.org/reference/classes/WP_User_Query/prepare_query/)
     *                       for a list of all available parameters. Passing an empty parameter
     *                       will return an empty array. Default empty array
     *                       `[]`.
     * @param array $options Optional. An array of options. None are currently supported. This
     *                       parameter exists to prevent future breaking changes. Default empty
     *                       array `[]`.
     *
     * @return iterable An array of users objects. Will be empty if no users were found.
     */
    public static function get_users(array $query = [], array $options = []): iterable
    {
        $factory = new UserFactory();

        return $factory->from($query);
    }

    /**
     * Gets a single user.
     *
     * By default, Timber will use the `Timber\User` class to create a your post objects. To
     * control which class is used for your post objects, use [Class Maps]().
     *
     * @api
     * @since 2.0.0
     * @example
     * ```php
     * $current_user = Timber::get_user();
     *
     * // Get user by ID.
     * $user = Timber::get_user( $user_id );
     *
     * // Convert a WP_User object to a Timber\User object.
     * $user = Timber::get_user( $wp_user_object );
     *
     * // Check if a user is logged in.
     *
     * $user = Timber::get_user();
     *
     * if ( $user ) {
     *     // Yay, user is logged in.
     * }
     * ```
     *
     * @todo Add links to Class Maps documentation in function summary.
     *
     * @param int|WP_User $user A WP_User object or a WordPress user ID. Defaults to the ID of the
     *                           currently logged-in user.
     *
     * @return User|null
     */
    public static function get_user($user = null)
    {
        /*
         * TODO in the interest of time, I'm implementing this logic here. If there's
         * a better place to do this or something that already implements this, let me know
         * and I'll switch over to that.
         */
        $user = $user ?: \get_current_user_id();

        $factory = new UserFactory();
        return $factory->from($user);
    }

    /**
     * Gets a user by field.
     *
     * This function works like
     * [`get_user_by()`](https://developer.wordpress.org/reference/functions/get_user_by/), but
     * returns a `Timber\User` object.
     *
     * @api
     * @since 2.0.0
     * @example
     * ```php
     * // Get a user by email.
     * $user = Timber::get_user_by( 'email', 'user@example.com' );
     *
     * // Get a user by login.
     * $user = Timber::get_user_by( 'login', 'keanu-reeves' );
     * ```
     *
     * @param string     $field The name of the field to retrieve the user with. One of: `id`,
     *                          `ID`, `slug`, `email` or `login`.
     * @param int|string $value The value to search for by `$field`.
     *
     * @return User|null
     */
    public static function get_user_by(string $field, $value)
    {
        $wp_user = \get_user_by($field, $value);

        if ($wp_user === false) {
            return null;
        }

        return static::get_user($wp_user);
    }

    /* Menu Retrieval
    ================================ */

    /**
     * Gets a nav menu object.
     *
     * @api
     * @since 2.0.0
     * @example
     * ```php
     * // Get a menu by location
     * $menu = Timber::get_menu( 'primary-menu' );
     *
     * // Get a menu by slug
     * $menu = Timber::get_menu( 'my-menu' );
     *
     * // Get a menu by name
     * $menu = Timber::get_menu( 'Main Menu' );
     *
     * // Get a menu by ID (term_id)
     * $menu = Timber::get_menu( 123 );
     * ```
     *
     * @param int|string $identifier A menu identifier: a term_id, slug, menu name, or menu location name
     * @param array      $args An associative array of options. Currently only one option is
     * supported:
     * - `depth`: How deep down the tree of menu items to query. Useful if you only want
     *   the first N levels of items in the menu.
     *
     * @return Menu|null
     */
    public static function get_menu($identifier = null, array $args = []): ?Menu
    {
        $factory = new MenuFactory();
        return $factory->from($identifier, $args);
    }

    /**
     * Gets a menu by field.
     *
     * @api
     * @since 2.0.0
     * @example
     * ```php
     * // Get a menu by location.
     * $menu = Timber::get_menu_by( 'location', 'primary' );
     *
     * // Get a menu by slug.
     * $menu = Timber::get_menu_by( 'slug', 'primary-menu' );
     * ```
     *
     * @param string     $field The name of the field to retrieve the menu with. One of: `id`,
     *                          `ID`, `term_id`, `slug`, `name` or `location`.
     * @param int|string $value The value to search for by `$field`.
     *
     * @return Menu|null
     */
    public static function get_menu_by(string $field, $value, array $args = []): ?Menu
    {
        $factory = new MenuFactory();
        $menu = null;

        $menu = match ($field) {
            'id', 'term_id', 'ID' => $factory->from_id($value, $args),
            'slug' => $factory->from_slug($value, $args),
            'name' => $factory->from_name($value, $args),
            'location' => $factory->from_location($value, $args),
            default => $menu,
        };

        return $menu;
    }

    /**
     * Gets a menu from the existing pages.
     *
     * @api
     * @since 2.0.0
     *
     * @example
     * ```php
     * $menu = Timber::get_pages_menu();
     * ```
     *
     * @param array $args Optional. Arguments for `wp_list_pages()`. Timber doesn’t use that
     *                    function under the hood, but supports all arguments for that function.
     *                    It will use `get_pages()` to get the pages that will be used for the Pages
     *                    Menu.
     */
    public static function get_pages_menu(array $args = [])
    {
        $factory = new PagesMenuFactory();

        $menu = $factory->from_pages($args);

        return $menu;
    }

    /**
     * Get the navigation menu location assigned to the given menu.
     *
     * @api
     * @since 2.3.0
     *
     * @param  WP_Term|int $term The menu to find; either a WP_Term object or a Term ID.
     * @return string|null
     */
    public static function get_menu_location($term): ?string
    {
        if ($term instanceof WP_Term) {
            $term_id = $term->term_id;
        } elseif (\is_int($term)) {
            $term_id = $term;
        } else {
            return null;
        }

        $locations = \array_flip(static::get_menu_locations());
        return $locations[$term_id] ?? null;
    }

    /**
     * Get the navigation menu locations with assigned menus.
     *
     * @api
     * @since 2.3.0
     *
     * @return array<string, (int|string)>
     */
    public static function get_menu_locations(): array
    {
        return \array_filter(
            \get_nav_menu_locations(),
            fn ($location) => \is_string($location) || \is_int($location)
        );
    }

    /* Comment Retrieval
    ================================ */

    /**
     * Get comments.
     *
     * @api
     * @since 2.0.0
     *
     * @param array|WP_Comment_Query $query
     * @param array                   $options Optional. None are currently supported.
     * @return array
     */
    public static function get_comments($query = [], array $options = []): iterable
    {
        $factory = new CommentFactory();

        return $factory->from($query);
    }

    /**
     * Gets comment.
     *
     * @api
     * @since 2.0.0
     * @param int|WP_Comment $comment
     * @return Comment|null
     */
    public static function get_comment($comment)
    {
        $factory = new CommentFactory();
        return $factory->from($comment);
    }

    /* Site Retrieval
    ================================ */

    /**
     * Get sites.
     * @api
     * @param array|bool $blog_ids
     * @return array
     */
    public static function get_sites($blog_ids = false)
    {
        if (!\is_array($blog_ids)) {
            global $wpdb;
            $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs ORDER BY blog_id ASC");
        }
        $return = [];
        foreach ($blog_ids as $blog_id) {
            $return[] = new Site($blog_id);
        }
        return $return;
    }

    /*  Template Setup and Display
    ================================ */

    /**
     * Get context.
     * @api
     * @deprecated 2.0.0, use `Timber::context()` instead.
     *
     * @return array
     */
    public static function get_context()
    {
        Helper::deprecated('get_context', 'context', '2.0.0');

        return self::context();
    }

    /**
     * Gets the global context.
     *
     * The context always contains the global context with the following variables:
     *
     * - `site` – An instance of `Timber\Site`.
     * - `request` - An instance of `Timber\Request`.
     * - `theme` - An instance of `Timber\Theme`.
     * - `user` - An instance of `Timber\User`.
     * - `http_host` - The HTTP host.
     * - `wp_title` - Title retrieved for the currently displayed page, retrieved through
     * `wp_title()`.
     * - `body_class` - The body class retrieved through `get_body_class()`.
     *
     * The global context will be cached, which means that you can call this function again without
     * losing performance.
     *
     * In addition to that, the context will contain template contexts depending on which template
     * is being displayed. For archive templates, a `posts` variable will be present that will
     * contain a collection of `Timber\Post` objects for the default query. For singular templates,
     * a `post` variable will be present that that contains a `Timber\Post` object of the `$post`
     * global.
     *
     * @api
     * @since 2.0.0
     *
     * @param array $extra Any extra data to merge in. Overrides whatever is already there for this
     *                     call only. In other words, the underlying context data is immutable and
     *                     unaffected by passing this param.
     *
     * @return array An array of context variables that is used to pass into Twig templates through
     *               a render or compile function.
     */
    public static function context(array $extra = [])
    {
        $context = self::context_global();

        if (\is_singular()) {
            // NOTE: this also handles the is_front_page() case.
            $context['post'] = Timber::get_post()->setup();
        } elseif (\is_home()) {
            $post = Timber::get_post();

            // When no page_on_front is set, there’s no post we can set up.
            if ($post) {
                $post->setup();
            }

            $context['post'] = $post;
            $context['posts'] = Timber::get_posts();
        } elseif (\is_category() || \is_tag() || \is_tax()) {
            $context['term'] = Timber::get_term();
            $context['posts'] = Timber::get_posts();
        } elseif (\is_search()) {
            $context['posts'] = Timber::get_posts();
            $context['search_query'] = \get_search_query();
        } elseif (\is_author()) {
            $context['author'] = Timber::get_user(\get_query_var('author'));
            $context['posts'] = Timber::get_posts();
        } elseif (\is_archive()) {
            $context['posts'] = Timber::get_posts();
        }

        return \array_merge($context, $extra);
    }

    /**
     * Gets the global context.
     *
     * This function is used by `Timber::context()` to get the global context. Usually, you don’t
     * call this function directly, except when you need the global context in a partial view.
     *
     * The global context will be cached, which means that you can call this function again without
     * losing performance.
     *
     * @api
     * @since 2.0.0
     * @example
     * ```php
     * add_shortcode( 'global_address', function() {
     *     return Timber::compile(
     *         'global_address.twig',
     *         Timber::context_global()
     *     );
     * } );
     * ```
     *
     * @return array An array of global context variables.
     */
    public static function context_global()
    {
        if (empty(self::$context_cache)) {
            self::$context_cache['site'] = new Site();
            self::$context_cache['theme'] = self::$context_cache['site']->theme;
            self::$context_cache['user'] = \is_user_logged_in() ? static::get_user() : false;

            self::$context_cache['http_host'] = URLHelper::get_scheme() . '://' . URLHelper::get_host();
            self::$context_cache['wp_title'] = Helper::get_wp_title();
            self::$context_cache['body_class'] = \implode(' ', \get_body_class());

            /**
             * Filters the global Timber context.
             *
             * By using this filter, you can add custom data to the global Timber context, which
             * means that this data will be available on every page that is initialized with
             * `Timber::context()`.
             *
             * Be aware that data will be cached as soon as you call `Timber::context()` for the
             * first time. That’s why you should add this filter before you call
             * `Timber::context()`.
             *
             * @see \Timber\Timber::context()
             * @since 0.21.7
             * @example
             * ```php
             * add_filter( 'timber/context', function( $context ) {
             *     // Example: A custom value
             *     $context['custom_site_value'] = 'Hooray!';
             *
             *     // Example: Add a menu to the global context.
             *     $context['menu'] = new \Timber\Menu( 'primary-menu' );
             *
             *     // Example: Add all ACF options to global context.
             *     $context['options'] = get_fields( 'options' );
             *
             *     return $context;
             * } );
             * ```
             * ```twig
             * <h1>{{ custom_site_value|e }}</h1>
             *
             * {% for item in menu.items %}
             *     {# Display menu item #}
             * {% endfor %}
             *
             * <footer>
             *     {% if options.footer_text is not empty %}
             *         {{ options.footer_text|e }}
             *     {% endif %}
             * </footer>
             * ```
             *
             * @param array $context The global context.
             */
            self::$context_cache = \apply_filters('timber/context', self::$context_cache);

            /**
             * Filters the global Timber context.
             *
             * @deprecated 2.0.0, use `timber/context`
             */
            self::$context_cache = \apply_filters_deprecated(
                'timber_context',
                [self::$context_cache],
                '2.0.0',
                'timber/context'
            );
        }

        return self::$context_cache;
    }

    /**
     * Compiles a Twig file.
     *
     * Passes data to a Twig file and returns the output. If the template file doesn't exist it will throw a warning
     * when WP_DEBUG is enabled.
     *
     * @api
     * @example
     * ```php
     * $data = array(
     *     'firstname' => 'Jane',
     *     'lastname' => 'Doe',
     *     'email' => 'jane.doe@example.org',
     * );
     *
     * $team_member = Timber::compile( 'team-member.twig', $data );
     * ```
     * @param array|string    $filenames        Name or full path of the Twig file to compile. If this is an array of file
     *                                          names or paths, Timber will compile the first file that exists.
     * @param array           $data             Optional. An array of data to use in Twig template.
     * @param bool|int|array  $expires          Optional. In seconds. Use false to disable cache altogether. When passed an
     *                                          array, the first value is used for non-logged in visitors, the second for users.
     *                                          Default false.
     * @param string          $cache_mode       Optional. Any of the cache mode constants defined in Timber\Loader.
     * @param bool            $via_render       Optional. Whether to apply optional render or compile filters. Default false.
     * @return bool|string                      The returned output.
     */
    public static function compile($filenames, $data = [], $expires = false, $cache_mode = Loader::CACHE_USE_DEFAULT, $via_render = false)
    {
        if (!\defined('TIMBER_LOADED')) {
            self::init();
        }
        $caller = LocationManager::get_calling_script_dir(1);
        $loader = new Loader($caller);
        $file = $loader->choose_template($filenames);

        $caller_file = LocationManager::get_calling_script_file(1);

        /**
         * Fires after the calling PHP file was determined in Timber’s compile
         * function.
         *
         * This action is used by the Timber Debug Bar extension.
         *
         * @since 1.1.2
         * @since 2.0.0 Switched from filter to action.
         *
         * @param string|null $caller_file The calling script file.
         */
        \do_action('timber/calling_php_file', $caller_file);

        if ($via_render) {
            /**
             * Filters the Twig template that should be rendered.
             *
             * @since 2.0.0
             *
             * @param string $file The chosen Twig template name to render.
             */
            $file = \apply_filters('timber/render/file', $file);

            /**
             * Filters the Twig file that should be rendered.
             *
             * @codeCoverageIgnore
             * @deprecated 2.0.0, use `timber/render/file`
             */
            $file = \apply_filters_deprecated(
                'timber_render_file',
                [$file],
                '2.0.0',
                'timber/render/file'
            );
        } else {
            /**
             * Filters the Twig template that should be compiled.
             *
             * @since 2.0.0
             *
             * @param string $file The chosen Twig template name to compile.
             */
            $file = \apply_filters('timber/compile/file', $file);

            /**
             * Filters the Twig template that should be compiled.
             *
             * @deprecated 2.0.0
             */
            $file = \apply_filters_deprecated(
                'timber_compile_file',
                [$file],
                '2.0.0',
                'timber/compile/file'
            );
        }
        $output = false;

        if ($file !== false) {
            if (\is_null($data)) {
                $data = [];
            }

            if ($via_render) {
                /**
                 * Filters the data that should be passed for rendering a Twig template.
                 *
                 * @since 2.0.0
                 *
                 * @param array  $data The data that is used to render the Twig template.
                 * @param string $file The name of the Twig template to render.
                 */
                $data = \apply_filters('timber/render/data', $data, $file);
                /**
                 * Filters the data that should be passed for rendering a Twig template.
                 *
                 * @codeCoverageIgnore
                 * @deprecated 2.0.0
                 */
                $data = \apply_filters_deprecated(
                    'timber_render_data',
                    [$data],
                    '2.0.0',
                    'timber/render/data'
                );
            } else {
                /**
                 * Filters the data that should be passed for compiling a Twig template.
                 *
                 * @since 2.0.0
                 *
                 * @param array  $data The data that is used to compile the Twig template.
                 * @param string $file The name of the Twig template to compile.
                 */
                $data = \apply_filters('timber/compile/data', $data, $file);

                /**
                 * Filters the data that should be passed for compiling a Twig template.
                 *
                 * @deprecated 2.0.0, use `timber/compile/data`
                 */
                $data = \apply_filters_deprecated(
                    'timber_compile_data',
                    [$data],
                    '2.0.0',
                    'timber/compile/data'
                );
            }

            $output = $loader->render($file, $data, $expires, $cache_mode);
        } else {
            if (\is_array($filenames)) {
                $filenames = \implode(", ", $filenames);
            }
            Helper::error_log('Error loading your template files: ' . $filenames . '. Make sure one of these files exists.');
        }

        /**
         * Filters the compiled result before it is returned in `Timber::compile()`.
         *
         * It adds the possibility to filter the output ready for render.
         *
         * @since 2.0.0
         *
         * @param string|bool $output the compiled output.
         */
        $output = \apply_filters('timber/compile/result', $output);

        /**
         * Fires after a Twig template was compiled and before the compiled data
         * is returned.
         *
         * This action can be helpful if you need to debug Twig template
         * compilation.
         *
         * @since 2.0.0
         *
         * @param string            $output       The compiled output.
         * @param string            $file         The name of the Twig template that was compiled.
         * @param array             $data         The data that was used to compile the Twig template.
         * @param bool|int|array    $expires      The expiration time of the cache in seconds, or false to disable cache.
         * @param string            $cache_mode   Any of the cache mode constants defined in Timber\Loader.
         */
        \do_action('timber/compile/done', $output, $file, $data, $expires, $cache_mode);

        /**
         * Fires after a Twig template was compiled and before the compiled data
         * is returned.
         *
         * @deprecated 2.0.0, use `timber/compile/done`
         */
        \do_action_deprecated('timber_compile_done', [], '2.0.0', 'timber/compile/done');

        return $output;
    }

    /**
     * Compile a string.
     *
     * @api
     * @example
     * ```php
     * $data = array(
     *     'username' => 'Jane Doe',
     * );
     *
     * $welcome = Timber::compile_string( 'Hi {{ username }}, I’m a string with a custom Twig variable', $data );
     * ```
     * @param string $string A string with Twig variables.
     * @param array  $data   Optional. An array of data to use in Twig template.
     * @return bool|string
     */
    public static function compile_string($string, $data = [])
    {
        $dummy_loader = new Loader();
        $twig = $dummy_loader->get_twig();
        $template = $twig->createTemplate($string);
        return $template->render($data);
    }

    /**
     * Fetch function.
     *
     * @api
     * @deprecated 2.0.0 use Timber::compile()
     * @param array|string $filenames  Name of the Twig file to render. If this is an array of files, Timber will
     *                                 render the first file that exists.
     * @param array        $data       Optional. An array of data to use in Twig template.
     * @param bool|int     $expires    Optional. In seconds. Use false to disable cache altogether. When passed an
     *                                 array, the first value is used for non-logged in visitors, the second for users.
     *                                 Default false.
     * @param string       $cache_mode Optional. Any of the cache mode constants defined in Timber\Loader.
     * @return bool|string The returned output.
     */
    public static function fetch($filenames, $data = [], $expires = false, $cache_mode = Loader::CACHE_USE_DEFAULT)
    {
        Helper::deprecated(
            'fetch',
            'Timber::compile() (see https://timber.github.io/docs/v2/reference/timber/#compile for more information)',
            '2.0.0'
        );
        $output = self::compile($filenames, $data, $expires, $cache_mode, true);

        /**
         * Filters the compiled result before it is returned.
         *
         * @see \Timber\Timber::fetch()
         * @since 0.16.7
         * @deprecated 2.0.0 use timber/compile/result
         *
         * @param string $output The compiled output.
         */
        $output = \apply_filters_deprecated(
            'timber_compile_result',
            [$output],
            '2.0.0',
            'timber/compile/result'
        );

        return $output;
    }

    /**
     * Renders a Twig file.
     *
     * Passes data to a Twig file and echoes the output.
     *
     * @api
     * @example
     * ```php
     * $context = Timber::context();
     *
     * Timber::render( 'index.twig', $context );
     * ```
     * @param array|string   $filenames      Name or full path of the Twig file to render. If this is an array of file
     *                                       names or paths, Timber will render the first file that exists.
     * @param array          $data           Optional. An array of data to use in Twig template.
     * @param bool|int|array $expires        Optional. In seconds. Use false to disable cache altogether. When passed an
     *                                       array, the first value is used for non-logged in visitors, the second for users.
     *                                       Default false.
     * @param string         $cache_mode     Optional. Any of the cache mode constants defined in Timber\Loader.
     */
    public static function render($filenames, $data = [], $expires = false, $cache_mode = Loader::CACHE_USE_DEFAULT): void
    {
        $output = self::compile($filenames, $data, $expires, $cache_mode, true);
        echo $output;
    }

    /**
     * Render a string with Twig variables.
     *
     * @api
     * @example
     * ```php
     * $data = array(
     *     'username' => 'Jane Doe',
     * );
     *
     * Timber::render_string( 'Hi {{ username }}, I’m a string with a custom Twig variable', $data );
     * ```
     * @param string $string A string with Twig variables.
     * @param array  $data   An array of data to use in Twig template.
     */
    public static function render_string($string, $data = [])
    {
        $compiled = self::compile_string($string, $data);
        echo $compiled;
    }

    /*  Sidebar
    ================================ */

    /**
     * Get sidebar.
     * @api
     * @param string  $sidebar
     * @param array   $data
     * @return bool|string
     */
    public static function get_sidebar($sidebar = 'sidebar.php', $data = [])
    {
        if (\strstr(\strtolower($sidebar), '.php')) {
            return self::get_sidebar_from_php($sidebar, $data);
        }
        return self::compile($sidebar, $data);
    }

    /**
     * Get sidebar from PHP
     * @api
     * @param string  $sidebar
     * @param array   $data
     * @return string
     */
    public static function get_sidebar_from_php($sidebar = '', $data = [])
    {
        $caller = LocationManager::get_calling_script_dir(1);
        $uris = LocationManager::get_locations($caller);
        \ob_start();
        $found = false;
        foreach ($uris as $namespace => $uri_locations) {
            if (\is_array($uri_locations)) {
                foreach ($uri_locations as $uri) {
                    if (\file_exists(\trailingslashit($uri) . $sidebar)) {
                        include \trailingslashit($uri) . $sidebar;
                        $found = true;
                    }
                }
            }
        }
        if (!$found) {
            Helper::error_log('error loading your sidebar, check to make sure the file exists');
        }
        $ret = \ob_get_contents();
        \ob_end_clean();

        return $ret;
    }

    /**
     * Get widgets.
     *
     * @api
     * @param int|string $widget_id Optional. Index, name or ID of dynamic sidebar. Default 1.
     * @return string
     */
    public static function get_widgets($widget_id)
    {
        return \trim(Helper::ob_function('dynamic_sidebar', [$widget_id]));
    }

    /**
     * Get pagination.
     *
     * @api
     * @deprecated 2.0.0
     * @link https://timber.github.io/docs/v2/guides/pagination/
     * @param array $prefs an array of preference data.
     * @return array|mixed
     */
    public static function get_pagination($prefs = [])
    {
        Helper::deprecated(
            'get_pagination',
            '{{ posts.pagination }} (see https://timber.github.io/docs/v2/guides/pagination/ for more information)',
            '2.0.0'
        );

        return Pagination::get_pagination($prefs);
    }
}
