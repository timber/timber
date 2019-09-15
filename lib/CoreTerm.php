<?php

namespace Timber;

abstract class CoreTerm extends Core {

	public $PostClass = 'Timber\Post';

	/**
	 * @api
	 * @var string the human-friendly name of the term (ex: French Cuisine)
	 */
	public $name;

	/**
	 * Gets a term meta value.
	 *
	 * Returns meta information stored with a term. This will use both data stored under (old) ACF
	 * hacks and new (WP 4.6+) where term meta has its own table. If retrieving a special ACF field
	 * (repeater, etc.) you can use the output immediately in Twig — no further processing is
	 * required.
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
	 * @param string $field_name The field name for which you want to get the value.
	 * @param array  $args       An array of arguments for getting the meta value. Third-party
	 *                           integrations can use this argument to make their API arguments
	 *                           available in Timber. Default empty.
	 * @return mixed The meta field value.
	 */
	public function meta( $field_name, $args = array() ) {
		/**
		 * Filters the value for a term meta field before it is fetched from the database.
		 *
		 * @todo  Add description, example
		 *
		 * @see   \Timber\Term::meta()
		 * @since 2.0.0
		 *
		 * @param string       $value      The field value. Passing a non-null value will skip
		 *                                 fetching the value from the database. Default null.
		 * @param int          $post_id    The post ID.
		 * @param string       $field_name The name of the meta field to get the value for.
		 * @param \Timber\Term $term       The term object.
		 * @param array        $args       An array of arguments.
		 */
		$value = apply_filters(
			'timber/term/pre_meta',
			null,
			$this->ID,
			$field_name,
			$this,
			$args
		);

		if ( null === $value ) {
			$value = get_term_meta($this->ID, $field_name, true);
		}

		/**
		 * Filters the value for a term meta field.
		 *
		 * This filter is used by the ACF Integration.
		 *
		 * @todo  Add description, example
		 *
		 * @see   \Timber\Term::meta()
		 * @since 0.21.9
		 *
		 * @param mixed        $value The field value.
		 * @param int          $term_id     The term ID.
		 * @param string       $field_name  The name of the meta field to get the value for.
		 * @param \Timber\Term $term        The term object.
		 * @param array        $args        An array of arguments.
		 */
		$value = apply_filters(
			'timber/term/meta',
			$value,
			$this->ID,
			$field_name,
			$this,
			$args
		);

		/**
		 * Filters the value for a term meta field.
		 *
		 * @deprecated 2.0.0, use `timber/term/meta`
		 */
		$value = apply_filters_deprecated(
			'timber/term/meta/field',
			array( $value, $this->ID, $field_name, $this ),
			'2.0.0',
			'timber/term/meta'
		);

		/**
		 * Filters the value for a term meta field.
		 *
		 * @deprecated 2.0.0, use `timber/term/meta`
		 */
		$value = apply_filters_deprecated(
			'timber_term_get_meta_field',
			array( $value, $this->ID, $field_name, $this ),
			'2.0.0',
			'timber/term/meta'
		);

		return $value;
	}

}