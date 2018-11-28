<?php

namespace Timber;

use Timber\Core;
use Timber\CoreInterface;
use Timber\CommentThread;
use Timber\Term;
use Timber\User;
use Timber\Image;
use Timber\Helper;
use Timber\URLHelper;
use Timber\PostGetter;
use Timber\PostType;

use WP_Post;

/**
 * Class Post
 *
 * This is the object you use to access or extend WordPress posts. Think of it as Timber's (more
 * accessible) version of `WP_Post`. This is used throughout Timber to represent posts retrieved
 * from WordPress making them available to Twig templates. See the PHP and Twig examples for an
 * example of what it’s like to work with this object in your code.
 *
 * @api
 * @example
 *
 * **single.php**
 *
 * ```php
 * $context = Timber::context();
 *
 * Timber::render( 'single.twig', $context );
 * ```
 *
 * **single.twig**
 *
 * ```twig
 * <article>
 *     <h1 class="headline">{{ post.title }}</h1>
 *     <div class="body">
 *         {{ post.content }}
 *     </div>
 * </article>
 * ```
 *
 * ```html
 * <article>
 *     <h1 class="headline">The Empire Strikes Back</h1>
 *     <div class="body">
 *         It is a dark time for the Rebellion. Although the Death Star has been
 *         destroyed, Imperial troops have driven the Rebel forces from their
 *         hidden base and pursued them across the galaxy.
 *     </div>
 * </article>
 * ```
 */
class Post extends Core implements CoreInterface, Setupable {

	/**
	 * @var string The name of the class to handle images by default
	 */
	public $ImageClass = 'Timber\Image';

	/**
	 * @var string The name of the class to handle posts by default
	 */
	public $PostClass = 'Timber\Post';

	/**
	 * @var string The name of the class to handle terms by default
	 */
	public $TermClass = 'Timber\Term';

	/**
	 * @var string What does this class represent in WordPress terms?
	 */
	public $object_type = 'post';

	/**
	 * @api
	 * @var array Stores custom meta data
	 */
	public $custom = array();

	/**
	 * @var string What does this class represent in WordPress terms?
	 */
	public static $representation = 'post';

	/**
	 * @internal
	 * @var string Stores the processed content internally
	 */
	protected $_content;

	/**
	 * @var string The returned permalink from WP's get_permalink function
	 */
	protected $_permalink;

	/**
	 * @var array Stores the results of the next Timber\Post in a set inside an array (in order to manage by-taxonomy)
	 */
	protected $_next = array();

	/**
	 * @var array Stores the results of the previous Timber\Post in a set inside an array (in order to manage by-taxonomy)
	 */
	protected $_prev = array();

	/**
	 * @var string Stores the CSS classes for the post (ex: "post post-type-book post-123")
	 */
	protected $_css_class;

	/**
	 * @api
	 * @var int $id the numeric WordPress id of a post
	 */
	public $id;

	/**
	 * @api
	 * @var string The numeric WordPress id of a post, capitalized to match WordPress usage.
	 */
	public $ID;

	/**
	 * @api
	 * @var int The numeric ID of the a post's author corresponding to the wp_user database table
	 */
	public $post_author;

	/**
	 * @api
	 * @var string The raw text of a WP post as stored in the database
	 */
	public $post_content;

	/**
	 * @api
	 * @var string The raw date string as stored in the WP database, ex: 2014-07-05 18:01:39
	 */
	public $post_date;

	/**
	 * @api
	 * @var string The raw text of a manual post excerpt as stored in the database
	 */
	public $post_excerpt;

	/**
	 * @api
	 * @var int The numeric ID of a post's parent post
	 */
	public $post_parent;

	/**
	 * @api
	 * @var string The status of a post ("draft", "publish", etc.)
	 */
	public $post_status;

	/**
	 * @api
	 * @var string The raw text of a post's title as stored in the database
	 */
	public $post_title;

	/**
	 * @api
	 * @var string The name of the post type, this is the machine name (so "my_custom_post_type" as
	 *      opposed to "My Custom Post Type")
	 */
	public $post_type;

	/**
	 * @api
	 * @var string The URL-safe slug, this corresponds to the poorly-named "post_name" in the WP
	 *      database, ex: "hello-world"
	 */
	public $slug;

	public $_thumbnail_id;

	/**
	 * @var string Stores the PostType object for the Post
	 */
	protected $__type;

	/**
	 * If you send the constructor nothing it will try to figure out the current post id based on
	 * being inside The_Loop.
	 *
	 * @api
	 * @example
	 * ```php
	 * $post = new Timber\Post();
	 * $other_post = new Timber\Post($random_post_id);
	 * ```
	 *
	 * @param mixed $pid
	 */
	public function __construct( $pid = null ) {
		$pid = $this->determine_id($pid);
		$this->init($pid);
	}

	/**
	 * This is helpful for twig to return properties and methods see:
	 * https://github.com/fabpot/Twig/issues/2
	 *
	 * This is also here to ensure that {{ post.class }} remains usable.
	 *
	 * @api
	 *
	 * @return mixed
	 */
	public function __get( $field ) {
		if ( 'class' === $field ) {
			return $this->css_class();
		}
		return parent::__get($field);
	}

	/**
	 * This is helpful for twig to return properties and methods see:
	 * https://github.com/fabpot/Twig/issues/2
	 *
	 * This is also here to ensure that {{ post.class }} remains usable
	 *
	 * @api
	 *
	 * @return mixed
	 */
	public function __call( $field, $args ) {
		if ( 'class' === $field ) {
			$class = isset($args[0]) ? $args[0] : '';
			return $this->css_class($class);
		}

		return parent::__call($field, $args);
	}

	/**
	 * Sets up a post.
	 *
	 * Sets up the `$post` global, and other global variables as well as variables in the
	 * `$wp_query` global that makes Timber more compatible with WordPress.
	 *
	 * This function will be called automatically when you loop over Timber posts as well as in
	 * `Timber::context()`.
	 *
	 * @api
	 * @since 2.0.0
	 *
	 * @return \Timber\Post The post instance.
	 */
	public function setup() {
		global $post;
		global $wp_query;

		// Overwrite post global.
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.OverrideProhibited
		$post = $this;

		// Mimick WordPress behavior to improve compatibility with third party plugins.
		$wp_query->in_the_loop = true;

		// The setup_postdata() function will call the 'the_post' action.
		$wp_query->setup_postdata( $post->ID );

		return $this;
	}

