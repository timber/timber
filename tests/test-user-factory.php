<?php

use Timber\User;
use Timber\Factory\UserFactory;

class AdminUser extends User {}
class SpecialUser extends User {}

/**
 * @group factory
 * @group users-api
 */
class TestUserFactory extends Timber_UnitTestCase {
	public function tearDown() {
		global $wpdb;
		$wpdb->query("TRUNCATE TABLE {$wpdb->users}");
		parent::tearDown();
	}

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

	public function testGetUserWithArrayOfIds() {
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

		// pass a list of IDs
		list($admin, $normie) = $userFactory->from([$admin_id, $normie_id]);

		$this->assertInstanceOf(AdminUser::class, $admin);
		$this->assertInstanceOf(User::class,      $normie);

		remove_filter( 'timber/user/classmap', $my_class_map );
	}

	public function testGetUserFromWpUserObject() {
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

		$admin_wp_user  = get_user_by('id', $admin_id);
		$normie_wp_user = get_user_by('id', $normie_id);

		$userFactory = new UserFactory();
		$this->assertInstanceOf(AdminUser::class, $userFactory->from($admin_wp_user));
		$this->assertInstanceOf(User::class, $userFactory->from($normie_wp_user));

		remove_filter( 'timber/user/classmap', $my_class_map );
	}

	public function testGetUserWithAssortedArray() {
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
		$editor_id = $this->factory->user->create([
			'user_login' => 'ddd',
			'user_email' => 'ed@example.com',
			'role'       => 'editor',
		]);

		// create instances of different kinds of user objects
		$userFactory    = new UserFactory();
		$admin_user     = $userFactory->from($admin_id);
		$normie_wp_user = get_user_by('id', $normie_id);

		// pass an array containing a User, a WP_User instance, and an ID
		$users = $userFactory->from([$admin_user, $normie_wp_user, $editor_id]);

		$this->assertInstanceOf(AdminUser::class, $users[0]);
		$this->assertInstanceOf(User::class,      $users[1]);
		$this->assertInstanceOf(User::class,      $users[2]);

		remove_filter( 'timber/user/classmap', $my_class_map );
	}

	public function testGetUserWithQueryArray() {
		$my_class_map = function(string $class, WP_User $user) {
			return in_array('administrator', $user->roles)
				? AdminUser::class
				: $class;
		};
		add_filter( 'timber/user/classmap', $my_class_map, 10, 2 );

		$subscriber_id = $this->factory->user->create([
			'user_login' => 'aaa',
			'user_email' => 'sub@example.com',
			'role'       => 'subscriber',
		]);
		$admin_id = $this->factory->user->create([
			'user_login' => 'bbb',
			'user_email' => 'administrator@example.com',
			'role'       => 'administrator',
		]);
		$author_id = $this->factory->user->create([
			'user_login' => 'ccc',
			'user_email' => 'author@example.com',
			'role'       => 'author',
		]);

		// create instances of different kinds of user objects
		$userFactory    = new UserFactory();

		// pass an array containing a User and WP_User instance
		$users = $userFactory->from([
			'role__in' => ['administrator', 'author'],
		]);

		$this->assertCount(2, $users);
		$this->assertInstanceOf(AdminUser::class, $users[0]);
		$this->assertInstanceOf(User::class,      $users[1]);

		remove_filter( 'timber/user/classmap', $my_class_map );
	}

	public function testGetUserWithUserQuery() {
		$my_class_map = function(string $class, WP_User $user) {
			return in_array('administrator', $user->roles)
				? AdminUser::class
				: $class;
		};
		add_filter( 'timber/user/classmap', $my_class_map, 10, 2 );

		$subscriber_id = $this->factory->user->create([
			'user_login' => 'aaa',
			'user_email' => 'sub@example.com',
		]);
		$admin_id = $this->factory->user->create([
			'user_login' => 'bbb',
			'user_email' => 'admin@example.com',
			'role'       => 'administrator',
		]);
		$author_id = $this->factory->user->create([
			'user_login' => 'ccc',
			'user_email' => 'author@example.com',
			'role'       => 'author',
		]);

		// create instances of different kinds of user objects
		$userFactory    = new UserFactory();
		$userQuery      = new WP_User_Query([
			'role__in' => ['administrator', 'author'],
		]);

		// pass an array containing a User and WP_User instance
		$users = $userFactory->from($userQuery);

		$this->assertCount(2, $users);
		$this->assertInstanceOf(AdminUser::class, $users[0]);
		$this->assertInstanceOf(User::class,      $users[1]);

		remove_filter( 'timber/user/classmap', $my_class_map );
	}
}
