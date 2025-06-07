<?php

namespace Timber;

use Stringable;
use WP_Term;

/**
 * Class Term
 *
 * Terms: WordPress has got 'em, you want 'em. Categories. Tags. Custom Taxonomies. You don't care,
 * you're a fiend. Well let's get this under control:
 *
 * @phpstan-consistent-constructor
 * @api
 * @example
 * ```php
 * // Get a term by its ID
 * $context['term'] = Timber::get_term(6);
 *
 * // Get a term when on a term archive page
 * $context['term_page'] = Timber::get_term();
 *
 * // Get a term with a slug
 * $context['team'] = Timber::get_term('patriots');
 * Timber::render('index.twig', $context);
 * ```
 * ```twig
 * <h2>{{ term_page.name }} Archives</h2>
 * <h3>Teams</h3>
 * <ul>
 *     <li>{{ st_louis.name}} - {{ st_louis.description }}</li>
 *     <li>{{ team.name}} - {{ team.description }}</li>
 * </ul>
 * ```
 * ```html
 * <h2>Team Archives</h2>
 * <h3>Teams</h3>
 * <ul>
 *     <li>St. Louis Cardinals - Winner of 11 World Series</li>
 *     <li>New England Patriots - Winner of 6 Super Bowls</li>
 * </ul>
 * ```
 */
class Term extends CoreEntity implements Stringable
{
    /**
     * The underlying WordPress Core object.
     *
     * @since 2.0.0
     *
     * @var WP_Term|null
     */
    protected ?WP_Term $wp_object = null;

    public $object_type = 'term';

    public static $representation = 'term';

    public $_children;

    /**
     * @api
     * @var string the human-friendly name of the term (ex: French Cuisine)
     */
    public $name;

    /**
     * @api
     * @var string the WordPress taxonomy slug (ex: `post_tag` or `actors`)
     */
    public $taxonomy;

    /**
     * Term ID.
     *
     * @var int
     */
    public $term_id;

    /**
     * The term's slug.
     *
     * @var string
     */
    public $slug;

    /**
     * The term's term_group.
     *
     * @var int
     */
    public $term_group;

    /**
     * Term Taxonomy ID.
     *
     * @var int
     */
    public $term_taxonomy_id;

    /**
     * The term's description.
     *
     * Protected visibility to make Twig use the description() method first.
     *
     * @var string
     */
    protected $description;

    /**
     * ID of a term's parent term.
     *
     * @var int
     */
    public $parent;

    /**
     * Cached object count for this term.
     *
     * @var int
     */
    public $count;

    /**
     * Stores the term object's sanitization level.
     *
     * Does not correspond to a database field.
     *
     * @var string
     */
    public $filter;

    /**
     * @internal
     */
    protected function __construct()
    {
    }

    /**
     * @internal
     *
     * @param WP_Term      $wp_term The vanilla WordPress term object to build from.
     * @return static
     */
    public static function build(WP_Term $wp_term): static
    {
        $term = new static();
        $term->init($wp_term);
        return $term;
    }

    /**
     * The string the term will render as by default
     *
     * @api
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /* Setup
       ===================== */
    /**
     * @internal
     */
    protected function init(WP_Term $term)
    {
        $this->ID = $term->term_id;
        $this->id = $term->term_id;
        $this->wp_object = $term;
        $this->import($term);
    }

    /**
     * @internal
     * @param int|object|array $tid
     * @return mixed
     */
    protected function get_term($tid)
    {
        if (\is_object($tid) || \is_array($tid)) {
            return $tid;
        }
        $tid = self::get_tid($tid);

        if (\is_array($tid)) {
            //there's more than one matching $term_id, let's figure out which is correct
            if (isset($this->taxonomy) && \strlen($this->taxonomy)) {
                foreach ($tid as $term_id) {
                    $maybe_term = \get_term($term_id, $this->taxonomy);
                    if ($maybe_term) {
                        return $maybe_term;
                    }
                }
            }
            $tid = $tid[0];
        }

        if (isset($this->taxonomy) && \strlen($this->taxonomy)) {
            return \get_term($tid, $this->taxonomy);
        } else {
            global $wpdb;
            $query = $wpdb->prepare("SELECT taxonomy FROM $wpdb->term_taxonomy WHERE term_id = %d LIMIT 1", $tid);
            $tax = $wpdb->get_var($query);
            if (isset($tax) && \strlen((string) $tax)) {
                $this->taxonomy = $tax;
                return \get_term($tid, $tax);
            }
        }
        return null;
    }

    /**
     * @internal
     * @return int|array
     */
    protected static function get_tid(mixed $tid)
    {
        global $wpdb;
        if (\is_numeric($tid)) {
            return $tid;
        }
        if (\gettype($tid) === 'object') {
            $tid = $tid->term_id;
        }
        if (\is_numeric($tid)) {
            $query = $wpdb->prepare("SELECT term_id FROM $wpdb->terms WHERE term_id = %d", $tid);
        } else {
            $query = $wpdb->prepare("SELECT term_id FROM $wpdb->terms WHERE slug = %s", $tid);
        }
        $result = $wpdb->get_col($query);
        if ($result) {
            if (\count($result) == 1) {
                return $result[0];
            }
            return $result;
        }
        return false;
    }

    /* Public methods
    ===================== */

    /**
     * Gets the underlying WordPress Core object.
     *
     * @since 2.0.0
     *
     * @return WP_Term|null
     */
    public function wp_object(): ?WP_Term
    {
        return $this->wp_object;
    }

    /**
     * @internal
     * @return array
     */
    public function children()
    {
        if (!isset($this->_children)) {
            $children = \get_term_children($this->ID, $this->taxonomy);
            foreach ($children as &$child) {
                $child = Timber::get_term($child);
            }
            $this->_children = $children;
        }
        return $this->_children;
    }