	/**
	 * Resets variables after post has been used.
	 *
	 * This function will be called automatically when you loop over Timber posts.
	 *
	 * @api
	 * @since 2.0.0
	 *
	 * @return \Timber\Post The post instance.
	 */
	public function teardown() {
		global $wp_query;

		$wp_query->in_the_loop = false;

		return $this;
	}

	/**
	 * Determined whether or not an admin/editor is looking at the post in "preview mode" via the
	 * WordPress admin
	 * @internal
	 * @return bool
	 */
	protected static function is_previewing() {
		global $wp_query;
		if ( isset($_GET['preview']) && isset($_GET['preview_nonce']) && wp_verify_nonce($_GET['preview_nonce'], 'post_preview_'.$wp_query->queried_object_id) ) {
			return true;
		}
	}

	/**
	 * tries to figure out what post you want to get if not explictly defined (or if it is, allows it to be passed through)
	 * @internal
	 * @param mixed a value to test against
	 * @return int the numberic id we should be using for this post object
	 */
	protected function determine_id( $pid ) {
		global $wp_query;
		if ( $pid === null &&
			isset($wp_query->queried_object_id)
			&& $wp_query->queried_object_id
			&& isset($wp_query->queried_object)
			&& is_object($wp_query->queried_object)
			&& get_class($wp_query->queried_object) == 'WP_Post'
		) {
			$pid = $wp_query->queried_object_id;
		} else if ( $pid === null && $wp_query->is_home && isset($wp_query->queried_object_id) && $wp_query->queried_object_id ) {
			//hack for static page as home page
			$pid = $wp_query->queried_object_id;
		} else if ( $pid === null ) {
			$gtid = false;
			$maybe_post = get_post();
			if ( isset($maybe_post->ID) ) {
				$gtid = true;
			}
			if ( $gtid ) {
				$pid = get_the_ID();
			}
			if ( !$pid ) {
				global $wp_query;
				if ( isset($wp_query->query['p']) ) {
					$pid = $wp_query->query['p'];
				}
			}
		}
		if ( $pid === null && ($pid_from_loop = PostGetter::loop_to_id()) ) {
			$pid = $pid_from_loop;
		}
		return $pid;
	}

	/**
	 * Outputs the title of the post if you do something like `<h1>{{post}}</h1>`
	 *
	 * @api
	 * @return string
	 */
	public function __toString() {
		return $this->title();
	}

	protected function get_post_preview_object() {
		global $wp_query;
		if ( $this->is_previewing() ) {
			$revision_id = $this->get_post_preview_id( $wp_query );
			return new $this->PostClass( $revision_id );
		}
	}

	protected function get_post_preview_id( $query ) {
		$can = array(
			get_post_type_object($query->queried_object->post_type)->cap->edit_post,
		);

		if ( $query->queried_object->author_id !== get_current_user_id() ) {
			$can[] = get_post_type_object($query->queried_object->post_type)->cap->edit_others_posts;
		}

		$can_preview = array();

		foreach ( $can as $type ) {
			if ( current_user_can($type, $query->queried_object_id) ) {
				$can_preview[] = true;
			}
		}

		if ( count($can_preview) !== count($can) ) {
			return;
		}

		$revisions = wp_get_post_revisions($query->queried_object_id);

		if ( !empty($revisions) ) {
			$revision = reset($revisions);
			return $revision->ID;
		}

		return false;
	}

	/**
	 * Initializes a Post
	 * @internal
	 * @param integer $pid
	 */
	protected function init( $pid = null ) {
		if ( $pid === null ) {
			$pid = get_the_ID();
		}
		if ( is_numeric($pid) ) {
			$this->ID = $pid;
		}
		$post_info = $this->get_info($pid);
		$this->import($post_info);
	}

	/**
	 * updates the post_meta of the current object with the given value
	 * @param string $field
	 * @param mixed $value
	 */
	public function update( $field, $value ) {
		if ( isset($this->ID) ) {
			update_post_meta($this->ID, $field, $value);
			$this->$field = $value;
		}
	}


	/**
	 * takes a mix of integer (post ID), string (post slug),
	 * or object to return a WordPress post object from WP's built-in get_post() function
	 * @internal
	 * @param integer $pid
	 * @return WP_Post on success
	 */
	protected function prepare_post_info( $pid = 0 ) {
		if ( is_string($pid) || is_numeric($pid) || (is_object($pid) && !isset($pid->post_title)) || $pid === 0 ) {
			$pid  = self::check_post_id($pid);
			$post = get_post($pid);
			if ( $post ) {
				return $post;
			}
		}
		// we can skip if already is WP_Post.
		return $pid;
	}


	/**
	 * Helps you find the post id regardless of whether you send a string or whatever.
	 *
	 * @internal
	 * @param integer $pid number to check against.
	 * @return integer ID number of a post
	 */
	protected function check_post_id( $pid ) {
		if ( is_numeric($pid) && 0 === $pid ) {
			$pid = get_the_ID();
			return $pid;
		}
		if ( ! is_numeric($pid) && is_string($pid) ) {
			$pid = PostGetter::get_post_id_by_name($pid);
		}
		return $pid;
	}

	/**
	 * Get a preview (excerpt) of your post.
	 *
	 * If you an excerpt is set on the post, the excerpt will be used. Otherwise it will try to pull
	 * from a preview from `post_content`. If there’s a `<!-- more -->` tag in the post content,
	 * it will use that to mark where to pull through.
	 *
	 * @api
	 * @see \Timber\PostPreview
	 *
	 * @return \Timber\PostPreview
	 */
	public function preview() {
		return new PostPreview($this);
	}

