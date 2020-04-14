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
		 * Installs a translation.
		 *
		 * Deletes already installed translation first.
		 *
		 * @param string $locale The locale to install.
		 */
		public static function install_translation( $locale ) {
			require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );

			self::uninstall_translation( $locale );
			wp_download_language_pack( $locale );
		}

		/**
		 * Deletes translation files for a locale.
		 *
		 * Logic borrowed from WP CLI Language Command
		 *
		 * @link https://github.com/wp-cli/language-command/blob/master/src/Core_Language_Command.php
		 *
		 * @param string $locale The locale to delete.
		 *
		 * @return bool Whether the locale was deleted.
		 */
		public static function uninstall_translation( $locale ) {
			global $wp_filesystem;

			$available    = wp_get_installed_translations( 'core' );
			$translations = array_keys( $available['default'] );

			if ( ! in_array( $locale, $translations, true ) ) {
				return false;
			}

			$files = scandir( WP_LANG_DIR );

			if ( ! $files ) {
				return false;
			}

			$current_locale = get_locale();
			if ( $locale === $current_locale ) {
				// Language is active.
				return true;
			}

			// As of WP 4.0, no API for deleting a language pack
			WP_Filesystem();
			$deleted = false;
			foreach ( $files as $file ) {

				if ( '.' === $file[0] || is_dir( $file ) ) {
					continue;
				}

				$extension_length = strlen( $locale ) + 4;
				$ending           = substr( $file, -$extension_length );
				$starting = substr( $file, 0, strlen( $locale ) );

				if ( ! in_array( $file, [ $locale . '.po', $locale . '.mo' ], true )
					&& ! in_array( $ending, [ '-' . $locale . '.po', '-' . $locale . '.mo' ], true )
					&& $locale !== $starting
				) {
					continue;
				}

				/** @var WP_Filesystem_Base $wp_filesystem */
				$deleted = $wp_filesystem->delete( trailingslashit( WP_LANG_DIR ) . $file );
			}

			if ( $deleted ) {
				return true;
			}

			return false;
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
		 * @param string $locale The locale to switch to.
		 */
		function change_locale( $locale ) {
			// Check if the translation is already installed.
			if ( ! in_array( $locale, get_available_languages() ) ) {
				self::install_translation( $locale );
			}

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
