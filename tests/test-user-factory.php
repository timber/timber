<?php

use Timber\User;
use Timber\Factory\UserFactory;

class AdminUser extends User {}
class SpecialUser extends User {}

/**
 * @group factory
 */
class TestUserFactory extends Timber_UnitTestCase {

	public function testGetUser() {
		$id = $this->factory->user->create([
			'user_email' => 'me@example.com',
		]);

		$userFactory = new UserFactory();
		$user        = $userFactory->from($id);

		$this->assertInstanceOf(User::class, $user);
	}

	public function testGetUserWithOverrides() {
		$my_class_map = function(string $class, WP_User $user) {
			return in_array('administrator', $user->roles)
				? AdminUser::class
				: $class;
		};
		add_filter( 'timber/user/classmap', $my_class_map, 10, 2 );

		$admin_id = $this->factory->user->create([
			'user_email' => 'me@example.com',
			'role'       => 'administrator',
		]);
		$normie_id = $this->factory->user->create([
			'user_email' => 'someone@example.com',
		]);

		$userFactory = new UserFactory();
		$admin       = $userFactory->from($admin_id);
		$normie      = $userFactory->from($normie_id);

		$this->assertInstanceOf(AdminUser::class, $admin);
		$this->assertInstanceOf(User::class,      $normie);

		remove_filter( 'timber/user/classmap', $my_class_map );
	}
}
