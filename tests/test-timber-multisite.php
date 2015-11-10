<?php

class TestTimberMultisite extends Timber_UnitTestCase {

	function testGetSubDomainSites() {
		if ( !is_multisite()) {
			$this->markTestSkipped("You can't get sites except on Multisite");
			return;
		}
		$bids[] = self::createSubDomainSite('foo.example.org', 'My Foo');
		$bids[] = self::createSubDomainSite('quack.example.org', "Ducks R Us");
		$sites = Timber::get_sites();
		$this->assertEquals('http://foo.example.org', $sites[1]->url);
		$this->assertEquals("Ducks R Us", $sites[2]->name);
	}

	function testGetSubDirectorySites() {
		if ( !is_multisite()) {
			$this->markTestSkipped("You can't get sites except on Multisite");
			return;
		}
		$bids[] = self::createSubDirectorySite('/bar/', 'My Bar');
		$bids[] = self::createSubDirectorySite('/bark/', "Barks R Us");
		$sites = Timber::get_sites();
		$this->assertEquals('http://example.org/bar', $sites[1]->url);
		$this->assertEquals("example.org", $sites[2]->domain);
		$this->assertEquals('http://example.org/bark', $sites[2]->url);
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

	function tearDown() {
		if (is_multisite()) {
			switch_to_blog(1);
			$sites = Timber::get_sites();
			foreach($sites as $site) {
				if ($site->ID > 0) {
					wpmu_delete_blog($site->ID, true);
				}
			}
		}
	}

}
