<?php

	class TimberSiteTest extends WP_UnitTestCase {

		function testStandardThemeLocation(){
			$site = new TimberSite();
			$this->assertEquals(WP_CONTENT_SUBDIR.'/themes/twentyfourteen', $site->theme->path);
		}

		function testChildParentThemeLocation(){
			TestTimberLoader::_setupChildTheme();
			$this->assertFileExists(WP_CONTENT_DIR.'/themes/fake-child-theme/style.css');
			switch_theme('fake-child-theme');
			$site = new TimberSite();
			$this->assertEquals(WP_CONTENT_SUBDIR.'/themes/fake-child-theme', $site->theme->path);
			$this->assertEquals(WP_CONTENT_SUBDIR.'/themes/twentythirteen', $site->theme->parent->path);
		}

		function testLegacyThemeDir(){
			$context = Timber::get_context();
			switch_theme('twentyfourteen');
			$this->assertEquals(WP_CONTENT_SUBDIR.'/themes/twentyfourteen', $context['theme_dir']);
		}

		function testThemeFromContext(){
			$context = Timber::get_context();
			switch_theme('twentyfourteen');
			$this->assertEquals('twentyfourteen', $context['theme']->slug);
		}

		function testThemeFromSiteContext(){
			$context = Timber::get_context();
			switch_theme('twentyfourteen');
			$this->assertEquals('twentyfourteen', $context['site']->theme->slug);
		}


	}
