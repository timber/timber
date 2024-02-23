<?php

/**
 * @group integrations
 */
class TestTimberURLHelper extends Timber_UnitTestCase
{
    private $mockUploadDir = false;

    public function set_up()
    {
        $_SERVER['SERVER_PORT'] = 80;
    }

    public function testHTTPSCurrentURL()
    {
        $this->go_to('/');
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = 443;
        $url = Timber\URLHelper::get_current_url();
        $this->assertEquals('https://example.org/', trailingslashit($url));
        $_SERVER['HTTPS'] = 'off';
        unset($_SERVER['HTTPS']);
    }

    public function testSwapProtocolHTTPtoHTTPS()
    {
        $url = 'http://nytimes.com/news/reports/2017';
        $url = Timber\URLHelper::swap_protocol($url);
        $this->assertStringStartsWith('https://', $url);
    }

    public function testSwapProtocolHTTPStoHTTP()
    {
        $url = 'https://nytimes.com/news/reports/2017';
        $url = Timber\URLHelper::swap_protocol($url);
        $this->assertStringStartsWith('http://', $url);
    }

    public function testStartsWith()
    {
        $haystack = 'http://nytimes.com/news/reports/2017';
        $starts_with = 'http://nytimes.com/news';
        $nope = 'http://bostonglobe.com';
        $this->assertTrue(Timber\URLHelper::starts_with($haystack, $starts_with));
        $this->assertFalse(Timber\URLHelper::starts_with($haystack, $nope));
    }

    public function testStartsWithHTTPs()
    {
        $haystack = 'http://nytimes.com/news/reports/2017';
        $starts_with = 'https://nytimes.com/news';
        $nope = 'http://bostonglobe.com';
        $this->assertTrue(Timber\URLHelper::starts_with($haystack, $starts_with));
        $this->assertFalse(Timber\URLHelper::starts_with($haystack, $nope));
    }

    public function testStartsWithHTTPsFlip()
    {
        $haystack = 'https://nytimes.com/news/reports/2017';
        $starts_with = 'http://nytimes.com/news';
        $nope = 'http://bostonglobe.com';
        $this->assertTrue(Timber\URLHelper::starts_with($haystack, $starts_with));
        $this->assertFalse(Timber\URLHelper::starts_with($haystack, $nope));
    }

    public function testFileSystemToURL()
    {
        $image = TestTimberImage::copyTestAttachment();
        $url = Timber\URLHelper::file_system_to_url($image);
        $this->assertStringEndsWith('://example.org/wp-content/uploads/' . date('Y/m') . '/arch.jpg', $url);
    }

    public function testContentSubDirectory()
    {
        $subdir = Timber\URLHelper::get_content_subdir();
        $this->assertEquals('/wp-content', $subdir);
    }

    public function testURLToFileSystem()
    {
        $url = 'http://example.org/wp-content/uploads/2012/06/mypic.jpg';
        $file = Timber\URLHelper::url_to_file_system($url);
        $this->assertStringStartsWith(ABSPATH, $file);
        $this->assertStringEndsWith('/2012/06/mypic.jpg', $file);
        $this->assertStringNotContainsString($file, 'http://example.org');
        $this->assertStringNotContainsString($file, '//');
    }

    public function testGetHost()
    {
        $http_host = $_SERVER['HTTP_HOST'];
        $server_name = $_SERVER['SERVER_NAME'];
        $_SERVER['HTTP_HOST'] = '';
        $_SERVER['SERVER_NAME'] = 'example.org';
        $host = Timber\URLHelper::get_host();
        $this->assertEquals('example.org', $host);
        $_SERVER['HTTP_HOST'] = $http_host;
        $_SERVER['SERVER_NAME'] = $server_name;
    }

    public function testGetHostEmpty()
    {
        $http_host = $_SERVER['HTTP_HOST'];
        $server_name = $_SERVER['SERVER_NAME'];
        $_SERVER['HTTP_HOST'] = '';
        $_SERVER['SERVER_NAME'] = '';
        $host = Timber\URLHelper::get_host();
        $this->assertSame('', $host);
        $_SERVER['HTTP_HOST'] = $http_host;
        $_SERVER['SERVER_NAME'] = $server_name;
    }

