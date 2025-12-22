<?php

use function Mantle\Testing\manager;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$rootDir = realpath(__DIR__ . '/..');

// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
putenv("WP_CORE_DIR=$rootDir/tmp/wordpress");
putenv("CACHEDIR=$rootDir/tmp/test-cache");

// Pre-download SQLite from develop branch for PHP 8.5 compatibility.
// Mantle expects sqlite-database-integration-main.zip with -main folder inside.
cache_sqlite_develop(getenv('CACHEDIR'));

function cache_sqlite_develop(string $cacheDir): void
{
    $sqliteZip = $cacheDir . '/sqlite-database-integration-main.zip';

    if (is_file($sqliteZip)) {
        return;
    }

    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    // Download develop branch, extract, rename folder, re-zip with correct name
    $url = 'https://github.com/WordPress/sqlite-database-integration/archive/refs/heads/develop.zip';
    shell_exec("curl -sL " . escapeshellarg($url) . " -o " . escapeshellarg("$cacheDir/tmp.zip"));
    shell_exec("unzip -q " . escapeshellarg("$cacheDir/tmp.zip") . " -d " . escapeshellarg($cacheDir));
    rename("$cacheDir/sqlite-database-integration-develop", "$cacheDir/sqlite-database-integration-main");
    shell_exec("cd " . escapeshellarg($cacheDir) . " && zip -rq sqlite-database-integration-main.zip sqlite-database-integration-main");
    shell_exec("rm -rf " . escapeshellarg("$cacheDir/tmp.zip") . " " . escapeshellarg("$cacheDir/sqlite-database-integration-main"));
}

/**
 * Parse TIMBER_TEST_PLUGINS environment variable.
 *
 * @return array List of plugins to activate (e.g., ['acf', 'coauthors-plus', 'wpml'])
 */
function timber_get_test_plugins(): array
{
    $plugins = getenv('TIMBER_TEST_PLUGINS');
    if (empty($plugins)) {
        return [];
    }
    return array_filter(array_map(trim(...), explode(',', $plugins)));
}

/**
 * Check if a specific plugin should be activated for this test run.
 */
function timber_test_has_plugin(string $plugin): bool
{
    return in_array($plugin, timber_get_test_plugins(), true);
}

/**
 * Map plugin shortnames to their main file path (relative to wp-content/plugins/).
 */
function timber_get_plugin_map(): array
{
    return [
        'acf' => 'advanced-custom-fields/acf.php',
        'coauthors-plus' => 'co-authors-plus/co-authors-plus.php',
    ];
}

/**
 * Get full path to a test plugin.
 */
function timber_get_plugin_path(string $plugin): ?string
{
    $map = timber_get_plugin_map();
    if (!isset($map[$plugin])) {
        return null;
    }

    return dirname(__DIR__) . '/wp-content/plugins/' . $map[$plugin];
}

// WPML mock function - must be defined before WordPress loads
if (timber_test_has_plugin('wpml')) {
    /**
     * Mocked function for testing menus in WPML
     */
    function wpml_object_id_filter($element_id, $element_type = 'post', $return_original_if_missing = false, $language_code = null)
    {
        return $element_id;
    }
}

// Load test helper classes (global namespace)
require_once __DIR__ . '/Support/timber-mock-classes.php';
foreach (glob(__DIR__ . '/Support/*.php') as $file) {
    // Skip CustomGuestAuthor - loaded later after Timber is initialized
    if (basename($file) === 'CustomGuestAuthor.php') {
        continue;
    }
    require_once $file;
}
require_once __DIR__ . '/Fixtures/assets/Sport.php';

$manager = manager();

// Enable multisite based on WP_MULTISITE env var
$isMultisite = filter_var(getenv('WP_MULTISITE'), FILTER_VALIDATE_BOOLEAN);
$manager->with_multisite($isMultisite);

$manager
    ->with_sqlite()
    ->after(function () {
        // Copy test themes to WP install (after WordPress is installed, before it loads)
        $themes_src = __DIR__ . '/Fixtures/themes';
        $themes_dest = getenv('WP_CORE_DIR') . '/wp-content/themes';

        // Ensure themes directory exists (may not be created by Mantle)
        if (!is_dir($themes_dest)) {
            mkdir($themes_dest, 0755, true);
        }

        foreach (glob($themes_src . '/*', GLOB_ONLYDIR) as $theme_dir) {
            $theme_name = basename($theme_dir);
            $dest_dir = $themes_dest . '/' . $theme_name;
            if (!is_dir($dest_dir)) {
                shell_exec("cp -R " . escapeshellarg($theme_dir) . " " . escapeshellarg($dest_dir));
            }
        }

    })
    ->loaded(function () {
        // Load Timber
        Timber\Timber::init();

        // Load plugins based on TIMBER_TEST_PLUGINS environment variable
        if (timber_test_has_plugin('acf')) {
            $path = timber_get_plugin_path('acf');
            if ($path && file_exists($path)) {
                require_once $path;
            }
        }

        if (timber_test_has_plugin('coauthors-plus')) {
            $path = timber_get_plugin_path('coauthors-plus');
            if ($path && file_exists($path)) {
                require_once $path;
            }
            // Load CustomGuestAuthor after Timber and CoAuthors are loaded
            require_once __DIR__ . '/Support/CustomGuestAuthor.php';
        }

        // WPML mock - define the language constant
        if (timber_test_has_plugin('wpml')) {
            if (!defined('ICL_LANGUAGE_CODE')) {
                define('ICL_LANGUAGE_CODE', 'en');
            }
        }
    })
    ->install();
