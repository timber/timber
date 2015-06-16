<?php

	class TestTimberURLHelper extends WP_UnitTestCase {

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

        function testDownloadURL(){
            if ( !TimberImageTest::is_connected() ){
                $this->markTestSkipped('Cannot test external images when not connected to internet');
                return;
            }
            $url = 'http://i1.nyt.com/images/misc/nytlogo379x64.gif';
            $result = TimberURLHelper::download_url($url);
            $this->assertContains('/nytlogo379x64', $result);
            $this->assertStringEndsWith('.tmp', $result);
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
