<?php

	class TestTimberLoader extends WP_UnitTestCase {

		function testTwigLoadsFromChildTheme(){
			$this->_setupParentTheme();
			$this->_setupChildTheme();
			$this->assertFileExists(WP_CONTENT_DIR.'/themes/fake-child-theme/style.css');
			switch_theme('fake-child-theme');
			$child_theme = get_stylesheet_directory_uri();
			$this->assertEquals('http://example.org/wp-content/themes/fake-child-theme', $child_theme);
			$context = array();
			$str = Timber::compile('single.twig', $context);
			$this->assertEquals('I am single.twig', trim($str));
		}

		function _setupChildTheme(){
			$dest_dir = WP_CONTENT_DIR.'/themes/fake-child-theme';
			if (!file_exists($dest_dir)) {
    			mkdir($dest_dir, 0777, true);
			}
			if (!file_exists($dest_dir.'/views')) {
    			mkdir($dest_dir.'/views', 0777, true);
			}
			copy(__DIR__.'/assets/style.css', $dest_dir.'/style.css');
			copy(__DIR__.'/assets/single.twig', $dest_dir.'/views/single.twig');
		}

		function testTwigLoadsFromParentTheme(){
			$this->_setupParentTheme();
			$this->_setupChildTheme();
			$templates = array('single-parent.twig', 'single.twig');
			$str = Timber::compile($templates, array());
			$this->assertEquals('I am single.twig in twentyfourteen', trim($str));
		}

		function _setupParentTheme(){
			$dest_dir = WP_CONTENT_DIR.'/themes/twentyfourteen';
			if (!file_exists($dest_dir.'/views')) {
    			mkdir($dest_dir.'/views', 0777, true);
			}
			copy(__DIR__.'/assets/single-parent.twig', $dest_dir.'/views/single.twig');
			copy(__DIR__.'/assets/single-parent.twig', $dest_dir.'/views/single-post.twig');
		}

		function testTwigLoadsFromRelativeToScript(){

		}

		function testTwigLoadsFromAbsolutePathOnServer(){

		}

		function testTwigLoadsFromAlternateDirName(){

		}

		function testTwigLoadsFromLocation(){

		}


	}