	/**
	 * Get a preview (excerpt) of your post.
	 *
	 * @api
	 * @deprecated 1.3.1, use `{{ post.preview }}` instead.
	 * @see        \Timber\Post::preview()
	 *
	 * @param int         $len      The number of words that WordPress should use to make the
	 *                              preview.
	 *                              (Isn’t this better than [this
	 *                              mess](http://wordpress.org/support/topic/changing-the-default-length-of-the_excerpt-1?replies=14)?).
	 *                              If you’ve set a post excerpt on a post, we’ll use that for the
	 *                              preview text; otherwise the first X words of `post_content`.
	 * @param bool        $force    What happens if your custom post excerpt is longer then the
	 *                              length requested? By default (`$force = false`) it will use the
	 *                              full `post_excerpt`. However, you can set this to `true` to
	 *                              *force* your excerpt to be of the desired length.
	 * @param string      $readmore The text you want to use for the 'readmore' link.
	 * @param bool|string $strip    `true` for default, `false` for none, a string for a list of
	 *                              custom attributes.
	 * @param string      $end      The text to end the preview with. Default `...`.
	 *
	 * @return string The post preview.
	 */
	public function get_preview( $len = 50, $force = false, $readmore = 'Read More', $strip = true, $end = '&hellip;' ) {
		Helper::deprecated('{{ post.get_preview }}', '{{ post.preview }}', '1.3.1');
		$pp = new PostPreview($this);

		/** This filter is documented in PostPreview.php */
		add_filter('timber/post/preview/read_more_class', function(){
			/**
			 * Filters the CSS class used for the preview link for a post.
			 *
			 * This filter only applies when you use `{{ post.get_preview() }}`. When you want to
			 * change the CSS class for all preview links in general, you can use the
			 * `timber/post/preview/read_more_class` filter.
			 *
			 * @since 0.22.3
			 * @example
			 * ```php
			 * // Change the CSS class for preview links
			 * add_filter( 'timber/post/get_preview/read_more_class', function( $class ) {
			 *     return 'post__read-more__link';
			 * } );
			 * ```
			 *
			 * @param string $class The CSS class to use for the preview link. Default `read-more`.
			 */
			return apply_filters('timber/post/get_preview/read_more_class', "read-more");
		});

		return $pp->length($len)->force($force)->read_more($readmore)->strip($strip)->end($end);
	}

	/**
	 * Gets the post meta data values and attaches it to the current object.
	 *
	 * @param int $pid A post ID.
	 */
	public function import_custom( $pid ) {
		$customs = $this->get_meta_values($pid);
		$this->import($customs);
	}

	/**
	 * Used internally to fetch the metadata fields (wp_postmeta table)
	 * and attach them to our Timber\Post object
	 * @internal
	 *
	 * @param int $pid
	 *
	 * @return array
	 */
	protected function get_meta_values( $pid ) {
		$customs = array();

		/**
		 * Fires before post meta data is imported into the object.
		 *
		 * @since 2.0.0
		 *
		 * @param array        $customs An array of custom meta values. Passing an non-empty array
		 *                              will skip fetching the values from the database and will use
		 *                              the filtered values instead.
		 * @param int          $pid     The post ID.
		 * @param \Timber\Post $post    The post object.
		 */
		$customs = apply_filters( 'timber/post/pre_get_meta_values', $customs, $pid, $this );

		/**
		 * Fires before post meta data is imported into the object.
		 *
		 * @deprecated 2.0.0, use `timber/post/pre_get_meta_values`
		 */
		do_action_deprecated(
			'timber_post_get_meta_pre',
			array( $customs, $pid, $this ),
			'2.0.0',
			'timber/post/pre_get_meta_values'
		);

		if ( !is_array($customs) || empty($customs) ) {
			$customs = get_post_custom($pid);
		}

		foreach ( $customs as $key => $value ) {
			if ( is_array($value) && count($value) == 1 && isset($value[0]) ) {
				$value = $value[0];
			}
			$customs[$key] = maybe_unserialize($value);
		}

		/**
		 * Filters post meta data.
		 *
		 * This filter is used by the ACF Integration.
		 *
		 * @todo Add description, example
		 *
		 * @since 2.0.0
		 *
		 * @param array        $customs Post meta data.
		 * @param int          $pid     The post ID.
		 * @param \Timber\Post $post    The post object.
		 */
		$customs = apply_filters( 'timber/post/get_meta_values', $customs, $pid, $this );

		/**
		 * Filters post meta data.
		 *
		 * @deprecated 2.0.0, use `timber/post/get_meta_values`
		 */
		$customs = apply_filters_deprecated(
			'timber_post_get_meta',
			array( $customs, $pid, $this ),
			'2.0.0',
			'timber/post/get_meta_values'
		);

		return $customs;
	}

	/**
	 * @internal
	 * @param int $i
	 * @return string
	 */
	protected static function get_wp_link_page( $i ) {
		$link = _wp_link_page($i);
		$link = new \SimpleXMLElement($link.'</a>');
		if ( isset($link['href']) ) {
			return $link['href'];
		}
	}

	/**
	 * Used internally by init, etc. to build Timber\Post object.
	 *
	 * @internal
	 * @param  int|null $pid The ID to generate info from.
	 * @return null|object|WP_Post
	 */
	protected function get_info( $pid = null ) {
		$post = $this->prepare_post_info($pid);
		if ( !isset($post->post_status) ) {
			return null;
		}

		$post->status = $post->post_status;
		$post->id = $post->ID;
		$post->slug = $post->post_name;

		$customs = $this->get_meta_values($post->ID);

		if ( $this->is_previewing() ) {
			global $wp_query;
			$rev_id = $this->get_post_preview_id($wp_query);
			$customs = $this->get_meta_values($rev_id);
		}

		$post->custom = $customs;
		return $post;
	}

	/**
	 * Gets the comment form for use on a single article page
	 *
	 * @api
	 * @param array This $args array thing is a mess, [fix at some point](http://codex.wordpress.org/Function_Reference/comment_form)
	 * @return string of HTML for the form
	 */
	public function comment_form( $args = array() ) {
		return trim(Helper::ob_function( 'comment_form', array( $args, $this->ID ) ));
	}

