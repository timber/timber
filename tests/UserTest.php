<?php

namespace Timber\Tests;

use Mantle\Testing\Attributes\PermalinkStructure;
use PHPUnit\Framework\Attributes\Group;
use Timber\Timber;
use Timber\User;

#[Group('integrations')]
#[Group('users-api')]
class UserTest extends TimberIntegrationTestCase
{
    public function set_up()
    {
        parent::set_up();

        // Restore integration-free Class Map for users.
        $this->add_filter_temporarily('timber/user/class', fn () => User::class);
    }

    public function testIDDataType()
    {
        $uid = static::factory()->user->create([
            'display_name' => 'James Marshall',
        ]);
        $user = Timber::get_user($uid);
        $this->assertEquals('integer', \gettype($user->id));
        $this->assertEquals('integer', \gettype($user->ID));
    }

    public function testInitWithID()
    {
        $uid = static::factory()->user->create([
            'display_name' => 'Baberaham Lincoln',
        ]);
        $user = Timber::get_user($uid);
        $this->assertEquals('Baberaham Lincoln', $user->name);
        $this->assertEquals($uid, $user->id);
    }

    public function testInitWithSlug()
    {
        $uid = static::factory()->user->create([
            'display_name' => 'Tito Bottitta',
            'user_login' => 'mbottitta',
        ]);
        $user = Timber::get_user_by('login', 'mbottitta');
        $this->assertEquals('Tito Bottitta', $user->name);
        $this->assertEquals($uid, $user->id);
    }

    public function testPostWithBlankUser()
    {
        $post_id = \wp_insert_post(
            [
                'post_title' => 'Baseball',
                'post_content' => 'is fine, I guess',
                'post_status' => 'publish',
            ]
        );
        $post = Timber::get_post($post_id);
        $template = '{{ post.title }} by {{ post.author }}';
        $str = Timber::compile_string($template, [
            'post' => $post,
        ]);
        $this->assertEquals('Baseball by', \trim($str));
    }

    public function testUserCapability()
    {
        static::factory()->user->create([
            'display_name' => 'Tito Bottitta',
            'user_login' => 'mbottitta',
            'role' => 'editor',
        ]);

        static::factory()->user->create([
            'display_name' => 'Baberaham Lincoln',
            'user_login' => 'blincoln',
            'role' => 'subscriber',
        ]);

        $post_id = \wp_insert_post(
            [
                'post_title' => 'Baseball',
                'post_content' => 'is fine, I guess',
                'post_status' => 'publish',
            ]
        );

        $user = Timber::get_user_by('login', 'mbottitta');
        $subscriber = Timber::get_user_by('login', 'blincoln');

        $this->assertTrue($user->can('edit_posts'));
        $this->assertTrue($user->can('edit_post', $post_id));
        $this->assertFalse($user->can('activate_plugins'));
        $this->assertFalse($subscriber->can('edit_posts'));
        $this->assertFalse($subscriber->can('edit_post', $post_id));
    }

    public function testUserRole()
    {
        $uid = static::factory()->user->create([
            'display_name' => 'Tito Bottitta',
            'user_login' => 'mbottitta',
            'role' => 'editor',
        ]);
        $user = Timber::get_user_by('login', 'mbottitta');
        $this->assertArrayHasKey('editor', $user->roles());
    }

    public function testDescription()
    {
        $uid = static::factory()->user->create([
            'display_name' => 'Baberaham Lincoln',
            'user_login' => 'blincoln',
        ]);
        \update_user_meta($uid, 'description', 'Sixteenth President');
        $user = Timber::get_user($uid);
        $this->assertEquals('Sixteenth President', $user->meta('description'));

        $pid = static::factory()->post->create([
            'post_author' => $uid,
        ]);
        $post = Timber::get_post($pid);
        $str = Timber::compile_string("{{ post.author.description }}", [
            'post' => $post,
        ]);
        $this->assertEquals('Sixteenth President', $str);
    }

    public function testUserData()
    {
        $user_id = static::factory()->user->create([
            'display_name' => 'Baberaham Lincoln',
            'user_login' => 'blincoln',
            'user_email' => 'babe@exmample.org',
        ]);
        $user = Timber::get_user($user_id);

        \update_user_meta($user_id, 'first_name', 'Baberaham');
        \update_user_meta($user_id, 'last_name', 'Lincoln');
        \update_user_meta($user_id, 'description', 'Sixteenth President');

        $result = Timber::compile_string(
            '{{ user.first_name }}, {{ user.last_name }}, {{ user.user_nicename }}, {{ user.display_name }}, {{ user.user_email }}, {{ user.description }}',
            [
                'user' => $user,
            ]
        );

        $this->assertEquals('Baberaham, Lincoln, blincoln, Baberaham Lincoln, babe@exmample.org, Sixteenth President', $result);

        $result = Timber::compile_string(
            "{{ user.meta('first_name') }}, {{ user.meta('last_name') }}, {{ user.user_nicename }}, {{ user.display_name }}, {{ user.user_email }}, {{ user.meta('description') }}",
            [
                'user' => $user,
            ]
        );

        $this->assertEquals('Baberaham, Lincoln, blincoln, Baberaham Lincoln, babe@exmample.org, Sixteenth President', $result);
    }