    /**
     * Returns the description of the term.
     *
     * Strips any surrounding `<p></p>` tags from the description.
     *
     * @api
     * @return string
     */
    public function description()
    {
        $prefix = '<p>';
        $desc = \term_description($this->ID, $this->taxonomy);
        if (\str_starts_with((string) $desc, $prefix)) {
            $desc = \substr((string) $desc, \strlen($prefix));
        }
        $desc = \preg_replace('/' . \preg_quote('</p>', '/') . '$/', '', (string) $desc);
        return \trim((string) $desc);
    }

    /**
     * Checks whether the current user can edit the term.
     *
     * @api
     * @example
     * ```twig
     * {% if term.can_edit %}
     *     <a href="{{ term.edit_link }}">Edit</a>
     * {% endif %}
     * ```
     * @return bool
     */
    public function can_edit(): bool
    {
        return \current_user_can('edit_term', $this->ID);
    }

    /**
     * Gets the edit link for a term if the current user has the correct rights.
     *
     * @api
     * @example
     * ```twig
     * {% if term.can_edit %}
     *    <a href="{{ term.edit_link }}">Edit</a>
     * {% endif %}
     * ```
     * @return string|null The edit URL of a term in the WordPress admin or null if the current user can’t edit the
     *                     term.
     */
    public function edit_link(): ?string
    {
        if (!$this->can_edit()) {
            return null;
        }

        return \get_edit_term_link($this->ID, $this->taxonomy);
    }

    /**
     * Returns a full link to the term archive page like `https://example.com/category/news`
     *
     * @api
     * @example
     * ```twig
     * See all posts in: <a href="{{ term.link }}">{{ term.name }}</a>
     * ```
     *
     * @return string
     */
    public function link()
    {
        $link = \get_term_link($this->wp_object);

        /**
         * Filters the link to the term archive page.
         *
         * @see   \Timber\Term::link()
         * @since 0.21.9
         *
         * @param string       $link The link.
         * @param Term $term The term object.
         */
        $link = \apply_filters('timber/term/link', $link, $this);

        return $link;
    }

    /**
     * Returns a relative link (path) to the term archive page like `/category/news`
     *
     * @api
     * @example
     * ```twig
     * See all posts in: <a href="{{ term.path }}">{{ term.name }}</a>
     * ```
     * @return string
     */
    public function path()
    {
        $link = $this->link();
        $rel = URLHelper::get_rel_url($link, true);

        /**
         * Filters the relative link (path) to a term archive page.
         *
         * ```
         * add_filter( 'timber/term/path', function( $rel, $term ) {
         *     if ( $term->slug === 'news' ) {
         *        return '/category/modified-url';
         *     }
         *
         *     return $rel;
         * }, 10, 2 );
         * ```
         *
         * @see   \Timber\Term::path()
         * @since 0.21.9
         *
         * @param string       $rel  The relative link.
         * @param Term $term The term object.
         */
        $rel = \apply_filters('timber/term/path', $rel, $this);

        return $rel;
    }

    /**
     * Gets posts that have the current term assigned.
     *
     * @api
     * @example
     * Query the default posts_per_page for this Term:
     *
     * ```twig
     * <h4>Recent posts in {{ term.name }}</h4>
     *
     * <ul>
     * {% for post in term.posts() %}
     *     <li>
     *         <a href="{{ post.link }}">{{ post.title }}</a>
     *     </li>
     * {% endfor %}
     * </ul>
     * ```
     *
     * Query exactly 3 Posts from this Term:
     *
     * ```twig
     * <h4>Recent posts in {{ term.name }}</h4>
     *
     * <ul>
     * {% for post in term.posts(3) %}
     *     <li>
     *         <a href="{{ post.link }}">{{ post.title }}</a>
     *     </li>
     * {% endfor %}
     * </ul>
     * ```
     *
     * If you need more control over the query that is going to be performed, you can pass your
     * custom query arguments in the first parameter.
     *
     * ```twig
     * <h4>Our branches in {{ region.name }}</h4>
     *
     * <ul>
     * {% for branch in region.posts({
     *     post_type: 'branch',
     *     posts_per_page: -1,
     *     orderby: 'menu_order'
     * }) %}
     *     <li>
     *         <a href="{{ branch.link }}">{{ branch.title }}</a>
     *     </li>
     * {% endfor %}
     * </ul>
     * ```
     *
     * @param int|array $query Optional. Either the number of posts or an array of arguments for
     *                         the post query to be performed. Default is an empty array, the
     *                         equivalent of:
     *                         ```php
     *                         [
     *                           'posts_per_page' => get_option('posts_per_page'),
     *                           'post_type'      => 'any',
     *                           'tax_query'      => [ ...tax query for this Term... ]
     *                         ]
     *                         ```
     * @see https://timber.github.io/docs/v2/guides/posts/
     * @see https://timber.github.io/docs/v2/guides/class-maps/
     * @return ?PostCollectionInterface
     */
    public function posts($query = [])
    {
        if (\is_int($query)) {
            $query = [
                'posts_per_page' => $query,
                'post_type' => 'any',
            ];
        }

        $tax_query = [
            // Force a tax_query constraint on this term.
            'relation' => 'AND',
            [
                'field' => 'id',
                'terms' => $this->ID,
                'taxonomy' => $this->taxonomy,
            ],
        ];

        // Merge a clause for this Term into any user-specified tax_query clauses.
        $query['tax_query'] = \array_merge($query['tax_query'] ?? [], $tax_query);

        return Timber::get_posts($query);
    }

    /**
     * @api
     * @return string
     */
    public function title()
    {
        return $this->name;
    }
}
