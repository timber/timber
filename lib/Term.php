<?php

namespace Timber;

use Timber\Core;
use Timber\CoreInterface;

use Timber\Post;
use Timber\Helper;
use Timber\URLHelper;

/**
 * Terms: WordPress has got 'em, you want 'em. Categories. Tags. Custom
 * Taxonomies. You don't care, you're a fiend. Well let's get this under control:
 *
 * @example
 * ```php
 * //Get a term by its ID
 * $context['term'] = new Timber\Term(6);
 * //Get a term when on a term archive page
 * $context['term_page'] = new Timber\Term();
 * //Get a term with a slug
 * $context['team'] = new Timber\Term('patriots');
 * //Get a team with a slug from a specific taxonomy
 * $context['st_louis'] = new Timber\Term('cardinals', 'baseball');
 * Timber::render('index.twig', $context);
 * ```
 * ```twig
 * <h2>{{term_page.name}} Archives</h2>
 * <h3>Teams</h3>
 * <ul>
 *     <li>{{st_louis.name}} - {{st_louis.description}}</li>
 *     <li>{{team.name}} - {{team.description}}</li>
 * </ul>
 * ```
 * ```html
 * <h2>Team Archives</h2>
 * <h3>Teams</h3>
 * <ul>
 *     <li>St. Louis Cardinals - Winner of 11 World Series</li>
 *     <li>New England Patriots - Winner of 4 Super Bowls</li>
 * </ul>
 * ```
 */
class Term extends Core implements CoreInterface {

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
	 * @param int $tid
	 * @param string $tax
	 */
	public function __construct( $tid = null, $tax = '' ) {
		if ( null === $tid ) {
			$tid = $this->get_term_from_query();
		}
		if ( strlen($tax) ) {
			$this->taxonomy = $tax;
		}
		$this->init($tid);
	}

