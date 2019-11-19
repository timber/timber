<?php

namespace Timber;

class Admin {

	public static function init() {
		$filter = add_filter('plugin_row_meta', array(__CLASS__, 'meta_links'), 10, 2);
		$action = add_action('in_plugin_update_message-timber-library/timber.php', array(__CLASS__, 'in_plugin_update_message'), 10, 2);
		$action = add_action('in_plugin_update_message-timber/timber.php', array(__CLASS__, 'in_plugin_update_message'), 10, 2);
		if ( $filter && $action ) {
			return true;
		}
	}

	/**
	 * @param array   $links
	 * @param string  $file
	 * @return array
	 */
	public static function meta_links( $links, $file ) {
		if ( strstr($file, '/timber.php') ) {
			unset($links[2]);
			$links[] = '<a href="' . admin_url( '/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=timber-library&amp;TB_iframe=true&amp;width=600&amp;height=550' ) . '" class="thickbox" aria-label="More information about Timber" data-title="Timber">View details</a>';
			$links[] = '<a href="http://upstatement.com/timber" target="_blank">Homepage</a>';
			$links[] = '<a href="https://timber.github.io/docs/" target="_blank">Documentation</a>';
			$links[] = '<a href="https://timber.github.io/docs/getting-started/setup/" target="_blank">Starter Guide</a>';
			return $links;
		}
		return $links;
	}

	/**
	 *	@codeCoverageIgnore
	 */
	protected static function disable_update() {
		$m = '<br>Is your theme in active development? That is, is someone actively in PHP files writing new code? If you answered "no", then <i>DO NOT UPGRADE</i>. ';
		$m .= "We're so serious about it, we've even disabled the update link. If you really really think you should upgrade you can still <a href='https://wordpress.org/plugins/timber-library/'>download from WordPress.org</a>, but that's on you!";
		$m .= '<style>#timber-library-update .update-link {pointer-events: none;
   cursor: default; opacity:0.3;}</style>';
   		return $m;
	}

	/**
	 *	@codeCoverageIgnore
	 */
	protected static function update_message_milestone() {
		$m = '<br><b>Warning:</b> Timber 1.0 removed a number of features and methods. Before upgrading please test your theme on a local or staging site to ensure that your theme will work with the newest version.<br>

			<br><strong>Is your theme in active development?</strong> That is, is someone actively in PHP files writing new code? If you answered "no", then <i>do not upgrade</i>. You will not benefit from Timber 1.0<br>';

		$m .= '<br>Read the <strong><a href="https://timber.github.io/docs/upgrade-guides/1.0/">Upgrade Guide</a></strong> for more information<br>';

		$m .= "<br>You can also <b><a href='https://downloads.wordpress.org/plugin/timber-library.0.22.6.zip'>upgrade to version 0.22.6</a></b> if you want to upgrade, but are unsure if you're ready for 1.0<br>";
		$m .= self::disable_update();
		return $m;
	}

	/**
	 *	@codeCoverageIgnore
	 */
	protected static function update_message_major() {
		$m = '<br><b>Warning:</b> This new version of Timber introduces some major new features which might have unknown effects on your site.';


		$m .= self::disable_update();
		return $m;
	}

	/**
	 *	@codeCoverageIgnore
	 */
	protected static function update_message_minor() {
		$m = "<br><b>Warning:</b> This new version of Timber introduces some new features which might have unknown effects on your site. We have automated tests to help us catch potential issues, but nothing is 100%. You're likely safe to upgrade, but do so very carefully and only if you have an experienced WordPress developer available to help you debug potential issues.";
		return $m;
	}

	public static function get_upgrade_magnitude( $current_version, $new_version ) {
		$current_version_array = explode('.', (string)$current_version);
		$new_version_array = explode('.', (string)$new_version);
		if ( $new_version_array[0] > $current_version_array[0]) {
			return 'milestone';
		} elseif ( $new_version_array[1] > $current_version_array[1] ) {
			return 'major';
		} elseif ( isset($new_version_array[2]) && isset($current_version_array[2]) && $new_version_array[2] > $current_version_array[2] ) {
			return 'minor';
		}
		return 'unknown';
	}

	/**
	 *  Displays an update message for plugin list screens.
	 *  Shows only the version updates from the current until the newest version
	 *
	 *	@codeCoverageIgnore
	 *
	 *  @type	function
	 *  @date	4/22/16
	 *
	 *  @param	{array}		$plugin_data
	 *  @param	{object}	$r
	 */
	public static function in_plugin_update_message( $plugin_data, $r ) {
		$current_version = $plugin_data['Version'];
		$new_version = $plugin_data['new_version'];
		$upgrade_magnitude = self::get_upgrade_magnitude($current_version, $new_version);
		if ( $upgrade_magnitude == 'milestone' ) {
			$message = self::update_message_milestone();
			echo '<br />'.sprintf($message);
			return;
		} elseif ( $upgrade_magnitude == 'major' ) {
			//major version
			$message = self::update_message_major();
			echo '<br />'.sprintf($message);
			return;
		}
		$message = self::update_message_minor();
		echo '<br />'.($message);
		return;

	}

}
