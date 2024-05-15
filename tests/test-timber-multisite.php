<?php

class TestTimberMultisite extends Timber_UnitTestCase
{
    public function set_up()
    {
        self::clear();
        parent::set_up();
    }

    public function testGetSubDomainSites()
    {
        if (!is_multisite()) {
            $this->markTestSkipped("You can't get sites except on Multisite");
            return;
        }
        $bids[] = self::createSubDomainSite('foo.example.org', 'My Foo');
        $bids[] = self::createSubDomainSite('quack.example.org', "Ducks R Us");
        $sites = Timber::get_sites();
        $this->assertEquals('http://foo.example.org', $sites[1]->url);
        $this->assertEquals("Ducks R Us", $sites[2]->name);
        $this->assertEquals('http://quack.example.org', $sites[2]->link());
    }

    public function testGetSubDirectorySites()
    {
        if (!is_multisite()) {
            $this->markTestSkipped("You can't get sites except on Multisite");
            return;
        }
        $bids[] = self::createSubDirectorySite('/bar/', 'My Bar');
        $bids[] = self::createSubDirectorySite('/bark/', "Barks R Us");
        $sites = Timber::get_sites();
        $this->assertEquals('http://example.org/bark', $sites[2]->url);
        $this->assertEquals('http://example.org/bar', $sites[1]->url);
        $this->assertEquals("example.org", $sites[2]->domain);
    }

    public function testPostGettingAcrossSites()
    {
        if (!is_multisite()) {
            $this->markTestSkipped("You can't get sites except on Multisite");
            return;
        }
        $site_ids[] = self::createSubDomainSite('foo.example.org', 'My Foo');
        $site_ids[] = self::createSubDomainSite('quack.example.org', "Ducks R Us");
        $site_ids[] = self::createSubDomainSite('duck.example.org', "More Ducks R Us");

        $post_titles = ["I don't like zebras", "Zebra and a half", "Have a zebra of a time"];
        //$others = $this->factory->post->create_many(8);
        foreach ($site_ids as $site_id) {
            switch_to_blog($site_id);
            $this->factory->post->create([
                'post_title' => array_pop($post_titles),
            ]);
            restore_current_blog();
        }

        $timber_posts = [];
        $wp_posts = [];
        $sites = Timber::get_sites();
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            //error_log(print_r($site, true));
            // fetch all the posts
            $timber_query = Timber::get_posts([
                'post_type' => 'post',
            ]);
            foreach ($timber_query as $post) {
                $timber_posts[] = $post;
            }

            $wp_query = get_posts([
                'post_type' => 'post',
            ]);
            foreach ($wp_query as $post) {
                $wp_posts[] = $post;
            }
            restore_current_blog();
            // display all posts
        }

        $this->assertSame(6, count($timber_posts));
        $this->assertSame(6, count($wp_posts));

