<?php

	class Timber_UnitTestCase extends WP_UnitTestCase {

		/**
		 * Overload WP_UnitTestcase to ignore deprecated notices
		 * thrown by use of wp_title() in Timber
		 */
		public function expectedDeprecated() {
		    if ( false !== ( $key = array_search( 'wp_title', $this->caught_deprecated ) ) ) {
		        unset( $this->caught_deprecated[ $key ] );
		    }
		    parent::expectedDeprecated();
		}

		public static function enable_error_log( $opt = true ) {
			global $timber_disable_error_log;
			$timber_disable_error_log = !$opt;
		}

		public static function setPermalinkStructure( $struc = '/%postname%/' ) {
			global $wp_rewrite;
			$wp_rewrite->set_permalink_structure( $struc );
			$wp_rewrite->flush_rules();
			update_option( 'permalink_structure', $struc );
			flush_rewrite_rules( true );
		}

		function tearDown() {
			self::resetPermalinks();
			parent::tearDown();
			Timber::$context_cache = array();
		}

		function resetPermalinks() {
			delete_option( 'permalink_structure' );
			global $wp_rewrite;
			$wp_rewrite->set_permalink_structure( false );
			$wp_rewrite->init();
			$wp_rewrite->flush_rules();
			flush_rewrite_rules( true );
		}

		function setupCustomWPDirectoryStructure() {
			add_filter('content_url', [$this, 'setContentUrl']);
			add_filter('option_upload_path', [$this, 'setUploadPath']);
			add_filter('option_upload_url_path', [$this, 'setUploadUrlPath']);
			add_filter('option_siteurl', [$this, 'setSiteUrl']);
			add_filter('option_home_url', [$this, 'setHomeUrl']);
		}

		function tearDownCustomWPDirectoryStructure() {
			remove_filter('content_url', [$this, 'setContentUrl']);
			remove_filter('option_upload_path', [$this, 'setUploadPath']);
			remove_filter('option_upload_url_path', [$this, 'setUploadUrlPath']);
			remove_filter('option_siteurl', [$this, 'setSiteUrl']);
			remove_filter('option_home_url', [$this, 'setHomeUrl']);
		}

		function setContentUrl($url) {
			return 'http://' . $_SERVER['HTTP_HOST'] . '/content';
		}

		function setUploadPath($dir) {
			return $_SERVER['DOCUMENT_ROOT'] .'content/uploads';
		}

		function setUploadUrlPath($dir) {
			return 'http://' . $_SERVER['HTTP_HOST'] . '/content/uploads';
		}

		function setSiteUrl($url) {
			return 'http://' . $_SERVER['HTTP_HOST'] . '/wp';
		}

		function clearPosts() {
			global $wpdb;
			$wpdb->query("TRUNCATE TABLE $wpdb->posts;");
		}

		/**
		 * Changes to a different locale.
		 *
		 * The translations for the locale you might want to use maybe don’t exist yet. You will
		 * have to download it first through wp_download_language_pack(). Check the bootstrap.php
		 * file to see how it works.
		 *
		 * After you used this function in a test, don’t forget to restore the current locale using
		 * $this->restore_locale().
		 *
		 * @see \Timber_UnitTestCase::restore_locale()
		 *
		 * @param string $locale
		 */
		function change_locale( $locale = 'de_DE' ) {
			switch_to_locale( $locale );
		}

		/**
		 * Restores the locale after it was changed by $this->change_locale().
		 *
		 * @see \Timber_UnitTestCase::change_locale()
		 */
		function restore_locale() {
			restore_current_locale();
		}
	}
