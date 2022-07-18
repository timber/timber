<?php

require_once __DIR__ . '/../vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

$_tests_dir = getenv('WP_TESTS_DIR');

if (!$_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
$_phpunit_polyfills_path = getenv('WP_TESTS_PHPUNIT_POLYFILLS_PATH');
if (false !== $_phpunit_polyfills_path) {
    define('WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path);
}

if (!file_exists("{$_tests_dir}/includes/functions.php")) {
    echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    exit(1);
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin()
{
    require dirname(__FILE__) . '/../vendor/autoload.php';
    \Timber\Timber::init();

    require dirname(__FILE__) . '/../wp-content/plugins/advanced-custom-fields/acf.php';
    if (file_exists(dirname(__FILE__) . '/../wp-content/plugins/co-authors-plus/co-authors-plus.php')) {
        include dirname(__FILE__) . '/../wp-content/plugins/co-authors-plus/co-authors-plus.php';
    }
}

tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";

require_once __DIR__ . '/Timber_UnitTestCase.php';
require_once __DIR__ . '/TimberAttachment_UnitTestCase.php';
require_once __DIR__ . '/timber-mock-classes.php';

if (!function_exists('is_post_type_viewable')) {
    function is_post_type_viewable($post_type_object)
    {
        return $post_type_object->publicly_queryable || ($post_type_object->_builtin && $post_type_object->public);
    }
}

/**
 * This constant is always defined by WPML.
 */
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

// Make sure translations are installed.
Timber_UnitTestCase::install_translation('de_DE');

/**
 * Bootstrap the CLI dependencies.
 *
 * This is important to test the CLI classes.
 */
if (!defined('WP_CLI_ROOT')) {
    define('WP_CLI_ROOT', "phar://{$_tests_dir}/wp-cli.phar/vendor/wp-cli/wp-cli");
}

require_once WP_CLI_ROOT . '/php/utils.php';
require_once WP_CLI_ROOT . '/php/dispatcher.php';
require_once WP_CLI_ROOT . '/php/class-wp-cli.php';
require_once WP_CLI_ROOT . '/php/class-wp-cli-command.php';

\WP_CLI\Utils\load_dependencies();

require_once __DIR__ . '/WpCliLogger.php';

\WP_CLI::set_logger(new WpCliLogger(false));