    public function testPrepend()
    {
        $joined = Timber\URLHelper::prepend_to_url('example.com', '/thing/foo');
        $this->assertEquals('example.com/thing/foo', $joined);
    }

    public function testPrependWithPort()
    {
        $joined = Timber\URLHelper::prepend_to_url('http://example.com:8080/thing/', '/jiggly');
        $this->assertEquals('http://example.com:8080/jiggly/thing/', $joined);
    }

    public function testPrependWithFragment()
    {
        $joined = Timber\URLHelper::prepend_to_url('http://example.com/thing/#foo', '/jiggly');
        $this->assertEquals('http://example.com/jiggly/thing/#foo', $joined);
    }

    public function testPrependWithQuery()
    {
        $joined = Timber\URLHelper::prepend_to_url('http://example.com/?s=foo&jolly=good', '/search');
        $this->assertEquals('http://example.com/search/?s=foo&jolly=good', $joined);
    }

    public function testUserTrailingSlashIt()
    {
        global $wp_rewrite;
        $wp_rewrite->use_trailing_slashes = true;
        $link = '2016/04/my-silly-story';
        $url = Timber\URLHelper::user_trailingslashit($link);
        $this->assertEquals($link . '/', $url);
        $wp_rewrite->use_trailing_slashes = false;
    }

    public function testDoubleSlashesWithHTTP()
    {
        $url = 'http://nytimes.com/news//world/thing.html';
        $expected_url = 'http://nytimes.com/news/world/thing.html';
        $url = Timber\URLHelper::remove_double_slashes($url);
        $this->assertEquals($expected_url, $url);
    }

    public function testDoubleSlashesWithHTTPS()
    {
        $url = 'https://nytimes.com/news//world/thing.html';
        $expected_url = 'https://nytimes.com/news/world/thing.html';
        $url = Timber\URLHelper::remove_double_slashes($url);
        $this->assertEquals($expected_url, $url);
    }

    public function testDoubleSlashesWithS3()
    {
        $url = 's3://bucket/folder//thing.html';
        $expected_url = 's3://bucket/folder/thing.html';
        $url = Timber\URLHelper::remove_double_slashes($url);
        $this->assertEquals($expected_url, $url);
    }

    public function testDoubleSlashesWithGS()
    {
        $url = 'gs://bucket/folder//thing.html';
        $expected_url = 'gs://bucket/folder/thing.html';
        $url = Timber\URLHelper::remove_double_slashes($url);
        $this->assertEquals($expected_url, $url);
    }

    public function testUserTrailingSlashItFailure()
    {
        $link = 'http:///example.com';
        $url = Timber\URLHelper::user_trailingslashit($link);
        $this->assertEquals($link, $url);
    }

    public function testUnPreSlashIt()
    {
        $str = '/wp-content/themes/undefeated/style.css';
        $str = Timber\URLHelper::unpreslashit($str);
        $this->assertEquals('wp-content/themes/undefeated/style.css', $str);
    }

    public function testPreSlashIt()
    {
        $before = 'thing/foo';
        $after = Timber\URLHelper::preslashit($before);
        $this->assertEquals('/' . $before, $after);
    }

    public function testPreSlashItNadda()
    {
        $before = '/thing/foo';
        $after = Timber\URLHelper::preslashit($before);
        $this->assertEquals($before, $after);
    }

    public function testPathBase()
    {
        $struc = '/%year%/%monthnum%/%postname%/';
        $this->setPermalinkStructure($struc);
        $this->assertEquals('/', Timber\URLHelper::get_path_base());
    }

