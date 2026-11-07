<?php

/**
 * Fixture functions that call Timber::compile() from this directory, so that
 * LocationManager::get_calling_script_dir() resolves to caller-a/.
 */

use Timber\Timber;

/**
 * @return string|false
 */
function timber_test_shared_env_compile_a()
{
    return Timber::compile('only-a.twig');
}

/**
 * Renders a template that triggers a nested compile from caller-b/ and then includes a
 * template that only exists in caller-a/.
 *
 * @return string|false
 */
function timber_test_shared_env_compile_outer()
{
    return Timber::compile('outer.twig');
}

/**
 * Compiles a shared template whose `{% extends 'base.twig' %}` resolves to caller-a/base.twig.
 *
 * @return string|false
 */
function timber_test_shared_env_compile_child_a()
{
    return Timber::compile('shared-environment/child.twig');
}

/**
 * Compiles a shared template whose `{% use 'traits.twig' %}` resolves to caller-a/traits.twig.
 *
 * @return string|false
 */
function timber_test_shared_env_compile_uses_a()
{
    return Timber::compile('shared-environment/uses.twig');
}
