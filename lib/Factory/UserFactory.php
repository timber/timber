<?php

namespace Timber\Factory;

use Timber\CoreInterface;
use Timber\User;

use WP_User;

class UserFactory {
	public function from($params) {
		if (is_int($params)) {
			return $this->from_id($params);
		}
	}

	protected function from_id(int $id) {
		return $this->build(get_user_by('id', $id));
	}

	protected function build(WP_User $user) : CoreInterface {
		$class = apply_filters( 'timber/user/classmap', User::class, $user );

		return new $class($user);
	}
}
