<?php

	class Timber_UnitTestCase extends WP_UnitTestCase {
		/**
		 * Maintain a list of hook removals to perform at the end of each test.
		 */
		private $temporary_hook_removals = [];

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

			// remove any hooks added during this test run
			foreach ($this->temporary_hook_removals as $callback) {
				$callback();
			}
			// reset hooks
			$this->temporary_hook_removals = [];
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

		function truncate(string $table) {
			global $wpdb;
			$wpdb->query("TRUNCATE TABLE {$wpdb->$table}");
		}

		/**
		 * Exactly the same as add_filter, but automatically calls remove_filter with the same
		 * arguments during tearDown().
		 */
		protected function add_filter_temporarily(string $filter, callable $callback, int $pri = 10, int $count = 1) {
			add_filter($filter, $callback, $pri, $count);
			$this->temporary_hook_removals[] = function() use ($filter, $callback, $pri, $count) {
				remove_filter($filter, $callback, $pri, $count);
			};
		}

	}
