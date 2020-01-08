<?php

namespace Timber\Factory;

use Timber\CoreInterface;
use Timber\User;

use WP_User_Query;
use WP_User;

class UserFactory {
	public function from($params) {
		if (is_int($params)) {
			return $this->from_id($params);
		}

		if ($params instanceof WP_User_Query) {
			return $this->from_wp_user_query($params);
		}

		if (is_object($params)) {
			// assume we have some kind of WP user object, Timber or otherwise
			return $this->from_user_object($params);
		}

		if ($this->is_numeric_array($params)) {
			// we have a numeric array of objects and/or IDs
			return array_map([$this, 'from'], $params);
		}

		if (is_array($params)) {
			// we have a query array to be passed to WP_User_Query::__construct()
			return $this->from_wp_user_query(new WP_User_Query($params));
		}
	}

	protected function from_id(int $id) {
		$wp_user = get_user_by('id', $id);

		return $wp_user ? $this->build($wp_user) : null;
	}

	protected function from_user_object(object $obj) : CoreInterface {
		if ($obj instanceof CoreInterface) {
			// we already have some kind of Timber Core object
			return $obj;
		}

		if ($obj instanceof WP_User) {
			return $this->build($obj);
		}

		throw new \InvalidArgumentException(sprintf(
			'Expected an instance of Timber\CoreInterface or WP_User, got %s',
			get_class($obj)
		));
	}

	// @todo return a UserCollection instance?
	protected function from_wp_user_query(WP_User_Query $query) : Iterable {
		return array_map([$this, 'build'], $query->get_results());
	}

	protected function build(WP_User $user) : CoreInterface {
		$class = apply_filters( 'timber/user/classmap', User::class, $user );

		return $class::build($user);
	}

	protected function is_numeric_array(array $arr) {
		foreach (array_keys($arr) as $k) {
			if ( ! is_int($k) ) return false;
		}
		return true;
	}
}
