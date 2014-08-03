<?php

class TestTimberTemplateLoader extends WP_UnitTestCase
{
    var $custom_render_pid;

	function setUp() {
		parent::setUp();
		$this->theme_root = plugin_dir_path( __FILE__ ) . '/assets/themes';

		$this->orig_theme_dir = $GLOBALS['wp_theme_directories'];
		$GLOBALS['wp_theme_directories'] = array( WP_CONTENT_DIR . '/themes', $this->theme_root );

		add_filter( 'theme_root', array(&$this, '_theme_root') );
		add_filter( 'stylesheet_root', array(&$this, '_theme_root') );
		add_filter( 'template_root', array(&$this, '_theme_root') );

		// clear caches
		wp_clean_themes_cache();
		unset( $GLOBALS['wp_themes'] );

        // Setup CPT
        register_post_type( 'course', array( 'public' => true ) );

        // Setup custom render page
        $this->custom_render_pid = $this->factory->post->create( array(
            'post_type' => 'page',
            'post_name' => 'custom-render'
        ) );

        $theme = get_theme('Timber Template Loader Theme');
		switch_theme($theme['Template'], $theme['Stylesheet']);

	}

	function tearDown() {
		$GLOBALS['wp_theme_directories'] = $this->orig_theme_dir;
		remove_filter( 'theme_root', array(&$this, '_theme_root'));
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

    function go_to_page( $pid ) {
        $url = add_query_arg( array(
            'page_id' => $pid,
        ), '/' );

        return $this->go_to( $url );
    }

    /**
     *  @expectedDeprecated get_theme
     *  @expectedDeprecated get_themes
     */
    function testTestTheme() {
        $theme = get_theme('Timber Template Loader Theme');
        $this->assertFalse( empty($theme) );
        $this->assertEquals( $theme['Stylesheet'], get_stylesheet() );
    }

    /**
     *  @expectedDeprecated get_theme
     *  @expectedDeprecated get_themes
     */
    function testRenderCustom() {
        ob_start();
        Timber::render( 'page-custom-render.twig', array(
            'render_me' => 'Custom content'
        ));
        $content = ob_get_clean();
        $this->assertEquals( 'Render me: Custom content', $content );
    }

    /**
     *  @expectedDeprecated get_theme
     *  @expectedDeprecated get_themes
     */
    function testCompileCustom() {
        $content = Timber::compile( 'page-custom-render.twig', array(
            'render_me' => 'Custom content'
        ));
        $this->assertEquals( 'Render me: Custom content', $content );
    }

    /**
     *  @expectedDeprecated get_theme
     *  @expectedDeprecated get_themes
     */
    function testRenderAutoload() {
        $this->go_to_page( $this->custom_render_pid );

        ob_start();
        Timber::render( TimberLoader::AUTOLOAD_TEMPLATE, array(
            'render_me' => 'Custom content'
        ));

        $content = ob_get_clean();

        $this->assertEquals( 'Render me: Custom content', $content );
    }

    /**
     *  @expectedDeprecated get_theme
     *  @expectedDeprecated get_themes
     */
    function testCompileAutoload() {
        $this->go_to_page( $this->custom_render_pid );

        $content = Timber::compile( TimberLoader::AUTOLOAD_TEMPLATE, array(
            'render_me' => 'Custom content'
        ));

        $this->assertEquals( 'Render me: Custom content', $content );
    }

    /**
     *  @expectedDeprecated get_theme
     *  @expectedDeprecated get_themes
     */
    function testCompileArgs(){
        $this->go_to( home_url('/?p=1') );
        $whatever = Timber::compile(array('stuff' => 'stuffyes'));
    }

    /**
     *  @expectedDeprecated get_theme
     *  @expectedDeprecated get_themes
     */
    function testRenderOverload() {
        $this->go_to_page( $this->custom_render_pid );

        ob_start();
        Timber::render( array(
            'render_me' => 'Custom content'
        ));

        $content = ob_get_clean();

        $this->assertEquals( 'Render me: Custom content', $content );
    }

    /**
     *  @expectedDeprecated get_theme
     *  @expectedDeprecated get_themes
     */
    function testCompileOverload() {
        $this->go_to_page( $this->custom_render_pid );

        $content = Timber::compile( array(
            'render_me' => 'Custom content'
        ));

        $this->assertEquals( 'Render me: Custom content', $content );
    }

    /**
     *  @expectedDeprecated get_theme
     *  @expectedDeprecated get_themes
     */
    function testLoadTemplate() {

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
            if ( in_array( $post_type, array( 'page' ) ) ) {
                $url = add_query_arg( array(
                    'page_id' => $pid,
                ), '/' );

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

        function _get_contents_with_template_loader( $url ) {
            $this->go_to( $url );

            $context = Timber::get_context( true );
            return Timber::compile( $context );

        }

}