        // ensure that the current site's post count is distinct from our test condition
        $current_site_all_posts = get_posts([
            'post_type' => 'post',
        ]);
        $this->assertSame(2, count($current_site_all_posts));
    }

    /**
     * @ticket #2269
     */
    public function testPostGettingAcrossSitesNoArgs()
    {
        if (!is_multisite()) {
            $this->markTestSkipped("You can't get sites except on Multisite");
            return;
        }
        $site_ids[] = self::createSubDomainSite('foo.example.org', 'My Foo');
        $site_ids[] = self::createSubDomainSite('quack.example.org', "Ducks R Us");
        $site_ids[] = self::createSubDomainSite('duck.example.org', "More Ducks R Us");

        $post_titles = ["I don't like zebras", "Zebra and a half", "Have a zebra of a time"];
        foreach ($site_ids as $site_id) {
            switch_to_blog($site_id);
            $this->factory->post->create([
                'post_title' => 'Zebras are good on site ID = ' . $site_id,
            ]);
            restore_current_blog();
        }
        $this->go_to('/');
        $timber_posts = [];
        $wp_posts = [];
        $sites = Timber::get_sites();
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            // fetch all the posts
            $timber_query = Timber::get_posts();
            foreach ($timber_query as $post) {
                $timber_posts[] = $post;
            }

            $wp_query = get_posts();
            foreach ($wp_query as $post) {
                $wp_posts[] = $post;
            }
            restore_current_blog();
            // display all posts
        }
        // testing that in multisite we get back posts in a loop
        $this->assertGreaterThan(0, count($timber_posts));
        $this->assertGreaterThan(0, count($wp_posts));

        $this->markTestIncomplete(
            "WordPress's get_posts() and Timber::get_posts() behave differently here. This could be resolved in the future with investigations on defaults with no arguments and they should be handled"
        );
    }

    public function testPostSearchAcrossSites()
    {
        if (!is_multisite()) {
            $this->markTestSkipped("You can't get sites except on Multisite");
            return;
        }
        $site_ids[] = self::createSubDomainSite('foo.example.org', 'My Foo');
        restore_current_blog();
        $site_ids[] = self::createSubDomainSite('quack.example.org', "Ducks R Us");
        restore_current_blog();
        $site_ids[] = self::createSubDomainSite('duck.example.org', "More Ducks R Us");
        restore_current_blog();

        $post_titles = ["I don't like zebras", "Zebra and a half", "Have a zebra of a time"];
        $others = $this->factory->post->create_many(8);
        foreach ($site_ids as $site_id) {
            switch_to_blog($site_id);
            $this->factory->post->create([
                'post_title' => array_pop($post_titles),
            ]);
            restore_current_blog();
        }

        $timber_posts = [];
        $wp_posts = [];
        $sites = Timber::get_sites();
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            // fetch all the posts
            $timber_query = Timber::get_posts([
                's' => 'zebra',
            ]);
            foreach ($timber_query as $post) {
                $timber_posts[] = $post;
            }

            $wp_query = get_posts([
                's' => 'zebra',
            ]);
            foreach ($wp_query as $post) {
                $wp_posts[] = $post;
            }
            restore_current_blog();
            // display all posts
        }

        $this->assertSame(3, count($timber_posts));
        $this->assertSame(3, count($wp_posts));

        // ensure that the current site's post count is distinct from our test condition
        $current_site_all_posts = get_posts();
        $this->assertSame(5, count($current_site_all_posts));
    }

    /**
     * Tests whether images accessed with switch_to_blog() get the correct url.
     *
     * @ticket https://github.com/timber/timber/issues/1312
     */
    public function test_switch_to_blog_with_timber_images()
    {
        if (!is_multisite()) {
            $this->markTestSkipped("You can't get sites except on Multisite");

            return;
        }

        // Load image and cache for Timber\Image::wp_upload_dir() in site 1.
        $image_1 = Timber::get_image(TestTimberImage::get_attachment());

        // Create site 2 and switch to it.
        self::createSubDirectorySite('/site-2/', 'Site 2');

        // Create and load image in site 2.
        $site_2_upload_dir = wp_upload_dir();
        $image_2 = Timber::get_image(TestTimberImage::get_attachment());

        $image_2_src = (string) $image_2->src();
        restore_current_blog();

        $this->assertStringStartsWith($site_2_upload_dir['baseurl'], $image_2_src);

        // test resizing
        $template = '{{ image|resize(300, 300) }}?template=true';
        $img_resized_src = Timber::compile_string($template, [
            'image' => $image_2_src,
        ]);
        $this->assertStringStartsWith($site_2_upload_dir['baseurl'], $img_resized_src);
    }

    public function testTimberSiteWPObject()
    {
        $this->skipWithoutMultisite();

        $ts = new Timber\Site();
        $this->assertInstanceOf('WP_Site', $ts->wp_object());
    }

    public static function createSubDomainSite($domain = 'test.example.org', $title = 'Multisite Test')
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $blog_id = wpmu_create_blog($domain, '/', $title, 1);
        switch_to_blog($blog_id);
        return $blog_id;
    }

    public static function createSubDirectorySite($dir = '/mysite/', $title = 'Multisite Subdir Test')
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $blog_id = wpmu_create_blog('example.org', $dir, $title, 1);
        switch_to_blog($blog_id);
        return $blog_id;
    }

    public static function clear()
    {
        if (!is_multisite()) {
            return;
        }
        global $wpdb;
        $query = "DELETE FROM $wpdb->blogs WHERE blog_id > 1";
        $wpdb->query($query);
        $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs ORDER BY blog_id ASC");
    }

    public function tear_down()
    {
        self::clear();
        parent::tear_down();
    }
}
