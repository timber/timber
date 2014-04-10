<?php

class TimberAdmin {

	function __construct() {
		if (!is_admin()) {
			return;
		}
		add_filter( 'plugin_action_links', array(&$this, 'settings_link'), 10, 2 );
	}

    /**
     * @param array $links
     * @param string $file
     * @return array
     */
    function settings_link( $links, $file ) {
		if (strstr($file, '/timber.php')){
		    return array_merge(
		        array(
		            'settings' => '<a href="https://github.com/jarednova/timber/wiki" target="_blank">Documentation</a> | <a href="https://github.com/jarednova/timber/wiki/getting-started" target="_blank">Starter Guide</a>'
		        ),
		        $links
		    );
		}
		return $links;
	}

}

global $timber_admin;
$timber_admin = new TimberAdmin();