	/**
	 * Gets the terms associated with the post.
	 *
	 * @api
	 * @example
	 * ```twig
	 * <section id="job-feed">
	 * {% for post in job %}
	 *     <div class="job">
	 *         <h2>{{ post.title }}</h2>
	 *         <p>{{ post.terms('category')|join(', ') }}</p>
	 *     </div>
	 * {% endfor %}
	 * </section>
	 * ```
	 * ```html
	 * <section id="job-feed">
	 *     <div class="job">
	 *         <h2>Cheese Maker</h2>
	 *         <p>Food, Cheese, Fromage</p>
	 *     </div>
	 *     <div class="job">
	 *         <h2>Mime</h2>
	 *         <p>Performance, Silence</p>
	 *     </div>
	 * </section>
	 * ```
	 * ```php
	 * // Get all terms of a taxonomy.
	 * $terms = $post->terms( 'category' );
	 *
	 * // Get terms of multiple taxonomies.
	 * $terms = $post->terms( array( 'books', 'movies' ) );
	 *
	 * // Use custom arguments for taxonomy query and options.
	 * $terms = $post->terms( array(
     *     'query' => [
     *         'taxonomy' => 'custom_tax',
     *         'orderby'  => 'count',
     *     ],
     *     'merge'      => false,
     *     'term_class' => 'My_Term_Class'
     * ) );
	 * ```
	 *
	 * @param string|array $args {
	 *     Optional. Name of the taxonomy or array of arguments.
	 *
	 *     @type array $query       Any array of term query parameters for getting the terms. See
	 *                              `WP_Term_Query::__construct()` for supported arguments. Use the
	 *                              `taxonomy` argument to choose which taxonomies to get. Defaults
	 *                              to querying all registered taxonomies for the post type. You can
	 *                              use custom or built-in WordPress taxonomies (category, tag).
	 *                              Timber plays nice and figures out that `tag`, `tags` or
	 *                              `post_tag` are all the same (also for `categories` or
	 *                              `category`). For custom taxonomies you need to define the
	 *                              proper name.
	 *     @type bool $merge        Whether the resulting array should be one big one (`true`) or
	 *                              whether it should be an array of sub-arrays for each taxonomy
	 *                              (`false`). Default `true`.
	 *     @type string $term_class The Timber term class to use for the term objects.
	 * }
	 * @return array An array of taxonomies.
	 */
	public function terms( $args = array() ) {
		// Make it possible to use a category or an array of categories as a shorthand.
		if ( ! is_array( $args ) || isset( $args[0] ) ) {
			$args = array(
				'query' => array(
					'taxonomy' => $args,
				),
			);
		}

		// Defaults.
		$args = wp_parse_args( $args, array(
			'query' => array(
				'taxonomy' => 'all',
			),
			'merge' => true,
			'term_class' => $this->TermClass,
		) );

		$tax        = $args['query']['taxonomy'];
		$merge      = $args['merge'];
		$term_class = $args['term_class'];
		$taxonomies = array();

		// Build an array of taxonomies.
		if ( is_array( $tax ) ) {
			$taxonomies = $tax;
		} elseif ( is_string( $tax ) ) {
			if ( in_array( $tax, array( 'all', 'any', '' ) ) ) {
				$taxonomies = get_object_taxonomies($this->post_type);
			} else {
				$taxonomies = array($tax);
			}
		}

		$terms = wp_get_post_terms( $this->ID, $taxonomies, $args['query'] );

		if ( is_wp_error( $terms ) ) {
			/**
			 * @var $terms \WP_Error
			 */
			Helper::error_log( 'Error retrieving terms for taxonomies on a post in lib/Post.php' );
			Helper::error_log( 'tax = ' . print_r( $tax, true ) );
			Helper::error_log( 'WP_Error: ' . $terms->get_error_message() );

			return $terms;
		}

		// Map over array of WordPress terms and transform them into instances of chosen term class.
		$terms = array_map( function( $term ) use ( $term_class ) {
			return call_user_func( array( $term_class, 'from' ), $term->term_id, $term->taxonomy );
		}, $terms );

		if ( ! $merge ) {
			$terms_sorted = array();

			// Initialize sub-arrays.
			foreach ( $taxonomies as $taxonomy ) {
				$terms_sorted[ $taxonomy ] = array();
			}

			// Fill terms into arrays.
			foreach ( $terms as $term ) {
				$terms_sorted[ $term->taxonomy ][] = $term;
			}

			return $terms_sorted;
		}

		return $terms;
	}

	/**
	 * @api
	 * @param string|int $term_name_or_id
	 * @param string $taxonomy
	 * @return bool
	 */
	public function has_term( $term_name_or_id, $taxonomy = 'all' ) {
		if ( $taxonomy == 'all' || $taxonomy == 'any' ) {
			$taxes = get_object_taxonomies($this->post_type, 'names');
			$ret = false;
			foreach ( $taxes as $tax ) {
				if ( has_term($term_name_or_id, $tax, $this->ID) ) {
					$ret = true;
					break;
				}
			}
			return $ret;
		}
		return has_term($term_name_or_id, $taxonomy, $this->ID);
	}

	/**
	 * @api
	 * @return int the number of comments on a post
	 */
	public function comment_count() {
		return get_comments_number($this->ID);
	}


	/**
	 * @api
	 * @param string $field_name
	 * @return boolean
	 */
	public function has_field( $field_name ) {
		return (!$this->meta($field_name)) ? false : true;
	}

	/**
	 * Gets the field object data from Advanced Custom Fields.
	 * This includes metadata on the field like whether it's conditional or not.
	 *
	 * @api
	 * @since 1.6.0
	 * @param string $field_name of the field you want to lookup.
	 * @return mixed
	 */
	public function field_object( $field_name ) {
		/**
		 * Filters field object data from Advanced Custom Fields.
		 *
		 * This filter is used by the ACF Integration.
		 *
		 * @todo Add example
		 *
		 * @see   \Timber\Post::field_object()
		 * @since 1.6.0
		 *
		 * @param array        $value      The field object array.
		 * @param int          $post_id    The post ID.
		 * @param string       $field_name The ACF field name.
		 * @param \Timber\Post $post       The post object.
		 */
		$value = apply_filters('timber/post/meta_object_field', null, $this->ID, $field_name, $this);

		$value = $this->convert($value, __CLASS__);
		return $value;
	}

