<?php

/**
 * Fixture function that calls Timber::compile() from this directory, so that
 * LocationManager::get_calling_script_dir() resolves to caller-b/.
 */

use Timber\Timber;

/**
 * @return string|false
 */
function timber_test_shared_env_compile_b()
{
    return Timber::compile('only-b.twig');
}
