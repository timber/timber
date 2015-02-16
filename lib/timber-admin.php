<?php

class TimberAdmin {

	function __construct() {
		add_filter( 'plugin_row_meta', array( $this, 'meta_links' ), 10, 2 );
	}

	/**
	 *
	 *
	 * @param array   $links
	 * @param string  $file
	 * @return array
	 */
	function meta_links( $links, $file ) {
		if ( strstr( $file, '/timber.php' ) ) {
			unset($links[2]);
			$links[] = '<a href="/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=timber-library&amp;TB_iframe=true&amp;width=600&amp;height=550" class="thickbox" aria-label="More information about Timber" data-title="Timber">View details</a>';
			$links[] = '<a href="http://upstatement.com/timber" target="_blank">Homepage</a>';
			$links[] = '<a href="https://github.com/jarednova/timber/wiki" target="_blank">Documentation</a>';
			$links[] = '<a href="https://github.com/jarednova/timber/wiki/getting-started" target="_blank">Starter Guide</a>';
			return $links;
		}
		return $links;
	}

}

global $timber_admin;
$timber_admin = new TimberAdmin();
