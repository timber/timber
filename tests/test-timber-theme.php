<?php

	class TestTimberTheme extends Timber_UnitTestCase {

		protected $backup_wp_theme_directories;

		function testThemeVersion() {
			switch_theme('twentyseventeen');
			$theme = new TimberTheme();
			$this->assertGreaterThan(1.2, $theme->version);
			switch_theme('default');
		}

		function testThemeMods(){
			set_theme_mod('foo', 'bar');
			$theme = new TimberTheme();
			$mods = $theme->theme_mods();
			$this->assertEquals('bar', $mods['foo']);
			$bar = $theme->theme_mod('foo');
			$this->assertEquals('bar', $bar);
		}

		function testPath() {
			$context = Timber::context();
			$theme = $context['site']->theme;
			$output = Timber::compile_string('{{site.theme.path}}', $context);
			$this->assertEquals('/wp-content/themes/'.$theme->slug, $output);
		}

		function testPathWithPort() {
			/* setUp */
			update_option( 'siteurl', 'http://example.org:3000', true );
			update_option( 'home', 'http://example.org:3000', true );
			self::setPermalinkStructure();
            $old_port = $_SERVER['SERVER_PORT'];
            $_SERVER['SERVER_PORT'] = 3000;
            if (!isset($_SERVER['SERVER_NAME'])){
                $_SERVER['SERVER_NAME'] = 'example.org';
            }

            /* test */
            $theme = new Timber\Theme();
			$this->assertEquals('/wp-content/themes/default', $theme->path());

			/* tearDown */
            $_SERVER['SERVER_PORT'] = $old_port;
            update_option( 'siteurl', 'http://example.org', true );
            update_option( 'home', 'http://example.org', true );
		}

		function testPathOnSubdirectoryInstall() {
			update_option( 'siteurl', 'http://example.org/wordpress', true );
			$context = Timber::context();
			$theme = $context['site']->theme;
			$output = Timber::compile_string('{{site.theme.path}}', $context);
			$this->assertEquals('/wp-content/themes/'.$theme->slug, $output);
		}

		function testLink() {
			$context = Timber::context();
			$theme = $context['site']->theme;
			$output = Timber::compile_string('{{site.theme.link}}', $context);
			$this->assertEquals('http://example.org/wp-content/themes/'.$theme->slug, $output);
		}

		function testLinkOnSubdirectoryInstall() {
			update_option( 'siteurl', 'http://example.org/wordpress', true );
			$context = Timber::context();
			$theme = $context['site']->theme;
			$output = Timber::compile_string('{{site.theme.link}}', $context);
			$this->assertEquals('http://example.org/wp-content/themes/'.$theme->slug, $output);
		}

		function testThemeGet() {
			switch_theme('twentyseventeen');
			$context = Timber::context();
			$output = Timber::compile_string('{{site.theme.get("Name")}}', $context);
			$this->assertEquals('Twenty Seventeen', $output);
			switch_theme('default');
		}

		function testThemeDisplay() {
			switch_theme('twentyseventeen');
			$context = Timber::context();
			$output = Timber::compile_string('{{site.theme.display("Description")}}', $context);
			$this->assertEquals("Twenty Seventeen brings your site to life with header video and immersive featured images. With a focus on business sites, it features multiple sections on the front page as well as widgets, navigation and social menus, a logo, and more. Personalize its asymmetrical grid with a custom color scheme and showcase your multimedia content with post formats. Our default theme for 2017 works great in many languages, for any abilities, and on any device.", $output);
			switch_theme('default');
		}

		function setUp() {
			global $wp_theme_directories;

			parent::setUp();

			$this->backup_wp_theme_directories = $wp_theme_directories;
			$wp_theme_directories = array( WP_CONTENT_DIR . '/themes' );

			wp_clean_themes_cache();
			unset( $GLOBALS['wp_themes'] );

		}

		function tearDown() {
			global $wp_theme_directories;

			$wp_theme_directories = $this->backup_wp_theme_directories;

			wp_clean_themes_cache();
			unset( $GLOBALS['wp_themes'] );
			parent::tearDown();
		}
	}
