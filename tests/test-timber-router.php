s<?php

class TimberTestRouter extends WP_UnitTestCase
{
    public function testThemeRoute()
    {
        $template = Timber::load_template('single.php');
        $this->assertTrue($template);
    }

    public function testThemeRouteDoesntExist()
    {
        $template = Timber::load_template('singlefoo.php');
        $this->assertFalse($template);
    }

    public function testFullPathRoute()
    {
        $hello = WP_CONTENT_DIR.'/plugins/hello.php';
        $template = Timber::load_template($hello);
        $this->assertTrue($template);
    }

    public function testFullPathRouteDoesntExist()
    {
        $hello = WP_CONTENT_DIR.'/plugins/hello-foo.php';
        $template = Timber::load_template($hello);
        $this->assertFalse($template);
    }

    public function testRouterClass()
    {
        $this->assertTrue(class_exists('AltoRouter'));
    }

    public function testAppliedRoute()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        global $matches;
        $matches = array();
        $phpunit = $this;
        Timber::add_route('foo', function () use ($phpunit) {
            global $matches;
            $phpunit->assertTrue(true);
            $matches[] = true;
        });
        $this->go_to(home_url('foo'));
        $this->matchRoutes();
        $this->assertEquals(1, count($matches));
    }

    public function testRouteAgainstPostName()
    {
        $post_name = 'jared';
        $post = $this->factory->post->create(array('post_title' => 'Jared', 'post_name' => $post_name));
        global $matches;
        $matches = array();
        $phpunit = $this;
        Timber::add_route('randomthing/'.$post_name, function () use ($phpunit) {
            global $matches;
            $phpunit->assertTrue(true);
            $matches[] = true;
        });
        $this->go_to(home_url('/randomthing/'.$post_name));
        $this->matchRoutes();
        $this->assertEquals(1, count($matches));
    }

    public function testFailedRoute()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        global $matches;
        $matches = array();
        $phpunit = $this;
        Timber::add_route('foo', function () use ($phpunit) {
            $phpunit->assertTrue(false);
            $matches[] = true;
        });
        $this->go_to(home_url('bar'));
        $this->matchRoutes();
        $this->assertEquals(0, count($matches));
    }

    public function testRouteWithVariable()
    {
        $post_name = 'ziggy';
        $post = $this->factory->post->create(array('post_title' => 'Ziggy', 'post_name' => $post_name));
        global $matches;
        $matches = array();
        $phpunit = $this;
        Timber::add_route('mything/:slug', function ($params) use ($phpunit) {
            global $matches;
            $matches = array();
            if ('ziggy' == $params['slug']) {
                $matches[] = true;
            }
        });
        $this->go_to(home_url('/mything/'.$post_name));
        $this->matchRoutes();
        $this->assertEquals(1, count($matches));
    }

    public function testRouteWithAltoVariable()
    {
        $post_name = 'ziggy';
        $post = $this->factory->post->create(array('post_title' => 'Ziggy', 'post_name' => $post_name));
        global $matches;
        $matches = array();
        $phpunit = $this;
        Timber::add_route('mything/[*:slug]', function ($params) use ($phpunit) {
            global $matches;
            $matches = array();
            if ('ziggy' == $params['slug']) {
                $matches[] = true;
            }
        });
        $this->go_to(home_url('/mything/'.$post_name));
        $this->matchRoutes();
        $this->assertEquals(1, count($matches));
    }

    public function testRouteWithMultiArguments()
    {
        $phpunit = $this;
        Timber::add_route('artist/[:artist]/song/[:song]', function ($params) use ($phpunit) {
            global $matches;
            $matches = array();
            if ($params['artist'] == 'smashing-pumpkins') {
                $matches[] = true;
            }
            if ($params['song'] == 'mayonaise') {
                $matches[] = true;
            }
        });
        $this->go_to(home_url('/artist/smashing-pumpkins/song/mayonaise'));
        $this->matchRoutes();
        global $matches;
        $this->assertEquals(2, count($matches));
    }

    public function testRouteWithMultiArgumentsOldStyle()
    {
        $phpunit = $this;
        global $matches;
        Timber::add_route('studio/:studio/movie/:movie', function ($params) use ($phpunit) {
            global $matches;
            $matches = array();
            if ($params['studio'] == 'universal') {
                $matches[] = true;
            }
            if ($params['movie'] == 'brazil') {
                $matches[] = true;
            }
        });
        $this->go_to(home_url('/studio/universal/movie/brazil/'));
        $this->matchRoutes();
        $this->assertEquals(2, count($matches));
    }

    public function testVerySimpleRoute()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        global $matches;
        $matches = array();
        $phpunit = $this;
        Timber::add_route('crackers', function () use ($phpunit) {
            global $matches;
            $matches = array();
            $matches[] = true;
        });
        $this->go_to(home_url('crackers'));
        $this->matchRoutes();
        $this->assertEquals(1, count($matches));
    }

    public function testVerySimpleRouteTrailingSlash()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        global $matches;
        $matches = array();
        $phpunit = $this;
        Timber::add_route('bip/', function () use ($phpunit) {
            global $matches;
            $matches = array();
            $matches[] = true;
        });
        $this->go_to(home_url('bip'));
        $this->matchRoutes();
        $this->assertEquals(1, count($matches));
    }

    public function testVerySimpleRouteTrailingSlashInRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        global $matches;
        $matches = array();
        $phpunit = $this;
        Timber::add_route('bopp', function () use ($phpunit) {
            global $matches;
            $matches = array();
            $matches[] = true;
        });
        $this->go_to(home_url('bopp/'));
        $this->matchRoutes();
        $this->assertEquals(1, count($matches));
    }


    public function testVerySimpleRouteTrailingSlashInRequestAndMapping()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        global $matches;
        $matches = array();
        $phpunit = $this;
        Timber::add_route('zappers', function () use ($phpunit) {
            global $matches;
            $matches = array();
            $matches[] = true;
        });
        $this->go_to(home_url('zappers/'));
        $this->matchRoutes();
        $this->assertEquals(1, count($matches));
    }

    public function testVerySimpleRoutePreceedingSlash()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        global $matches;
        $matches = array();
        $phpunit = $this;
        Timber::add_route('/gobbles', function () use ($phpunit) {
            global $matches;
            $matches = array();
            $matches[] = true;
        });
        $this->go_to(home_url('gobbles'));
        $this->matchRoutes();
        $this->assertEquals(1, count($matches));
    }



    public function matchRoutes()
    {
        Routes::match_current_request();
    }
}
