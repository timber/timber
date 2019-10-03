<?php

namespace Timber\Factory;

use Timber\CoreInterface;
use Timber\Term;

use WP_Term;

/**
 * Internal API class for instantiating Terms
 */
class TermFactory {
	public function from($params) {
		if (is_int($params)) {
			return $this->from_id($params);
		}

		return $this->from_terms_array($params);

		// @todo from_query_array
		// @todo from_query
	}

	protected function from_id(int $id) {
		return $this->build(get_term($id));
	}

	protected function from_terms_array(array $terms) : array {
		return array_map([$this, 'build'], $terms);
	}

	protected function get_term_class(WP_Term $term) : string {
		// Get the user-configured Class Map
		$map = apply_filters( 'timber/term/classmap', [
			'post_tag' => Term::class,
			'category' => Term::class,
		]);

		$class = $map[$term->taxonomy] ?? null;

		if (is_callable($class)) {
			$class = $class($term);
		}

    // If we don't have a term class by now, fallback on the default class
		return $class ?? Term::class;
	}

	protected function build(WP_Term $term) : CoreInterface {
		$class = $this->get_term_class($term);

    // @todo make Core constructors protected, call Term::build() here
		return new $class($term);
	}
}
