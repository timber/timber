<?php
	
	class TimberImageMultisiteTest extends WP_UnitTestCase {

		function createSubDomainSite() {
			$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
			$blog_id = wpmu_create_blog('test.example.org', '/', 'Multisite Test', 1);
			$this->assertGreaterThan(1, $blog_id);
			switch_to_blog($blog_id);
			return $blog_id;
		}

		function createSubDirectorySite() {
			$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
			$blog_id = wpmu_create_blog('example.org', '/mysite/', 'Multisite Test', 1);
			$this->assertGreaterThan(1, $blog_id);
			switch_to_blog($blog_id);
			return $blog_id;
		}

		function tearDown() {
			if (is_multisite()) {
				switch_to_blog(1);
			}
		}

		function testSubDomainImageLocaion() {
			if ( !is_multisite() ) {
				$this->markTestSkipped('Test is only for Multisite');
				return;
			}
			$blog_id = $this->createSubDomainSite();
			$pretend_image = 'http://example.org/wp-content/2015/08/fake-pic.jpg';
			$is_external = TimberURLHelper::is_external_content( $pretend_image );
			$this->assertFalse($is_external);
		}

		function testSubDirectoryImageLocaion() {
			if ( !is_multisite() ) {
				$this->markTestSkipped('Test is only for Multisite');
				return;
			}
			$blog_id = $this->createSubDirectorySite();
			$blog_details = get_blog_details($blog_id);
			print_r($blog_details);
			echo 'content_url='.content_url();
			$pretend_image = 'http://example.org/wp-content/2015/08/fake-pic.jpg';
			$is_external = TimberURLHelper::is_external_content( $pretend_image );

			$this->assertFalse($is_external);
		}

	}
