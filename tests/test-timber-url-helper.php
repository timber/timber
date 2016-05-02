<?php

	class TestTimberURLHelper extends Timber_UnitTestCase {

		private $mockUploadDir = false;

        function testURLToFileSystem() {
            $url = 'http://example.org/wp-content/uploads/2012/06/mypic.jpg';
            $file = TimberURLHelper::url_to_file_system($url);
            $this->assertStringStartsWith(ABSPATH, $file);
            $this->assertStringEndsWith('/2012/06/mypic.jpg', $file);
            $this->assertNotContains($file, 'http://example.org');
            $this->assertNotContains($file, '//');
        }

        function testPathBase() {
        	$this->assertEquals('/', TimberURLHelper::get_path_base());
        }

        function testIsLocal() {
        	$this->assertFalse(TimberURLHelper::is_local('http://wordpress.org'));
        }

        function testCurrentURL(){
            if (!isset($_SERVER['SERVER_PORT'])){
                $_SERVER['SERVER_PORT'] = 80;
            }
            if (!isset($_SERVER['SERVER_NAME'])){
                $_SERVER['SERVER_NAME'] = 'example.org';
            }
            $this->go_to('/');
            $url = TimberURLHelper::get_current_url();
            $this->assertEquals('http://example.org/', $url);
        }

        function testIsURL(){
            $url = 'http://example.org';
            $not_url = '/blog/2014/05/whatever';
            $this->assertTrue(TimberURLHelper::is_url($url));
            $this->assertFalse(TimberURLHelper::is_url($not_url));
      		$this->assertFalse(TimberURLHelper::is_url(8000));
        }

        function testIsExternal(){
            $local = 'http://example.org';
            $subdomain = 'http://cdn.example.org';
            $external = 'http://upstatement.com';
            $this->assertFalse(TimberURLHelper::is_external($local));
            $this->assertFalse(TimberURLHelper::is_external($subdomain));
            $this->assertTrue(TimberURLHelper::is_external($external));
        }

		function testIsExternalContent() {
			$internal = 'http://example.org/wp-content/uploads/my-image.png';
			$internal_in_abspath = 'http://example.org/wp/uploads/my-image.png';
			$internal_in_uploads = 'http://example.org/uploads/uploads/my-image.png';
			$external = 'http://upstatement.com/my-image.png';

			$this->assertFalse( TimberURLHelper::is_external_content( $internal ) );
			$this->assertTrue( TimberURLHelper::is_external_content( $internal_in_uploads ) );
			$this->assertTrue( TimberURLHelper::is_external_content( $internal_in_abspath ) );
			$this->assertTrue( TimberURLHelper::is_external_content( $external ) );
		}

		function testIsExternalContentMovingFolders() {
			$internal = 'http://example.org/wp-content/uploads/my-image.png';
			$internal_in_abspath = 'http://example.org/wp/uploads/my-image.png';
			$internal_in_uploads = 'http://example.org/uploads/my-image.png';
			$external = 'http://upstatement.com/my-image.png';

			add_filter( 'upload_dir', array( &$this, 'mockUploadDir' ) );
			add_filter( 'content_url', array( &$this, 'mockContentUrl' ) );

			$this->mockUploadDir = true;

			$this->assertFalse( TimberURLHelper::is_external_content( $internal ) );
			$this->assertFalse( TimberURLHelper::is_external_content( $internal_in_uploads ) );
			$this->assertFalse( TimberURLHelper::is_external_content( $internal_in_abspath ) );
			$this->assertTrue( TimberURLHelper::is_external_content( $external ) );

			$this->mockUploadDir = false;
		}

		function mockContentUrl($url) {
			return ( $this->mockUploadDir ) ? site_url( 'wp' ) : $url;
		}

		function mockUploadDir($path) {
			if ( $this->mockUploadDir ) {

				$path['url'] = str_replace( $path['baseurl'], site_url().'/uploads', $path['url'] );
				$path['baseurl'] = site_url().'/uploads';

				$path['path'] = str_replace( $path['basedir'], ABSPATH.'uploads', $path['path'] );
				$path['basedir'] = ABSPATH . 'uploads';

				$path['relative'] = '/uploads';
			}

			return $path;
		}

        function testGetRelURL(){
            $local = 'http://example.org/directory';
            $subdomain = 'http://cdn.example.org/directory';
            $external = 'http://upstatement.com';
            $rel_url = '/directory/';
            $this->assertEquals('/directory', TimberURLHelper::get_rel_url($local));
            $this->assertEquals($subdomain, TimberURLHelper::get_rel_url($subdomain));
            $this->assertEquals($external, TimberURLHelper::get_rel_url($external));
            $this->assertEquals($rel_url, TimberURLHelper::get_rel_url($rel_url));
        }

        function testRemoveTrailingSlash(){
            $url_with_trailing_slash = 'http://example.org/directory/';
            $root_url = "/";
            $this->assertEquals('http://example.org/directory', TimberURLHelper::remove_trailing_slash($url_with_trailing_slash));
            $this->assertEquals('/', TimberURLHelper::remove_trailing_slash($root_url));
        }

        function testGetParams(){
            $_SERVER['REQUEST_URI'] = 'http://example.org/blog/post/news/2014/whatever';
            $params = TimberURLHelper::get_params();
            $this->assertEquals(7, count($params));
            $whatever = TimberURLHelper::get_params(-1);
            $blog = TimberURLHelper::get_params(2);
            $this->assertEquals('whatever', $whatever);
            $this->assertEquals('blog', $blog);
        }


    }
