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

/**
 * Compiles a shared template whose `{% extends 'base.twig' %}` resolves to caller-b/base.twig.
 *
 * @return string|false
 */
function timber_test_shared_env_compile_child_b()
{
    return Timber::compile('shared-environment/child.twig');
}

/**
 * Compiles a shared template whose `{% use 'traits.twig' %}` resolves to caller-b/traits.twig.
 *
 * @return string|false
 */
function timber_test_shared_env_compile_uses_b()
{
    return Timber::compile('shared-environment/uses.twig');
}
