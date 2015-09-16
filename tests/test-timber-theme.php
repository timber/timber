<?php

	class TestTimberTheme extends WP_UnitTestCase {

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
			$output = Timber::compile_string('{{site.theme.path}}', $context);
			$this->assertEquals('/wp-content/themes/twentyfifteen', $output);
		}

		function testPathOnSubdirectoryInstall() {
			update_option( 'siteurl', 'http://example.org/wordpress', true );
			$context = Timber::get_context();
			$output = Timber::compile_string('{{site.theme.path}}', $context);
			$this->assertEquals('/wp-content/themes/twentyfifteen', $output);
		}

		function testLink() {
			$context = Timber::get_context();
			$output = Timber::compile_string('{{site.theme.link}}', $context);
			$this->assertEquals('http://example.org/wp-content/themes/twentyfifteen', $output);
		}

		function testLinkOnSubdirectoryInstall() {
			update_option( 'siteurl', 'http://example.org/wordpress', true );
			$context = Timber::get_context();
			$output = Timber::compile_string('{{site.theme.link}}', $context);
			$this->assertEquals('http://example.org/wp-content/themes/twentyfifteen', $output);
		}

		function tearDown() {
			update_option( 'siteurl', 'http://example.org', true );
		}


	}