    public function testIsLocal()
    {
        // Local.
        $this->assertTrue(Timber\URLHelper::is_local($_SERVER['HTTP_HOST']));
        $this->assertTrue(Timber\URLHelper::is_local('example.org'));
        $this->assertTrue(Timber\URLHelper::is_local('//example.org'));
        $this->assertTrue(Timber\URLHelper::is_local('http://example.org'));
        $this->assertTrue(Timber\URLHelper::is_local('https://example.org'));
        $this->assertTrue(Timber\URLHelper::is_local('https://example.org/'));
        $this->assertTrue(Timber\URLHelper::is_local('https://example.org/example'));

        // External.
        $this->assertFalse(Timber\URLHelper::is_local('wordpress.org'));
        $this->assertFalse(Timber\URLHelper::is_local('//wordpress.org'));
        $this->assertFalse(Timber\URLHelper::is_local('//wordpress.org/example.org'));
        $this->assertFalse(Timber\URLHelper::is_local('http://wordpress.org'));
        $this->assertFalse(Timber\URLHelper::is_local('https://wordpress.org'));
        $this->assertFalse(Timber\URLHelper::is_local('https://example.com/example.org'));
        $this->assertFalse(Timber\URLHelper::is_local('http://example.com/' . $_SERVER['HTTP_HOST']));
        $this->assertFalse(Timber\URLHelper::is_local('http://foo' . $_SERVER['HTTP_HOST'] . '/' . $_SERVER['HTTP_HOST']));
    }

    public function testCurrentURLWithServerPort()
    {
        $old_port = $_SERVER['SERVER_PORT'];
        $_SERVER['SERVER_PORT'] = 3000;
        if (!isset($_SERVER['SERVER_NAME'])) {
            $_SERVER['SERVER_NAME'] = 'example.org';
        }
        $this->go_to('/');
        $url = Timber\URLHelper::get_current_url();
        $this->assertEquals('http://example.org:3000/', $url);
        $_SERVER['SERVER_PORT'] = $old_port;
    }

    public function testCurrentURL()
    {
        $_SERVER['SERVER_PORT'] = 80;
        $_SERVER['SERVER_NAME'] = 'example.org';
        $this->go_to('/');
        $url = Timber\URLHelper::get_current_url();
        $this->assertEquals('http://example.org/', $url);
    }

    public function testCurrentURLIsSecure()
    {
        if (!isset($_SERVER['SERVER_PORT'])) {
            $_SERVER['SERVER_PORT'] = 443;
        }
        if (!isset($_SERVER['SERVER_NAME'])) {
            $_SERVER['SERVER_NAME'] = 'example.org';
        }
        $_SERVER['HTTPS'] = 'on';
        $this->go_to('/');
        $url = Timber\URLHelper::get_current_url();
        $this->assertEquals('https://example.org/', $url);
    }

    public function testUrlSchemeIsSecure()
    {
        $_SERVER['HTTPS'] = 'on';
        $scheme = Timber\URLHelper::get_scheme();
        $this->assertEquals('https', $scheme);
    }

    public function testUrlSchemeIsNotSecure()
    {
        $_SERVER['HTTPS'] = 'off';
        $scheme = Timber\URLHelper::get_scheme();
        $this->assertEquals('http', $scheme);
    }

    public function testIsURL()
    {
        $url = 'http://example.org';
        $not_url = '/blog/2014/05/whatever';
        $this->assertTrue(Timber\URLHelper::is_url($url));
        $this->assertFalse(Timber\URLHelper::is_url($not_url));
        $this->assertFalse(Timber\URLHelper::is_url(8000));
    }

    public function testIsExternal()
    {
        // Local.
        $this->assertFalse(Timber\URLHelper::is_external('example.org'));
        $this->assertFalse(Timber\URLHelper::is_external('//example.org'));
        $this->assertFalse(Timber\URLHelper::is_external('http://example.org'));
        $this->assertFalse(Timber\URLHelper::is_external('https://example.org/'));
        $this->assertFalse(Timber\URLHelper::is_external('https://example.org/example.com'));
        $this->assertFalse(Timber\URLHelper::is_external('https://example.org/example'));

        // Subdomain.
        $this->assertTrue(Timber\URLHelper::is_external('//cdn.example.org'));
        $this->assertTrue(Timber\URLHelper::is_external('http://cdn.example.org'));
        $this->assertTrue(Timber\URLHelper::is_external('https://cdn.example.org'));
        $this->assertTrue(Timber\URLHelper::is_external('cdn.example.org'));

        // External.
        $this->assertTrue(Timber\URLHelper::is_external('upstatement.com'));
        $this->assertTrue(Timber\URLHelper::is_external('//upstatement.com'));
        $this->assertTrue(Timber\URLHelper::is_external('http://upstatement.com'));
        $this->assertTrue(Timber\URLHelper::is_external('https://upstatement.com'));

        // Other.
        $this->assertTrue(Timber\URLHelper::is_external('https://example.com/' . $_SERVER['HTTP_HOST']));
        $this->assertTrue(Timber\URLHelper::is_external('https://foo' . $_SERVER['HTTP_HOST'] . '/' . $_SERVER['HTTP_HOST']));
    }

