<?php

class TestTimberMultisite extends Timber_UnitTestCase {

	function setUp() {
		self::clear();
		parent::setUp();
	}

	function testGetSubDomainSites() {
		self::clear();
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
		self::clear();
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
