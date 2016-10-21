<?php

namespace Timber;

use Timber\Core;
use Timber\CoreInterface;

use Timber\Factory\Factory;
use Timber\Post;
use Timber\Helper;
use Timber\URLHelper;

/**
 * Terms: WordPress has got 'em, you want 'em. Categories. Tags. Custom Taxonomies. You don't care, you're a fiend. Well let's get this under control
 * @example
 * ```php
 * //Get a term by its ID
 * $context['term'] = new TimberTerm(6);
 * //Get a term when on a term archive page
 * $context['term_page'] = new TimberTerm();
 * //Get a term with a slug
 * $context['team'] = new TimberTerm('patriots');
 * //Get a team with a slug from a specific taxonomy
 * $context['st_louis'] = new TimberTerm('cardinals', 'baseball');
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
	 * @param \WP_Term|mixed $term
	 * @param string $tax Deprecated as of v2.0.0
	 */
	public function __construct( $term, $tax = 'category' ) {

		if ( ! $term instanceof \WP_Term ) {
			_doing_it_wrong( 'Timber\Term::__construct', 'Please use Timber\Factory\Factory::get_term() to instantiate Timber Terms', '2.0.0' );
			$term = Factory::get_term( $term, $tax );
		}

		if ( isset( $term->id ) ) {
			$term->ID = $term->id;
		} else if ( isset( $term->term_id ) ) {
			$term->ID = $term->term_id;
		}

		if ( isset( $term->ID ) ) {
			$term->id = $term->ID;
			$this->import( $term );
			if ( isset( $term->term_id ) ) {
				$custom = $this->get_term_meta( $term->term_id );
				$this->import( $custom );
			}
		}
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->name;
	}

	/**
	 *
	 * @deprecated Use Factory::get_term()
	 *
	 * @param $tid
	 * @param $taxonomy
	 *
	 * @return Term
	 */
	public static function from( $tid, $taxonomy ) {
		return Factory::get_term( $tid, $taxonomy );
	}

	/**
	 * @internal
	 * @param int $tid
	 * @return array
	 */
	protected function get_term_meta( $tid ) {
		$customs = array();
		$customs = apply_filters('timber_term_get_meta', $customs, $tid, $this);
		return apply_filters('timber/term/meta', $customs, $tid, $this);
	}

	/* Public methods
	===================== */

	/**
	 * @internal
	 * @return string
	 */
	public function get_edit_url() {
		return get_edit_term_link($this->ID, $this->taxonomy);
	}

	/**
	 * @internal
	 * @param string $field_name
	 * @return string
	 */
	public function get_meta_field( $field_name ) {
		if ( !isset($this->$field_name) ) {
			$field_value = '';
			$field_value = apply_filters('timber_term_get_meta_field', $field_value, $this->ID, $field_name, $this);
			$field_value = apply_filters('timber/term/meta/field', $field_value, $this->ID, $field_name, $this);
			$this->$field_name = $field_value;
		}
		return $this->$field_name;
	}

	/**
	 * @internal
	 * @deprecated since 1.0
	 * @return string
	 */
	public function get_path() {
		return $this->path();
	}

	/**
	 * @internal
	 * @deprecated since 1.0
	 * @return string
	 */
	public function get_link() {
		return $this->link();
	}

	/**
	 * Get Posts that have been "tagged" with the particular term
	 * @internal
	 * @param int $numberposts
	 * @param string $post_type
	 * @param string $PostClass
	 * @return array|bool|null
	 */
	public function get_posts( $numberposts = 10, $post_type = 'any', $PostClass = '' ) {
		if ( !strlen($PostClass) ) {
			$PostClass = $this->PostClass;
		}
		$default_tax_query = array(array(
			'field' => 'id',
			'terms' => $this->ID,
			'taxonomy' => $this->taxonomy,
		));
		if ( is_string($numberposts) && strstr($numberposts, '=') ) {
			$args = $numberposts;
			$new_args = array();
			parse_str($args, $new_args);
			$args = $new_args;
			$args['tax_query'] = $default_tax_query;
			if ( !isset($args['post_type']) ) {
				$args['post_type'] = 'any';
			}
			if ( class_exists($post_type) ) {
				$PostClass = $post_type;
			}
		} else if ( is_array($numberposts) ) {
			//they sent us an array already baked
			$args = $numberposts;
			if ( !isset($args['tax_query']) ) {
				$args['tax_query'] = $default_tax_query;
			}
			if ( class_exists($post_type) ) {
				$PostClass = $post_type;
			}
			if ( !isset($args['post_type']) ) {
				$args['post_type'] = 'any';
			}
		} else {
			$args = array(
				'numberposts' => $numberposts,
				'tax_query' => $default_tax_query,
				'post_type' => $post_type
			);
		}
		return Timber::get_posts($args, $PostClass);
	}

	/**
	 * @internal
	 * @return array
	 */
	public function get_children() {
		if ( !isset($this->_children) ) {
			$children = get_term_children($this->ID, $this->taxonomy);
			foreach ( $children as &$child ) {
				$child = Factory::get_term( $child );
			}
			$this->_children = $children;
		}
		return $this->_children;
	}

	/**
	 *
	 *
	 * @param string  $key
	 * @param mixed   $value
	 */
	public function update( $key, $value ) {
		$value = apply_filters('timber_term_set_meta', $value, $key, $this->ID, $this);
		$this->$key = $value;
	}

	/* Alias
	====================== */

	/**
	 * @api
	 * @return array
	 */
	public function children() {
		return $this->get_children();
	}

	/**
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
		return $this->get_edit_url();
	}


	/**
	 * @api
	 * @return string
	 */
	public function link() {
		$link = get_term_link($this);
		$link = apply_filters('timber_term_link', $link, $this);
		return apply_filters('timber/term/link', $link, $this);
	}

	/**
	 * @api
	 * @param string $field_name
	 * @return string
	 */
	public function meta( $field_name ) {
		return $this->get_meta_field($field_name);
	}

	/**
	 * @api
	 * @return string
	 */
	public function path() {
		$link = $this->get_link();
		$rel = URLHelper::get_rel_url($link, true);
		$rel = apply_filters('timber_term_path', $rel, $this);
		return apply_filters('timber/term/path', $rel, $this);
	}

	/**
	 * @api
	 * @param int $numberposts_or_args
	 * @param string $post_type_or_class
	 * @param string $post_class
	 * @example
	 * ```twig
	 * <h4>Recent posts in {{term.name}}</h4>
	 * <ul>
	 * {% for post in term.posts(3, 'post') %}
	 *     <li><a href="{{post.link}}">{{post.title}}</a></li>
	 * {% endfor %}
	 * </ul>
	 * ```
	 * @return array|bool|null
	 */
	public function posts( $numberposts_or_args = 10, $post_type_or_class = 'any', $post_class = '' ) {
		return $this->get_posts($numberposts_or_args, $post_type_or_class, $post_class);
	}

	/**
	 * @api
	 * @return string
	 */
	public function title() {
		return $this->name;
	}
}
