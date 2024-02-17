<?php

use Timber\Integration\CoAuthorsPlus\CoAuthorsPlusUser;

/**
 * @group posts-api
 * @group integrations
 * @group coauthors-plus
 */
class TestTimberIntegrationsCoAuthors extends Timber_UnitTestCase
{
    /**
     * Overload WP_UnitTestcase to ignore deprecated notices
     * thrown by use of wp_title() in Timber
     */
    public function expectedDeprecated()
    {
        if (false !== ($key = array_search('WP_User->id', $this->caught_deprecated))) {
            unset($this->caught_deprecated[$key]);
        }
        parent::expectedDeprecated();
    }

    public function setUp(): void
    {
        if (!class_exists('CoAuthors_Plus')) {
            $this->markTestSkipped('CoAuthors Plus plugin is not loaded');
        }
        parent::setUp();
    }

    /* ----------------
     * Helper functions
     ---------------- */

    public static function create_guest_author($args)
    {
        $cap = new CoAuthors_Guest_Authors();
        $guest_id = $cap->create($args);

        return $guest_id;
    }

    public static function attach_featured_image($guest_id, $thumb)
    {
        $filename = self::copyTestImage($thumb);
        $wp_filetype = wp_check_filetype(basename($filename), null);
        $attachment = [
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
            'post_excerpt' => '',
            'post_status' => 'inherit',
        ];
        $attach_id = wp_insert_attachment($attachment, $filename, $guest_id);
        add_post_meta($guest_id, '_thumbnail_id', $attach_id, true);
        return $attach_id;
    }

    public static function copyTestImage($img = 'avt-1.jpg', $dest_name = null)
    {
        $upload_dir = wp_upload_dir();
        if (is_null($dest_name)) {
            $dest_name = $img;
        }
        $destination = $upload_dir['path'] . '/' . $dest_name;
        copy(__DIR__ . '/assets/' . $img, $destination);
        return $destination;
    }
    /* ----------------
     * Tests
     ---------------- */

    public function testAuthors()
    {
        $uid = $this->factory->user->create([
            'display_name' => 'Jen Weinman',
            'user_login' => 'aquajenus',
        ]);
        $pid = $this->factory->post->create([
            'post_author' => $uid,
        ]);
        $post = Timber::get_post($pid);
        $template_string = '{% for author in post.authors %}{{author.name}}{% endfor %}';
        $str = Timber::compile_string($template_string, [
            'post' => $post,
        ]);
        $this->assertEquals('Jen Weinman', $str);
    }

    public function testGuestAuthor()
    {
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);

        $user_login = 'bmotia';
        $display_name = 'Motia';
        $guest_id = self::create_guest_author(
            [
                'user_login' => $user_login,
                'display_name' => $display_name,
            ]
        );

        global $coauthors_plus;
        $coauthors_plus->add_coauthors($pid, [$user_login]);

        $authors = $post->authors();
        $author = $authors[0];
        $this->assertEquals($display_name, $author->display_name);
        $this->assertInstanceOf(CoAuthorsPlusUser::class, $author);
    }

    public function testGuestAuthorWithRegularAuthor()
    {
        $uid = $this->factory->user->create([
            'display_name' => 'Alexander Hamilton',
            'user_login' => 'ahamilton',
        ]);
        $pid = $this->factory->post->create([
            'post_author' => $uid,
        ]);
        $post = Timber::get_post($pid);

        $user_login = 'bmotia';
        $display_name = 'Motia';
        $guest_id = self::create_guest_author(
            [
                'user_login' => $user_login,
                'display_name' => $display_name,
            ]
        );

        global $coauthors_plus;
        $coauthors_plus->add_coauthors($pid, ['ahamilton', $user_login]);

        $authors = $post->authors();
        $author = $authors[1];
        $this->assertEquals($display_name, $author->display_name);
        $this->assertInstanceOf(CoAuthorsPlusUser::class, $author);
        $template_string = '{% for author in post.authors %}{{author.name}}, {% endfor %}';
        $str = Timber::compile_string($template_string, [
            'post' => $post,
        ]);
        $this->assertEquals('Alexander Hamilton, Motia,', trim($str));
    }

    /**
     * Co-Authors originally created as guests can be linked to a real WordPress user account. In these instances, we want to use the linked account's information
     */
    public function testLinkedGuestAuthor()
    {
        global $coauthors_plus;

        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);

        $user_login = 'truelogin';
        $display_name = 'True Name';

        $uid = $this->factory->user->create([
            'display_name' => $display_name,
            'user_login' => $user_login,
        ]);
        $user = Timber::get_user($uid);

        $guest_login = 'linkguestlogin';
        $guest_display_name = 'LGuest D Name';
        $guest_id = self::create_guest_author(
            [
                'user_login' => $guest_login,
                'display_name' => $guest_display_name,
            ]
        );
        add_post_meta($guest_id, 'cap-linked_account', $user_login, true);

        $coauthors_plus->add_coauthors($pid, [$user_login]);

        $coauthors_plus->force_guest_authors = false;
        $authors = $post->authors();

        /**
         * Here we're testing to see if we get the LINKED guest author account ("Mr. True Name")
         * instead of the temporary guest name ("LGuest D Name") that was created.
         */
        $author = $authors[0];
        $this->assertEquals("True Name", $author->name());
        $this->assertInstanceOf('Timber\User', $author);
        $this->assertInstanceOf(CoAuthorsPlusUser::class, $author);

        /**
         * Here we're testing that when we FORCE guest authors, it uses the original guest author
         * account ("LGuest D Name") when reporting the user's name.
         */
        $coauthors_plus->force_guest_authors = true;
        $authors = $post->authors();
        $author = $authors[0];
        $this->assertEquals($guest_display_name, $author->name());
        $this->assertInstanceOf('Timber\User', $author);
        $this->assertInstanceOf(CoAuthorsPlusUser::class, $author);
    }

    /**
     * @group attachments
     */
    public function testGuestAuthorAvatar()
    {
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $user_login = 'withfeaturedimage';
        $display_name = 'Have Featured';
        $email = 'admin@admin.com';
        $guest_id = self::create_guest_author(
            [
                'user_email' => $email,
                'user_login' => $user_login,
                'display_name' => $display_name,
            ]
        );
        $attach_id = self::attach_featured_image($guest_id, 'avt-1.jpg');
        $image = Timber::get_post($attach_id);

        global $coauthors_plus;
        $coauthors_plus->add_coauthors($pid, [$user_login]);

        // NOTE: this used to be `{{author.avatar.src}}` but now avatar() just returns a string
        $template_string = '{% for author in post.authors %}{{author.avatar}}{% endfor %}';
        $str1 = Timber::compile_string($template_string, [
            'post' => $post,
        ]);
        $this->assertEquals($image->src(), $str1);

        add_filter('timber/co_authors_plus/prefer_gravatar', '__return_true');
        $str2 = Timber::compile_string($template_string, [
            'post' => $post,
        ]);
        $this->assertEquals(get_avatar_url($email), $str2);
    }
}