	/**
	 * Gets a post meta value.
	 *
	 * Returns a meta value for a post that’s saved in the post meta database table.
	 *
	 * @api
	 *
	 * @param string $field_name The field name for which you want to get the value.
	 * @return mixed The meta field value.
	 */
  public function meta( $field_name = null ) {
    if ( $rd = $this->get_revised_data_from_method('meta', $field_name) ) {
	    return $rd;
	  }

		/**
		 * Filters the value for a post meta field before it is fetched from the database.
		 *
		 * @todo  Add description, example
		 *
		 * @see   \Timber\Post::meta()
		 * @since 2.0.0
		 *
		 * @param string       $value      The field value. Default null.
		 * @param int          $post_id    The post ID.
		 * @param string       $field_name The name of the meta field to get the value for.
		 * @param \Timber\Post $post       The post object.
		 */
		$value = apply_filters( 'timber/post/pre_meta', null, $this->ID, $field_name, $this );

		if ( null === $field_name ) {
			Helper::warn('You have not set what meta field you want to retrive this can cause strange behavior and is not recommended');
		}

		if ( "meta" === $field_name ) {
			Helper::warn('You are trying to retrive a meta field named "meta" this can cause strange behavior and is not recommended');
		}

		/**
		 * Filters the value for a post meta field before it is fetched from the database.
		 *
		 * @deprecated 2.0.0, use `timber/post/pre_meta`
		 */
		$value = apply_filters_deprecated(
			'timber_post_get_meta_field_pre',
			array( $value, $this->ID, $field_name, $this ),
			'2.0.0',
			'timber/post/pre_meta'
		);

		if ( $value === null ) {
			$value = get_post_meta($this->ID, $field_name);
			if ( is_array($value) && count($value) == 1 ) {
				$value = $value[0];
			}
			if ( is_array($value) && count($value) == 0 ) {
				$value = null;
			}
		}

		/**
		 * Filters the value for a post meta field.
		 *
		 * This filter is used by the ACF Integration.
		 *
		 * @todo  Add description, example
		 *
		 * @see   \Timber\Post::meta()
		 * @since 2.0.0
		 *
		 * @param string       $value      The field value.
		 * @param int          $post_id    The post ID.
		 * @param string       $field_name The name of the meta field to get the value for.
		 * @param \Timber\Post $post       The post object.
		 */
		$value = apply_filters( 'timber/post/meta', $value, $this->ID, $field_name, $this );

		/**
		 * Filters the value for a post meta field.
		 *
		 * @deprecated 2.0.0, use `timber/post/meta`
		 */
		$value = apply_filters_deprecated(
			'timber_post_get_meta_field',
			array( $value, $this->ID, $field_name, $this ),
			'2.0.0',
			'timber/post/meta'
		);

		$value = $this->convert($value, __CLASS__);
		return $value;
	}

	/**
	 * Import field data onto this object
	 *
	 * @api
	 * @deprecated since 2.0.0
	 * @param string $field_name
	 */
	public function import_field( $field_name ) {
		$this->$field_name = $this->meta($field_name);
	}

	/**
	 * Get the CSS classes for a post without cache.
	 * For usage you should use `{{post.class}}`
	 *
	 * @internal
	 * @param string $class additional classes you want to add.
	 * @example
	 * ```twig
	 * <article class="{{ post.post_class }}">
	 *    {# Some stuff here #}
	 * </article>
	 * ```
	 *
	 * ```html
	 * <article class="post-2612 post type-post status-publish format-standard has-post-thumbnail hentry category-data tag-charleston-church-shooting tag-dylann-roof tag-gun-violence tag-hate-crimes tag-national-incident-based-reporting-system">
	 *    {# Some stuff here #}
	 * </article>
	 * ```
	 * @return string a space-seperated list of classes
	 */
	public function post_class( $class = '' ) {
		global $post;
		$old_global_post = $post;
    $post = $this;

		$class_array = get_post_class($class, $this->ID);
		if ( $this->is_previewing() ) {
			$class_array = get_post_class($class, $this->post_parent);
		}

		if ( is_array($class_array) ) {
			$class_array = implode(' ', $class_array);
		}

        $post = $old_global_post;
		return $class_array;
	}

	/**
	 * Get the CSS classes for a post, but with caching css post classes. For usage you should use `{{ post.class }}` instead of `{{post.css_class}}` or `{{post.post_class}}`
	 *
	 * @internal
	 * @param string $class additional classes you want to add.
	 * @see \Timber\Post::$_css_class
	 * @example
	 * ```twig
	 * <article class="{{ post.class }}">
	 *    {# Some stuff here #}
	 * </article>
	 * ```
	 *
	 * @return string a space-seperated list of classes
	 */
	public function css_class( $class = '' ) {
		if ( ! $this->_css_class ) {
			$this->_css_class = $this->post_class();
		}

		return trim(sprintf('%s %s', $this->_css_class, $class));
	}

	/**
	 * @return array
	 * @codeCoverageIgnore
	 */
	public function get_method_values() {
		$ret = parent::get_method_values();
		$ret['author'] = $this->author();
		$ret['categories'] = $this->categories();
		$ret['category'] = $this->category();
		$ret['children'] = $this->children();
		$ret['comments'] = $this->comments();
		$ret['content'] = $this->content();
		$ret['edit_link'] = $this->edit_link();
		$ret['format'] = $this->format();
		$ret['link'] = $this->link();
		$ret['next'] = $this->next();
		$ret['pagination'] = $this->pagination();
		$ret['parent'] = $this->parent();
		$ret['path'] = $this->path();
		$ret['prev'] = $this->prev();
		$ret['terms'] = $this->terms();
		$ret['tags'] = $this->tags();
		$ret['thumbnail'] = $this->thumbnail();
		$ret['title'] = $this->title();
		return $ret;
	}

	/**
	 * Return the author of a post
	 *
	 * @api
	 * @example
	 * ```twig
	 * <h1>{{post.title}}</h1>
	 * <p class="byline">
	 *     <a href="{{post.author.link}}">{{post.author.name}}</a>
	 * </p>
	 * ```
	 * @return User|null A User object if found, false if not
	 */
	public function author() {
		if ( isset($this->post_author) ) {
			return new User($this->post_author);
		}
	}

	/**
	 * Got more than one author? That's cool, but you'll need Co-Authors plus or another plugin to access any data
	 *
	 * @api
	 * @return array
	 */
	public function authors() {
		/**
		 * Filters authors for a post.
		 *
		 * This filter is used by the CoAuthorsPlus integration.
		 *
		 * @todo  Add example
		 *
		 * @see   \Timber\Post::authors()
		 * @since 1.1.4
		 *
		 * @param array        $authors An array of User objects. Default: User object for `post_author`.
		 * @param \Timber\Post $post    The post object.
		 */
		return apply_filters('timber/post/authors', array($this->author()), $this);
	}

	/**
	 * Get the author (WordPress user) who last modified the post
	 *
	 * @api
	 * @example
	 * ```twig
	 * Last updated by {{ post.modified_author.name }}
	 * ```
	 * ```html
	 * Last updated by Harper Lee
	 * ```
	 * @return User|null A User object if found, false if not
	 */
	public function modified_author() {
		$user_id = get_post_meta($this->ID, '_edit_last', true);
		return ($user_id ? new User($user_id) : $this->author());
	}

