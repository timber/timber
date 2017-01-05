<?php

	class TestTimberTheme extends Timber_UnitTestCase {

		protected $theme_slug = 'twentyeleven';
		protected $theme_name = 'Twenty Eleven';
		protected $default_themes = array(
				'twentyten', 'twentyeleven', 'twentytwelve', 'twentythirteen',
				'twentyfourteen', 'twentyfifteen', 'twentysixteen', 'twentyseventeen',
		);
		
		function testThemeMods(){
			set_theme_mod('foo', 'bar');
			$theme = new TimberTheme();
			$mods = $theme->theme_mods();
			$this->assertEquals('bar', $mods['foo']);
			$bar = $theme->theme_mod('foo');
			$this->assertEquals('bar', $bar);
		}

		function testPath() {
			$context = Timber::get_context();
			$theme = $context['site']->theme;
			$output = Timber::compile_string('{{site.theme.path}}', $context);
			$this->assertEquals('/wp-content/themes/'.$theme->slug, $output);
		}

		function testPathOnSubdirectoryInstall() {
			update_option( 'siteurl', 'http://example.org/wordpress', true );
			$context = Timber::get_context();
			$theme = $context['site']->theme;
			$output = Timber::compile_string('{{site.theme.path}}', $context);
			$this->assertEquals('/wp-content/themes/'.$theme->slug, $output);
		}

		function testLink() {
			$context = Timber::get_context();
			$theme = $context['site']->theme;
			$output = Timber::compile_string('{{site.theme.link}}', $context);
			$this->assertEquals('http://example.org/wp-content/themes/'.$theme->slug, $output);
		}

		function testLinkOnSubdirectoryInstall() {
			update_option( 'siteurl', 'http://example.org/wordpress', true );
			$context = Timber::get_context();
			$theme = $context['site']->theme;
			$output = Timber::compile_string('{{site.theme.link}}', $context);
			$this->assertEquals('http://example.org/wp-content/themes/'.$theme->slug, $output);
		}

		function setUp() {
			global $wp_theme_directories;

			parent::setUp();

			$backup_wp_theme_directories = $wp_theme_directories;
			$wp_theme_directories = array( WP_CONTENT_DIR . '/themes' );

			wp_clean_themes_cache();
			unset( $GLOBALS['wp_themes'] );

		}

		function tearDown() {
			global $wp_theme_directories;

			$wp_theme_directories = $this->wp_theme_directories;

			wp_clean_themes_cache();
			unset( $GLOBALS['wp_themes'] );
			update_option( 'siteurl', 'http://example.org', true );
			parent::tearDown();
		}
	}
