<?php

use Yoast\WPTestUtils\WPIntegration;

require_once dirname(__DIR__) . '/vendor/yoast/wp-test-utils/src/WPIntegration/bootstrap-functions.php';

$_tests_dir = Yoast\WPTestUtils\WPIntegration\get_path_to_wp_test_dir();

if (!is_file("{$_tests_dir}/includes/functions.php")) {
    echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    exit(1);
}

// Get access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Callback to manually load the plugin
 */
function _manually_load_plugin()
{
	global $timber;
	$timber = new \Timber\Timber();

    require dirname(__FILE__) . '/../wp-content/plugins/advanced-custom-fields/acf.php';
    if (file_exists(dirname(__FILE__) . '/../wp-content/plugins/co-authors-plus/co-authors-plus.php')) {
        include dirname(__FILE__) . '/../wp-content/plugins/co-authors-plus/co-authors-plus.php';
    }
}

// Add plugin to active mu-plugins to make sure it gets loaded.
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// WPML integration
define('ICL_LANGUAGE_CODE', 'en');

/**
 * Mocked function for testing menus in WPML
 */
function wpml_object_id_filter($element_id, $element_type = 'post', $return_original_if_missing = false, $language_code = null)
{
    $locations = get_nav_menu_locations();
    if (isset($locations['extra-menu'])) {
        return $locations['extra-menu'];
    }
    return $element_id;
}

/*
 * Bootstrap WordPress. This will also load the Composer autoload file, the PHPUnit Polyfills
 * and the custom autoloader for the TestCase and the mock object classes.
 */
WPIntegration\bootstrap_it();
