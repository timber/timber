<?php

namespace Timber\Factory;

use Timber\CoreInterface;
use Timber\Term;

use WP_Term;

/**
 * Internal API class for instantiating posts
 */
class TermFactory {
	public function get_term(int $id) {
		return $this->build(get_term($id));
	}

	public function from($queryOrTerms) {
		return $this->from_terms_array($queryOrTerms);
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
