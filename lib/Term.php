<?php

namespace Timber;

use WP_Term;

/**
 * Class Term
 *
 * Terms: WordPress has got 'em, you want 'em. Categories. Tags. Custom Taxonomies. You don't care,
 * you're a fiend. Well let's get this under control:
 *
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
class Term extends Core implements CoreInterface, MetaInterface {

	public $PostClass = 'Timber\Post';
	public $TermClass = 'Term';

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
	 * @internal
	 */
	protected function __construct() {}

	/**
	 * @internal
	 * @param \WP_Term the vanilla WP term object to build from
	 * @return \Timber\Term
	 */
	public static function build(WP_Term $wp_term, array $_options = []) : self {
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
	public function __toString() {
		return $this->name;
	}

	/**
	 * @api
	 *
	 * @param $tid
	 * @param $taxonomy
	 *
	 * @return static
	 */
	public static function from( $tid, $taxonomy ) {
		if ( is_array($tid) ) {
			return array_map( function($term) use ($taxonomy) {
				return new static($term, $taxonomy);
			}, $tid);
		}
		return new static($tid, $taxonomy);
	}


	/* Setup
	===================== */

	/**
	 * @internal
	 * @return integer
	 */
	protected function get_term_from_query() {
		global $wp_query;
		if ( isset($wp_query->queried_object) ) {
			$qo = $wp_query->queried_object;
			if ( isset($qo->term_id) ) {
				return $qo->term_id;
			}
		}
		if ( isset($wp_query->tax_query->queries[0]['terms'][0]) ) {
			return $wp_query->tax_query->queries[0]['terms'][0];
		}
	}

	/**
	 * @internal
	 * @param \WP_Term $term
	 */
	protected function init( WP_Term $term ) {
		$this->ID = $term->term_id;
		$this->id = $term->term_id;
		$this->import($term);
	}

	/**
	 * @internal
	 * @param int $tid
	 * @return mixed
	 */
	protected function get_term( $tid ) {
		if ( is_object($tid) || is_array($tid) ) {
			return $tid;
		}
		$tid = self::get_tid($tid);

		if ( isset($this->taxonomy) && strlen($this->taxonomy) ) {
			return get_term($tid, $this->taxonomy);
		} else {
			global $wpdb;
			$query = $wpdb->prepare("SELECT taxonomy FROM $wpdb->term_taxonomy WHERE term_id = %d LIMIT 1", $tid);
			$tax = $wpdb->get_var($query);
			if ( isset($tax) && strlen($tax) ) {
				$this->taxonomy = $tax;
				return get_term($tid, $tax);
			}
		}
		return null;
	}

	/**
	 * @internal
	 * @param mixed $tid
	 * @return int|bool
	 */
	protected function get_tid( $tid ) {
		global $wpdb;
		if ( is_numeric($tid) ) {
			return $tid;
		}
		if ( gettype($tid) === 'object' ) {
			$tid = $tid->term_id;
		}
		$query = $wpdb->prepare("SELECT * FROM $wpdb->terms WHERE slug = %s", $tid);
		$result = $wpdb->get_row($query);
		if ( isset($result->term_id) ) {
			$result->ID = $result->term_id;
			$result->id = $result->term_id;
			return $result->ID;
		}
		return false;
	}


	/* Public methods
	===================== */

	/**
	 * @api
	 * @deprecated 2.0.0, use `{{ term.edit_link }}` instead.
	 * @return string
	 */
	public function get_edit_url() {
		Helper::deprecated('{{ term.get_edit_url }}', '{{ term.edit_link }}', '2.0.0');
		return $this->edit_link();
	}

	/**
	 * Gets a term meta value.
	 * @api
	 * @deprecated 2.0.0, use `{{ term.meta('field_name') }}` instead.
	 *
	 * @param string $field_name The field name for which you want to get the value.
	 * @return string The meta field value.
	 */
	public function get_meta_field( $field_name ) {
		Helper::deprecated(
			"{{ term.get_meta_field('field_name') }}",
			"{{ term.meta('field_name') }}",
			'2.0.0'
		);
		return $this->meta($field_name);
	}

	/**
	 * @internal
	 * @return array
	 */
	public function children() {
		if ( !isset($this->_children) ) {
			$children = get_term_children($this->ID, $this->taxonomy);
			foreach ( $children as &$child ) {
				$child = Timber::get_term($child);
			}
			$this->_children = $children;
		}
		return $this->_children;
	}

	/**
	 * Return the description of the term
	 *
	 * @api
	 * @return string
	 */
	public function description() {
		$prefix = '<p>';
		$desc = term_description($this->ID, $this->taxonomy);
		if ( substr($desc, 0, strlen($prefix)) == $prefix ) {
			$desc = substr($desc, strlen($prefix));
		}
		$desc = preg_replace('/'.preg_quote('</p>', '/').'$/', '', $desc);
		return trim($desc);
	}

	/**
	 * @api
	 * @return string
	 */
	public function edit_link() {
		return get_edit_term_link($this->ID, $this->taxonomy);
	}

	/**
	 * Returns a full link to the term archive page like `http://example.com/category/news`
	 *
	 * @api
	 * @example
	 * ```twig
	 * See all posts in: <a href="{{ term.link }}">{{ term.name }}</a>
	 * ```
	 *
	 * @return string
	 */
	public function link() {
		$link = get_term_link($this);

		/**
		 * Filters the link to the term archive page.
		 *
		 * @see   \Timber\Term::link()
		 * @since 0.21.9
		 *
		 * @param string       $link The link.
		 * @param \Timber\Term $term The term object.
		 */
		$link = apply_filters('timber/term/link', $link, $this);

		/**
		 * Filters the link to the term archive page.
		 *
		 * @deprecated 0.21.9, use `timber/term/link`
		 */
		$link = apply_filters_deprecated(
			'timber_term_link',
			array( $link, $this ),
			'2.0.0',
			'timber/term/link'
		);

		return $link;
	}

	/**
	 * Gets a term meta value.
	 *
	 * Returns a meta value or all meta values for all custom fields of a term saved in the term
	 * meta database table.
	 *
	 * Fetching all values is only advised during development, because it can have a big performance
	 * impact, when all filters are applied.
	 *
	 * @api
	 * @example
	 * ```twig
	 * <div class="location-info">
	 *   <h2>{{ term.name }}</h2>
	 *   <p>{{ term.meta('address') }}</p>
	 * </div>
	 * ```
	 *
	 * @param string $field_name Optional. The field name for which you want to get the value. If
	 *                           no field name is provided, this function will fetch values for all
	 *                           custom fields. Default empty string.
	 * @param array  $args       {
	 *      An array of arguments for getting the meta value. Third-party integrations can use this
	 *      argument to make their API arguments available in Timber. Default empty array.
	 *
	 *      @type bool $apply_filters Whether to apply filtering of meta values. You can also use
	 *                                the `raw_meta()` method as a shortcut to apply this argument.
	 *                                Default true.
	 * }
	 * @return mixed The custom field value or an array of custom field values. Null if no value
	 *               could be found.
	 */
	public function meta( $field_name = '', $args = array() ) {
		$args = wp_parse_args( $args, [
			'apply_filters' => true,
		] );

		$term_meta = null;

		if ( $args['apply_filters'] ) {
			/**
			 * Filters the value for a term meta field before it is fetched from the database.
			 *
			 * @example
			 * ```php
			 * // Disable fetching meta values.
			 * add_filter( 'timber/term/pre_meta', '__return_false' );
			 *
			 * // Add your own meta data.
			 * add_filter( 'timber/term/pre_meta', function( $term_meta, $term_id, $term ) {
			 *     $term_meta = array(
			 *         'custom_data_1' => 73,
			 *         'custom_data_2' => 274,
			 *     );
			 *
			 *     return $term_meta;
			 * }, 10, 3);
			 * ```
			 *
			 * @see   \Timber\Term::meta()
			 * @since 2.0.0
			 *
			 * @param string       $term_meta  The field value. Passing a non-null value will skip
			 *                                 fetching the value from the database. Default null.
			 * @param int          $post_id    The post ID.
			 * @param string       $field_name The name of the meta field to get the value for.
			 * @param \Timber\Term $term       The term object.
			 * @param array        $args       An array of arguments.
			 */
			$term_meta = apply_filters(
				'timber/term/pre_meta',
				$term_meta,
				$this->ID,
				$field_name,
				$this,
				$args
			);
		}

		if ( null === $term_meta ) {
			$term_meta = get_term_meta( $this->ID, $field_name, true );

			// Mimick $single argument when fetching all meta values.
			if ( empty( $field_name ) && is_array( $term_meta ) && ! empty( $term_meta )  ) {
				$term_meta = array_map( function( $meta ) {
					if ( 1 === count( $meta ) && isset( $meta[0] ) ) {
						return $meta[0];
					}

					return $meta;
				}, $term_meta );
			}

			// Empty result.
			if ( empty( $term_meta ) ) {
				$term_meta = empty( $field_name ) ? [] : null;
			}
		}

		if ( $args['apply_filters'] ) {
			/**
			 * Filters the value for a term meta field.
			 *
			 * This filter is used by the ACF Integration.
			 *
			 * @see   \Timber\Term::meta()
			 * @todo  Add description, example
			 *
			 * @since 0.21.9
			 *
			 * @param mixed        $term_meta  The field value.
			 * @param int          $term_id    The term ID.
			 * @param string       $field_name The name of the meta field to get the value for.
			 * @param \Timber\Term $term       The term object.
			 * @param array        $args       An array of arguments.
			 */
			$term_meta = apply_filters(
				'timber/term/meta',
				$term_meta,
				$this->ID,
				$field_name,
				$this,
				$args
			);

			/**
			 * Filters term meta data fetched from the database.
			 *
			 * @deprecated 2.0.0, use `timber/term/meta`
			 */
			$term_meta = apply_filters_deprecated(
				'timber_term_get_meta',
				array( $term_meta, $this->ID, $this ),
				'2.0.0',
				'timber/term/meta'
			);

			/**
			 * Filters the value for a term meta field.
			 *
			 * @deprecated 2.0.0, use `timber/term/meta`
			 */
			$term_meta = apply_filters_deprecated(
				'timber/term/meta/field',
				array( $term_meta, $this->ID, $field_name, $this ),
				'2.0.0',
				'timber/term/meta'
			);

			/**
			 * Filters the value for a term meta field.
			 *
			 * @deprecated 2.0.0, use `timber/term/meta`
			 */
			$term_meta = apply_filters_deprecated(
				'timber_term_get_meta_field',
				array( $term_meta, $this->ID, $field_name, $this ),
				'2.0.0',
				'timber/term/meta'
			);
		}

		return $term_meta;
	}

	/**
	 * Gets a term meta value directly from the database.
	 *
	 * Returns a raw meta value or all raw meta values saved in the term meta database table. In
	 * comparison to `meta()`, this function will return raw values that are not filtered by third-
	 * party plugins.
	 *
	 * Fetching raw values for all custom fields will not have a big performance impact, because
	 * WordPress gets all meta values, when the first meta value is accessed.
	 *
	 * @api
	 * @since 2.0.0
	 *
	 * @param string $field_name Optional. The field name for which you want to get the value. If
	 *                           no field name is provided, this function will fetch values for all
	 *                           custom fields. Default empty string.
	 * @param array  $args       Optional. An array of args for `Term::meta()`. Default empty array.
	 * @return null|mixed The custom field value(s). Null if no value could be found, an empty array
	 *                    if all fields were requested but no values could be found.
	 */
	public function raw_meta( $field_name = '', $args = array() ) {
		return $this->meta( $field_name, array_merge(
			$args,
			[
				'apply_filters' => false,
			]
		) );
	}

	/**
	 * Gets a term meta value.
	 *
	 * @api
	 * @deprecated 2.0.0, use `{{ term.meta('field_name') }}` instead.
	 * @see \Timber\Term::meta()
	 *
	 * @param string $field_name The field name for which you want to get the value.
	 * @return mixed The meta field value.
	 */
	public function get_field( $field_name = null ) {
		Helper::deprecated(
			"{{ term.get_field('field_name') }}",
			"{{ term.meta('field_name') }}",
			'2.0.0'
		);

		return $this->meta( $field_name );
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
	public function path() {
		$link = $this->link();
		$rel = URLHelper::get_rel_url($link, true);

		/**
		 * Filters the relative link (path) to a term archive page.
		 *
		 * @todo Add example
		 *
		 * @see   \Timber\Term::path()
		 * @since 0.21.9
		 *
		 * @param string       $rel  The relative link.
		 * @param \Timber\Term $term The term object.
		 */
		$rel = apply_filters('timber/term/path', $rel, $this);

		/**
		 * Filters the relative link (path) to a term archive page.
		 *
		 * @deprecated 2.0.0, use `timber/term/path`
		 */
		$rel = apply_filters_deprecated(
			'timber_term_path',
			array( $rel, $this ),
			'2.0.0',
			'timber/term/path'
		);

		return $rel;
	}

	/**
	 * Gets posts that have the current term assigned.
	 *
	 * @api
	 * @example
	 * ```twig
	 * <h4>Recent posts in {{ term.name }}</h4>
	 *
	 * <ul>
	 * {% for post in term.posts(3, 'post') %}
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
	 *     posts_per_page: -1,
	 *     orderby: 'menu_order'
	 * }, 'branch', 'Branch') %}
	 *     <li>
	 *         <a href="{{ branch.link }}">{{ branch.title }}</a>
	 *     </li>
	 * {% endfor %}
	 * </ul>
	 * ```
	 *
	 * @param int|array $numberposts_or_args Optional. Either the number of posts or an array of
	 *                                       arguments for the post query that this method is going.
	 *                                       to perform. Default `10`.
	 * @param string $post_type_or_class     Optional. Either the post type to get or the name of
	 *                                       post class to use for the returned posts. Default
	 *                                       `any`.
	 * @param string $post_class             Optional. The name of the post class to use for the
	 *                                       returned posts. Default `Timber\Post`.
	 * @return \Timber\PostQuery
	 */
	public function posts( $numberposts_or_args = 10, $post_type_or_class = 'any', $post_class = '' ) {
		if ( !strlen($post_class) ) {
			$post_class = $this->PostClass;
		}
		$default_tax_query = array(array(
			'field' => 'id',
			'terms' => $this->ID,
			'taxonomy' => $this->taxonomy,
		));
		if ( is_string($numberposts_or_args) && strstr($numberposts_or_args, '=') ) {
			$args = $numberposts_or_args;
			$new_args = array();
			parse_str($args, $new_args);
			$args = $new_args;
			$args['tax_query'] = $default_tax_query;
			if ( !isset($args['post_type']) ) {
				$args['post_type'] = 'any';
			}
			if ( class_exists($post_type_or_class) ) {
				$post_class = $post_type_or_class;
			}
		} else if ( is_array($numberposts_or_args) ) {
			//they sent us an array already baked
			$args = $numberposts_or_args;
			if ( !isset($args['tax_query']) ) {
				$args['tax_query'] = $default_tax_query;
			}
			if ( class_exists($post_type_or_class) ) {
				$post_class = $post_type_or_class;
			}
			if ( !isset($args['post_type']) ) {
				$args['post_type'] = 'any';
			}
		} else {
			$args = array(
				'numberposts_or_args' => $numberposts_or_args,
				'tax_query' => $default_tax_query,
				'post_type' => $post_type_or_class
			);
		}

		return new PostQuery( array(
			'query'      => $args,
			'post_class' => $post_class,
		) );
	}


	/**
	 * @api
	 * @return string
	 */
	public function title() {
		return $this->name;
	}

	/** DEPRECATED DOWN HERE
	 * ======================
	 **/

	/**
	 * Get Posts that have been "tagged" with the particular term
	 *
	 * @api
	 * @deprecated 2.0.0 use `{{ term.posts }}` instead
	 *
	 * @param int $numberposts
	 * @param string $post_type
	 * @param string $PostClass
	 * @return array|bool|null
	 */
	public function get_posts( $numberposts = 10, $post_type = 'any', $PostClass = '' ) {
		Helper::deprecated('{{ term.get_posts }}', '{{ term.posts }}', '2.0.0');
		return $this->posts($numberposts, $post_type, $PostClass);
	}

	/**
	 * @api
	 * @deprecated 2.0.0, use `{{ term.children }}` instead.
	 *
	 * @return array
	 */
	public function get_children() {
		Helper::deprecated('{{ term.get_children }}', '{{ term.children }}', '2.0.0');

		return $this->children();
	}

	/**
	 * Updates term_meta of the current object with the given value.
	 *
	 * @deprecated 2.0.0 Use `update_term_meta()` instead.
	 *
	 * @param string $key   The key of the meta field to update.
	 * @param mixed  $value The new value.
	 */
	public function update( $key, $value ) {
		Helper::deprecated( 'Timber\Term::update()', 'update_term_meta()', '2.0.0' );

		/**
		 * Filters term meta value that is going to be updated.
		 *
		 * @deprecated 2.0.0 with no replacement
		 */
		$value = apply_filters_deprecated(
			'timber_term_set_meta',
			array( $value, $key, $this->ID, $this ),
			'2.0.0',
			false,
			'This filter will be removed in a future version of Timber. There is no replacement.'
		);

		/**
		 * Filters term meta value that is going to be updated.
		 *
		 * This filter is used by the ACF Integration.
		 *
		 * @deprecated 2.0.0, with no replacement
		 */
		$value = apply_filters_deprecated(
			'timber/term/meta/set',
			array( $value, $key, $this->ID, $this ),
			'2.0.0',
			false,
			'This filter will be removed in a future version of Timber. There is no replacement.'
		);

		$this->$key = $value;
	}

}
