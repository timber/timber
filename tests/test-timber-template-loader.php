<?php

class TestTimberTemplateLoader extends WP_UnitTestCase
{
	function setUp() {
		parent::setUp();
		$this->theme_root = plugin_dir_path( __FILE__ ) . '/assets/themes';

		$this->orig_theme_dir = $GLOBALS['wp_theme_directories'];
		$GLOBALS['wp_theme_directories'] = array( WP_CONTENT_DIR . '/themes', $this->theme_root );

		add_filter('theme_root', array(&$this, '_theme_root'));
		add_filter( 'stylesheet_root', array(&$this, '_theme_root') );
		add_filter( 'template_root', array(&$this, '_theme_root') );

		// clear caches
		wp_clean_themes_cache();
		unset( $GLOBALS['wp_themes'] );

        // Setup CPT
        register_post_type( 'course', array( 'public' => true ) );

	}

	function tearDown() {
		$GLOBALS['wp_theme_directories'] = $this->orig_theme_dir;
		remove_filter('theme_root', array(&$this, '_theme_root'));
		remove_filter( 'stylesheet_root', array(&$this, '_theme_root') );
		remove_filter( 'template_root', array(&$this, '_theme_root') );

		wp_clean_themes_cache();
		unset( $GLOBALS['wp_themes'] );
		parent::tearDown();
	}

	// replace the normal theme root dir with our premade test dir
	function _theme_root($dir) {
		return $this->theme_root;
	}

    function testLoadTemplate() {

        $theme = get_theme('Timber Template Loader Theme');
		$this->assertFalse( empty($theme) );

		switch_theme($theme['Template'], $theme['Stylesheet']);

        // expected template => new post args
        $test_posts = array(

            // POSTS
            'single.twig' => array(),

            // PAGES
            'page.twig'   => array(
                'post_type' => 'page'
            ),

            'page-my-page.twig' => array(
                'post_type' => 'page',
                'post_name' => 'my-page'
            ),

            // @todo page-{$page_id}.twig?

            // CPT
            'single-course.twig'   => array(
                'post_type' => 'course'
            ),
        );

        foreach( $test_posts as $expected => $args ) {

            $pid = $this->factory->post->create( $args );

            // @see https://unit-tests.trac.wordpress.org/ticket/106
            $post_type = isset( $args['post_type'] ) ? $args['post_type'] : 'post';
            if ( in_array( $post_type, array( 'page', 'post' ) ) ) {
                $url = get_permalink( $pid );

            } else {
                $url = add_query_arg( array(
                    'p' => $pid,
                    'post_type' => $post_type
                ), '/' );

            }

            $str = $this->_get_contents_with_template_loader( $url );
            $this->assertEquals( "This is " . $expected, $str );

        }

    }

        /**
         * @todo Change this method when template loader API has been set
         */
        function _get_contents_with_template_loader( $url ) {
            $this->go_to( $url );

            ob_start();
            TimberTemplateLoader::load_template();
            return ob_get_clean();

        }
}
