<?php

class TestTimberMultisite extends Timber_UnitTestCase {

	function setUp() {
		self::clear();
		parent::setUp();
	}

	function testGetSubDomainSites() {
		if ( !is_multisite() ) {
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

	function testGetSubDirectorySites() {
		if ( !is_multisite() ) {
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

	function testPostGettingAcrossSites() {
		if ( !is_multisite() ) {
			$this->markTestSkipped("You can't get sites except on Multisite");
			return;
		}
		$site_ids[] = self::createSubDomainSite('foo.example.org', 'My Foo');
		$site_ids[] = self::createSubDomainSite('quack.example.org', "Ducks R Us");
		$site_ids[] = self::createSubDomainSite('duck.example.org', "More Ducks R Us");

		$post_titles = ["I don't like zebras", "Zebra and a half", "Have a zebra of a time"];
		//$others = $this->factory->post->create_many(8);
		foreach($site_ids as $site_id) {
			switch_to_blog($site_id);
			$this->factory->post->create(array('post_title' => array_pop($post_titles)));
			
		}

		$timber_posts = array();
		$wp_posts = array();
		$sites = Timber::get_sites();
		foreach ($sites as $site) {
		    switch_to_blog($site->blog_id);
		    //error_log(print_r($site, true));
		    // fetch all the posts 
		    $timber_query = Timber::get_posts(array('post_type' => 'post'));
		    foreach ($timber_query as $post) {
		        $timber_posts[] = $post;
		    }

		    $wp_query = get_posts(array('post_type' => 'post'));
		    foreach ($wp_query as $post) {
		        $wp_posts[] = $post;
		    }
		    restore_current_blog();
		    // display all posts
		}
		
		$this->assertEquals(6, count($timber_posts));
		$this->assertEquals(6, count($wp_posts));

		// ensure tha the current site's post count is distinct from our test condition
		$current_site_all_posts = get_posts(array('post_type' => 'post')); 
		$this->assertEquals(2, count($current_site_all_posts));
	}

	/**
	 * @ticket #2269
	 */
	function testPostGettingAcrossSitesNoArgs() {
		if ( !is_multisite() ) {
			$this->markTestSkipped("You can't get sites except on Multisite");
			return;
		}
		$site_ids[] = self::createSubDomainSite('foo.example.org', 'My Foo');
		$site_ids[] = self::createSubDomainSite('quack.example.org', "Ducks R Us");
		$site_ids[] = self::createSubDomainSite('duck.example.org', "More Ducks R Us");

		$post_titles = ["I don't like zebras", "Zebra and a half", "Have a zebra of a time"];
		foreach($site_ids as $site_id) {
			switch_to_blog($site_id);
			$this->factory->post->create(['post_title' => 'Zebras are good on site ID = '.$site_id]);
			
		}
		$this->go_to('/');
		$timber_posts = array();
		$wp_posts = array();
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


	function testPostSearchAcrossSites() {
		if ( !is_multisite() ) {
			$this->markTestSkipped("You can't get sites except on Multisite");
			return;
		}
		$site_ids[] = self::createSubDomainSite('foo.example.org', 'My Foo');
		$site_ids[] = self::createSubDomainSite('quack.example.org', "Ducks R Us");
		$site_ids[] = self::createSubDomainSite('duck.example.org', "More Ducks R Us");

		$post_titles = ["I don't like zebras", "Zebra and a half", "Have a zebra of a time"];
		$others = $this->factory->post->create_many(8);
		foreach($site_ids as $site_id) {
			switch_to_blog($site_id);
			$this->factory->post->create(array('post_title' => array_pop($post_titles)));
			
		}

		$timber_posts = array();
		$wp_posts = array();
		$sites = Timber::get_sites();
		foreach ($sites as $site) {
		    switch_to_blog($site->blog_id);
		    // fetch all the posts 
		    $timber_query = Timber::get_posts(['s' => 'zebra']);
		    foreach ($timber_query as $post) {
		        $timber_posts[] = $post;
		    }

		    $wp_query = get_posts(['s' => 'zebra']);
		    foreach ($wp_query as $post) {
		        $wp_posts[] = $post;
		    }
		    restore_current_blog();
		    // display all posts
		}
		
		$this->assertEquals(3, count($timber_posts));
		$this->assertEquals(3, count($wp_posts));

		// ensure tha the current site's post count is distinct from our test condition
		$current_site_all_posts = get_posts(); 
		$this->assertEquals(5, count($current_site_all_posts));
	}

	public static function createSubDomainSite($domain = 'test.example.org', $title = 'Multisite Test' ) {
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$blog_id = wpmu_create_blog($domain, '/', $title, 1);
		switch_to_blog($blog_id);
		return $blog_id;
	}

	public static function createSubDirectorySite($dir = '/mysite/', $title = 'Multisite Subdir Test' ) {
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$blog_id = wpmu_create_blog('example.org', $dir, $title, 1);
		switch_to_blog($blog_id);
		return $blog_id;
	}

	public static function clear() {
		if ( !is_multisite() ) {
			return;
		}
		global $wpdb;
		$query = "DELETE FROM $wpdb->blogs WHERE blog_id > 1";
		$wpdb->query($query);
		$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs ORDER BY blog_id ASC");
	}

	function tearDown() {
		self::clear();
		parent::tearDown();
	}

}
