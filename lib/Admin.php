<?php

namespace Timber;

class Admin {
	
	public static function init() {
		$filter = add_filter('plugin_row_meta', array( __CLASS__, 'meta_links' ), 10, 2);
		$action = add_action('in_plugin_update_message-timber-library/timber.php', array( __CLASS__, 'in_plugin_update_message'), 10, 2);
		$action = add_action('in_plugin_update_message-timber/timber.php', array( __CLASS__, 'in_plugin_update_message'), 10, 2);
		if ($filter && $action) {
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
			$links[] = '<a href="/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=timber-library&amp;TB_iframe=true&amp;width=600&amp;height=550" class="thickbox" aria-label="More information about Timber" data-title="Timber">View details</a>';
			$links[] = '<a href="http://upstatement.com/timber" target="_blank">Homepage</a>';
			$links[] = '<a href="https://github.com/timber/timber/wiki" target="_blank">Documentation</a>';
			$links[] = '<a href="https://github.com/timber/timber/wiki/getting-started" target="_blank">Starter Guide</a>';
			$links[] = '<p><a href="https://downloads.wordpress.org/plugin/timber-library.0.22.6.zip">Site not working on 1.0? Downgrade to version 0.22.6</a></p>';
			return $links;
		}
		return $links;
	}

	/**
	 *  in_plugin_update_message
	 *
	 *  Displays an update message for plugin list screens.
	 *  Shows only the version updates from the current until the newest version
	 *
	 *  @type	function
	 *  @date	4/22/16
	 *
	 *  @param	{array}		$plugin_data
	 *  @param	{object}	$r
	 */
	public static function in_plugin_update_message( $plugin_data, $r ) {
		$m = '';

		if ( version_compare("1.0.0", $plugin_data->new_version) <= 0 ) {
			//a version of 1.0.0 or greater is availalbe
			$m .= '<p><b>Warning:</b> Timber 1.0 removed a number of features and methods. Before upgrading please test your theme on a local or staging site to ensure that your theme will work with the newest version.</p> 

			<p><strong>Is your theme in active development?</strong> That is, is someone actively in PHP files writing new code? If you answered "no", then <i>do not upgrade</i>. You will not benefit from Timber 1.0</p>';

			$m .= '<p>Read the <strong><a href="https://github.com/timber/timber/wiki/1.0-Upgrade-Guide">Upgrade Guide</a></strong> for more information</p>';

			$m .= "<p>You can also <b><a href='https://downloads.wordpress.org/plugin/timber-library.0.22.6.zip'>upgrade to version 0.22.6</a></b> if you want to upgrade, but are unsure if you're ready for 1.0";

		}

		if ( version_compare("1.0.0", $plugin_data->Version) <= 0 ) {
			$m .= "<p>Are you seeing errors since upgrading to 1.0? Download <b><a href='https://downloads.wordpress.org/plugin/timber-library.0.22.6.zip'>Version 0.22.6</a></b> to bring things back to stability.";
		}
		
		// show message
		echo '<br />'.sprintf($m);
	
	}

}
