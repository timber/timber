<?php

namespace Timber\Factory;

use Timber\CoreInterface;
use Timber\Menu;

use WP_Term;

/**
 * Internal API class for instantiating Menus
 */
class MenuFactory {
	public function from($params) {
		if (is_int($params) || is_string($params) && is_numeric($params)) {
			return $this->from_id((int) $params);
		}

		if (is_object($params)) {
			return $this->from_term_obj($params);
		}

		return false;
	}

	protected function from_id(int $id) {
		// WP Menus are WP_Term objects under the hood.
		$term = wp_get_nav_menu_object($id);

		if (!$term) {
			return false;
		}

		return $this->build($term);
	}

	protected function from_term_obj(object $obj) : CoreInterface {
		if ($obj instanceof CoreInterface) {
			// We already have a Timber Core object of some kind
			return $obj;
		}

		if ($obj instanceof WP_Term) {
			return $this->build($obj);
		}

		throw new \InvalidArgumentException(sprintf(
			'Expected an instance of Timber\CoreInterface or WP_Term, got %s',
			get_class($obj)
		));
	}

	protected function get_menu_class(WP_Term $term) : string {
		// Get the user-configured Class Map, defaulting to the Menu class
		$class = apply_filters('timber/menu/classmap', Menu::class, $term);

		return $class ?? Menu::class;
	}

	protected function build(WP_Term $term) : CoreInterface {
		$class = $this->get_menu_class($term);

		return $class::build($term);
	}
}