	/**
	 * The string the term will render as by default
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->name;
	}

	/**
	 * @param $tid
	 * @param $taxonomy
	 *
	 * @return static
	 */
	public static function from( $tid, $taxonomy ) {
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
	 * @param int $tid
	 */
	protected function init( $tid ) {
		$term = $this->get_term($tid);
		if ( isset($term->term_id) ) {
			$term->ID = $term->term_id;
		}
		if ( isset($term->ID) ) {
			$term->id = $term->ID;
			$this->import($term);
		}
		if ( isset($term->term_id) ) {
			$this->custom = $this->get_term_meta($term->term_id);
		}
	}

	/**
	 * @internal
	 * @param int $tid
	 * @return array
	 */
	protected function get_term_meta( $tid ) {
		$customs = array();

		/**
		 * Filters term meta data.
		 *
		 * This filter is used by the ACF Integration.
		 *
		 * @todo Add example
		 *
		 * @since 0.21.9
		 *
		 * @param array        $customs Custom term meta data.
		 * @param int          $term_id Term ID.
		 * @param \Timber\Term $term    Term object.
		 */
		$customs = apply_filters('timber/term/meta', $customs, $tid, $this);

		/**
		 * Filters term meta data.
		 *
		 * @deprecated 2.0.0, use `timber/term/meta`
		 */
		$customs = apply_filters_deprecated(
			'timber_term_get_meta',
			array( $customs, $tid, $this ),
			'2.0.0',
			'timber/term/meta'
		);

		return $customs;
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
	 * @param int $tid
	 * @return int
	 */
	protected function get_tid( $tid ) {
		global $wpdb;
		if ( is_numeric($tid) ) {
			return $tid;
		}
		if ( gettype($tid) === 'object' ) {
			$tid = $tid->term_id;
		}
		if ( is_numeric($tid) ) {
			$query = $wpdb->prepare("SELECT * FROM $wpdb->terms WHERE term_id = %d", $tid);
		} else {
			$query = $wpdb->prepare("SELECT * FROM $wpdb->terms WHERE slug = %s", $tid);
		}
		$result = $wpdb->get_row($query);
		if ( isset($result->term_id) ) {
			$result->ID = $result->term_id;
			$result->id = $result->term_id;
			return $result->ID;
		}
		return 0;
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
	 * @internal
	 * @deprecated 2.0.0, use `{{ term.meta("my_field") }}` instead.
	 * @param string $field_name
	 * @return string
	 */
	public function get_meta_field( $field_name ) {
		return $this->meta($field_name);
	}


	/**
	 * @api
	 * @return array
	 */
	public function children() {
		if ( !isset($this->_children) ) {
			$children = get_term_children($this->ID, $this->taxonomy);
			foreach ( $children as &$child ) {
				$child = new Term($child);
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
	 * Returns a full link to the term archive page like
	 * `http://example.com/category/news`
	 * @api
	 * @example
	 * ```twig
	 * See all posts in: <a href="{{ term.link }}">{{ term.name }}</a>
	 * ```
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
	 * Retrieves and outputs meta information stored with a term. This will use
	 * both data stored under (old) ACF hacks and new (WP 4.6+) where term meta
	 * has its own table. If retrieving a special ACF field (repeater, etc.) you
	 * can use the output immediately in Twig — no further processing is
	 * required.
	 *
	 * @api
	 * @param string $field_name
	 * @example
	 * ```twig
	 * <div class="location-info">
	 *   <h2>{{ term.name }}</h2>
	 *   <p>{{ term.meta('address') }}</p>
	 * </div>
	 * ```
	 * @return string
	 */
	public function meta( $field_name ) {
		if ( !isset($this->$field_name) ) {
			$field_value = get_term_meta($this->ID, $field_name, true);
			if ( !$field_value ) {

				/**
				 * Filters the value for a term meta field.
				 *
				 * This filter is used by the ACF Integration.
				 *
				 * @todo Add example
				 *
				 * @see   \Timber\Term::meta()
				 * @since 0.21.9
				 *
				 * @param mixed        $field_value The field value.
				 * @param int          $term_id     The term ID.
				 * @param string       $field_name  The name of the meta field to get the value for.
				 * @param \Timber\Term $term        The term object.
				 */
				$field_value = apply_filters('timber/term/meta/field', $field_value, $this->ID, $field_name, $this);

				/**
				 * Filters the value for a term meta field.
				 *
				 * @deprecated 2.0.0, use `timber/term/meta/field`
				 */
				$field_value = apply_filters_deprecated(
					'timber_term_get_meta_field',
					array( $field_value, $this->ID, $field_name, $this ),
					'2.0.0',
					'timber/term/meta/field'
				);
			}
			$this->$field_name = $field_value;
		}
		return $this->$field_name;
	}

	/**
	 * Returns a relative link (path) to the term archive page like
	 * `/category/news`
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
	 * @api
	 * @param int $numberposts_or_args
	 * @param string $post_type_or_class
	 * @param string $post_class
	 * @example
	 * ```twig
	 * <h4>Recent posts in {{ term.name }}</h4>
	 * <ul>
	 * {% for post in term.posts(3, 'post') %}
	 *     <li><a href="{{post.link}}">{{post.title}}</a></li>
	 * {% endfor %}
	 * </ul>
	 * ```
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

		return new PostQuery( $args, $post_class );
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
	 * @deprecated 2.0.0 use `{{ term.posts }}` instead
	 * @internal
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
	 * @deprecated 2.0.0, use `{{ term.children }}` instead.
	 * @return array
	 */
	public function get_children() {
		Helper::deprecated('{{ term.get_children }}', '{{ term.children }}', '2.0.0');

		return $this->children();
	}


	/**
	 *
	 * @deprecated 2.0.0 with no replacement
	 * @param string  $key
	 * @param mixed   $value
	 */
	public function update( $key, $value ) {
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
		 * @deprecated 2.0.0 with no replacement
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