	/**
	 * Get the categories on a particular post
	 *
	 * @api
	 * @return array of Timber\Term objects
	 */
	public function categories() {
		return $this->terms('category');
	}

	/**
	 * Returns a category attached to a post
	 *
	 * @api
	 * If mulitpuile categories are set, it will return just the first one
	 * @return \Timber\Term|null
	 */
	public function category() {
		$cats = $this->categories();
		if ( count($cats) && isset($cats[0]) ) {
			return $cats[0];
		}
	}

	/**
	 * Returns an array of children on the post as Timber\Posts
	 * (or other claass as you define).
	 *
	 * @api
	 * @example
	 * ```twig
	 * {% if post.children %}
	 *     Here are the child pages:
	 *     {% for child in post.children %}
	 *         <a href="{{ child.link }}">{{ child.title }}</a>
	 *     {% endfor %}
	 * {% endif %}
	 * ```
	 * @param string|array $post_type _optional_ use to find children of a particular post type (attachment vs. page for example). You might want to restrict to certain types of children in case other stuff gets all mucked in there. You can use 'parent' to use the parent's post type or you can pass an array of post types.
	 * @param string|bool  $child_post_class _optional_ a custom post class (ex: 'MyTimber\Post') to return the objects as. By default (false) it will use Timber\Post::$post_class value.
	 * @return array
	 */
	public function children( $post_type = 'any', $child_post_class = false ) {
		if ( $child_post_class === false ) {
			$child_post_class = $this->PostClass;
		}
		if ( $post_type === 'parent' ) {
			$post_type = $this->post_type;
		}
		if ( is_array($post_type) ) {
			$post_type = implode('&post_type[]=', $post_type);
		}
		$query = 'post_parent=' . $this->ID . '&post_type[]=' . $post_type . '&numberposts=-1&orderby=menu_order title&order=ASC&post_status[]=publish';
		if ( $this->post_status === 'publish' ) {
			$query .= '&post_status[]=inherit';
		}
		$children = get_children($query);
		foreach ( $children as &$child ) {
			$child = new $child_post_class($child->ID);
		}
		$children = array_values($children);
		return $children;
	}

	/**
	 * Gets the comments on a Timber\Post and returns them as an array of `Timber\Comment` objects (or whatever comment class you set).
	 *
	 * @api
	 * @example
	 * ```twig
	 * {# single.twig #}
	 * <h4>Comments:</h4>
	 * {% for comment in post.comments %}
	 * 	<div class="comment-{{comment.ID}} comment-order-{{loop.index}}">
	 * 		<p>{{comment.author.name}} said:</p>
	 * 		<p>{{comment.content}}</p>
	 * 	</div>
	 * {% endfor %}
	 * ```
	 *
	 * @param int $count Set the number of comments you want to get. `0` is analogous to "all"
	 * @param string $order use ordering set in WordPress admin, or a different scheme
	 * @param string $type For when other plugins use the comments table for their own special purposes, might be set to 'liveblog' or other depending on what's stored in yr comments table
	 * @param string $status Could be 'pending', etc.
	 * @param string $CommentClass What class to use when returning Comment objects. As you become a Timber pro, you might find yourself extending Timber\Comment for your site or app (obviously, totally optional)
	 * @return bool|array
	 */
	public function comments( $count = null, $order = 'wp', $type = 'comment', $status = 'approve', $CommentClass = 'Timber\Comment' ) {
		global $overridden_cpage, $user_ID;
		$overridden_cpage = false;

		$commenter = wp_get_current_commenter();
		$comment_author_email = $commenter['comment_author_email'];

		$args = array('status' => $status, 'order' => $order, 'type' => $type);
		if ( $count > 0 ) {
			$args['number'] = $count;
		}
		if ( strtolower($order) == 'wp' || strtolower($order) == 'wordpress' ) {
			$args['order'] = get_option('comment_order');
		}

		if ( $user_ID ) {
			$args['include_unapproved'] = array($user_ID);
		} elseif ( !empty($comment_author_email) ) {
			$args['include_unapproved'] = array($comment_author_email);
		}
		$ct = new CommentThread($this->ID, false);
		$ct->CommentClass = $CommentClass;
		$ct->init($args);
		return $ct;
	}

	/**
	 * If the Password form is to be shown, show it!
	 * @return string|void
	 */
	protected function maybe_show_password_form() {
		if ( $this->password_required() ) {
			$show_pw = false;

			/**
			 * Filters whether the password form should be shown for password protected posts.
			 *
			 * This filter runs only when you call `{{ post.content }}` for a password protected
			 * post. When this filter returns `true`, a password form will be shown instead of the
			 * post content. If you want to modify the form itself, you can use the
			 * `timber/post/content/password_form` filter.
			 *
			 * @since 1.1.4
			 * @example
			 * ```php
			 * // Always show password form for password protected posts.
			 * add_filter( 'timber/post/content/show_password_form_for_protected', '__return_true' );
			 * ```
			 *
			 * @param bool $show_pw Whether the password form should be shown. Default `false`.
			 */
			$show_pw = apply_filters('timber/post/content/show_password_form_for_protected', $show_pw);

			if ( $show_pw ) {
				/**
				 * Filters the password form output.
				 *
				 * As an alternative to this filter, you could also use WordPress’s `the_password_form` filter.
				 * The difference to this filter is, that you’ll also have the post object available as a second
				 * parameter, in case you need that.
				 *
				 * @since 1.1.4
				 *
				 * @example
				 * ```php
				 * // Modify the password form.
				 * add_filter( 'timber/post/content/password_form', function( $form, $post ) {
				 *     return Timber::compile( 'assets/password-form.twig', array( 'post' => $post ) );
				 * }, 10, 2 );
				 * ```
				 *
				 * @param string       $form Form output. Default WordPress password form output generated by `get_the_password_form()`.
				 * @param \Timber\Post $post The post object.
				 */
				return apply_filters('timber/post/content/password_form', get_the_password_form($this->ID), $this);
			}
		}
	}

	/**
	 *
	 */
	protected function get_revised_data_from_method( $method, $args = false ) {
		if ( ! is_array($args) ) {
			$args = array($args);
		}
		$rev = $this->get_post_preview_object();
		if ( $rev && $this->ID == $rev->post_parent && $this->ID != $rev->ID ) {
			return call_user_func_array( array($rev, $method), $args );
		}
	}