    public function testInitShouldUnsetPassword()
    {
        $uid = static::factory()->user->create([
            'display_name' => 'Tom Riddle',
        ]);
        $user = Timber::get_user($uid);
        $this->assertFalse(\property_exists($user, 'user_pass'));
    }

    public function testInitWithObject()
    {
        $uid = static::factory()->user->create([
            'display_name' => 'Baberaham Lincoln',
        ]);
        $wp_user = \get_user_by('id', $uid);
        $user = Timber::get_user($wp_user);
        $this->assertEquals('Baberaham Lincoln', $user->name);
    }

    #[PermalinkStructure('/blog/%year%/%monthnum%/%postname%/')]
    public function testLinks()
    {
        $uid = static::factory()->user->create([
            'display_name' => 'Baberaham Lincoln',
            'user_login' => 'lincoln',
        ]);
        $uid = \get_user_by('id', $uid);
        $user = Timber::get_user($uid);
        $this->assertEquals('http://example.org/blog/author/lincoln/', \trailingslashit($user->link()));
        $this->assertEquals('/blog/author/lincoln/', \trailingslashit($user->path()));
        $user->president = '16th';
        $this->assertEquals('16th', $user->president);
    }

    public function testIsCurrent()
    {
        $uid = static::factory()->user->create([
            'display_name' => 'Charles vonderZwen',
            'user_login' => 'cvanderzwen',
        ]);

        $user = Timber::get_user($uid);

        \wp_set_current_user($uid);

        $this->assertTrue($user->is_current());
    }

    public function testProfileLink()
    {
        $uid = static::factory()->user->create([
            'display_name' => 'Boaty McBoatface',
            'user_login' => 'BMcBoatface',
        ]);

        \wp_set_current_user($uid);

        $user = Timber::get_user($uid);

        $this->assertEquals('http://example.org/wp-admin/profile.php', $user->profile_link());
    }

    public function testAvatar()
    {
        // Restore integration-free Class Map for users.
        // CoAuthorsPlus overrides avatar behavior, so we disable it explicitly.
        $this->add_filter_temporarily('timber/user/class', fn () => User::class);

        $uid = static::factory()->user->create([
            'display_name' => 'Maciej Palmowski',
            'user_login' => 'palmiak',
            'user_email' => 'm.palmowski@spiders.agency',
        ]);
        $user = Timber::get_user($uid);
        $this->assertStringContainsString('gravatar.com/avatar/', $user->avatar());
        $this->assertStringEndsWith('?s=96&d=mm&r=g', $user->avatar());
        $this->assertStringEndsWith('?s=120&d=mm&r=g', $user->avatar([
            'size' => 120,
        ]));
    }

    public function testEditLink()
    {
        $admin_id = static::factory()->user->create([
            'display_name' => 'Admin User',
            'user_login' => 'adminuser',
            'role' => 'administrator',
        ]);

        $subscriber_id = static::factory()->user->create([
            'display_name' => 'Subscriber Sam',
            'user_login' => 'subsam',
            'role' => 'subscriber',
        ]);

        $editor_id = static::factory()->user->create([
            'display_name' => 'Emilia Editore',
            'user_login' => 'eeditore',
            'role' => 'editor',
        ]);

        $admin = Timber::get_user($admin_id);
        $subscriber = Timber::get_user($subscriber_id);
        $editor = Timber::get_user($editor_id);

        // Test admin role.
        \wp_set_current_user($admin_id);
        $this->assertEquals(
            'http://example.org/wp-admin/user-edit.php?user_id=' . $subscriber_id,
            $subscriber->edit_link()
        );
        $this->assertEquals('http://example.org/wp-admin/user-edit.php?user_id=' . $editor_id, $editor->edit_link());
        $this->assertEquals('http://example.org/wp-admin/profile.php', $admin->edit_link());

        // Test subscriber role.
        \wp_set_current_user($subscriber_id);
        $this->assertEquals('http://example.org/wp-admin/profile.php', $subscriber->edit_link());
        $this->assertNull($editor->edit_link());
        $this->assertNull($admin->edit_link());

        \wp_set_current_user(0);
    }

    public function testWPObject()
    {
        $user_id = static::factory()->user->create();
        $user = Timber::get_user($user_id);

        $this->assertInstanceOf('\WP_User', $user->wp_object());
    }
}
