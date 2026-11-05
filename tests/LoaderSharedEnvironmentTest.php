<?php

namespace Timber\Tests;

use Timber\Loader;
use Timber\Timber;
use Twig\Environment;

require_once __DIR__ . '/Fixtures/shared-environment/caller-a/functions.php';
require_once __DIR__ . '/Fixtures/shared-environment/caller-b/functions.php';

/**
 * Covers the `timber/twig/environment/shared` filter.
 */
class LoaderSharedEnvironmentTest extends TimberIntegrationTestCase
{
    private int $builds = 0;

    public function set_up()
    {
        parent::set_up();

        $this->builds = 0;
        \add_filter('timber/twig', [$this, 'count_build']);
    }

    public function tear_down()
    {
        \remove_filter('timber/twig/environment/shared', '__return_true');
        \remove_filter('timber/twig', [$this, 'count_build']);

        // Empties the shared slot. Builds one throwaway environment with the filter off.
        (new Loader())->clear_cache_twig();

        parent::tear_down();
    }

    public function count_build(Environment $twig): Environment
    {
        $this->builds++;

        return $twig;
    }

    private function enable_shared_environment(): void
    {
        \add_filter('timber/twig/environment/shared', '__return_true');
    }

    public function testBuildsAnEnvironmentPerRenderByDefault()
    {
        Timber::compile('shared-environment/plain.twig');
        Timber::compile('shared-environment/plain.twig');
        Timber::compile('shared-environment/plain.twig');

        $this->assertSame(3, $this->builds);
    }

    public function testReusesTheEnvironmentWhenShared()
    {
        $this->enable_shared_environment();

        $this->assertSame('plain', Timber::compile('shared-environment/plain.twig'));
        $this->assertSame('plain', Timber::compile('shared-environment/plain.twig'));
        $this->assertSame('plain', Timber::compile('shared-environment/plain.twig'));

        $this->assertSame(1, $this->builds);
    }

    public function testClearCacheTwigDiscardsTheSharedEnvironment()
    {
        $this->enable_shared_environment();

        Timber::compile('shared-environment/plain.twig');
        // clear_cache_twig() reads the cache location through get_twig(), which returns the
        // shared instance (no build), then empties the slot.
        (new Loader())->clear_cache_twig();
        Timber::compile('shared-environment/plain.twig');

        $this->assertSame(2, $this->builds);
    }

    public function testCompileStringReusesTheSharedEnvironment()
    {
        $this->enable_shared_environment();

        Timber::compile('shared-environment/plain.twig');

        $this->assertSame('2', Timber::compile_string('{{ 1 + 1 }}'));
        $this->assertSame(1, $this->builds);
    }

    public function testKeepsCallerRelativeLookupWhenShared()
    {
        $this->enable_shared_environment();

        $this->assertSame('caller-a', \timber_test_shared_env_compile_a());
        $this->assertSame('caller-b', \timber_test_shared_env_compile_b());
        $this->assertSame(1, $this->builds);
    }

    public function testRestoresTheOuterLoaderAfterANestedCompile()
    {
        $this->enable_shared_environment();

        // outer.twig (caller-a) triggers a compile from caller-b through function(), then
        // includes partial-a.twig, which only resolves with caller-a's loader.
        $this->assertSame('outer:caller-b:partial-a', \timber_test_shared_env_compile_outer());
        $this->assertSame(1, $this->builds);
    }

    public function testHonorsLoaderMutationsFromHooksByDefault()
    {
        $add_namespace = function (Environment $twig): Environment {
            $twig->getLoader()->addPath(__DIR__ . '/Fixtures/shared-environment/caller-b', 'shared_b');

            return $twig;
        };
        $this->add_filter_temporarily('timber/loader/twig', $add_namespace);

        $output = Timber::compile('shared-environment/includes-namespaced.twig');

        $this->assertSame('caller-b', $output);
    }
}