    public function testIsExternalContent()
    {
        $internal = 'http://example.org/wp-content/uploads/my-image.png';
        $internal_in_abspath = 'http://example.org/wp/uploads/my-image.png';
        $internal_in_uploads = 'http://example.org/uploads/uploads/my-image.png';
        $external = 'http://upstatement.com/my-image.png';

        $this->assertFalse(Timber\URLHelper::is_external_content($internal));
        $this->assertTrue(Timber\URLHelper::is_external_content($internal_in_uploads));
        $this->assertTrue(Timber\URLHelper::is_external_content($internal_in_abspath));
        $this->assertTrue(Timber\URLHelper::is_external_content($external));
    }

    public function testIsExternalContentMovingFolders()
    {
        $internal = 'http://example.org/wp-content/uploads/my-image.png';
        $internal_in_abspath = 'http://example.org/wp/uploads/my-image.png';
        $internal_in_uploads = 'http://example.org/uploads/my-image.png';
        $external = 'http://upstatement.com/my-image.png';

        add_filter('upload_dir', [&$this, 'mockUploadDir']);
        add_filter('content_url', [&$this, 'mockContentUrl']);

        $this->mockUploadDir = true;

        $this->assertFalse(Timber\URLHelper::is_external_content($internal));
        $this->assertFalse(Timber\URLHelper::is_external_content($internal_in_uploads));
        $this->assertFalse(Timber\URLHelper::is_external_content($internal_in_abspath));
        $this->assertTrue(Timber\URLHelper::is_external_content($external));

        $this->mockUploadDir = false;
    }

    public function mockContentUrl($url)
    {
        return ($this->mockUploadDir) ? site_url('wp') : $url;
    }

    public function mockUploadDir($path)
    {
        if ($this->mockUploadDir) {
            $path['url'] = str_replace($path['baseurl'], site_url() . '/uploads', $path['url']);
            $path['baseurl'] = site_url() . '/uploads';

            $path['path'] = str_replace($path['basedir'], ABSPATH . 'uploads', $path['path']);
            $path['basedir'] = ABSPATH . 'uploads';

            $path['relative'] = '/uploads';
        }

        return $path;
    }

    public function testGetRelURL()
    {
        $local = 'http://example.org/directory';
        $subdomain = 'http://cdn.example.org/directory';
        $external = 'http://upstatement.com';
        $rel_url = '/directory/';
        $this->assertEquals('/directory', Timber\URLHelper::get_rel_url($local));
        $this->assertEquals($subdomain, Timber\URLHelper::get_rel_url($subdomain));
        $this->assertEquals($external, Timber\URLHelper::get_rel_url($external));
        $this->assertEquals($rel_url, Timber\URLHelper::get_rel_url($rel_url));
    }

    public function testRemoveTrailingSlash()
    {
        $url_with_trailing_slash = 'http://example.org/directory/';
        $root_url = "/";
        $this->assertEquals('http://example.org/directory', Timber\URLHelper::remove_trailing_slash($url_with_trailing_slash));
        $this->assertEquals('/', Timber\URLHelper::remove_trailing_slash($root_url));
    }

    public function testGetParams()
    {
        $_SERVER['REQUEST_URI'] = 'http://example.org/blog/post/news/2014/whatever';
        $params = Timber\URLHelper::get_params();
        $this->assertSame(7, count($params));
        $whatever = Timber\URLHelper::get_params(-1);
        $blog = Timber\URLHelper::get_params(2);
        $this->assertEquals('whatever', $whatever);
        $this->assertEquals('blog', $blog);
    }

    public function testGetParamsNada()
    {
        $_SERVER['REQUEST_URI'] = 'http://example.org/blog/post/news/2014/whatever';
        $params = Timber\URLHelper::get_params(93);
        $this->assertFalse($params);
    }
}
