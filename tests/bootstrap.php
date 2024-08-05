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

function tt_get_arg(string $key)
{
    foreach ($_SERVER['argv'] as $index => $arg) {
        if ($key === substr($arg, 0, strlen($key))) {
            return [
                'index' => $index,
                $key => str_replace("{$key}=", '', $arg),
            ];
        }
    }
    return false;
}

function tt_is_group(string $group_name)
{
    $group = tt_get_arg('--group');
    if (false === $group) {
        return false;
    }

    $group_name_index = ++$group['index'];

    if (!isset($_SERVER['argv'][$group_name_index])) {
        return false;
    }

    return ($group_name === $_SERVER['argv'][$group_name_index]);
}

// Add plugin to active mu-plugins to make sure it gets loaded.
tests_add_filter('muplugins_loaded', function () {
    // Load Timber
    Timber\Timber::init();

    if (tt_is_group('acf')) {
        require __DIR__ . '/../wp-content/plugins/advanced-custom-fields/acf.php';
    }

    if (tt_is_group('coauthors-plus')) {
        require __DIR__ . '/../wp-content/plugins/co-authors-plus/co-authors-plus.php';
    }

    if (tt_is_group('wpml')) {
        // WPML integration
        define('ICL_LANGUAGE_CODE', 'en');
    }
});

if (tt_is_group('wpml')) {
    /**
     * Mocked function for testing menus in WPML
     */
    function wpml_object_id_filter($element_id, $element_type = 'post', $return_original_if_missing = false, $language_code = null)
    {
        return $element_id;
    }
}

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

WP_CLI::set_logger(new WpCliLogger(false));

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

/*
 * Bootstrap WordPress. This will also load the Composer autoload file, the PHPUnit Polyfills
 * and the custom autoloader for the TestCase and the mock object classes.
 */
WPIntegration\bootstrap_it();