	/**
	 * Gets the actual content of a WP Post, as opposed to post_content this will run the hooks/filters attached to the_content. \This guy will return your posts content with WordPress filters run on it (like for shortcodes and wpautop).
	 *
	 * @api
	 * @example
	 * ```twig
	 * <div class="article">
	 *     <h2>{{post.title}}</h2>
	 *     <div class="content">{{ post.content }}</div>
	 * </div>
	 * ```
	 * @param int $page
	 * @return string
	 */
	public function content( $page = 0, $len = -1 ) {
		if ( $rd = $this->get_revised_data_from_method('content', array($page, $len) ) ) {
			return $rd;
		}
		if ( $form = $this->maybe_show_password_form() ) {
			return $form;
		}
		if ( $len == -1 && $page == 0 && $this->_content ) {
			return $this->_content;
		}
		$content = $this->post_content;
		if ( $len > 0 ) {
			$content = wp_trim_words($content, $len);
		}
		if ( $page ) {
			$contents = explode('<!--nextpage-->', $content);
			$page--;
			if ( count($contents) > $page ) {
				$content = $contents[$page];
			}
		}
		$content = apply_filters('the_content', ($content));
		if ( $len == -1 && $page == 0 ) {
			$this->_content = $content;
		}
		return $content;
	}

	/**
	 * @return string
	 */
	public function paged_content() {
		global $page;
		return $this->content($page, -1);
	}

	/**
	 * Get the date to use in your template!
	 *
	 * @api
	 * @example
	 * ```twig
	 * Published on {{ post.date }} // Uses WP's formatting set in Admin
	 * OR
	 * Published on {{ post.date('F jS') }} // Jan 12th
	 * ```
	 *
	 * ```html
	 * Published on January 12, 2015
	 * OR
	 * Published on Jan 12th
	 * ```
	 * @param string $date_format
	 * @return string
	 */
	public function date( $date_format = '' ) {
		$df       = $date_format ? $date_format : get_option('date_format');
		$the_date = (string) mysql2date($df, $this->post_date);
		return apply_filters('get_the_date', $the_date, $df);
	}

	/**
	 * Get the time to use in your template
	 *
	 * @api
	 * @example
	 * ```twig
	 * Published at {{ post.time }} // Uses WP's formatting set in Admin
	 * OR
	 * Published at {{ post.time | time('G:i') }} // 13:25
	 * ```
	 *
	 * ```html
	 * Published at 1:25 pm
	 * OR
	 * Published at 13:25
	 * ```
	 * @param  string $time_format
	 * @return string
	 */
	public function time( $time_format = '' ) {
		$tf       = $time_format ? $time_format : get_option('time_format');
		$the_time = (string) mysql2date($tf, $this->post_date);
		return apply_filters('get_the_time', $the_time, $tf);
	}


	/**
	 * Returns the post_type object with labels and other info
	 *
	 * @api
	 * @since 1.0.4
	 * @example
	 *
	 * ```twig
	 * This post is from <span>{{ post.type.labels.name }}</span>
	 * ```
	 *
	 * ```html
	 * This post is from <span>Recipes</span>
	 * ```
	 * @return PostType
	 */
	public function type() {
		if ( isset($this->custom['type']) ) {
			return $this->custom['type'];
		}
		if ( ! $this->__type instanceof PostType ) {
			$this->__type = new PostType($this->post_type);
		}
		return $this->__type;
	}

	/**
	 * Returns the edit URL of a post if the user has access to it
	 *
	 * @api
	 * @return bool|string the edit URL of a post in the WordPress admin
	 */
	public function edit_link() {
		if ( $this->can_edit() ) {
			return get_edit_post_link($this->ID);
		}
	}

	/**
	 * @api
	 * @return mixed
	 */
	public function format() {
		return get_post_format($this->ID);
	}

	/**
	 * whether post requires password and correct password has been provided
	 * @api
	 * @return boolean
	 */
	public function password_required() {
		return post_password_required($this->ID);
	}

	/**
	 * get the permalink for a post object
	 * @api
	 * @example
	 * ```twig
	 * <a href="{{post.link}}">Read my post</a>
	 * ```
	 * @return string ex: http://example.org/2015/07/my-awesome-post
	 */
	public function link() {
		if ( isset($this->_permalink) ) {
			return $this->_permalink;
		}
		$this->_permalink = get_permalink($this->ID);
		return $this->_permalink;
	}

	/**
	 * Gets a post meta value.
	 *
	 * @api
	 * @deprecated 2.0.0, use `{{ post.meta('field_name') }}` instead.
	 * @see \Timber\Post::meta()
	 *
	 * @param string $field_name The field name for which you want to get the value.
	 * @return mixed The meta field value.
	 */
	public function get_field( $field_name = null ) {
		Helper::deprecated(
			"{{ post.get_field('field_name') }}",
			"{{ post.meta('field_name' }}",
			'2.0.0'
		);

		if ( $field_name === null ) {
			//on the off-chance the field is actually named meta
			$field_name = 'meta';
		}

		return $this->meta( $field_name );
	}

	/**
	 * @api
	 * @return string
	 */
	public function name() {
		return $this->title();
	}

	/**
	 * @api
	 * @param string $date_format
	 * @return string
	 */
	public function modified_date( $date_format = '' ) {
		$df = $date_format ? $date_format : get_option('date_format');
		$the_time = $this->modified_time($df);
		return apply_filters('get_the_modified_date', $the_time, $date_format);
	}

	/**
	 * @api
	 * @param string $time_format
	 * @return string
	 */
	public function modified_time( $time_format = '' ) {
		$tf = $time_format ? $time_format : get_option('time_format');
		$the_time = get_post_modified_time($tf, false, $this->ID, true);
		return apply_filters('get_the_modified_time', $the_time, $time_format);
	}

	/**
	 * @api
	 * @param bool $in_same_term
	 * @return mixed
	 */
	public function next( $in_same_term = false ) {
		if ( !isset($this->_next) || !isset($this->_next[$in_same_term]) ) {
			global $post;
			$this->_next = array();
			$old_global = $post;
			$post = $this;
			if ( $in_same_term ) {
				$adjacent = get_adjacent_post(true, '', false, $in_same_term);
			} else {
				$adjacent = get_adjacent_post(false, '', false);
			}

			if ( $adjacent ) {
				$this->_next[$in_same_term] = new $this->PostClass($adjacent);
			} else {
				$this->_next[$in_same_term] = false;
			}
			$post = $old_global;
		}
		return $this->_next[$in_same_term];
	}

	/**
	 * Get a data array of pagination so you can navigate to the previous/next for a paginated post.
	 *
	 * @api
	 * @return array
	 */
	public function pagination() {
		global $post, $page, $numpages, $multipage;
		$post = $this;
		$ret = array();
		if ( $multipage ) {
			for ( $i = 1; $i <= $numpages; $i++ ) {
				$link = self::get_wp_link_page($i);
				$data = array('name' => $i, 'title' => $i, 'text' => $i, 'link' => $link);
				if ( $i == $page ) {
					$data['current'] = true;
				}
				$ret['pages'][] = $data;
			}
			$i = $page - 1;
			if ( $i ) {
				$link = self::get_wp_link_page($i);
				$ret['prev'] = array('link' => $link);
			}
			$i = $page + 1;
			if ( $i <= $numpages ) {
				$link = self::get_wp_link_page($i);
				$ret['next'] = array('link' => $link);
			}
		}
		return $ret;
	}


	/**
	 * Finds any WP_Post objects and converts them to Timber\Posts
	 *
	 * @api
	 * @param array|WP_Post $data
	 * @param string $class
	 */
	public function convert( $data, $class = '\Timber\Post' ) {
		if ( $data instanceof WP_Post ) {
			$data = new $class($data);
		} elseif ( is_array($data) ) {
			$func = __FUNCTION__;
			foreach ( $data as &$ele ) {
				if ( gettype($ele) === 'array' ) {
					$ele = $this->$func($ele, $class);
				} else {
					if ( $ele instanceof WP_Post ) {
						$ele = new $class($ele);
					}
				}
			}
		}
		return $data;
	}


	/**
	 * Gets the parent (if one exists) from a post as a Timber\Post object (or whatever is set in
	 * Timber\Post::$PostClass)
	 *
	 * @api
	 * @example
	 * ```twig
	 * Parent page: <a href="{{ post.parent.link }}">{{ post.parent.title }}</a>
	 * ```
	 * @return bool|\Timber\Post
	 */
	public function parent() {
		if ( !$this->post_parent ) {
			return false;
		}
		return new $this->PostClass($this->post_parent);
	}

	/**
	 * Gets the relative path of a WP Post, so while link() will return http://example.org/2015/07/my-cool-post
	 * this will return just /2015/07/my-cool-post
	 *
	 * @api
	 * @example
	 * ```twig
	 * <a href="{{post.path}}">{{post.title}}</a>
	 * ```
	 * @return string
	 */
	public function path() {
		return URLHelper::get_rel_url($this->link());
	}


	/**
	 * Get the previous post in a set
	 *
	 * @api
	 * @example
	 * ```twig
	 * <h4>Prior Entry:</h4>
	 * <h3>{{post.prev.title}}</h3>
	 * <p>{{post.prev.preview(25)}}</p>
	 * ```
	 * @param bool $in_same_term
	 * @return mixed
	 */
	public function prev( $in_same_term = false ) {
		if ( isset($this->_prev) && isset($this->_prev[$in_same_term]) ) {
			return $this->_prev[$in_same_term];
		}
		global $post;
		$old_global = $post;
		$post = $this;
		$within_taxonomy = ($in_same_term) ? $in_same_term : 'category';
		$adjacent = get_adjacent_post(($in_same_term), '', true, $within_taxonomy);
		$prev_in_taxonomy = false;
		if ( $adjacent ) {
			$prev_in_taxonomy = new $this->PostClass($adjacent);
		}
		$this->_prev[$in_same_term] = $prev_in_taxonomy;
		$post = $old_global;
		return $this->_prev[$in_same_term];
	}

	/**
	 * Gets the tags on a post, uses WP's post_tag taxonomy
	 *
	 * @api
	 * @return array
	 */
	public function tags() {
		return $this->terms('post_tag');
	}

	/**
	 * get the featured image as a Timber/Image
	 *
	 * @api
	 * @example
	 * ```twig
	 * <img src="{{ post.thumbnail.src }}" />
	 * ```
	 * @return Timber\Image|null of your thumbnail
	 */
	public function thumbnail() {
		$tid = get_post_thumbnail_id($this->ID);
		if ( $tid ) {
			return new $this->ImageClass($tid);
		}
	}


	/**
	 * Returns the processed title to be used in templates. This returns the title of the post after WP's filters have run. This is analogous to `the_title()` in standard WP template tags.
	 *
	 * @api
	 * @example
	 * ```twig
	 * <h1>{{ post.title }}</h1>
	 * ```
	 * @return string
	 */
	public function title() {
		if ( $rd = $this->get_revised_data_from_method('title') ) {
			return $rd;
		}
		return apply_filters('the_title', $this->post_title, $this->ID);
	}

	/**
	 * Returns the gallery
	 *
	 * @api
	 * @example
	 * ```twig
	 * {{ post.gallery }}
	 * ```
	 * @return html
	 */
	public function gallery( $html = true ) {
		if ( isset($this->custom['gallery']) ) {
			return $this->custom['gallery'];
		}
		$galleries = get_post_galleries($this->ID, $html);
		$gallery = reset($galleries);

		return apply_filters('get_post_gallery', $gallery, $this->ID, $galleries);
	}

	/**
	 * Returns the audio
	 *
	 * @api
	 * @example
	 * ```twig
	 * {{ post.audio }}
	 * ```
	 * @return html
	 */
	public function audio() {
		if ( isset($this->custom['audio']) ) {
			return $this->custom['audio'];
		}
		$audio = false;

		// Only get audio from the content if a playlist isn't present.
		if ( false === strpos($this->content(), 'wp-playlist-script') ) {
			$audio = get_media_embedded_in_content($this->content(), array('audio'));
		}

		return $audio;
	}

	/**
	 * Returns the video
	 *
	 * @api
	 * @example
	 * ```twig
	 * {{ post.video }}
	 * ```
	 * @return html
	 */
	public function video() {
		if ( isset($this->custom['video']) ) {
			return $this->custom['video'];
		}
		$video = false;

		// Only get video from the content if a playlist isn't present.
		if ( false === strpos($this->content(), 'wp-playlist-script') ) {
			$video = get_media_embedded_in_content($this->content(), array( 'video', 'object', 'embed', 'iframe' ));
		}

		return $video;
	}

}
